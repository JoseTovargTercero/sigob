<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

function registrar($info){
    global $db;

    $actividad = $info['actividad'];
    $denominacion = $info['nombre'];

    // Definir los valores a insertar en el array asociativo
    $campos_valores = [
        ['actividad', $actividad, true],
        ['denominacion', $denominacion]
    ];

    // Intentar insertar el registro
    try {
        $resultado = $db->insert('pl_actividades', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizar($info)
{
    global $db;

    $nombre = $info['nombre'];
    $actividad = $info['actividad'];
    $id = $info['id'];

    // Array con los campos a actualizar: [campo, valor, tipo]
    $valores = [
        ['actividad', $actividad, 's'],
        ['denominacion', $nombre, 's']
    ];

    $where = "id = $id"; // Condición

    try {

        $resultado = $db->update('pl_actividades', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminar($id)
{
    global $db;
    $condicion_consulta = "actividad = $id"; // Condición para buscar registros

    $tablas = [
        ['tabla' => 'entes', 'condicion' => $condicion_consulta],
        ['tabla' => 'entes_dependencias', 'condicion' => $condicion_consulta],
    ];

    try {

        $totalCoincidencias = $db->comprobar_existencia($tablas);
        // Si hay coincidencias, no se puede eliminar
        if ($totalCoincidencias > 0) {
            return json_encode(['error' => 'No se puede eliminar el elemento, está en uso.']);
        }

        $condicion = "id = $id"; // Condición para eliminar registros
        $resultado = $db->delete('pl_actividades', $condicion);
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