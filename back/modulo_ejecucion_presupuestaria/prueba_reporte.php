<?php

$url = "https://sigob.net/back/modulo_ejecucion_presupuestaria/pre_reportes.php";  // Cambia la URL si es necesario

$data = [
    "data" => [
        "ejercicio_fiscal" => "1",
        "tipo" => "compromiso",
        "tipo_tabla" => "dozavos",
        "tipo_fecha" => "trimestre",
        "fecha" => "1"
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error en cURL: ' . curl_error($ch);
} else {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="Reportes.zip"');
    echo $response;
}
curl_close($ch);
?>
