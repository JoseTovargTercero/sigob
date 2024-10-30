<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

function registrarInformacionContraloria($info) {
    global $db;

    $campos_valores = [
        ['nombre_apellido_contralor', $info['nombre_apellido_contralor'], true],
        ['domicilio', $info['domicilio']],
        ['telefono', $info['telefono']],
        ['pagina_web', $info['pagina_web']],
        ['email', $info['email']]
    ];

    try {
        $resultado = $db->insert('informacion_contraloria', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizarInformacionContraloria($info) {
    global $db;

    $valores = [
        ['nombre_apellido_contralor', $info['nombre_apellido_contralor'], 's'],
        ['domicilio', $info['domicilio'], 's'],
        ['telefono', $info['telefono'], 's'],
        ['pagina_web', $info['pagina_web'], 's'],
        ['email', $info['email'], 's']
    ];
    
    $where = "id = " . intval($info['id']);

    try {
        $resultado = $db->update('informacion_contraloria', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminarInformacionContraloria($id) {
    global $db;

    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->delete('informacion_contraloria', $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}



function registrarInformacionGobernacion($info) {
    global $db;

    $campos_valores = [
        ['identificacion', $info['identificacion'], true],
        ['domicilio', $info['domicilio']],
        ['telefono', $info['telefono']],
        ['pagina_web', $info['pagina_web']],
        ['fax', $info['fax']],
        ['codigo_postal', $info['codigo_postal']],
        ['nombre_apellido_gobernador', $info['nombre_apellido_gobernador']]
    ];

    try {
        $resultado = $db->insert('informacion_gobernacion', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizarInformacionGobernacion($info) {
    global $db;

    $valores = [
        ['identificacion', $info['identificacion'], 's'],
        ['domicilio', $info['domicilio'], 's'],
        ['telefono', $info['telefono'], 's'],
        ['pagina_web', $info['pagina_web'], 's'],
        ['fax', $info['fax'], 's'],
        ['codigo_postal', $info['codigo_postal'], 's'],
        ['nombre_apellido_gobernador', $info['nombre_apellido_gobernador'], 's']
    ];
    
    $where = "id = " . intval($info['id']);

    try {
        $resultado = $db->update('informacion_gobernacion', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminarInformacionGobernacion($id) {
    global $db;

    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->delete('informacion_gobernacion', $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function registrarPersonalDirectivo($info) {
    global $db;

    $campos_valores = [
        ['direccion', $info['direccion'], true],
        ['nombre_apellido', $info['nombre_apellido']],
        ['email', $info['email']],
        ['telefono', $info['telefono']]
    ];

    try {
        $resultado = $db->insert('personal_directivo', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizarPersonalDirectivo($info) {
    global $db;

    $valores = [
        ['direccion', $info['direccion'], 's'],
        ['nombre_apellido', $info['nombre_apellido'], 's'],
        ['email', $info['email'], 's'],
        ['telefono', $info['telefono'], 's']
    ];
    
    $where = "id = " . intval($info['id']);

    try {
        $resultado = $db->update('personal_directivo', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminarPersonalDirectivo($id) {
    global $db;

    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->delete('personal_directivo', $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}


function registrarInformacionConsejo($info) {
    global $db;

    $campos_valores = [
        ['nombre_apellido_presidente', $info['nombre_apellido_presidente'], true],
        ['nombre_apellido_secretario', $info['nombre_apellido_secretario']],
        ['domicilio', $info['domicilio']],
        ['telefono', $info['telefono']],
        ['pagina_web', $info['pagina_web']],
        ['email', $info['email']],
        ['consejo_local', $info['consejo_local']]
    ];

    try {
        $resultado = $db->insert('informacion_consejo', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function actualizarInformacionConsejo($info) {
    global $db;

    $valores = [
        ['nombre_apellido_presidente', $info['nombre_apellido_presidente'], 's'],
        ['nombre_apellido_secretario', $info['nombre_apellido_secretario'], 's'],
        ['domicilio', $info['domicilio'], 's'],
        ['telefono', $info['telefono'], 's'],
        ['pagina_web', $info['pagina_web'], 's'],
        ['email', $info['email'], 's'],
        ['consejo_local', $info['consejo_local'], 's']
    ];
    
    $where = "id = " . intval($info['id']);

    try {
        $resultado = $db->update('informacion_consejo', $valores, $where);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

function eliminarInformacionConsejo($id) {
    global $db;

    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->delete('informacion_consejo', $condicion);
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
            return json_encode([$resultado]);
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
            return json_encode([$resultado]);
        } else {
            return json_encode(['error' => 'No se encontraron registros.']);
        }
    } catch (Exception $e) {
        return json_encode(['error' => "Error: " . $e->getMessage()]);
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

switch ($data["tabla"]) {
    case 'informacion_contraloria':
        switch ($accion) {
            case "registrar":
                $response = isset($data["info"]) ? registrarInformacionContraloria($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "actualizar":
                $response = isset($data["info"]) ? actualizarInformacionContraloria($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "borrar":
                $response = isset($data['id']) ? eliminarInformacionContraloria($data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_por_id":
                $response = isset($data['id']) ? consultarInformacionPorId('informacion_contraloria', $data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarInformacionTodos('informacion_contraloria');
                break;
            default:
                $response = ["error" => "Acción inválida."];
        }
        break;

    case 'informacion_gobernacion':
        switch ($accion) {
            case "registrar":
                $response = isset($data["info"]) ? registrarInformacionGobernacion($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "actualizar":
                $response = isset($data["info"]) ? actualizarInformacionGobernacion($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "borrar":
                $response = isset($data['id']) ? eliminarInformacionGobernacion($data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_por_id":
                $response = isset($data['id']) ? consultarInformacionPorId('informacion_gobernacion', $data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarInformacionTodos('informacion_gobernacion');
                break;
            default:
                $response = ["error" => "Acción inválida."];
        }
        break;

    case 'personal_directivo':
        switch ($accion) {
            case "registrar":
                $response = isset($data["info"]) ? registrarPersonalDirectivo($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "actualizar":
                $response = isset($data["info"]) ? actualizarPersonalDirectivo($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "borrar":
                $response = isset($data['id']) ? eliminarPersonalDirectivo($data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_por_id":
                $response = isset($data['id']) ? consultarInformacionPorId('personal_directivo', $data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarInformacionTodos('personal_directivo');
                break;
            default:
                $response = ["error" => "Acción inválida."];
        }
        break;

    case 'informacion_consejo':
        switch ($accion) {
            case "registrar":
                $response = isset($data["info"]) ? registrarInformacionConsejo($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "actualizar":
                $response = isset($data["info"]) ? actualizarInformacionConsejo($data["info"]) : ["error" => "Datos faltantes."];
                break;
            case "borrar":
                $response = isset($data['id']) ? eliminarInformacionConsejo($data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_por_id":
                $response = isset($data['id']) ? consultarInformacionPorId('informacion_consejo', $data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarInformacionTodos('informacion_consejo');
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