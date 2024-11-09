<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

function actualizar($info)
{
    global $db;

    // si el monto nuevo es mayor al monto anterior, se debe verificar la disponibilidad presupuestaria por la partida, sector, programa y actividad correspondiente

    // debe actualizar el monto_actual en la partida usada

    // debe actualizar el monto en la distribucion_entes corresponidente por el key (igual que en el delete)

    // debe actualiza el monto_total (restando o sumando la diferencia entre el monto nuevo-monto anterior)



}

function eliminar($id)
{
    global $db;
    // debe actualizar el monto de la asignacion total al ente (asignacion_ente.sql), restando la del monto que se esta eliminando 

    // debe actualizar el monto_actual en la partida usada

    // debe eliminar mediante la distribucion al ente usando update, con el key (0,1,2,3,etc) identificas la posición en el json y lo eliminas

    // debe actualiza el monto_total (restando lo eliminado)

    // regresas un json con success o una excepción 

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
    case "actualizar":
        $response = actualizar($data['id'], $data['monto'], $data['key'], $data['monto_nuevo']);
        break;
    case "borrar":
        $response = eliminar($data['id'], $data['monto'], $data['key']);
        break;
    default:
        $response = ["error" => "Acción inválida."];
}

echo $response;
