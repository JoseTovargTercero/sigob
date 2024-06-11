<?php
require_once '../sistema_global/conexion.php';

// Verificar si el parámetro 'id' está presente en la URL
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Preparar la declaración SQL para eliminar el registro
    $sql = "DELETE FROM empleados WHERE id = ?";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Comprobar si la preparación de la declaración fue exitosa
    if (!$stmt) {
        die("Error en la preparación de la declaración: " . $conexion->error);
    }

    // Vincular el parámetro y ejecutar la consulta
    $stmt->bind_param("i", $id);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "Error al eliminar el registro: " . $stmt->error;
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    echo "No se ha proporcionado un ID.";
}

// Cerrar la conexión
$conexion->close();
?>
