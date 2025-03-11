<?php

require_once '../sistema_global/conexion.php';

header('Content-Type: application/json');

include_once '../sistema_global/sigob_api.php';


function gestionarAsignaciones($data, $method)
{


    $params = $_GET;

    $paramsId = isset($params['id']) ? $params['id'] : null;
    $paramsEjercicioId = isset($params['id_ejercicio']) ? $params['id_ejercicio'] : null;

    $url = "https://sigob.net/sigob_entes/api/asignaciones";


    try {


        if ($method == 'GET') {

            if (!$paramsEjercicioId) {
                return ["error" => "Se necesita el ejercicio fiscal"];
            }

            if ($paramsId) {
                return apiGet("$url?id=$paramsId&id_ejercicio=$paramsEjercicioId");
            } else {
                return apiGet("$url?id_ejercicio=$paramsEjercicioId");
            }
        }

        if ($method == 'POST') {


            if (!isset($data['accion'])) {
                return json_encode(["error" => "No se ha especificado acción."]);
            }



            $accion = $data['accion'];

            return apiPost($url, $data);

        }
        //  else if ($method == 'POST') {
        //     $data = json_decode(file_get_contents("php://input"), true);

        //     if (!isset($data['accion'])) {
        //         return json_encode(["error" => "No se ha especificado acción."]);
        //     }

        //     $accion = $data['accion'];

        //     return apiPost($url, $data);

        // }
        return ['error' => "Método no soportado"];




    } catch (Exception $e) {
        registrarError($e->getMessage());
        return ['error' => $e->getMessage()];
    }
}



$postData = file_get_contents("php://input");
$data = json_decode($postData, true);
$method = $_SERVER['REQUEST_METHOD'];

if ($method || !$postData) { // Verifica si se decodificó correctamente el JSON
    $response = json_encode(gestionarAsignaciones($data, $method));
    echo $response;
}





?>