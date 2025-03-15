<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/DatabaseCheckIntegrityHosting.php';
header('Content-Type: application/json');


  $sincronizacion = verificarColumnas('traspasos');
    $sincronizacion2 = verificarColumnas('traspaso_informacion');






 ?>