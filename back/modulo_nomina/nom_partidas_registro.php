<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

function validarCodigo($codigo) {
    // Validar que el código tenga el formato xxx.xx.xx.xx.xxxx
    return preg_match('/^\d{3}\.\d{2}\.\d{2}\.\d{2}\.\d{4}$/', $codigo);
}

function registrarPartida($codigo, $nombre, $descripcion) {
    global $conexion;

    try {
        // Verificar si el código ya existe en la tabla y si el status es igual a 0
        $sql = "SELECT * FROM partidas_presupuestarias WHERE codigo = ? AND status = 0";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return json_encode(['error' => "El código ya ha sido registrado anteriormente"]);
        }

        // Validar que el formato del código sea correcto
        if (!validarCodigo($codigo)) {
            return json_encode(['error' => "El formato del código no es válido"]);
        }

        // Si todo está correcto, proceder a insertar los datos
        $sql = "INSERT INTO partidas_presupuestarias (codigo, nombre, descripcion, status) VALUES (?, ?, ?, 0)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $codigo, $nombre, $descripcion);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response = json_encode(["success" => "Partida presupuestaria registrada correctamente"]);
        } else {
            $response = json_encode(['error' => "No se pudo registrar la partida presupuestaria"]);
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["codigo"]) && isset($data["nombre"]) && isset($data["descripcion"])) {
    $codigo = $data["codigo"];
    $nombre = $data["nombre"];
    $descripcion = $data["descripcion"];

    $response = registrarPartida($codigo, $nombre, $descripcion);
} else {
    $response = json_encode(['error' => "Datos incompletos"]);
}

echo $response;
