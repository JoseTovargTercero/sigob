<?php
require_once '../sistema_global/conexion.php';

// URL del servidor
$url = 'http://localhost/sigob/back/modulo_nomina/nom_modificar_agregar_empleado.php';

// Datos a enviar (el objeto JSON)
$data = array(
    "accion" => "agregar_empleado",
    "empleado" => 7,
    "grupo_nomina" => "4",
    "nominas" => array(
        array(
            "nomina" => "32",
            "conceptos" => array("sueldo_base", "21", "24", "25","26","27")
        ),
        array(
            "nomina" => "31",
            "conceptos" => array("sueldo_base", "21", "24", "26")
        )
    ),
    "info_reintegro" => array(
        "reintegro" => array(
            "reintegro" => "1",
            "datos" => array(
                "pagarDesde" => "2",
                "fechaIngreso" => "2022-11-21",
                "fechaEspecifica" => "2024-03-01"
            )
        )
    )
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
