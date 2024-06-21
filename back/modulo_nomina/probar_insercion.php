<?php

// URL del script de inserción
$url = 'http://localhost/sigob/back/modulo_nomina/nom_editar_solicitud.php';

// Array de prueba que se enviará
$datos = [
    [1, "nombre", "Juan Pérez"],
    [1, "departamento", "Ventas"],
    [2, "nombre", "María Gómez"],
    [2, "departamento", "Marketing"]
];

// Convertir el array a JSON
$data_json = json_encode($datos);

// Inicializar cURL
$ch = curl_init($url);

// Configurar cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);

// Ejecutar la solicitud y obtener la respuesta
$response = curl_exec($ch);

// Cerrar cURL
curl_close($ch);

// Mostrar la respuesta
echo $response;
?>
