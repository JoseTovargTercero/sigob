<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION["u_oficina"])) {
	session_regenerate_id(true);
}else {
    session_start();
	session_destroy();
	header("Location: " . constant('URL'));
}

?>