<?php
require_once 'config.php';
session_start();

if (!@$_SESSION["u_oficina"]) {
	session_destroy();
	header("Location: " . constant('URL'));
} else {

	// Obtener la URL actual
	$url = $_SERVER['REQUEST_URI'];

	// Obtener el ID de la oficina del usuario desde la sesión
	$u_oficina_id = $_SESSION['u_oficina_id'];

	// Asociar las oficinas con sus respectivos módulos/casos
	$casos = array(
		1 => '_nomina/',
		2 => '_registro_control/',
		3 => '_relaciones_laborales/',
		4 => '_pl_formulacion/',
	);

	// Verificar si la URL contiene 'mod_global' para permitir el acceso a todos los usuarios
	if (strpos($url, 'global') === false) {
		// Permitir acceso a 'mod_global'

		// Verificar si la oficina del usuario tiene un caso asociado
		if (isset($casos[$u_oficina_id])) {

			// Obtener el caso asociado a la oficina del usuario
			$caso = $casos[$u_oficina_id];

			// Verificar si la URL contiene el caso correspondiente
			if (strpos($url, $caso) === false) {
				// Si la URL no contiene el caso, redirigir al usuario a la página principal
				header("Location: " . constant('URL'));
				exit;
			}
		} else {
			// Si el id de oficina no es válido, redirigir también a la página principal
			header("Location: " . constant('URL'));
			exit;
		}
	}
}
