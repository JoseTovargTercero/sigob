<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para guardar un nuevo ente
function guardarEnte($ente_nombre, $tipo_ente)
{
    global $conexion;

    try {
        if (empty($ente_nombre) || empty($tipo_ente)) {
            throw new Exception("Faltaron uno o más valores (ente_nombre, tipo_ente)");
        }

        $sql = "INSERT INTO entes (ente_nombre, tipo_ente) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $ente_nombre, $tipo_ente);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Ente guardado correctamente"]);
        } else {
            throw new Exception("No se pudo guardar el ente");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar un ente existente
function actualizarEnte($id, $ente_nombre, $tipo_ente)
{
    global $conexion;

    try {
        if (empty($id) || empty($ente_nombre) || empty($tipo_ente)) {
            throw new Exception("Faltaron uno o más valores (id, ente_nombre, tipo_ente)");
        }

        $sql = "UPDATE entes SET ente_nombre = ?, tipo_ente = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssi", $ente_nombre, $tipo_ente, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Ente actualizado correctamente"]);
        } else {
            throw new Exception("No se pudo actualizar el ente");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un ente
function eliminarEnte($id)
{
    global $conexion;
    $user_id = $_SESSION['u_id']; // Obtener el user_id de la sesión actual

    try {
        if (empty($id)) {
            throw new Exception("Debe proporcionar un ID para eliminar");
        }

        // Eliminar el registro en entes
        $sql = "DELETE FROM entes WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Insertar un registro en audit_logs después de la eliminación
            $sqlAudit = "INSERT INTO audit_logs (action_type, table_name, situation, affected_rows, user_id, timestamp) 
                         VALUES (?, ?, ?, ?, ?, NOW())";
            $stmtAudit = $conexion->prepare($sqlAudit);
            $action_type = 'DELETE';
            $table_name = 'entes';
            $situation = "id=$id";
            $affected_rows = $stmt->affected_rows;
            $stmtAudit->bind_param("sssii", $action_type, $table_name, $situation, $affected_rows, $user_id);
            $stmtAudit->execute();

            return json_encode(["success" => "Ente eliminado correctamente"]);
        } else {
            throw new Exception("No se pudo eliminar el ente");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Función para obtener todos los entes
function obtenerTodosEntes()
{
    global $conexion;

    try {
        $sql = "SELECT id, ente_nombre, tipo_ente FROM entes";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $entes = [];
            while ($row = $result->fetch_assoc()) {
                $entes[] = $row;
            }
            return json_encode(["success" => $entes]);
        } else {
            return json_encode(["success" => "No se encontraron registros en entes."]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para obtener un ente por su ID
function obtenerEntePorId($id)
{
    global $conexion;

    try {
        if (empty($id)) {
            throw new Exception("Debe proporcionar un ID para la consulta");
        }

        $sql = "SELECT id, sector, programa, proyecto, actividad, ente_nombre, tipo_ente FROM entes WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $ente = $result->fetch_assoc();
            return json_encode(["success" => $ente]);
        } else {
            return json_encode(["error" => "No se encontró un registro con el ID proporcionado."]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    if ($accion === "insert") {
        if (empty($data["ente_nombre"]) || empty($data["tipo_ente"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (ente_nombre, tipo_ente)"]);
        } else {
            echo guardarEnte($data["ente_nombre"], $data["tipo_ente"]);
        }
    } elseif ($accion === "update") {
        if (empty($data["id"]) || empty($data["ente_nombre"]) || empty($data["tipo_ente"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (id, ente_nombre, tipo_ente)"]);
        } else {
            echo actualizarEnte($data["id"], $data["ente_nombre"], $data["tipo_ente"]);
        }
    } elseif ($accion === "delete") {
        if (empty($data["id"])) {
            echo json_encode(['error' => "Debe proporcionar un ID para eliminar"]);
        } else {
            echo eliminarEnte($data["id"]);
        }
    } elseif ($accion === "obtener") {
        echo obtenerTodosEntes();
    } elseif ($accion === "obtener_por_id") {
        if (empty($data["id"])) {
            echo json_encode(['error' => "Debe proporcionar un ID para la consulta"]);
        } else {
            echo obtenerEntePorId($data["id"]);
        }
    } else {
        echo json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    echo json_encode(['error' => "No se especificó ninguna acción"]);
}

?>