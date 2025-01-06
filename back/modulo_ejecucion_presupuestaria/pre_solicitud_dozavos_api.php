<?php

require_once '../sistema_global/conexion.php';

require_once '../sistema_global/notificaciones.php';
header('Content-Type: application/json');
require_once '../sistema_global/errores.php';
require_once 'pre_compromisos.php';
require_once 'pre_dispo_presupuestaria.php'; // Agregado
// Función para gestionar la solicitud y compromisos

function gestionarSolicitudDozavos($data)
{
    $url = "https://sigob.net/api/solicitudes";
    global $conexion;

    try {
        if (!isset($data['accion'])) {
            return json_encode(["error" => "No se ha especificado acción."]);
        }

        $accion = $data['accion'];

        // Acción: Consultar todos los registros
        if ($accion === 'consulta') {

            return apiGet($url);
        }

        if ($accion === 'consulta_id') {
            if (!isset($data['id'])) {
                throw new Exception("No se ha colocado el id a buscar");
            }

            $id = $data['id'];


            return apiGet("$url?id=$id");
        }

        if ($accion === 'registrar') {
            return apiPost($url, $data);
        }

        if ($accion === 'gestionar') {
            return apiPost($url, $data);
        }



    } catch (Exception $e) {
        registrarError($e->getMessage());
        return ['error' => $e->getMessage()];
    }
}


// Ejemplos de uso:

// // Petición GET (consulta)
// $urlConsulta = 'https://sigob.net/api/solicitudes?accion=consulta';
// $resultadoConsulta = ejecutarPeticionGet($urlConsulta);


// // Petición GET (consulta_id)
// $idConsulta = 123;
// $urlConsultaId = 'https://sigob.net/api/solicitudes?accion=consulta_id&id=' . $idConsulta;
// $resultadoConsultaId = ejecutarPeticionGet($urlConsultaId);


// // Petición POST (otra_accion)
// $accionPost = 'alguna_otra_accion';
// $datosPost = array('campo1' => 'valor1', 'campo2' => 'valor2');
// $dataCompletaPost = array('accion' => $accionPost);
// $dataCompletaPost = array_merge($dataCompletaPost, $datosPost);
// $urlPost = 'https://sigob.net/api/solicitudes';
// $resultadoPost = ejecutarPeticionPostJson($urlPost, $dataCompletaPost);





function apiGet($url)
{
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $headers = array(
            'Content-Type: application/json', // Indica que se envía JSON
            'Authorization: 123456789abcdef', // Encabezado de autorización
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $respuesta = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Error en curl: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            throw new Exception("Error HTTP: " . $httpCode . "\nRespuesta del servidor: " . $respuesta);
        }

        curl_close($ch);

        $datos = json_decode($respuesta, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
        }

        if (isset($data["error"])) {
            throw new Exception($datos['error']);
        }

        return array("success" => $datos['success']);

    } catch (Exception $e) {
        return array("error" => $e->getMessage());
    }
}

function apiPost($url, $data)
{
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers = array(
            'Content-Type: application/json', // Indica que se envía JSON
            'Authorization: 123456789abcdef', // Encabezado de autorización
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        $respuesta = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Error en curl: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            throw new Exception("Error HTTP: " . $httpCode . "\nRespuesta del servidor: " . $respuesta);
        }
        curl_close($ch);
        $datos = json_decode($respuesta, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
        }

        if (isset($data["error"])) {
            throw new Exception($datos['error']);
        }

        return ["success" => $datos['success']];

    } catch (Exception $e) {
        return ["error" => $e->getMessage()];
    }
}


$data = json_decode(file_get_contents("php://input"), true);
echo json_encode(gestionarSolicitudDozavos($data));

?>