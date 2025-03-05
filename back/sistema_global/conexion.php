<?php
require_once 'config.php';

$conexion = new mysqli(constant('HOST'), constant('USER'), constant('PASSWORD'), constant('DB'));
$conexion->set_charset(constant('CHARSET'));

if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión: ' . $conexion->connect_error]));
}

date_default_timezone_set('America/Manaus');

/* LIMPIAR DATOS */
function clear($campo) {
    $campo = strip_tags($campo);
    $campo = filter_var($campo, FILTER_UNSAFE_RAW);
    $campo = htmlspecialchars($campo);
    return $campo;
}

$local_db = $conexion;

// Desactivar la visualización de errores para evitar salida HTML en la respuesta JSON
mysqli_report(MYSQLI_REPORT_OFF);
error_reporting(0);
ini_set('display_errors', 0);

$remote_db = new mysqli('sigob.net', 'sigobnet_userroot', ']n^VmqjqCD1k', 'sigobnet_sigob_entes');

if ($remote_db->connect_error) {
    echo json_encode(['error' => 'No se pudo conectar con la base de datos remota: ' . $remote_db->connect_error]);
    exit;
}
?>
