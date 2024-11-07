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

    todo: Por favor! Comenta la configuracion usando:  uso que se le dara <
    * Cargar la lista de todos los empleados que sean mayores de 24 annios
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
} // Tabla por defecto

function _lista_programas($tabla)
{
    return [
        'columnas' => ["$tabla.*", "pl_sectores.sector AS sector_n"],
        'tabla' => $tabla,
        'where' => null,
        'order_by' => null,
        'join' => [
            'pl_sectores' => "$tabla.sector = pl_sectores.id"
        ]
    ];
} // carga un join a sectores


function _join_programas($tabla)
{
    global $data;
    $id_ejercicio = $data['id_ejercicio'];

    return [
        'columnas' => ["$tabla.*", "pl_programas.programa AS programa_n, pl_programas.denominacion, pl_sectores.sector AS sector_n, pl_sectores.id AS sector_id"],
        'tabla' => $tabla,
        'where' => "id_ejercicio='$id_ejercicio'",
        'order_by' => ['pl_programas.programa'],
        'join' => [
            'pl_programas' => "$tabla.programa = pl_programas.id",
            'pl_sectores' => "pl_programas.sector = pl_sectores.id"
        ]
    ];
} // carga el programa por join y filtra por id_ejercicio


function _to_users($tabla)
{
    return [
        'columnas' => ["$tabla.*", "system_users.u_nombre"],
        'tabla' => $tabla,
        'where' => null,
        'order_by' => null,
        'join' => [
            'system_users' => "$tabla.user_id = system_users.u_id"
        ]
    ];
} // Carga 