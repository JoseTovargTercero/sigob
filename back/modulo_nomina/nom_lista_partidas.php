<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

header('Content-Type: application/json');



$sql = "SELECT partida, descripcion FROM partidas_presupuestarias";
$stmt = $conexion->prepare($sql);
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
    echo json_encode(["success" => $datos]);
} else {
    echo json_encode(["error" => "No se encontraron partidas registradas"]);
}

$stmt->close();
$conexion->close();
