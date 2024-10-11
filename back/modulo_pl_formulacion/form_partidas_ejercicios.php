<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para obtener los datos de distribucion_presupuestaria según ejercicio y partida
function obtenerTotalesPorPartida($ejercicio, $partida) {
    global $conexion;

    try {
        // Validar que los parámetros no estén vacíos
        if (empty($ejercicio) || empty($partida)) {
            throw new Exception("Debe proporcionar un ejercicio y una partida");
        }

        // 1. Consultar en la tabla partidas_presupuestarias para obtener el id de la partida
        $sqlPartida = "SELECT id FROM partidas_presupuestarias WHERE partida = ?";
        $stmtPartida = $conexion->prepare($sqlPartida);
        $stmtPartida->bind_param("s", $partida);
        $stmtPartida->execute();
        $resultadoPartida = $stmtPartida->get_result();
        $filaPartida = $resultadoPartida->fetch_assoc();

        // Validar si se encontró la partida
        if (!$filaPartida) {
            throw new Exception("No se encontró ninguna partida con el valor proporcionado");
        }

        $idPartida = $filaPartida['id']; // Obtener el id de la partida

        // 2. Consultar la tabla distribucion_presupuestaria usando id_partida e id_ejercicio
        $sqlDistribucion = "SELECT monto_inicial, monto_actual FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("ii", $idPartida, $ejercicio);
        $stmtDistribucion->execute();
        $resultadoDistribucion = $stmtDistribucion->get_result();
        $filaDistribucion = $resultadoDistribucion->fetch_assoc();

        // Validar si se encontró el registro en distribucion_presupuestaria
        if (!$filaDistribucion) {
            throw new Exception("No se encontró ninguna distribución presupuestaria con los valores proporcionados");
        }

        // Preparar el resultado final
        $resultado = [
            "total_inicial" => $filaDistribucion['monto_inicial'],
            "total_restante" => $filaDistribucion['monto_actual']
        ];

        // Devolver el resultado en formato JSON
        return json_encode($resultado);

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["ejercicio"]) && isset($data["partida"])) {
    $ejercicio = $data["ejercicio"];
    $partida = $data["partida"];

    // Llamar a la función para obtener los totales
    echo obtenerTotalesPorPartida($ejercicio, $partida);
} else {
    echo json_encode(['error' => "No se proporcionaron los datos necesarios (ejercicio y partida)"]);
}

