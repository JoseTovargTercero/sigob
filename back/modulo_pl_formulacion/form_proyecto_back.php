<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);
$tabla_principal = 'pl_proyectos';

function registrar($info)
{
    global $db;
    global $tabla_principal;

    $proyecto = $info['proyecto'];
    $nombre = $info['nombre'];


    // Definir los valores a insertar en el array asociativo
    $campos_valores = [
        ['proyecto_id', $proyecto, true],
        ['denominacion', $nombre]
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
    $proyecto = $info['proyecto'];
    $id = $info['id'];

    $condicion_consulta = "proyecto_id = '$proyecto' AND id != $id"; // Condición para buscar registros
    $tablas = [['tabla' => $tabla_principal, 'condicion' => $condicion_consulta]];

    $totalCoincidencias = $db->comprobar_existencia($tablas);

    if ($totalCoincidencias === 0) {

        $valores = [
            ['proyecto_id', $proyecto],
            ['denominacion', $nombre]
        ];

        try {
            $where = "id = $id";
            $resultado = $db->update($tabla_principal, $valores, $where);
            return json_encode($resultado);
        } catch (Exception $e) {
            throw new Exception("Error: " . $e->getMessage());
        }
    } else {
        return json_encode(['error' => 'Ya existe un registro con los mismos datos']);
    }
}




function eliminar($id)
{
    global $db;
    global $tabla_principal;

    $condicion_consulta = "proyecto = $id"; // Condición para buscar registros

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

        $resultado = $db->delete($tabla_principal, "id = $id");
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
        $response = isset($data["data"]) ? registrar($data["data"]) : json_encode(["error" => "Datos de faltantes."]);
        break;
    case "actualizar":
        $response = isset($data["data"]) ? actualizar($data["data"]) : json_encode(["error" => "Datos faltantes."]);
        break;
    case "borrar":
        $response = isset($data['id']) ? eliminar($data['id']) : json_encode(["error" => "ID faltante."]);
        break;
    default:
        $response = json_encode(["error" => "Acción inválida."]);
}

echo $response;
