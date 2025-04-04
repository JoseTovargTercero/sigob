<?php

try {
    // Configura MySQLi para que lance excepciones en caso de error
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $db = ($_SERVER['SERVER_NAME'] == 'localhost' ? 'sigobnet_sigob_entes_dev' : 'sigobnet_sigob_entes');

    $remote_db = new mysqli('sigob.net', 'sigobnet_userroot', ']n^VmqjqCD1k', $db);

    if (!empty($_SESSION['error_remote_db'])) {
        unset($_SESSION['error_remote_db']);
    }
} catch (mysqli_sql_exception $e) {
    // Capturar el error de conexión y almacenarlo en la sesión
    $_SESSION['error_remote_db'] = json_encode(['msg' => 'No se pudo conectar con la base de datos remota', 'error' => $e->getMessage()]);
}



    //192.99.18.84

    /*  // Configuración de conexión a la base de datos remota
    $remoteHost = REMOTE_HOST;
    $remoteDb = 'sigobnet_sigob_entes_dev';
    $remoteUser = 'sigobnet_userroot';
    $remotePass = ']n^VmqjqCD1k';

    // Conexión a la base de datos del hosting
    $remote_db = new mysqli($remoteHost, $remoteUser, $remotePass, $remoteDb);
    $remote_db->set_charset('utf8mb4'); // Establecer charset


    if ($remote_db->connect_error) { // Verificar la conexión
        echo json_encode(['status' => 'error', 'mensaje' => "Conexión fallida a la base de datos del hosting: " . $remote_db->connect_error]);
        exit();
    }*/