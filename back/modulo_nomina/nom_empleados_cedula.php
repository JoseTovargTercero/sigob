<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$cedula = @$data['cedula'];
//$cedula = '27640176';

if (@$cedula != '') {

    $stmt = mysqli_prepare($conexion, "SELECT *, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) AS antiguedad, 
otros_años, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_años AS anios_totales 
FROM empleados WHERE cedula = ?");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            if ($row["status"]  == 'R') {
                echo json_encode(['status' => true, "otros_anios" => $row["anios_totales"]]);
            } else {
                echo json_encode(['status' => false, "mensaje" => "Empleado activo. No puede registrar mas de una vez al mismo empleado"]);
            }
        }
    }else {
        echo json_encode(['status' => true, "otros_anios" => '']);

    }
    $stmt->close();
} else {
    echo json_encode(['status' => false, "mensaje" => "No se ha proporcionado ninguna cedula"]);
    exit();
}

// Cerrar la conexión a la base de datos
$conexion->close();
