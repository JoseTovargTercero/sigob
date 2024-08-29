<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');


// Inicializar el array de respuesta
$response = array();

// Verificar si se recibió un ID y limpiarlo


try {

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT p.*, n.frecuencia FROM peticiones p JOIN nominas n ON p.nombre_nomina = n.nombre WHERE p.id = $id";
    } else {
        $sql = "SELECT p.*, n.frecuencia FROM peticiones p JOIN nominas n ON p.nombre_nomina = n.nombre";
    }

    $result = $conexion->query($sql);
    if ($result === false) {
        // Si hay un error en la consulta, mostrarlo y salir del script
        throw new Exception("Error en la consulta: $conexion->error");

    } else {
        $datos = array();

        if ($result->num_rows > 0) {
            // Recorrer los registros y almacenarlos en un array
            while ($row = $result->fetch_assoc()) {
                $peticiones[] = $row;
            }
            $response = json_encode(["success" => $peticiones]);
        } else {
            // Si no se encontraron resultados
            $response = json_encode(["error" => "No se encontraron peticiones registradas"]);
        }

    }
} catch (\Exception $e) {
    // En caso de error, revertir la transacción
    $conexion->rollback();
    // Devolver una respuesta de error al cliente
    $response = json_encode(['error' => $e->getMessage()]);
}

echo $response;




?>