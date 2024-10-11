<?php
require_once '../sistema_global/session.php';
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
function consultarPartida($informacion)
{
    global $conexion;

    try {

        if (!isset($informacion["id"])) {
            throw new Exception('No se ha indicado el ID de partida a consultar');
        }

        $id = $informacion["id"];

        $sql = "SELECT * FROM partidas_presupuestarias WHERE id = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("Error en la consulta: $conexion->error");
        }


        if ($result->num_rows > 0) {
            $response = json_encode(["success" => "Partida presupuestaria verificada"]);
        } else {
            $response = json_encode(["error" => "Esta partida no coincide con ningún registro"]);
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

$data = json_decode(file_get_contents("php://input"), true);
function procesarPeticion($data)
{

    if (isset($data["accion"])) {
        $accion = $data["accion"];
        if ($accion === "consultar") {
            if (!isset($data["informacion"]))
                return json_encode(['error' => "Acción no posee informacion"]);

            return consultarPartida($data["informacion"]);
        }

        return json_encode(['error' => "Acción no aceptada"]);
    } else {

        return obtenerPartidas();
    }
}

$response = procesarPeticion($data);

echo $response;
