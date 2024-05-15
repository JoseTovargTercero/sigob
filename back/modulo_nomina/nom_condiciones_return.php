<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');



// Obtener datos POST
$data = json_decode(file_get_contents('php://input'), true);
$condicion = isset($data['condicion']) ? $data['condicion'] : '';

// Verificar si se proporcionó la condición
if (empty($condicion)) {
    echo json_encode("error");
    $conexion->close();
    exit();
}

// Palabras clave prohibidas
$palabras_prohibidas = array('UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'INSERT', 'ALTER', 'GRANT', 'REVOKE');

// Verificar si la condición contiene palabras clave prohibidas
foreach ($palabras_prohibidas as $palabra) {
    if (stripos($condicion, $palabra) !== false) {
        echo json_encode("PROHIBIDO");
        $conexion->close();
        exit();
    }
}

// Construir y ejecutar la consulta
$sql = "SELECT COUNT(*) as cantidad FROM empleados WHERE $condicion";
$result = $conexion->query($sql);

if ($result === FALSE) {
    echo json_encode("error");
} else {
    $row = $result->fetch_assoc();
    echo json_encode($row['cantidad']);
}

// Cerrar conexión
$conexion->close();
?>
