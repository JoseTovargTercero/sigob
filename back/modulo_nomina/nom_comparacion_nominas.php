<?php
require_once '../sistema_global/conexion.php';


// Obtener el contenido JSON de la solicitud POST
$data = json_decode(file_get_contents('php://input'), true);

// Obtener correlativo y nombre_nomina del array JSON
$correlativo = $data['correlativo'];
$nombre_nomina = $data['nombre_nomina'];

// Consultar la información del registro que tenga el mismo correlativo y nombre_nomina
$sql = "SELECT * FROM peticiones WHERE correlativo = ? AND nombre_nomina = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $correlativo, $nombre_nomina);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $registro_actual = $result->fetch_assoc();
} else {
    echo json_encode(["error" => "No se encontró el registro actual."]);
    $conexion->close();
    exit();
}

// Consultar el registro anterior al correlativo actual que tenga el mismo nombre_nomina
$sql_anterior = "
    SELECT * FROM peticiones 
    WHERE nombre_nomina = ? AND correlativo < ? 
    ORDER BY correlativo DESC 
    LIMIT 1";
$stmt_anterior = $conexion->prepare($sql_anterior);
$stmt_anterior->bind_param("ss", $nombre_nomina, $correlativo);
$stmt_anterior->execute();
$result_anterior = $stmt_anterior->get_result();

if ($result_anterior->num_rows > 0) {
    $registro_anterior = $result_anterior->fetch_assoc();
} else {
    $registro_anterior = [
        "id" => 0,
        "empleados" => 0,
        "asignaciones" => 0,
        "deducciones" => 0,
        "total_pagar" => 0,
        "correlativo" => 0,
        "status" => "",
        "nombre_nomina" => ""
    ];
}

// Respuesta JSON con ambos registros
$response = [
    "registro_actual" => $registro_actual,
    "registro_anterior" => $registro_anterior
];

echo json_encode($response);

// Cerrar la conexión
$conexion->close();

?>
