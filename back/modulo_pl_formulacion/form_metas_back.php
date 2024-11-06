<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);


function registrar($info)
{
    global $db;

    $programa = $info['programa'];
    $denominacion = $info['denominacion'];
    $unidad_medida = $info['unidad_medida'];
    $cantidades = $info['cantidades'];
    $costo = $info['costo'];
    $id_ejercicio = $info['id_ejercicio'];

    // Definir los valores a insertar en el array asociativo
    $campos_valores = [
        ['programa', $programa, true],
        ['meta', $denominacion],
        ['unidad_medida', $unidad_medida],
        ['cantidad', $cantidades],
        ['costo', $costo],
        ['id_ejercicio', $id_ejercicio, true]
    ];

    // Intentar insertar el registro
    try {
        $resultado = $db->insert('pl_metas', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}


function actualizar($info)
{
    global $db;

    $programa = $info['programa'];
    $denominacion = $info['denominacion'];
    $unidad_medida = $info['unidad_medida'];
    $cantidades = $info['cantidades'];
    $costo = $info['costo'];
    $id = $info['id'];


    $valores = [
        ['programa', $programa],
        ['meta', $denominacion],
        ['unidad_medida', $unidad_medida],
        ['cantidad', $cantidades],
        ['costo', $costo],
        ['id', $id]
    ];

    $where = "id = $id"; // Condición

    try {
        $resultado = $db->update('pl_metas', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminar($id)
{
    global $db;
    try {
        $condicion = "id = $id"; // Condición para eliminar registros
        $resultado = $db->delete('pl_metas', $condicion);
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
