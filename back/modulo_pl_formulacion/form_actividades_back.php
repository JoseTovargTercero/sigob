<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';

$db = new DatabaseHandler($conexion);




// Función para insertar datos en plan_inversion y proyecto_inversion
function guardarEnte($proyectosArray)
{
    global $conexion;

    try {
        if (empty($proyectosArray)) {
            throw new Exception("El array de proyectos está vacío");
        }


        $sector = $proyectosArray['sector'];
        $programa = $proyectosArray['programa'];
        $proyecto = $proyectosArray['proyecto'];
        $actividad = $proyectosArray['actividad'];
        $nombre = $proyectosArray['nombre'];
        $tipo_ente = $proyectosArray['tipo_ente'];

        // verificar nombre
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_actividades` WHERE ente_nombre = ?");
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Ya existe un ente con el mismo nombre");
        }
        $stmt->close();



        $sql = "INSERT INTO entes (
        sector,
        programa,
        proyecto,
        actividad,
        ente_nombre,
        tipo_ente
        ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssss", $sector, $programa, $proyecto, $actividad, $nombre, $tipo_ente);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            $error = $stmt->error;
            throw new Exception("Error al insertar en la tabla proyecto_inversion. $error");
        }



        $stmt->close();

        return json_encode(["success" => "Datos guardados correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar datos en proyecto_inversion
function actualizar($info)
{
    global $db;

    $nombre = $info['nombre'];
    $actividad = $info['actividad'];
    $id = $info['id'];


    // falta validar la disponibilidad de nombre y actividad


    // Array con los campos a actualizar: [campo, valor, tipo]
    $valores = [
        ['actividad', $actividad, 's'],
        ['denominacion', $nombre, 's']
    ];


    $where = "id = $id"; // Condición

    try {

        $resultado = $db->update('pl_actividades', $valores, $where);
        echo json_encode($resultado);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
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
        $response = isset($data["unidad"]) ? guardarEnte($data["unidad"]) : ["error" => "Datos de 'unidad' faltantes."];
        break;

    case "actualizar":
        $response = isset($data["info"]) ? actualizar($data["info"]) : ["error" => "Datos faltantes."];
        break;

    case "borrar":
        $response = isset($data['id']) ? eliminar($data['id']) : ["error" => "ID faltante."]; //LISTO
        break;

    default:
        $response = ["error" => "Acción inválida."];
}

// Devolver la respuesta 
echo $response;
