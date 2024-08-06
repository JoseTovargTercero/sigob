<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Comprobar que se reciben los dos parámetros necesarios
if (!isset($data["dependencia"]) || !isset($data["cod_dependencia"])) {
    $response = array("success" => false, "message" => "Los parámetros 'dependencia' y 'codigo dependencia' son obligatorios.");
} else {
    // Construir la consulta SQL para insertar datos
    $sql = "INSERT INTO dependencias (dependencia, cod_dependencia) VALUES (?, ?)";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("ss", $data["dependencia"], $data["cod_dependencia"]);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        $response = array("success" => true, "message" => "Dependencia registrada correctamente.");
    } else {
        $response = array("success" => false, "message" => "Error al insertar datos: " . $conexion->error);
    }

    // Cerrar la declaración
    $stmt->close();
}

// Cerrar la conexión
$conexion->close();

// Devolver la respuesta en formato JSON
echo json_encode($response);
?>