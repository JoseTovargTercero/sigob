<?php

require_once 'conexion.php';
require_once 'session.php';
require_once 'errores.php';
require_once 'DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$tabla = $data['table'];
$configFunction = $data['config'] ?? '_default';

// Verificar que la función de configuración existe y es callable
if (!function_exists($configFunction)) {
    echo json_encode(['error' => "Configuración '$configFunction' no válida o no encontrada"]);
    exit;
}

// Llamar a la función de configuración y obtener los parámetros
$config = $configFunction($tabla);

// Llamar al método select con la configuración seleccionada
try {
    $resultado = $db->select(
        $config['columnas'],
        $config['tabla'],
        $config['where'],
        $config['order_by'],
        $config['join']
    );
    echo $resultado;
} catch (Exception $e) {
    throw new Exception("Error al ejecutar la consulta: " . $e->getMessage());
}














/*
    * Configuraciones:
    Agrega bloques con la configuracion

    todo: Por favor! Comenta la configuracion usando:  > nombre_archivo que solicita / uso que se le dara <
    * Ejemplo: nom_empleados.php / Cargar la lista de todos los empleados que sean mayores de 24 annios
*/

function _default($tabla)
{
    return [
        'columnas' => null,
        'tabla' => $tabla,
        'where' => null,
        'order_by' => null,
        'join' => null
    ];
}




/*
 *Ejemplo de uso
function _ejemplo_config($tabla)
{
    return [
        'columnas' => ['columna1', 'columna2'],
        'tabla' => $tabla,
        'condicion' => "columna1 = 'valor'",
        'order_by' => [
            ['nombre_campo' => 'columna2', 'asc_desc' => 'DESC']
        ],
        'join' => [
            'otra_tabla' => "$tabla.id_otro = otra_tabla.id"
        ]
    ];
}
    */
