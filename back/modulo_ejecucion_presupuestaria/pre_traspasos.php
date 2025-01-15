<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php'; 
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado
require_once 'pre_dispo_presupuestaria.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

function registrarTraspasoPartida($id_partida_t, $id_partida_r, $id_ejercicio, $monto) {
    global $conexion;

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

        // Verificar el estado de la partida receptora
        $sqlPartidaReceptora = "SELECT status FROM partidas_presupuestarias WHERE id = ?";
        $stmtPartidaReceptora = $conexion->prepare($sqlPartidaReceptora);
        $stmtPartidaReceptora->bind_param("i", $id_partida_r);
        $stmtPartidaReceptora->execute();
        $resultadoPartidaReceptora = $stmtPartidaReceptora->get_result();

        if ($resultadoPartidaReceptora->num_rows === 0) {
            throw new Exception("No se encontró la partida presupuestaria receptora.");
        }

        $filaPartidaReceptora = $resultadoPartidaReceptora->fetch_assoc();
        $statusPartidaReceptora = $filaPartidaReceptora['status'];

        if ($statusPartidaReceptora !== 0) {
            throw new Exception("La partida presupuestaria receptora no está disponible para recibir traspasos.");
        }



        // Paso 2: Consultar la tabla distribucion_presupuestaria para validar el monto actual
    $sqlDistribucion = "SELECT monto_actual FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
    $stmtDistribucion = $conexion->prepare($sqlDistribucion);
    $stmtDistribucion->bind_param("ii", $id_partida_t, $id_ejercicio);
    $stmtDistribucion->execute();
    $resultadoDistribucion = $stmtDistribucion->get_result();

    // Validar si se encontró un registro
    if ($resultadoDistribucion->num_rows === 0) {
        throw new Exception("No se encontró una distribución presupuestaria con el id_partida y id_ejercicio proporcionados");
    }

    // Obtener el monto actual
    $filaDistribucion = $resultadoDistribucion->fetch_assoc();
    $monto_actual = $filaDistribucion['monto_actual'];

    // Paso 3: Verificar que el monto_actual sea mayor o igual que el monto solicitado
    if ($monto_actual < $monto) {
        throw new Exception("El monto recibido es superior al monto actual de la partida presupuestaria transferente.");
    } else {
         // Registrar traspaso
        $fecha_actual = date("Y-m-d");
        $monto_anterior = $monto_actual;
        $monto_actual_nuevo = $monto_actual + $monto;

        $sqlInsertTraspaso = "INSERT INTO traspasos (id_partida_t, id_partida_r, id_ejercicio, monto, fecha, monto_anterior, monto_actual, status) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
        $stmtInsertTraspaso = $conexion->prepare($sqlInsertTraspaso);
        $stmtInsertTraspaso->bind_param("iiidsdd", $id_partida_t, $id_partida_r, $id_ejercicio, $monto, $fecha_actual, $monto_anterior, $monto_actual_nuevo);
        $stmtInsertTraspaso->execute();

        if ($stmtInsertTraspaso->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "El traspaso se registró correctamente"]);
        } else {
            throw new Exception("No se pudo registrar el traspaso.");
        }
    }

    } catch (Exception $e) {
        if ($conexion->in_transaction) {
            $conexion->rollback();
        }
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

function consultarTodosTraspasos() {
    global $conexion;

    $sql = "SELECT t.*, 
                   pt.nombre AS partida_traspasadora, 
                   pr.nombre AS partida_recibidora 
            FROM traspasos t
            LEFT JOIN partidas_presupuestarias pt ON t.id_partida_t = pt.id
            LEFT JOIN partidas_presupuestarias pr ON t.id_partida_r = pr.id";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {
        return json_encode($resultado->fetch_all(MYSQLI_ASSOC));
    } else {
        return json_encode(["message" => "No se encontraron traspasos."]);
    }
}

function consultarTraspasoPorId($id) {
    global $conexion;

    $sql = "SELECT t.*, 
                   pt.nombre AS partida_traspasadora, 
                   pr.nombre AS partida_recibidora 
            FROM traspasos t
            LEFT JOIN partidas_presupuestarias pt ON t.id_partida_t = pt.id
            LEFT JOIN partidas_presupuestarias pr ON t.id_partida_r = pr.id
            WHERE t.id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        return json_encode($resultado->fetch_assoc());
    } else {
        return json_encode(["error" => "No se encontró el traspaso."]);
    }
}


