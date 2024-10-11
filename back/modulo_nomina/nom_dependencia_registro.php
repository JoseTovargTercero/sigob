<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Comprobar que se reciben los dos parámetros necesarios
if (!isset($data["dependencia"]) || !isset($data["cod_dependencia"])) {
    $response = array("error" => "Los parámetros 'dependencia' y 'codigo dependencia' son obligatorios.");
} else {

    // Verificar si hay un registro con el mismo cod_dependencia
    $stmt_dep = $conexion->prepare("SELECT * FROM `dependencias` WHERE cod_dependencia = ?");
    $stmt_dep->bind_param('s', $data["cod_dependencia"]);
    $stmt_dep->execute();
    $result = $stmt_dep->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(["error" => "Error: ya existe una dependencia con el mismo código."]);
        $stmt_dep->close();
        exit;
    }
    $stmt_dep->close();

    // Construir la consulta SQL para insertar datos
    $sql = "INSERT INTO dependencias (dependencia, cod_dependencia, id_categoria) VALUES (?, ?, ?)";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("sss", $data["dependencia"], $data["cod_dependencia"], $data["id_categoria"]);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        $response = array("success" => "Dependencia registrada correctamente.");
    } else {
        $response = array("error" => "Error al insertar datos: " . $conexion->error);
    }

    // Cerrar la declaración
    $stmt->close();
}

// Cerrar la conexión
$conexion->close();

// Devolver la respuesta en formato JSON
echo json_encode($response);
?>