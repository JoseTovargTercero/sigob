<?php

require_once '../sistema_global/conexion.php';

header('Content-Type: application/json');


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

        if (array_key_exists('error', $datos)) {
            return ['status' => 200, 'error' => $datos['error']];
        } else {
            return ['status' => 200, 'success' => $datos['success']];
        }

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

        if (isset($datos["error"])) {
            throw new Exception($datos['error']);
        }

        return ["success" => $datos['success']];

    } catch (Exception $e) {
        return ["error" => $e->getMessage()];
    }
}

function consultarDisponibilidadApi($distribuciones, $id_ejercicio)
{
    $data = ["accion" => "consultar_disponibilidad", "distribuciones" => $distribuciones, "id_ejercicio" => $id_ejercicio];
    $url = "https://sigob.net/api/asignaciones";

    $response = apiPost($url, $data);

    return $response;
}
