<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

function registrarDescripcionPrograma($info)
{
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

function actualizarDescripcionPrograma($info)
{
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

function eliminarDescripcionPrograma($id)
{
    global $db;

    $condicion = "id = " . intval($id);

    try {
        $resultado = $db->delete('descripcion_programas', $condicion);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}

// Función para consultar la información de un programa por ID
function consultarDescripcionProgramaPorId($id)
{
    global $conexion;

    try {
        if (empty($id)) {
            throw new Exception("Debe proporcionar un ID para la consulta.");
        }

        $sql = "SELECT descripcion_programas.id, descripcion_programas.descripcion, 
                       pl_sectores.denominacion AS sector_denominacion, 
                       pl_programas.denominacion AS programa_denominacion
                FROM descripcion_programas
                JOIN pl_sectores ON descripcion_programas.id_sector = pl_sectores.id
                JOIN pl_programas ON descripcion_programas.id_programa = pl_programas.id
                WHERE descripcion_programas.id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $programa = $result->fetch_assoc();
            return json_encode(["success" => $programa]);
        } else {
            return json_encode(["error" => "Registro no encontrado."]);
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para consultar todos los programas
function consultarDescripcionProgramasTodos()
{
    global $conexion;

    try {
        $sql = "SELECT descripcion_programas.id, descripcion_programas.descripcion, 
                       pl_sectores.denominacion AS sector_denominacion, 
                       pl_sectores.id as id_sector,
                       pl_sectores.sector as sector,
                       pl_programas.denominacion AS programa_denominacion,
                       pl_programas.id AS id_programa,
                       pl_programas.programa AS programa
                FROM descripcion_programas
                JOIN pl_sectores ON descripcion_programas.id_sector = pl_sectores.id
                JOIN pl_programas ON descripcion_programas.id_programa = pl_programas.id";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $programas = $result->fetch_all(MYSQLI_ASSOC);
            return json_encode(["success" => $programas]);
        } else {
            return json_encode(["success" => "No se encontraron registros en descripcion_programas."]);
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para consultar todos los sectores
function consultarPlSectores()
{
    global $conexion;

    try {
        $sql = "SELECT * FROM pl_sectores";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $sectores = $result->fetch_all(MYSQLI_ASSOC);
            return json_encode(["success" => $sectores]);
        } else {
            return json_encode(["success" => "No se encontraron registros en pl_sectores."]);
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para consultar todos los programas
function consultarPlProgramas()
{
    global $conexion;

    try {
        $sql = "SELECT * FROM pl_programas";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $programas = $result->fetch_all(MYSQLI_ASSOC);
            return json_encode(["success" => $programas]);
        } else {
            return json_encode(["success" => "No se encontraron registros en pl_programas."]);
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
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
                $response = isset($data['id']) ? consultarDescripcionProgramaPorId($data['id']) : ["error" => "ID faltante."];
                break;
            case "consultar_todos":
                $response = consultarDescripcionProgramasTodos();
                break;
            case "consultar_sector":
                $response = consultarPlSectores();
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

echo $response;
?>