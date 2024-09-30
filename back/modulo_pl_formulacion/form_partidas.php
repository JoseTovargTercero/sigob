<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para validar el formato del código
function validarCodigo($partida)
{
    // Valida el formato xx.xx.si.xxx.xx.xx.xxxx
    return preg_match('/^\d{2}\.\d{2}\.\d{2}\.\d{3}\.\d{2}\.\d{2}\.\d{4}$/', $partida);
}

// Función para insertar una nueva partida
function registrarPartida($partida, $nombre, $descripcion)
{
    global $conexion;
    if (empty($partida) || empty($nombre) || empty($descripcion)) {
        return json_encode(['error' => "No puede registrar con campos vacíos"]);
    }

    try {
        // Verificar si el código ya existe y si status es 0
        $sql = "SELECT * FROM partidas_presupuestarias WHERE partida = ? AND status = 0";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $partida);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return json_encode(['error' => "El código ya ha sido registrado anteriormente"]);
        }

        // Validar el formato del código
        if (!validarCodigo($partida)) {
            return json_encode(['error' => "El formato del código no es válido"]);
        }

        // Registrar la nueva partida
        $sql = "INSERT INTO partidas_presupuestarias (partida, nombre, descripcion, status) VALUES (?, ?, ?, 0)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $partida, $nombre, $descripcion);
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

// Función para consultar todas las partidas presupuestarias
function consultarPartidas()
{
    global $conexion;

    try {
        // Consultar todas las partidas presupuestarias
        $sql = "SELECT * FROM partidas_presupuestarias";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $partidas = $result->fetch_all(MYSQLI_ASSOC); // Devuelve todos los resultados en un array asociativo
            return json_encode(['success' => $partidas]);
        } else {
            return json_encode(['error' => "No se encontraron partidas presupuestarias"]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}
// Función para consultar una partida presupuestaria por ID
function consultarPartidaPorId($id)
{
    global $conexion;

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para consultar"]);
        }

        // Consultar la partida por ID
        $sql = "SELECT * FROM partidas_presupuestarias WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $partida = $result->fetch_assoc(); // Devuelve el resultado como un array asociativo
            return json_encode(['success' => $partida]);
        } else {
            return json_encode(['error' => "No se encontró la partida presupuestaria con el ID proporcionado"]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Función para actualizar una partida
function actualizarPartida($id, $partida, $nombre, $descripcion)
{
    global $conexion;

    try {
        // Verificar que no falte ningún campo
        if (empty($id) || empty($partida) || empty($nombre) || empty($descripcion)) {
            return json_encode(['error' => "Debe rellenar todos los datos para actualizar"]);
        }

        // Validar el formato del código
        if (!validarCodigo($partida)) {
            return json_encode(['error' => "El formato del código no es válido"]);
        }

        // Actualizar la partida
        $sql = "UPDATE partidas_presupuestarias SET partida = ?, nombre = ?, descripcion = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $partida, $nombre, $descripcion, $id);
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
function eliminarPartida($id)
{
    global $conexion;

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para eliminar la partida"]);
        }

        // Verificar el estado de la partida
        $sql = "SELECT status FROM partidas_presupuestarias WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();

        // Si el status es 1, no permitir la eliminación
        if ($status == 1) {
            return json_encode(['error' => "No se puede eliminar una partida, si ya esta siendo utilizada."]);
        }

        // Si el status no es 1, proceder con la eliminación
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
    $partida = $data["partida"] ?? '';
    $nombre = $data["nombre"] ?? '';
    $descripcion = $data["descripcion"] ?? '';
    $id = $data["id"] ?? '';

    if ($accion === "insert") {
        $response = registrarPartida($partida, $nombre, $descripcion);
    } elseif ($accion === "update") {
        $response = actualizarPartida($id, $partida, $nombre, $descripcion);
    } elseif ($accion === "delete") {
        $response = eliminarPartida($id);
    } elseif ($accion === "consultar") {
        $response = consultarPartidas();
    } elseif ($accion === "consultar_id") {
        $response = consultarPartidaPorId($id);
    } else {
        $response = json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    $response = json_encode(['error' => "No se especificó ninguna acción"]);
}

echo $response;

