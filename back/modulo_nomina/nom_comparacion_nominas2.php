<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
// Obtener el contenido JSON de la solicitud POST
$data = json_decode(file_get_contents('php://input'), true);

// Obtener nombre_nomina del array JSON
$nombre_nomina = $data['nombre_nomina'];

// Consultar el último registro que tenga el mismo nombre_nomina y status = 1
$sql_ultimo = "
    SELECT * FROM peticiones 
    WHERE nombre_nomina = ? AND status = 1
    ORDER BY correlativo DESC 
    LIMIT 1";
$stmt_ultimo = $conexion->prepare($sql_ultimo);
$stmt_ultimo->bind_param("s", $nombre_nomina);
$stmt_ultimo->execute();
$result_ultimo = $stmt_ultimo->get_result();

if ($result_ultimo->num_rows > 0) {
    $registro_ultimo = $result_ultimo->fetch_assoc();
} else {
    $registro_ultimo = false;
}

// Respuesta JSON con el registro último
$response = [
    "registro_anterior" => $registro_ultimo
];

echo json_encode($response);

// Cerrar la conexión
$conexion->close();
?>