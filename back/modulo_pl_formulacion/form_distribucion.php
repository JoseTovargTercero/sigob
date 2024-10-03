<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/errores.php';

// Función para insertar datos en la tabla distribucion_presupuestaria
function guardarDistribucionPresupuestaria($dataArray) {
    global $conexion;

    try {
        // Verificar que el array no esté vacío
        if (empty($dataArray)) {
            throw new Exception("El array de datos está vacío");
        }

        foreach ($dataArray as $registro) {
            if (count($registro) !== 3) {
                throw new Exception("El formato del array no es válido");
            }

            $id_partida = $registro[0];
            $monto_inicial = $registro[1];
            $id_ejercicio = $registro[2];

            // Validar que no existan duplicados con el mismo id_partida e id_ejercicio
            $verificarSql = "SELECT * FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
            $stmtVerificar = $conexion->prepare($verificarSql);
            $stmtVerificar->bind_param("ii", $id_partida, $id_ejercicio);
            $stmtVerificar->execute();
            $resultadoVerificar = $stmtVerificar->get_result();

            if ($resultadoVerificar->num_rows > 0) {
                throw new Exception("Ya existe un registro con el mismo id_partida y id_ejercicio.");
            }

            // Verificar que el ejercicio fiscal esté abierto (status = 1)
            $sqlEjercicio = "SELECT status FROM ejercicio_fiscal WHERE id = ?";
            $stmtEjercicio = $conexion->prepare($sqlEjercicio);
            $stmtEjercicio->bind_param("i", $id_ejercicio);
            $stmtEjercicio->execute();
            $resultadoEjercicio = $stmtEjercicio->get_result();
            $filaEjercicio = $resultadoEjercicio->fetch_assoc();

            if ($filaEjercicio['status'] == 0) {
                throw new Exception("El ejercicio fiscal seleccionado ya fue cerrado.");
            }

            // Inicializar monto_actual con el mismo valor que monto_inicial
            $monto_actual = $monto_inicial;

            // Validar que los campos no estén vacíos
            if (empty($id_partida) || empty($monto_inicial) || empty($id_ejercicio)) {
                throw new Exception("Faltan datos en uno de los registros (id_partida, monto_inicial, id_ejercicio)");
            }

            // Insertar los datos en la tabla
            $sql = "INSERT INTO distribucion_presupuestaria (id_partida, monto_inicial, id_ejercicio, monto_actual, status) VALUES (?, ?, ?, ?, 1)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("isis", $id_partida, $monto_inicial, $id_ejercicio, $monto_actual);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                throw new Exception("Error al insertar en distribucion_presupuestaria");
            }

            $stmt->close();
        }

        return json_encode(["success" => "Datos de distribución presupuestaria guardados correctamente"]);

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para obtener todos los registros de la tabla distribucion_presupuestaria
function obtenerDistribuciones() {
    global $conexion;

    $sql = "SELECT * FROM distribucion_presupuestaria";
    $result = $conexion->query($sql);

    $distribuciones = [];

    while ($row = $result->fetch_assoc()) {
        $distribuciones[] = $row;
    }

    return json_encode($distribuciones);
}

// Función para obtener un solo registro por ID
function obtenerDistribucionPorId($id) {
    global $conexion;

    $sql = "SELECT * FROM distribucion_presupuestaria WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $distribucion = $result->fetch_assoc();
        return json_encode($distribucion);
    } else {
        return json_encode(['error' => 'No se encontró el registro']);
    }
}

// Función para actualizar un registro
function actualizarDistribucion($id, $id_partida, $monto_inicial, $id_ejercicio) {
    global $conexion;

    try {
        // Verificar que el registro exista
        $sql = "SELECT status FROM distribucion_presupuestaria WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $distribucion = $result->fetch_assoc();

        if (!$distribucion) {
            throw new Exception("El registro no existe");
        }

        // Verificar si el registro está activo
        if ($distribucion['status'] == 0) {
            throw new Exception("No se puede actualizar un registro cerrado.");
        }

        // Verificar que el ejercicio fiscal esté abierto (status = 1)
        $sqlEjercicio = "SELECT status FROM ejercicio_fiscal WHERE id = ?";
        $stmtEjercicio = $conexion->prepare($sqlEjercicio);
        $stmtEjercicio->bind_param("i", $id_ejercicio);
        $stmtEjercicio->execute();
        $resultadoEjercicio = $stmtEjercicio->get_result();
        $filaEjercicio = $resultadoEjercicio->fetch_assoc();

        if ($filaEjercicio['status'] == 0) {
            throw new Exception("El ejercicio fiscal seleccionado ya fue cerrado.");
        }

        // Actualizar el registro
        $sql = "UPDATE distribucion_presupuestaria SET id_partida = ?, monto_inicial = ?, id_ejercicio = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isii", $id_partida, $monto_inicial, $id_ejercicio, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Registro actualizado correctamente"]);
        } else {
            throw new Exception("Error al actualizar el registro");
        }

    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un registro
function eliminarDistribucion($id) {
    global $conexion;

    try {
        // Verificar que el registro exista
        $sql = "SELECT status FROM distribucion_presupuestaria WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $distribucion = $result->fetch_assoc();

        if (!$distribucion) {
            throw new Exception("El registro no existe");
        }

        // Verificar si el registro está activo
        if ($distribucion['status'] == 0) {
            throw new Exception("No se puede eliminar un registro cerrado.");
        }

        // Eliminar el registro
        $sql = "DELETE FROM distribucion_presupuestaria WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Registro eliminado correctamente"]);
        } else {
            throw new Exception("Error al eliminar el registro");
        }

    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    switch ($data["accion"]) {
        case 'crear':
            echo guardarDistribucionPresupuestaria($data["arrayDatos"]);
            break;

        case 'obtener':
            echo obtenerDistribuciones();
            break;

        case 'obtener_id':
            echo obtenerDistribucionPorId($data["id"]);
            break;

        case 'actualizar':
            echo actualizarDistribucion($data["id"], $data["id_partida"], $data["monto_inicial"], $data["id_ejercicio"]);
            break;

        case 'eliminar':
            echo eliminarDistribucion($data["id"]);
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    echo json_encode(['error' => 'No se especificó ninguna acción']);
}
