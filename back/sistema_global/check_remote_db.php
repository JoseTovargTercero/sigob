<?php

session_start();


$response = [];

if (isset($_SESSION['error_remote_db'])) {
    $response['error'] = $_SESSION['error_remote_db'];
} else {
    $response['success'] = "Conexión establecida correctamente.";
}

echo json_encode($response);
exit;
