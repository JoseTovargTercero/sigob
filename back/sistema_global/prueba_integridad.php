<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/DatabaseCheckIntegrityHosting.php';
header('Content-Type: application/json');


  $sincronizacion = backups('traspasos', 'id');
    $sincronizacion2 = backups('traspaso_informacion', 'id');






 ?>