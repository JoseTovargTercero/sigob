<?php
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');



// Obtener el contenido JSON de la solicitud POST
$data = json_decode(file_get_contents('php://input'), true);

// Obtener correlativo y nombre_nomina del array JSON
$correlativo = $data['correlativo'];

// Consulta SQL para obtener todos los registros de la tabla txt
$sql = "SELECT * FROM txt WHERE correlativo = $correlativo";
$result = $conexion->query($sql);

$txt = array();

if ($result->num_rows > 0) {
    // Recorrer los registros y almacenarlos en un array
    while($row = $result->fetch_assoc()) {
        $txt[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($txt);

// Cerrar la conexión
$conexion->close();
?>
