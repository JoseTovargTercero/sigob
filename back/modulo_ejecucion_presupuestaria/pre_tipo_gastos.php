<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para insertar un nuevo tipo de gasto con id_sector
function registrarTipoGasto($nombre, $id_sector)
{
    global $conexion;
    if (empty($nombre) || empty($id_sector)) {
        return json_encode(['error' => "No puede registrar con campos vacíos"]);
    }

    try {
        // Registrar el nuevo tipo de gasto
        $sql = "INSERT INTO tipo_gastos (nombre, id_sector) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $nombre, $id_sector);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Tipo de gasto registrado correctamente"]);
        } else {
            throw new Exception("No se pudo registrar el tipo de gasto");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para consultar todos los tipos de gastos, incluyendo id_sector
function consultarTiposGastos()
{
    global $conexion;

    try {
        // Consultar todos los tipos de gastos
        $sql = "SELECT * FROM tipo_gastos";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $tipos_gastos = $result->fetch_all(MYSQLI_ASSOC); // Devuelve todos los resultados en un array asociativo
            return json_encode($tipos_gastos);
        } else {
            return json_encode(['error' => "No se encontraron tipos de gastos"]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para consultar un tipo de gasto por ID, incluyendo id_sector
function consultarTipoGastoPorId($id)
{
    global $conexion;

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para consultar"]);
        }

        // Consultar el tipo de gasto por ID
        $sql = "SELECT * FROM tipo_gastos WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $tipo_gasto = $result->fetch_assoc(); // Devuelve el resultado como un array asociativo
            return json_encode($tipo_gasto);
        } else {
            return json_encode(['error' => "No se encontró el tipo de gasto con el ID proporcionado"]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar un tipo de gasto, incluyendo id_sector
function actualizarTipoGasto($id, $nombre, $id_sector)
{
    global $conexion;

    try {
        // Verificar que no falte ningún campo
        if (empty($id) || empty($nombre) || empty($id_sector)) {
            return json_encode(['error' => "Debe rellenar todos los datos para actualizar"]);
        }

        // Actualizar el tipo de gasto
        $sql = "UPDATE tipo_gastos SET nombre = ?, id_sector = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sii", $nombre, $id_sector, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Tipo de gasto actualizado correctamente"]);
        } else {
            throw new Exception("No se pudo actualizar el tipo de gasto");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un tipo de gasto
function eliminarTipoGasto($id)
{
    global $conexion;

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para eliminar el tipo de gasto"]);
        }

        // Eliminar el tipo de gasto
        $sql = "DELETE FROM tipo_gastos WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Tipo de gasto eliminado correctamente"]);
        } else {
            throw new Exception("No se pudo eliminar el tipo de gasto");
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
    $nombre = $data["nombre"] ?? '';
    $id_sector = $data["id_sector"] ?? '';
    $id = $data["id"] ?? '';

    if ($accion === "insert") {
        $response = registrarTipoGasto($nombre, $id_sector);
    } elseif ($accion === "update") {
        $response = actualizarTipoGasto($id, $nombre, $id_sector);
    } elseif ($accion === "delete") {
        $response = eliminarTipoGasto($id);
    } elseif ($accion === "consultar_todos") {
        $response = consultarTiposGastos();
    } elseif ($accion === "consultar_id") {
        $response = consultarTipoGastoPorId($id);
    } else {
        $response = json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    $response = json_encode(['error' => "No se especificó ninguna acción"]);
}

echo $response;
?>
