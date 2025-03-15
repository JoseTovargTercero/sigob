<?php
$remote_db = new mysqli('sigob.net', 'sigobnet_userroot', ']n^VmqjqCD1k', 'sigobnet_sigob_entes');

if ($remote_db->connect_error) {
    die(json_encode([
        'status' => 'error',
        'mensaje' => 'Conexión fallida: ' . $remote_db->connect_error
    ]));
}

echo json_encode(['status' => 'ok', 'mensaje' => 'Conexión exitosa']);
exit();


 ?>