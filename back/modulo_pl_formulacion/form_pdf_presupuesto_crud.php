<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

function registrarInformacionPersona($info) {
    global $db;

    $campos_valores = [
        ['nombres', $info['nombres'], true],
        ['cargo', $info['cargo'], true]
    ];

    try {
        $resultado = $db->insert('informacion_personas', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizarInformacionPersona($info) {
    global $db;

    $valores = [
        ['nombres', $info['nombres'], 's'],
        ['cargo', $info['cargo'], 's']
    ];

    $where = "id = " . intval($info['id']);

    try {
        $resultado = $db->update('informacion_personas', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminarInformacionPersona($id) {
    global $db;

    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->delete('informacion_personas', $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function registrarTitulo1($info) {
    global $db;

    $campos_valores = [
        ['articulo', $info['articulo'], true],
        ['descripcion', $info['descripcion'], true]
    ];

    try {
        $resultado = $db->insert('titulo_1', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizarTitulo1($info) {
    global $db;

    $valores = [
        ['articulo', $info['articulo'], 's'],
        ['descripcion', $info['descripcion'], 's']
    ];

    $where = "id = " . intval($info['id']);

    try {
        $resultado = $db->update('titulo_1', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminarTitulo1($id) {
    global $db;

    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->delete('titulo_1', $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}
function consultarInformacionPorId($tabla, $id) {
    global $db;
    
    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->select("*", $tabla, $condicion);
        
        if (!empty($resultado)) {
            // Devolver el resultado directamente, ya que cada campo es dinámico
            return $resultado;
        } else {
            return json_encode(['error' => 'Registro no encontrado.']);
        }
    } catch (Exception $e) {
        return json_encode(['error' => "Error: " . $e->getMessage()]);
    }
}

function consultarInformacionTodos($tabla) {
    global $db;

    try {
        $resultado = $db->select("*", $tabla);
        
        if (!empty($resultado)) {
            // Devolver todos los resultados directamente, sin especificar campos
            return $resultado;
        } else {
            return json_encode(['error' => 'No se encontraron registros.']);
        }
    } catch (Exception $e) {
        return json_encode(['error' => "Error: " . $e->getMessage()]);
    }
}

// Procesar solicitud según tabla y acción especificada
switch ($data["tabla"]) {
    case 'informacion_personas':
        switch ($accion) {
            case "registrar":
                $response = isset($data["info"]) ? registrarInformacionPersona($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "actualizar":
                $response = isset($data["info"]) ? actualizarInformacionPersona($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "borrar":
                $response = isset($data['id']) ? eliminarInformacionPersona($data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_por_id":
                $response = isset($data['id']) ? consultarInformacionPorId('informacion_personas', $data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarInformacionTodos('informacion_personas');
                break;
            default:
                $response = ["error" => "Acción inválida."];
        }
        break;

    case 'titulo_1':
        switch ($accion) {
            case "registrar":
                $response = isset($data["info"]) ? registrarTitulo1($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "actualizar":
                $response = isset($data["info"]) ? actualizarTitulo1($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "borrar":
                $response = isset($data['id']) ? eliminarTitulo1($data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_por_id":
                $response = isset($data['id']) ? consultarInformacionPorId('titulo_1', $data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarInformacionTodos('titulo_1');
                break;
            default:
                $response = ["error" => "Acción inválida."];
        }
        break;

    default:
        $response = ["error" => "Tabla inválida."];
}

echo $response;
?>
