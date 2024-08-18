<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

$id_grupo = $_POST["id_grupo"];
$frecuencia_pago = $_POST["frecuencia_pago"];

$stmt_o = $conexion->prepare("INSERT INTO frecuencias_por_grupo (id_grupo, tipo) VALUES (?, ?)");
$stmt_o->bind_param("ss", $id_grupo, $frecuencia_pago);

if (!$stmt_o->execute()) {
    echo "error";
        exit();
}
$stmt_o->close();
header("Location:".$_SERVER['HTTP_REFERER']);