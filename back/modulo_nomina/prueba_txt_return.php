<?php


// URL del servidor
$url = 'http://localhost/sigob/back/modulo_nomina/nom_txt_descargas2.php/';

// Datos a enviar (el objeto JSON)
$data = array(
    "id_empleado" => "5",
    "fecha_inicio" => "26-08-2024",
    "fecha_fin" => "01-09-2024",
    "nombre_nomina" => "004 Empleados Empleados Nacional",
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

// Imprimir la respuesta del servidor
echo $result;
?>
