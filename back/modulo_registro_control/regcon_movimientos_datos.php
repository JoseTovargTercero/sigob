<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Inicializar el array de respuesta
$response = array();

// Verificar si se recibió un ID y limpiarlo


try {

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT m.*, e.nombres, e.cedula FROM movimientos as m LEFT JOIN empleados as e on m.id_empleado = e.id WHERE m.id = $id";
    } else {
        if (isset($_GET["id_nomina"])) {
            $id_nomina = $_GET['id_nomina'];

            $sql = "SELECT m.*, e.nombres, e.cedula FROM movimientos as m LEFT JOIN empleados as e on m.id_empleado = e.id WHERE JSON_CONTAINS(m.id_nomina, $id_nomina)";
        } else {
            throw new Exception("No se ha otorgado una nomina");
        }

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
                $movimiento = array(
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
                    "cedula" => $row["cedula"],
                    "nombres" => $row["nombres"],
                );
                $datos[] = $movimiento;
            }

            $conexion->close();
            $response = json_encode(["success" => $datos]);

            // Cerrar la conexión a la base de datos

        } else {
            // Si no se encontraron resultados
            $response = json_encode(["success" => "No hay movimientos registrados en esta nomina"]);
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