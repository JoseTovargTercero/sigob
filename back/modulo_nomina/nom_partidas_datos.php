<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

// Inicializar el array de respuesta

// Verificar si se recibió un ID y limpiarlo

function obtenerPartidas()
{
    global $conexion;

    try {

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            $sql = "SELECT * FROM partidas_presupuestarias WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id);
        } else {
            $sql = "SELECT * FROM partidas_presupuestarias";
            $stmt = $conexion->prepare($sql);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("Error en la consulta: $conexion->error");
        }

        $datos = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }
            $response = json_encode(["success" => $datos]);
        } else {
            $response = json_encode(["error" => "No se encontraron partidas registradas"]);
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        // Registrar el error en un archivo de registro
        // error_log('Error: ' . $e->getMessage(), 3, '/ruta/al/archivo_de_error.log');
        return json_encode(['error' => $e->getMessage()]);
    }
}

$response = obtenerPartidas();

echo $response;

?>