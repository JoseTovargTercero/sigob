<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Verificar si el campo id_dependencia está presente y no está vacío
if (empty($data['id_dependencia'])) {
    echo json_encode(["success" => false, "message" => "Error: el campo id_dependencia no puede estar vacío."]);
    exit;
}

// Verificar si existe un empleado con la misma id_dependencia
$stmt_emp = $conexion->prepare("SELECT * FROM `empleados` WHERE id_dependencia = ?");
$stmt_emp->bind_param('s', $data["id_dependencia"]);
$stmt_emp->execute();
$result = $stmt_emp->get_result();
if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Error: existe un empleado registrado con esta dependencia."]);
    $stmt_emp->close();
    exit;
}
$stmt_emp->close();

// Eliminar el registro en la tabla dependencias
$stmt_dep = $conexion->prepare("DELETE FROM `dependencias` WHERE id_dependencia = ?");
$stmt_dep->bind_param('s', $data["id_dependencia"]);

// Ejecutar la consulta preparada
if ($stmt_dep->execute()) {
    echo json_encode(["success" => true, "message" => "Dependencia eliminada correctamente."]);
} else {
    echo json_encode(["success" => false, "message" => "Error al eliminar la dependencia: " . $conexion->error]);
}

// Cerrar la declaración y la conexión
$stmt_dep->close();
$conexion->close();
?>