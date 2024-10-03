<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';


$id = $_POST["id"];

$stmt2 = $conexion->prepare("UPDATE `ejercicio_fiscal` SET `status_ejercicio`='0' WHERE ano=?");
$stmt2->bind_param("s", $id);


if ($stmt2->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error']);
}

$stmt2->close();
