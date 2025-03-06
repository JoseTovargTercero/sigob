<?php

try {
    // Configura MySQLi para que lance excepciones en caso de error
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $remote_db = new mysqli('sigob.net', 'sigobnet_userroot', ']n^VmqjqCD1k', 'sigobnet_sigob_entes');

    if (!empty($_SESSION['error_remote_db'])) {
        unset($_SESSION['error_remote_db']);
    }
} catch (mysqli_sql_exception $e) {
    // Capturar el error de conexión y almacenarlo en la sesión
    $_SESSION['error_remote_db'] = json_encode(['msg' => 'No se pudo conectar con la base de datos remota', 'error' => $e->getMessage()]);
}
