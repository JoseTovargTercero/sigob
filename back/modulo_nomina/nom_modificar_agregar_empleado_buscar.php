<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

// Obtener los datos JSON enviados por AJAX
$data = json_decode(file_get_contents('php://input'), true);
$cedula = $data['cedula'];
$grupoActual = $data['grupoActual'];


$stmt = mysqli_prepare($conexion, "SELECT * FROM `empleados` WHERE cedula = ?");
$stmt->bind_param('s', $cedula);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nombre = $row['nombres'];
        $id_empleado = $row['id']; 
        $fecha_ingreso = $row['fecha_ingreso']; 
    }
}else {
    echo json_encode(['status' => 'ok', 'datos' => [$nombre, $id_empleado, $fecha_ingreso]]);

}
$stmt->close();





$stmt = mysqli_prepare($conexion, "SELECT * FROM `empleados_por_grupo` WHERE id_grupo = ? AND id_empleado = ?");
$stmt->bind_param('ii', $grupoActual, $id_empleado);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'mensaje' => 'El empleado ya pertenece al grupo actual']);
}else {
    echo json_encode(['status' => 'ok', 'datos' => [$nombre, $id_empleado, $fecha_ingreso]]);
}
$stmt->close();



$conexion->close();
