<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

require_once 'form_errores.php';

// Función para guardar en la tabla ejercicio_fiscal
function guardarEjercicioFiscal($ano, $situado, $divisor) {
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($ano) || empty($situado) || empty($divisor)) {
            throw new Exception("Faltaron uno o más valores (ano, situado, divisor)");
        }

        // Insertar los datos en la tabla
        $sql = "INSERT INTO ejercicio_fiscal (ano, situado, divisor) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $ano, $situado, $divisor);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Datos de ejercicio fiscal guardados correctamente"]);
        } else {
            throw new Exception("No se pudo guardar los datos de ejercicio fiscal");
        }

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar los datos en la tabla ejercicio_fiscal
function actualizarEjercicioFiscal($id, $ano, $situado, $divisor) {
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id) || empty($ano) || empty($situado) || empty($divisor)) {
            throw new Exception("Faltaron uno o más valores (id, ano, situado, divisor)");
        }

        // Actualizar los datos en la tabla
        $sql = "UPDATE ejercicio_fiscal SET ano = ?, situado = ?, divisor = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $ano, $situado, $divisor, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Datos de ejercicio fiscal actualizados correctamente"]);
        } else {
            throw new Exception("No se pudo actualizar los datos de ejercicio fiscal");
        }

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un registro en la tabla ejercicio_fiscal
function eliminarEjercicioFiscal($id) {
    global $conexion;

    try {
        // Validar que el ID no esté vacío
        if (empty($id)) {
            throw new Exception("Debe proporcionar un ID para eliminar");
        }

        // Eliminar el registro de la tabla
        $sql = "DELETE FROM ejercicio_fiscal WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Datos de ejercicio fiscal eliminados correctamente"]);
        } else {
            throw new Exception("No se pudo eliminar el registro de ejercicio fiscal");
        }

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    if ($accion === "insert") {
        if (empty($data["ano"]) || empty($data["situado"]) || empty($data["divisor"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (ano, situado, divisor)"]);
        } else {
            echo guardarEjercicioFiscal($data["ano"], $data["situado"], $data["divisor"]);
        }
    } elseif ($accion === "update") {
        if (empty($data["id"]) || empty($data["ano"]) || empty($data["situado"]) || empty($data["divisor"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (id, ano, situado, divisor)"]);
        } else {
            echo actualizarEjercicioFiscal($data["id"], $data["ano"], $data["situado"], $data["divisor"]);
        }
    } elseif ($accion === "delete") {
        if (empty($data["id"])) {
            echo json_encode(['error' => "Debe proporcionar un ID para eliminar"]);
        } else {
            echo eliminarEjercicioFiscal($data["id"]);
        }
    } else {
        echo json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    echo json_encode(['error' => "No se especificó ninguna acción"]);
}

