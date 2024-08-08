<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Inicializar el array de respuesta
$response = array();

// Verificar si se recibió un ID y limpiarlo
if (isset($_GET['id'])) {
    $id = $conexion->real_escape_string($_GET['id']);

    // Consulta SQL para obtener los datos de una fila específica por su ID
    $sql = "SELECT *
            FROM dependencias
            WHERE id_dependencia = $id";
} else {
    // Consulta SQL para obtener todos los registros
    $sql = "SELECT *
            FROM dependencias";
}

$result = $conexion->query($sql);

// Verificar si la consulta fue exitosa
if ($result === false) {
    // Si hay un error en la consulta, mostrarlo y salir del script
    $response['error'] = "Error en la consulta: " . $conexion->error;


} else {
    $datos = array();

    if ($result->num_rows > 0) {
        // Llenar el array con los datos obtenidos de la consulta
        while ($row = $result->fetch_assoc()) {
            $tabulador = array(
                "id_dependencia" => $row["id_dependencia"],
                "dependencia" => $row["dependencia"],
                "cod_dependencia" => $row["cod_dependencia"],
            );
            $datos[] = $tabulador;
        }

        $response['success'] = $datos;

    } else {
        // Si no se encontraron resultados
        $response['error'] = "No se encontraron resultados.";

    }
}

// Cerrar la conexión a la base de datos
$conexion->close();

// Pasar la respuesta a la vista en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>