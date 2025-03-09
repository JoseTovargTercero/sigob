<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// URL del servidor
$url = 'http://localhost/sigob/back/modulo_nomina/nom_empleados_registro.php/';

// Datos a enviar (el objeto JSON)
$data = array(
    "nacionalidad" => "V",
    "cedula" => '123456789',
    "nombres" => "Pedro Pablo",
    "otros_anios" => '0',
    "status" => 'A',
    "observacion" => NULL,
    "cod_cargo" => "0041",
    "banco" => "0102",
    "cuenta_bancaria" => "01020457770100648138",
    "hijos" => '3',
    "instruccion_academica" => '1',
    "discapacidades" => '0',
    "tipo_nomina" => '003',
    "id_dependencia" => '6',
    "verificado" => '0',
    "coreccion" => 'NULL',
    "beca" => '0',
    "fecha_ingreso" => "2010/05/02",
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
