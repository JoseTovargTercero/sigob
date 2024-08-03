<?php
session_start();
require_once '../sistema_global/conexion.php';

$condiciones = $_POST['condiciones'];
$columnas = $_POST['columnas'];
$formato = $_POST['formato'];
$almacenar = $_POST['almacenar'];
$nombre = $_POST['nombre'];

// Palabras clave prohibidas
$palabras_prohibidas = array('UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'INSERT', 'ALTER', 'GRANT', 'REVOKE');

// Verificar si las condiciones contienen palabras clave prohibidas
foreach ($palabras_prohibidas as $palabra) {
    if (stripos($condiciones, $palabra) !== false) {
        echo json_encode("PROHIBIDO");
        $conexion->close();
        exit();
    }
}

// Convertir columnas a string para almacenar en la base de datos
$columnas_str = implode(',', $columnas);

// Generar el reporte (esto dependerá de la lógica específica para generar el reporte en PDF o XLSX)
// Ejemplo:
if ($formato === 'pdf') {
    // Lógica para generar PDF
} elseif ($formato === 'xlsx') {
    // Lógica para generar XLSX
}

// Guardar el reporte en la base de datos si almacenar es verdadero
if ($almacenar === 'true') {
    $user_id = $_SESSION['id_usuario']; // Asegúrate de tener el ID del usuario en la sesión
    $nombre_reporte = $nombre ? $nombre : 'Reporte_Formulado';
    $sql = "INSERT INTO reportes (formulacion, columnas, formato, nombre, user) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('ssssi', $condiciones, $columnas_str, $formato, $nombre_reporte, $user_id);
    if ($stmt->execute()) {
        echo json_encode("Reporte guardado exitosamente");
    } else {
        echo json_encode("Error al guardar el reporte");
    }
    $stmt->close();
}

$conexion->close();
?>