function actualizarTraspasoPartida($id_traspaso, $id_partida_t, $id_partida_r, $id_ejercicio, $monto) {
    global $conexion;

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

        // Verificar el estado de la partida receptora
        $sqlPartidaReceptora = "SELECT status FROM partidas_presupuestarias WHERE id = ?";
        $stmtPartidaReceptora = $conexion->prepare($sqlPartidaReceptora);
        $stmtPartidaReceptora->bind_param("i", $id_partida_r);
        $stmtPartidaReceptora->execute();
        $resultadoPartidaReceptora = $stmtPartidaReceptora->get_result();

        if ($resultadoPartidaReceptora->num_rows === 0) {
            throw new Exception("No se encontró la partida presupuestaria receptora.");
        }

        $filaPartidaReceptora = $resultadoPartidaReceptora->fetch_assoc();
        $statusPartidaReceptora = $filaPartidaReceptora['status'];

        if ($statusPartidaReceptora !== 0) {
            throw new Exception("La partida presupuestaria receptora no está disponible para recibir traspasos.");
        }

        // Consultar la tabla distribucion_presupuestaria para validar el monto actual de la partida transferente
        $sqlDistribucion = "SELECT monto_actual FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("ii", $id_partida_t, $id_ejercicio);
        $stmtDistribucion->execute();
        $resultadoDistribucion = $stmtDistribucion->get_result();

        if ($resultadoDistribucion->num_rows === 0) {
            throw new Exception("No se encontró una distribución presupuestaria con el id_partida y id_ejercicio proporcionados.");
        }

        $filaDistribucion = $resultadoDistribucion->fetch_assoc();
        $monto_actual = $filaDistribucion['monto_actual'];

        // Validar que el monto actual sea suficiente
        if ($monto_actual < $monto) {
            throw new Exception("El monto recibido es superior al monto actual de la partida presupuestaria transferente.");
        }

        // Obtener el traspaso existente para comparar montos anteriores
        $sqlTraspaso = "SELECT monto FROM traspasos WHERE id = ?";
        $stmtTraspaso = $conexion->prepare($sqlTraspaso);
        $stmtTraspaso->bind_param("i", $id_traspaso);
        $stmtTraspaso->execute();
        $resultadoTraspaso = $stmtTraspaso->get_result();

        if ($resultadoTraspaso->num_rows === 0) {
            throw new Exception("No se encontró el traspaso con el ID proporcionado.");
        }

        $traspasoActual = $resultadoTraspaso->fetch_assoc();
        $montoAnterior = $traspasoActual['monto'];

        // Actualizar el traspaso
        $fecha_actual = date("Y-m-d");
        $monto_actual_nuevo = $monto_actual + $monto - $montoAnterior;

        $sqlActualizarTraspaso = "UPDATE traspasos SET id_partida_t = ?, id_partida_r = ?, id_ejercicio = ?, monto = ?, fecha = ?, monto_anterior = ?, monto_actual = ? WHERE id = ?";
        $stmtActualizarTraspaso = $conexion->prepare($sqlActualizarTraspaso);
        $stmtActualizarTraspaso->bind_param("iiidsddi", $id_partida_t, $id_partida_r, $id_ejercicio, $monto, $fecha_actual, $monto_actual, $monto_actual_nuevo, $id_traspaso);

        $stmtActualizarTraspaso->execute();

        if ($stmtActualizarTraspaso->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "El traspaso se actualizó correctamente."]);
        } else {
            throw new Exception("No se pudo actualizar el traspaso.");
        }
    } catch (Exception $e) {
        if ($conexion->in_transaction) {
            $conexion->rollback();
        }
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


function eliminarTraspaso($id) {
    global $conexion;

    try {
        $conexion->begin_transaction();

        $sqlEliminarTraspaso = "DELETE FROM traspasos WHERE id = ?";
        $stmtEliminarTraspaso = $conexion->prepare($sqlEliminarTraspaso);
        $stmtEliminarTraspaso->bind_param("i", $id);
        $stmtEliminarTraspaso->execute();

        if ($stmtEliminarTraspaso->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "El traspaso se eliminó correctamente"]);
        } else {
            throw new Exception("No se pudo eliminar el traspaso.");
        }

    } catch (Exception $e) {
        if ($conexion->in_transaction) {
            $conexion->rollback();
        }
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    switch ($data["accion"]) {
        case 'registrar':
            echo registrarTraspasoPartida($data["id_partida_t"], $data["id_partida_r"], $data["id_ejercicio"], $data["monto"]);
            break;
        case 'consultar_todos':
            echo consultarTodosTraspasos();
            break;
        case 'consultar_por_id':
            echo consultarTraspasoPorId($data["id"]);
            break;
        case 'actualizar':
            echo actualizarTraspaso($data["id"], $data["id_partida_t"], $data["id_partida_r"], $data["id_ejercicio"], $data["monto"]);
            break;
        case 'eliminar':
            echo eliminarTraspaso($data["id"]);
            break;
        default:
            echo json_encode(["error" => "Acción no válida"]);
            break;
    }
} else {
    echo json_encode(["error" => "No se especificó ninguna acción"]);
}