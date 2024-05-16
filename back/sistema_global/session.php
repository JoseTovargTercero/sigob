<?php
require_once 'config.php';
session_start();

if (!$_SESSION["u_oficina"]) {
	session_destroy();
	header("Location: " . constant('URL'));
}else {
	
	$url = $_SERVER['REQUEST_URI'];

	/**
	 * Checks if the user's office ID is not equal to '1' and if the URL contains the string 'mod_nomina'.
	 * If both conditions are true, it redirects the user to the constant URL.
	 *
	 * @param string $url The URL to check for the presence of 'mod_nomina'.
	 * @return void
	 */
	if ($_SESSION["u_oficina_id"] != '1' && strpos($url, '_nomina')) {
		header("Location: " . constant('URL'));
	}


}

?>