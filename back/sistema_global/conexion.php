<?php
require_once 'config.php';


$conexion = new mysqli(constant('HOST'), constant('USER'), constant('PASSWORD'), constant('DB'));
$conexion->set_charset(constant('CHARSET'));

if ($conexion->connect_error) {
	die('Error de conexion: ' . $conexion->connect_error);
}

date_default_timezone_set('America/Manaus');


/* LIMPIAR DATOS */
function clear($campo){
	$campo = strip_tags($campo);
	$campo = filter_var($campo, FILTER_UNSAFE_RAW);
	$campo = htmlspecialchars($campo);
	return $campo;
}
?>