<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
// URL del servidor
$url = 'http://localhost/sigob/back/modulo_nomina/nom_reportes_form.php';

// Datos a enviar (el objeto JSON)
$data = array(
    "formato" => "pdf",
    "almacenar" => "Si",
    "nombre" => "Reporte de Empleados",
    "columnas" => array("nombres", "cedula"),
    "condicion" => "", // Cambia esto a la condición que desees
    "tipoFiltro" => "nominas",
    "nominas" => array("33") // Puedes agregar los datos de nóminas si es necesario
);

// Convertir el array a formato JSON
$json = json_encode(array("data" => $data));

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
