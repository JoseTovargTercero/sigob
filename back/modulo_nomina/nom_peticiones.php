<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

// Consulta SQL para obtener registros de peticiones y la frecuencia de nominas con nombres coincidentes y status_archivos igual a 0
$sql = "
    SELECT p.*, n.frecuencia
    FROM peticiones p
    JOIN nominas n ON p.nombre_nomina = n.nombre
    WHERE p.status_archivos = 0
";
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
