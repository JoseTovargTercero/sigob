<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

// Consulta SQL para obtener todos los registros de la tabla peticiones
$sql = "SELECT * FROM peticiones";
$result = $conexion->query($sql);

$peticiones = array();

if ($result->num_rows > 0) {
    // Recorrer los registros y almacenarlos en un array
    while($row = $result->fetch_assoc()) {
        $peticiones[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($peticiones);

// Cerrar la conexión
$conexion->close();
?>
