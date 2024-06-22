<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

// Verificar si el parámetro 'id' está presente en la URL
if (isset($_POST['id'])) {
    $id = $_POST['id'];


    $stmt = $conexion->prepare("UPDATE `empleados` SET `verificado`='1' WHERE id=?");
    $stmt->bind_param("s", $id);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        notificar(['nomina'], 5);
        echo "ok";
    } else {
        echo "Error al actualizar el registro: " . $stmt->error;
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    echo "No se ha proporcionado un ID.";
}

// Cerrar la conexión
$conexion->close();
?>
