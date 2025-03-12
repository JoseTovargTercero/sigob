<?php

$url = "https://sigob.net/back/modulo_ejecucion_presupuestaria/pre_reportes.php";  // Cambia la URL si es necesario

$data = [
    "id_ejercicio" => "1",
    "tipo" => "solicitud_dozavos",
    "tipo_fecha" => "trimestre",
    "fecha" => "1",
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
