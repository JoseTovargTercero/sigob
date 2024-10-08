<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php'; 
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para realizar el traspaso de partidas presupuestarias
function traspasarPartida($id_partida_t, $id_partida_r, $id_ejercicio, $monto) {
    global $conexion;

    try {
        // Paso 1: Verificar el estado de la partida receptora (id_partida_r)
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

        // Paso 2: Obtener el id del tipo de gasto asociado a la partida transferente (id_partida_t)
        $sqlTipoGasto = "SELECT id FROM tipo_gastos WHERE id_partida = ?";
        $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
        $stmtTipoGasto->bind_param("i", $id_partida_t);
        $stmtTipoGasto->execute();
        $resultadoTipoGasto = $stmtTipoGasto->get_result();

        if ($resultadoTipoGasto->num_rows === 0) {
            throw new Exception("No se encontró el tipo de gasto asociado a la partida transferente.");
        }

        $filaTipoGasto = $resultadoTipoGasto->fetch_assoc();
        $id_tipo_gasto = $filaTipoGasto['id'];

        // Paso 3: Calcular la sumatoria de montos de gastos asociados al tipo de gasto y ejercicio fiscal
        $sqlSumaMontos = "SELECT SUM(monto) AS total_monto FROM gastos WHERE id_tipo = ? AND id_ejercicio = ?";
        $stmtSumaMontos = $conexion->prepare($sqlSumaMontos);
        $stmtSumaMontos->bind_param("ii", $id_tipo_gasto, $id_ejercicio);
        $stmtSumaMontos->execute();
        $resultadoSumaMontos = $stmtSumaMontos->get_result();

        if ($resultadoSumaMontos->num_rows === 0) {
            throw new Exception("No se encontraron gastos asociados al tipo de gasto y ejercicio fiscal.");
        }

        $filaSumaMontos = $resultadoSumaMontos->fetch_assoc();
        $total_monto_gastos = (float) $filaSumaMontos['total_monto'];

        // Paso 4: Obtener el monto actual de la distribución presupuestaria de la partida transferente (id_partida_t)
        $sqlDistribucion = "SELECT monto_actual FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("ii", $id_partida_t, $id_ejercicio);
        $stmtDistribucion->execute();
        $resultadoDistribucion = $stmtDistribucion->get_result();

        if ($resultadoDistribucion->num_rows === 0) {
            throw new Exception("No se encontró la distribución presupuestaria para la partida transferente y ejercicio fiscal.");
        }

        $filaDistribucion = $resultadoDistribucion->fetch_assoc();
        $monto_actual = (float) $filaDistribucion['monto_actual'];

        // Calcular el monto total actual luego del traspaso
        $monto_total_actual = $monto_actual - $total_monto_gastos;

        // Verificar que el monto recibido sea igual o menor al monto total actual
        if ($monto > $monto_total_actual) {
            throw new Exception("El monto recibido es superior al monto actual de la partida presupuestaria transferente.");
        }

        // Paso 5: Realizar el registro en la tabla de traspasos
        $fecha_actual = date("Y-m-d");
        $monto_anterior = $monto_total_actual;
        $monto_actual_nuevo = $monto_total_actual + $monto;

        $sqlInsertTraspaso = "INSERT INTO traspasos (id_partida_t, id_partida_r, id_ejercicio, monto, fecha, monto_anterior, monto_actual) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtInsertTraspaso = $conexion->prepare($sqlInsertTraspaso);
        $stmtInsertTraspaso->bind_param("iiidsdd", $id_partida_t, $id_partida_r, $id_ejercicio, $monto, $fecha_actual, $monto_anterior, $monto_actual_nuevo);
        $stmtInsertTraspaso->execute();

        if ($stmtInsertTraspaso->affected_rows > 0) {
            // Obtener el id del traspaso insertado
            $id_traspaso = $stmtInsertTraspaso->insert_id;

            // Llamada a registrarCompromiso con el id del traspaso
            $resultadoCompromiso = registrarCompromiso($id_traspaso, 'traspasos', 'Traspaso de partidas');

            // Paso 6: Actualizar la distribución presupuestaria con el nuevo monto actualizado
            $nuevoMontoActual = $monto_actual_nuevo;
            $sqlUpdateDistribucion = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id_partida = ? AND id_ejercicio = ?";
            $stmtUpdateDistribucion = $conexion->prepare($sqlUpdateDistribucion);
            $stmtUpdateDistribucion->bind_param("dii", $nuevoMontoActual, $id_partida_t, $id_ejercicio);
            $stmtUpdateDistribucion->execute();

            if ($stmtUpdateDistribucion->affected_rows > 0) {
                return json_encode(["success" => "Traspaso de partidas realizado correctamente"]);
            } else {
                throw new Exception("No se pudo actualizar el monto actual de la distribución presupuestaria.");
            }
        } else {
            throw new Exception("No se pudo realizar el traspaso de partidas.");
        }

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

// Verificar qué tipo de acción se solicita
if (isset($data["accion"]) && $data["accion"] === 'traspasar') {
    $id_partida_t = $data["id_partida_t"];
    $id_partida_r = $data["id_partida_r"];
    $id_ejercicio = $data["id_ejercicio"];
    $monto = $data["monto"];

    echo traspasarPartida($id_partida_t, $id_partida_r, $id_ejercicio, $monto);
} else {
    echo json_encode(['error' => 'Acción no válida o faltan parámetros']);
}
