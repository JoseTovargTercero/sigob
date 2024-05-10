<?php
require_once 'config.php';
session_start();

if (!$_SESSION["u_oficina"]) {
	session_destroy();
	header("Location: " . constant('URL'));
}

?>