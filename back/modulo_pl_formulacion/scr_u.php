<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';














function getSectorId($sector)
{ // para entes y sub entes
    global $conexion;

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_sectores` WHERE sector = ? ");
    $stmt->bind_param('s', $sector);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            return $row['id'];
        }
    }
    $stmt->close();
}

$tabla = 'pl_programas';


$stmt2 = $conexion->prepare("UPDATE `$tabla` SET `sector`= ? WHERE id=?");




$stmt = mysqli_prepare($conexion, "SELECT * FROM `$tabla` ");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $sector = $row['sector'];
        $resultado =  getSectorId($sector);

        $stmt2->bind_param("ss", $resultado, $id);
        $stmt2->execute();
    }
}
$stmt->close();



$stmt2->close();




























/*



function getSectorId($sector)
{ // para entes y sub entes
    global $conexion;

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_sectores` WHERE sector = ? ");
    $stmt->bind_param('s', $sector);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            return $row['id'];
        }
    }
    $stmt->close();
}

$tabla = 'entes_dependencias';


$stmt2 = $conexion->prepare("UPDATE `$tabla` SET `sector`= ? WHERE id=?");




$stmt = mysqli_prepare($conexion, "SELECT * FROM `$tabla` ");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $sector = $row['sector'];
        $resultado =  getSectorId($sector);

        $stmt2->bind_param("ss", $resultado, $id);
        $stmt2->execute();
    }
}
$stmt->close();



$stmt2->close();




function getProgramaId($sector, $programa)
{ // para entes y sub entes
    global $conexion;

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_programas` WHERE sector = ? AND programa =? ");
    $stmt->bind_param('ss', $sector, $programa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            return $row['id'];
        }
    }
    $stmt->close();
}

$tabla = 'entes_dependencias';


$stmt2 = $conexion->prepare("UPDATE `$tabla` SET `programa`= ? WHERE id=?");




$stmt = mysqli_prepare($conexion, "SELECT * FROM `$tabla` ");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $sector = $row['sector'];
        $programa = $row['programa'];
        $resultado =  getProgramaId($sector, $programa);

        $stmt2->bind_param("ss", $resultado, $id);
        $stmt2->execute();
    }
}
$stmt->close();



$stmt2->close();
*/
