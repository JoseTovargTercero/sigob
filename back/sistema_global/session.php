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
		5 =>  '_ejecucion_presupuestaria/',
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


	// Verificar acceso de los usuarios nivel 2
	if ($_SESSION["u_nivel"] == 2) {
		$url_completa = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// Verificar si la URL contiene 'mod_global' para permitir el acceso a todos
		if (strpos($url_completa, 'sigob/front') == true && strpos($url_completa, 'global_perfil') == false) {
			// verificar si la pagina que se esta cargando esta en el nivel de acceso del user
			$coincidencia = false;
			foreach ($_SESSION["permisos"] as $key => $value) {
				$url_acceso = constant('URL') . 'front/' . $value;

				if ($url_acceso == $url_completa) {
					$coincidencia = true;
				}
			}

			if ($coincidencia == false) {
				// Si no coincide, redirigir al usuario a la página principal
				header("Location: " . constant('URL'));
			}
		}
	}
}
