<?php
header('Content-Type: application/json');

require_once '../sistema_global/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_nomina = $_POST['nombre_nomina'];

    // Verificar si el nombre_nomina ya existe en la tabla nominas
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM nominas WHERE nombre = ?");
    $stmt->bind_param("s", $nombre_nomina);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
}

// Cerrar la conexión
$conexion->close();
?>
