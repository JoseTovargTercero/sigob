<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Definir divisor como una variable
$divisor = 12;


// Función para guardar en la tabla ejercicio_fiscal
function guardarEjercicioFiscal($ano, $situado, $divisor)
{
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($ano) || empty($situado)) {
            throw new Exception("Faltaron uno o más valores (ano, situado)");
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
function actualizarEjercicioFiscal($id, $ano, $situado, $divisor)
{
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id) || empty($ano) || empty($situado)) {
            throw new Exception("Faltaron uno o más valores (id, ano, situado)");
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
function eliminarEjercicioFiscal($id)
{
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

// Función para obtener todos los registros de la tabla ejercicio_fiscal (solo campos ano y situado)
function obtenerTodosEjerciciosFiscales()
{
    global $conexion;

    try {
        $sql = "SELECT id, ano, situado FROM ejercicio_fiscal";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $ejercicios = [];
            while ($row = $result->fetch_assoc()) {
                $ejercicios[] = $row;
            }
            return json_encode(["success" => $ejercicios]);
        } else {
            return json_encode(["success" => "No se encontraron registros en ejercicio_fiscal."]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para obtener un registro por ID (solo campos ano y situado)
function obtenerEjercicioFiscalPorId($id)
{
    global $conexion;

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para la consulta"]);
        }

        $sql = "SELECT ano, situado FROM ejercicio_fiscal WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $ejercicio = $result->fetch_assoc();
            return json_encode(["success" => $ejercicio]);
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
        if (empty($data["ano"]) || empty($data["situado"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (ano, situado)"]);
        } else {
            echo guardarEjercicioFiscal($data["ano"], $data["situado"], $divisor);
        }
    } elseif ($accion === "update") {
        if (empty($data["id"]) || empty($data["ano"]) || empty($data["situado"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (id, ano, situado)"]);
        } else {
            echo actualizarEjercicioFiscal($data["id"], $data["ano"], $data["situado"], $divisor);
        }
    } elseif ($accion === "delete") {
        if (empty($data["id"])) {
            echo json_encode(['error' => "Debe proporcionar un ID para eliminar"]);
        } else {
            echo eliminarEjercicioFiscal($data["id"]);
        }
    } elseif ($accion === "obtener_todos") {
        echo obtenerTodosEjerciciosFiscales();
    } elseif ($accion === "obtener_por_id") {
        if (empty($data["id"])) {
            echo json_encode(['error' => "Debe proporcionar un ID para la consulta"]);
        } else {
            echo obtenerEjercicioFiscalPorId($data["id"]);
        }
    } else {
        echo json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    echo json_encode(['error' => "No se especificó ninguna acción"]);
}
