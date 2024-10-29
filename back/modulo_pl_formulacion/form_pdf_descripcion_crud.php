<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

function registrarDescripcionPrograma($info) {
    global $db;

    $campos_valores = [
        ['id_sector', $info['id_sector'], true],
        ['id_programa', $info['id_programa'], true],
        ['descripcion', $info['descripcion'], true]
    ];

    try {
        $resultado = $db->insert('descripcion_programas', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizarDescripcionPrograma($info) {
    global $db;

    $valores = [
        ['id_sector', $info['id_sector'], 'i'],
        ['id_programa', $info['id_programa'], 'i'],
        ['descripcion', $info['descripcion'], 's']
    ];
    
    $where = "id = " . intval($info['id']);

    try {
        $resultado = $db->update('descripcion_programas', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminarDescripcionPrograma($id) {
    global $db;

    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->delete('descripcion_programas', $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}


switch ($data["tabla"]) {
    case 'descripcion_programas':
        switch ($accion) {
            case "registrar":
                $response = isset($data["info"]) ? registrarDescripcionPrograma($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "actualizar":
                $response = isset($data["info"]) ? actualizarDescripcionPrograma($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "borrar":
                $response = isset($data['id']) ? eliminarDescripcionPrograma($data['id']) : ["error" => "ID faltante."];
                break;
            default:
                $response = ["error" => "Acción inválida."];
        }
        break;

    // Agrega las demás tablas según sea necesario
    default:
        $response = ["error" => "Tabla inválida."];
}
