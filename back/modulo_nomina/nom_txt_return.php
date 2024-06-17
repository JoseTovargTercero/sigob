<?php
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

// Obtener el contenido JSON de la solicitud POST
$data = json_decode(file_get_contents('php://input'), true);

// Obtener correlativo del array JSON
$correlativo = $data['correlativo'];

// Consulta SQL para obtener los identificadores únicos de la tabla txt con el correlativo especificado
$sql = "SELECT DISTINCT identificador, correlativo FROM txt WHERE correlativo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $correlativo);
$stmt->execute();
$result = $stmt->get_result();

$identificadores = array();

if ($result->num_rows > 0) {
    // Recorrer los registros y almacenarlos en un array
    while ($row = $result->fetch_assoc()) {
        $identificadores[] = array(
            'identificador' => $row['identificador'],
            'correlativo' => $row['correlativo']
        );
    }
}

// Devolver los datos en formato JSON
echo json_encode($identificadores);

// Cerrar la conexión
$stmt->close();
$conexion->close();
?>
