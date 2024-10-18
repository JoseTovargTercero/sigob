<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para guardar un nuevo sector presupuestario
function guardarSectorPresupuestario($sector, $programa, $proyecto, $nombre) {
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($sector) || empty($programa) || empty($proyecto) || empty($nombre)) {
            throw new Exception("Faltaron uno o más valores (sector, programa, proyecto, nombre)");
        }

        // Validar que sector, programa y proyecto sean de 2 dígitos
        if (!preg_match('/^\d{2}$/', $sector) || !preg_match('/^\d{2}$/', $programa) || !preg_match('/^\d{2}$/', $proyecto)) {
            throw new Exception("El formato de sector, programa y proyecto debe ser de 2 dígitos.");
        }

        // Verificar si ya existe un registro con la misma combinación de sector, programa y proyecto
        $sql = "SELECT id FROM pl_sectores_presupuestarios WHERE sector = ? AND programa = ? AND proyecto = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $sector, $programa, $proyecto);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            throw new Exception("Ya existe un registro con la misma combinación de sector, programa y proyecto.");
        }
        
        // Insertar el nuevo sector presupuestario
        $sql = "INSERT INTO pl_sectores_presupuestarios (sector, programa, proyecto, nombre) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $sector, $programa, $proyecto, $nombre);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Sector presupuestario guardado correctamente."]);
        } else {
            throw new Exception("No se pudo guardar el sector presupuestario.");
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar un sector presupuestario existente
function actualizarSectorPresupuestario($id, $sector, $programa, $proyecto, $nombre) {
    global $conexion;

    try {
        if (empty($id) || empty($sector) || empty($programa) || empty($proyecto) || empty($nombre)) {
            throw new Exception("Faltaron uno o más valores (id, sector, programa, proyecto, nombre)");
        }

        // Validar que sector, programa y proyecto sean de 2 dígitos
        if (!preg_match('/^\d{2}$/', $sector) || !preg_match('/^\d{2}$/', $programa) || !preg_match('/^\d{2}$/', $proyecto)) {
            throw new Exception("El formato de sector, programa y proyecto debe ser de 2 dígitos.");
        }

        // Verificar si ya existe un registro con la misma combinación de sector, programa y proyecto
        $sql = "SELECT id FROM pl_sectores_presupuestarios WHERE sector = ? AND programa = ? AND proyecto = ? AND id != ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $sector, $programa, $proyecto, $id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            throw new Exception("Ya existe un registro con la misma combinación de sector, programa y proyecto.");
        }

        // Actualizar el sector presupuestario
        $sql = "UPDATE pl_sectores_presupuestarios SET sector = ?, programa = ?, proyecto = ?, nombre = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssi", $sector, $programa, $proyecto, $nombre, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Sector presupuestario actualizado correctamente."]);
        } else {
            throw new Exception("No se pudo actualizar el sector presupuestario.");
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un sector presupuestario
function eliminarSectorPresupuestario($id) {
    global $conexion;

    try {
        if (empty($id)) {
            throw new Exception("Debe proporcionar un ID para eliminar.");
        }

        // Eliminar el sector presupuestario
        $sql = "DELETE FROM pl_sectores_presupuestarios WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Sector presupuestario eliminado correctamente."]);
        } else {
            throw new Exception("No se pudo eliminar el sector presupuestario.");
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para obtener todos los sectores presupuestarios
function obtenerTodosSectoresPresupuestarios() {
    global $conexion;

    try {
        $sql = "SELECT id, sector, programa, proyecto, nombre FROM pl_sectores_presupuestarios";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $sectores = $result->fetch_all(MYSQLI_ASSOC);
            return json_encode(["success" => $sectores]);
        } else {
            return json_encode(["success" => "No se encontraron registros en pl_sectores_presupuestarios."]);
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para obtener un sector presupuestario por su ID
function obtenerSectorPresupuestarioPorId($id) {
    global $conexion;

    try {
        if (empty($id)) {
            throw new Exception("Debe proporcionar un ID para la consulta.");
        }

        $sql = "SELECT id, sector, programa, proyecto, nombre FROM pl_sectores_presupuestarios WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $sector = $result->fetch_assoc();
            return json_encode(["success" => $sector]);
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
        echo guardarSectorPresupuestario($data["sector"], $data["programa"], $data["proyecto"], $data["nombre"]);
    } elseif ($accion === "update") {
        echo actualizarSectorPresupuestario($data["id"], $data["sector"], $data["programa"], $data["proyecto"], $data["nombre"]);
    } elseif ($accion === "delete") {
        echo eliminarSectorPresupuestario($data["id"]);
    } elseif ($accion === "obtener_todos") {
        echo obtenerTodosSectoresPresupuestarios();
    } elseif ($accion === "obtener_por_id") {
        echo obtenerSectorPresupuestarioPorId($data["id"]);
    } else {
        echo json_encode(['error' => "Acción no aceptada."]);
    }
} else {
    echo json_encode(['error' => "No se especificó ninguna acción."]);
}

?>
