<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

if (isset($_POST['correlativo'])) {
    $correlativo = $_POST['correlativo'];

    $sql = "UPDATE peticiones SET status = 1 WHERE correlativo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correlativo);

    if ($stmt->execute()) {
        echo "Registro actualizado correctamente";
    } else {
        echo "Error al actualizar el registro: " . $conexion->error;
    }

    $stmt->close();
}

$conexion->close();
?>
