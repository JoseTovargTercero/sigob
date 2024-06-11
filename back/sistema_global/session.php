<?php
require_once 'config.php';
session_start();

if (!$_SESSION["u_oficina"]) {
	session_destroy();
	header("Location: " . constant('URL'));
}else {
	
	$url = $_SERVER['REQUEST_URI'];





	$u_oficina_id = $_SESSION['u_oficina_id'];

	// Asociar las oficinas con sus respectivos casos
	$casos = array(
		1 => '_nomina',
		2 => '_registro_control',
		3 => '_relaciones_laborales',
		4 => '_atencion_trabajador'
	);

	// Verificar si la oficina del usuario coincide con la URL
	if (isset($casos[$u_oficina_id])) {
		$caso = $casos[$u_oficina_id];
		if (strpos($url, $caso) === false) {
			header("Location: " . constant('URL'));
			exit;
		}
	} else {
		// Si el id de oficina no es válido, redirigir también
		header("Location: " . constant('URL'));
		exit;
	}




}


?>