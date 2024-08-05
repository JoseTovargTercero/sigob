<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');


// Obtener datos POST
$data = json_decode(file_get_contents('php://input'), true);
$tipo = isset($data['tipo']) ? $data['tipo'] : '';

if (empty($tipo)) {
    echo json_encode(array("error" => "No se proporcionó un tipo"));
    $conexion->close();
    exit();
}




// Escapar el nombre de la columna para evitar inyección SQL
$tipo = $conexion->real_escape_string($tipo);

if ($tipo == 'nominas') {
    $tabla = 'nominas';
}elseif($tipo == 'nominas_g') {
    $tabla = 'nominas_grupos';
}else{
    echo json_encode(array("error" => "No se reconoce el tipo"));
    $conexion->close();
    exit();

}




// Consultar valores distintos de la columna
$sql = "SELECT id, nombre FROM $tabla";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {

    $valores = array();
    while($row = $result->fetch_assoc()) {
        $valores[] = $row;
    }
    echo json_encode($valores);
} else {
    echo json_encode(array());
}

// Cerrar conexión
$conexion->close();
?>
