<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// URL del servidor
$url = 'http://localhost/sigob/back/modulo_nomina/registro_empleados.php/';

// Datos a enviar (el objeto JSON)
$data = array(
    "nacionalidad" => "1",
    "Cedula" => 123456789,
    "cod_empleado" => 441151,
    "nombres" => "Pedro Pablo",
    "fecha_ingreso" => "2010/05/02",
    "otros_años" => 0,
    "status" => 1,
    "observacion" => "N/A",
    "cod_cargo" => "25212",
    "cargo" => 12,
    "banco" => "Venezuela",
    "cuenta_bancaria" => "1002555541124",
    "hijos" => 3,
    "instruccion_academica" => 1,
    "discapacidades" => 0,
    "becas" => 0,
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