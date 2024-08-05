<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Construir la consulta SQL para insertar datos
$sql = "INSERT INTO dependencias (dependencia, cod_dependencia) VALUES (?, ?)";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros y ejecutar la consulta
$stmt->bind_param("ss", $data["dependencia"], $data["cod_dependencia"]);

// Ejecutar la consulta preparada
if ($stmt->execute()) {
    echo "Datos insertados correctamente.";
} else {
    echo "Error al insertar datos: " . $conexion->error;
}

// Cerrar la declaración y la conexión
$stmt->close();
$conexion->close();
?>