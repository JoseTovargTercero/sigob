<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php'; 
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado
require_once 'pre_dispo_presupuestaria.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

function traspasarPartida($id_partida_t, $id_partida_r, $id_ejercicio, $monto) {
    global $conexion;

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

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


          // Llamar a la función y obtener el resultado
    $resultado = consultarDisponibilidad($id_partida_t, $id_ejercicio, $monto);

    if ($resultado['exito']) {
        $monto_actual = $resultado['monto_actual'];
    } else {
        throw new Exception("El monto recibido es superior al monto actual de la partida presupuestaria transferente.");
        $monto_actual = $resultado['monto_actual'];
    }



        // Paso 5: Realizar el registro en la tabla de traspasos
        $fecha_actual = date("Y-m-d");
        $monto_anterior = $monto_actual;
        $monto_actual_nuevo = $monto_actual + $monto;

        $sqlInsertTraspaso = "INSERT INTO traspasos (id_partida_t, id_partida_r, id_ejercicio, monto, fecha, monto_anterior, monto_actual) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtInsertTraspaso = $conexion->prepare($sqlInsertTraspaso);
        $stmtInsertTraspaso->bind_param("iiidsdd", $id_partida_t, $id_partida_r, $id_ejercicio, $monto, $fecha_actual, $monto_anterior, $monto_actual_nuevo);
        $stmtInsertTraspaso->execute();

        if ($stmtInsertTraspaso->affected_rows > 0) {
            $id_traspaso = $stmtInsertTraspaso->insert_id;

            // Llamada a registrarCompromiso
            $resultadoCompromiso = registrarCompromiso($id_traspaso, 'traspasos', 'Traspaso de partidas');

            // Actualizar el estado de la partida transferente
            $sqlUpdateStatus = "UPDATE partidas_presupuestarias SET status = 1 WHERE id = ?";
            $stmtUpdateStatus = $conexion->prepare($sqlUpdateStatus);
            $stmtUpdateStatus->bind_param("i", $id_partida_t);
            $stmtUpdateStatus->execute();

            // Paso 6: Actualizar la distribución presupuestaria con el nuevo monto inicial
            $nuevoMontoInicial = $monto_actual_nuevo;
            $sqlUpdateDistribucion = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id_partida = ? AND id_ejercicio = ?";
            $stmtUpdateDistribucion = $conexion->prepare($sqlUpdateDistribucion);
            $stmtUpdateDistribucion->bind_param("dii", $nuevoMontoInicial, $id_partida_t, $id_ejercicio);
            $stmtUpdateDistribucion->execute();

            if ($stmtUpdateDistribucion->affected_rows > 0) {
                // Confirmar la transacción
                $conexion->commit();
                return json_encode(["success" => "Traspaso de partidas realizado correctamente"]);
            } else {
                throw new Exception("No se pudo actualizar el monto inicial de la distribución presupuestaria.");
            }
        } else {
            throw new Exception("No se pudo realizar el traspaso de partidas.");
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
