<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

require_once 'form_errores.php';

// Función para validar el formato del código
function validarCodigo($codigo) {
    // Valida el formato xx.xx.si.xxx.xx.xx.xxxx
    return preg_match('/^\d{2}\.\d{2}\.si\.\d{3}\.\d{2}\.\d{2}\.\d{4}$/', $codigo);
}

// Función para insertar una nueva partida
function registrarPartida($codigo, $nombre, $descripcion) {
    global $conexion;

    try {
        // Verificar si el código ya existe y si status es 0
        $sql = "SELECT * FROM partidas_presupuestarias WHERE codigo = ? AND status = 0";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return json_encode(['error' => "El código ya ha sido registrado anteriormente"]);
        }

        // Validar el formato del código
        if (!validarCodigo($codigo)) {
            return json_encode(['error' => "El formato del código no es válido"]);
        }

        // Registrar la nueva partida
        $sql = "INSERT INTO partidas_presupuestarias (codigo, nombre, descripcion, status) VALUES (?, ?, ?, 0)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $codigo, $nombre, $descripcion);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Partida presupuestaria registrada correctamente"]);
        } else {
            throw new Exception("No se pudo registrar la partida presupuestaria");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar una partida
function actualizarPartida($id, $codigo, $nombre, $descripcion) {
    global $conexion;

    try {
        // Verificar que no falte ningún campo
        if (empty($id) || empty($codigo) || empty($nombre) || empty($descripcion)) {
            return json_encode(['error' => "Debe rellenar todos los datos para actualizar"]);
        }

        // Validar el formato del código
        if (!validarCodigo($codigo)) {
            return json_encode(['error' => "El formato del código no es válido"]);
        }

        // Actualizar la partida
        $sql = "UPDATE partidas_presupuestarias SET codigo = ?, nombre = ?, descripcion = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $codigo, $nombre, $descripcion, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Partida presupuestaria actualizada correctamente"]);
        } else {
            throw new Exception("No se pudo actualizar la partida presupuestaria");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar una partida
function eliminarPartida($id) {
    global $conexion;

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para eliminar la partida"]);
        }

        // Eliminar la partida
        $sql = "DELETE FROM partidas_presupuestarias WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Partida presupuestaria eliminada correctamente"]);
        } else {
            throw new Exception("No se pudo eliminar la partida presupuestaria");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la petición
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];
    $codigo = $data["codigo"] ?? '';
    $nombre = $data["nombre"] ?? '';
    $descripcion = $data["descripcion"] ?? '';
    $id = $data["id"] ?? '';

    if ($accion === "insert") {
        $response = registrarPartida($codigo, $nombre, $descripcion);
    } elseif ($accion === "update") {
        $response = actualizarPartida($id, $codigo, $nombre, $descripcion);
    } elseif ($accion === "delete") {
        $response = eliminarPartida($id);
    } else {
        $response = json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    $response = json_encode(['error' => "No se especificó ninguna acción"]);
}

echo $response;
