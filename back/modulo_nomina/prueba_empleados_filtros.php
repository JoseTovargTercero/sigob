<?php

// URL del servidor
$url = 'http://localhost/sigob/back/modulo_nomina/nom_empleados_filtros.php/';

// Datos a enviar (el objeto JSON)
$data = array(
    "empleados" => array(26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 37, 38, 39, 40, 41), // Array de IDs de empleados
    "concepto" => "21",
);

// Convertir el array a formato JSON
$json = json_encode($data);

// Configurar las opciones de la solicitud
$options = array(
    'http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $json
    )
);

// Crear el contexto de la solicitud
$context = stream_context_create($options);

// Realizar la solicitud HTTP POST
$result = file_get_contents($url, false, $context);

// Verificar si la solicitud fue exitosa
if ($result === FALSE) {
    die('Error en la solicitud HTTP POST');
}

// Imprimir la respuesta del servidor
echo $result;
?>
