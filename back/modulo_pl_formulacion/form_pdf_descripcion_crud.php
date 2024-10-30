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
function consultarInformacionPorId($tabla, $id) {
    global $db;
    
    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->select($tabla, "*", $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function consultarInformacionTodos($tabla) {
    global $db;

    try {
        $resultado = $db->select($tabla, "*");
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

// Procesar solicitud según tabla y acción especificada
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
            case "consultar_por_id":
                $response = isset($data['id']) ? consultarInformacionPorId('descripcion_programas', $data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarInformacionTodos('descripcion_programas');
                break;
            default:
                $response = ["error" => "Acción inválida."];
        }
        break;

    default:
        $response = ["error" => "Tabla inválida."];
}

echo json_encode($response);
?>
