<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

function obtenerPartidasFaltantes($id_ejercicio)
{
    global $conexion;

    try {
        // Obtener todas las partidas presupuestarias
        $sqlPartidas = "SELECT id, partida, descripcion FROM partidas_presupuestarias";
        $stmtPartidas = $conexion->prepare($sqlPartidas);
        $stmtPartidas->execute();
        $resultPartidas = $stmtPartidas->get_result();

        $partidas = [];
        while ($row = $resultPartidas->fetch_assoc()) {
            $partidas[$row['id']] = [
                "partida" => $row['partida'],
                "descripcion" => $row['descripcion']
            ];
        }

        // Obtener todas las partidas que ya existen en distribucion_presupuestaria para el id_ejercicio
        $sqlDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id_ejercicio = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("i", $id_ejercicio);
        $stmtDistribucion->execute();
        $resultDistribucion = $stmtDistribucion->get_result();

        while ($row = $resultDistribucion->fetch_assoc()) {
            if (isset($partidas[$row['id_partida']])) {
                unset($partidas[$row['id_partida']]); // Eliminar las que ya existen en distribución
            }
        }

        return json_encode($partidas, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        return json_encode(["error" => $e->getMessage()]);
    }
}







// Procesar la petición
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];
    $id_ejercicio = $data["id_ejercicio"] ?? '';


    if ($accion === "consulta") {
        $response = obtenerPartidasFaltantes($id_ejercicio);
    } 
} else {
    $response = json_encode(['error' => "No se especificó ninguna acción"]);
}

echo $response;





 ?>