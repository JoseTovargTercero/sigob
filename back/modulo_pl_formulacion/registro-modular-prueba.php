<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

function procesarAccion($accion, $tabla, $data)
{
    global $db;

    switch ($accion) {
        case 'registrar':
            return isset($data["info"]) ? insertarRegistro($tabla, $data["info"]) : ["error" => "Datos faltantes."];
        case 'actualizar':
            return isset($data["info"]) ? actualizarRegistro($tabla, $data["info"]) : ["error" => "Datos faltantes."];
        case 'borrar':
            return isset($data['id']) ? eliminarRegistro($tabla, $data['id']) : ["error" => "ID faltante."];
        default:
            return ["error" => "Acción inválida."];
    }
}

function insertarRegistro($tabla, $info)
{
    global $db;

    $campos_valores = [];
    foreach ($info as $campo => $valor) {
        $campos_valores[] = [$campo, $valor];
    }

    try {
        $resultado = $db->insert($tabla, $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}

function actualizarRegistro($tabla, $info)
{
    global $db;

    $valores = [];
    foreach ($info as $campo => $valor) {
        if ($campo !== 'id') {
            $valores[] = [$campo, $valor, 's'];
        }
    }

    try {
        $where = "id = " . intval($info['id']);
        $resultado = $db->update($tabla, $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}

function eliminarRegistro($tabla, $id)
{
    global $db;

    try {
        $resultado = $db->delete($tabla, "id = " . intval($id));
        return json_encode($resultado);
    } catch (Exception $e) {
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}

// PROCESAR SOLICITUDES
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["accion"]) || !isset($data["tabla"])) {
    echo json_encode(["error" => "Acción o tabla no especificada."]);
    exit;
}

$accion = $data["accion"];
$tabla = $data["tabla"];
$response = procesarAccion($accion, $tabla, $data);

echo json_encode($response);
