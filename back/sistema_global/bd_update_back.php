<?php

require_once 'conexion.php';
require_once 'session.php';
require_once 'errores.php';
require_once 'DatabaseHandler.php';
$db = new DatabaseHandler($conexion);


function registrar($id)
{
    global $db;

    // Definir los valores a insertar en el array asociativo
    $campos_valores = [
        ['actualizacion', $id, true]
    ];

    // Intentar insertar el registro
    try {
        $resultado = $db->insert('system_bd', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}



function ejecutar($id, $qry) {
    global $conexion;

    try {
        // Intentar ejecutar la consulta completa
        if ($conexion->multi_query($qry)) {
            do {
                /* almacenar el primer resultado si existe */
                if ($result = $conexion->store_result()) {
                    $result->free();
                }
                /* Prepararse para el siguiente resultado en caso de que haya múltiples */
            } while ($conexion->next_result());
            
            return json_encode(["success" => "Consulta ejecutada con éxito."]);
        } else {
            throw new Exception("Error en la ejecución de la consulta: " . $conexion->error);
        }
    } catch (Exception $e) {
        // Enviar el mensaje de error en JSON
        return json_encode(["error" => $e->getMessage()]);
    }

}





header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$accion = $data["accion"];
$response = null;


switch ($accion) {
    case "salvar_ejecutado":
        $response = isset($data["id"]) ? registrar($data["id"]) : ["error" => "Datos de faltantes."];
        break;
    case "ejecutar":
        $response = isset($data["id"]) && isset($data["qry"]) ? ejecutar($data["id"], $data["qry"]) : ["error" => "Datos de faltantes."];
        break;

    default:
        $response = ["error" => "Acción inválida."];
}

echo $response;
