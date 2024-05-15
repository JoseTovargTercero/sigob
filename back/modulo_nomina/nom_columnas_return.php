<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');


// Obtener datos POST
$data = json_decode(file_get_contents('php://input'), true);
$columna = isset($data['columna']) ? $data['columna'] : '';

// Verificar si se proporcionó la columna
if (empty($columna)) {
    echo json_encode(array("error" => "No se proporcionó una columna"));
    $conexion->close();
    exit();
}

// Escapar el nombre de la columna para evitar inyección SQL
$columna = $conexion->real_escape_string($columna);

// Consultar valores distintos de la columna
$sql = "SELECT DISTINCT `$columna` FROM empleados";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    $valores = array();
    while($row = $result->fetch_assoc()) {
        $valores[] = $row[$columna];
    }
    echo json_encode($valores);
} else {
    echo json_encode(array());
}

// Cerrar conexión
$conexion->close();
?>
