<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Verificar si algún campo está vacío
foreach ($data as $key => $value) {
    if ($value == '') {
        echo json_encode(["success" => false, "message" => "Error: el campo $key no puede estar vacío."]);
        exit;
    }
}

// Verificar si el ID de dependencia está presente y no está vacío
if (empty($data['id_dependencia'])) {
    echo json_encode(["success" => false, "message" => "Error: el campo id_dependencia no puede estar vacío."]);
    exit;
}

// Verificar si cod_dependencia está presente y no está vacío
if (empty($data['cod_dependencia'])) {
    echo json_encode(["success" => false, "message" => "Error: el campo cod_dependencia no puede estar vacío."]);
    exit;
}

// Verificar si hay un registro con el mismo cod_dependencia
$stmt_dep = $conexion->prepare("SELECT * FROM `dependencias` WHERE cod_dependencia = ?");
$stmt_dep->bind_param('s', $data["cod_dependencia"]);
$stmt_dep->execute();
$result = $stmt_dep->get_result();
if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Error: ya existe una dependencia con el mismo código."]);
    $stmt_dep->close();
    exit;
}
$stmt_dep->close();

// Construir la consulta SQL para actualizar datos
$sql = "UPDATE dependencias SET dependencia = ?, cod_dependencia = ? WHERE id_dependencia = ?";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros y ejecutar la consulta
$stmt->bind_param("sss", $data["dependencia"], $data["cod_dependencia"], $data["id_dependencia"]);

// Ejecutar la consulta preparada
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Datos actualizados correctamente."]);
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar datos: " . $conexion->error]);
}

// Cerrar la declaración y la conexión
$stmt->close();
$conexion->close();
?>