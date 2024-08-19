<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Inicializar el array de respuesta
$response = array();

// Verificar si se recibió un ID y limpiarlo


try {

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM movimientos WHERE id = $id AND status = 0";
    } else {
        $sql = "SELECT * FROM movimientos WHERE status = 0";
    }


    $result = $conexion->query($sql);
    if ($result === false) {
        // Si hay un error en la consulta, mostrarlo y salir del script
        throw new Exception("Error en la consulta: $conexion->error");

    } else {
        $datos = array();

        if ($result->num_rows > 0) {
            // Llenar el array con los datos obtenidos de la consulta
            while ($row = $result->fetch_assoc()) {
                $tabulador = array(
                    "id" => $row["id"],
                    "id_empleado" => $row["id_empleado"],
                    "id_nomina" => $row["id_nomina"],
                    "fecha_movimiento" => $row["fecha_movimiento"],
                    "accion" => $row["accion"],
                    "tabla" => $row["tabla"],
                    "campo" => $row["campo"],
                    "descripcion" => $row["descripcion"],
                    "valor_anterior" => $row["valor_anterior"],
                    "valor_nuevo" => $row["valor_nuevo"],
                    "usuario_id" => $row["usuario_id"],
                    "status" => $row["status"],
                );
                $datos[] = $tabulador;
            }

            $conexion->close();
            $response = json_encode(["success" => $datos]);

            // Cerrar la conexión a la base de datos

        } else {
            // Si no se encontraron resultados
            throw new Exception("No se encontraron resultados.");
        }

    }
} catch (\Exception $e) {
    // En caso de error, revertir la transacción
    $conexion->rollback();
    // Devolver una respuesta de error al cliente
    $response = json_encode(['error' => $e->getMessage()]);
}

header('Content-Type: application/json');
echo $response;




?>