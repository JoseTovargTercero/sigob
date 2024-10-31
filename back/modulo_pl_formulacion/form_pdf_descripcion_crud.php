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
    $tabla_principal = 'descripcion_programas';

    $valores = [
        ['id_sector', $info['id_sector'], 'i'],
        ['id_programa', $info['id_programa'], 'i'],
        ['descripcion', $info['descripcion'], 's']
    ];

    try {
        $where = "id = " . intval($info['id']);
        $resultado = $db->update($tabla_principal, $valores, $where);
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

function consultarInformacionPorId($id) {
    global $db;

    $id = intval($id);

    try {
        $query = "SELECT descripcion_programas.id, descripcion_programas.descripcion, 
                         pl_sectores.denominacion AS sector_denominacion, 
                         pl_programas.denominacion AS programa_denominacion
                  FROM descripcion_programas
                  JOIN pl_sectores ON descripcion_programas.id_sector = pl_sectores.id
                  JOIN pl_programas ON descripcion_programas.id_programa = pl_programas.id
                  WHERE descripcion_programas.id = $id";

        $resultado = $db->query($query);

        if (!empty($resultado)) {
            return $resultado;
        } else {
            return json_encode(['error' => 'Registro no encontrado.']);
        }
    } catch (Exception $e) {
        return json_encode(['error' => "Error: " . $e->getMessage()]);
    }
}

function consultarInformacionTodos() {
    global $db;

    try {
        $query = "SELECT descripcion_programas.id, descripcion_programas.descripcion, 
                         pl_sectores.denominacion AS sector_denominacion, 
                         pl_programas.denominacion AS programa_denominacion
                  FROM descripcion_programas
                  JOIN pl_sectores ON descripcion_programas.id_sector = pl_sectores.id
                  JOIN pl_programas ON descripcion_programas.id_programa = pl_programas.id";

        $resultado = $db->query($query);

        if (!empty($resultado)) {
            return $resultado;
        } else {
            return json_encode(['error' => 'No se encontraron registros.']);
        }
    } catch (Exception $e) {
        return json_encode(['error' => "Error: " . $e->getMessage()]);
    }
}

// Nueva función para consultar la tabla pl_sectores
function consultarPlSectores() {
    global $db;

    try {
        $query = "SELECT * FROM pl_sectores";
        $resultado = $db->query($query);

        if (!empty($resultado)) {
            return $resultado;
        } else {
            return json_encode(['error' => 'Sector no encontrado.']);
        }
    } catch (Exception $e) {
        return json_encode(['error' => "Error: " . $e->getMessage()]);
    }
}

// Nueva función para consultar la tabla pl_programas
function consultarPlProgramas() {
    global $db;

    try {
        $query = "SELECT * FROM pl_programas";
        $resultado = $db->query($query);

        if (!empty($resultado)) {
            return $resultado;
        } else {
            return json_encode(['error' => 'Programa no encontrado.']);
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
                $response = isset($data['id']) ? consultarInformacionPorId($data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarInformacionTodos();
                break;
            case "consultar_sector":
                $response =  consultarPlSectores() ;
                break;
            case "consultar_programa":
                $response = consultarPlProgramas();
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
