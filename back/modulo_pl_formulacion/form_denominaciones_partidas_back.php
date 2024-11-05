<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);
$tabla_principal = 'pl_partidas';

function registrar($info)
{
    global $tabla_principal;

    global $db;

    $partida = $info['partida'];
    $denominacion = $info['nombre'];

    $campos_valores = [
        ['partida', $partida, true],
        ['denominacion', $denominacion]
    ];

    // Intentar insertar el registro
    try {
        $resultado = $db->insert($tabla_principal, $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizar($info)
{
    global $db;
    global $tabla_principal;

    $nombre = $info['nombre'];
    $partida = $info['partida'];
    $id = $info['id'];

    // Array con los campos a actualizar: [campo, valor, tipo]
    $valores = [
        ['partida', $partida, 's'],
        ['denominacion', $nombre, 's']
    ];

    $where = "id = $id"; // Condición

    try {

        $resultado = $db->update($tabla_principal, $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminar($id)
{
    global $tabla_principal;
    global $db;

    try {
        $condicion = "id = $id"; // Condición para eliminar registros
        $resultado = $db->delete($tabla_principal, $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}


// PROCESAR SOLICITUDES
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["accion"])) {
    echo json_encode(["error" => "Acción no especificada."]);
    exit;
}

$accion = $data["accion"];
$response = null;

switch ($accion) {
    case "registrar":
        $response = isset($data["info"]) ? registrar($data["info"]) : ["error" => "Datos de faltantes."];
        break;
    case "actualizar":
        $response = isset($data["info"]) ? actualizar($data["info"]) : ["error" => "Datos faltantes."];
        break;
    case "borrar":
        $response = isset($data['id']) ? eliminar($data['id']) : ["error" => "ID faltante."];
        break;
    default:
        $response = ["error" => "Acción inválida."];
}

echo $response;
