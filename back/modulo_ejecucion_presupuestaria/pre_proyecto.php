<?php
function ejecutarProyecto($comentario, $id_proyecto)
{
    global $conexion;

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

        // Paso 1: Consultar el id_plan de la tabla proyecto_inversion usando id_proyecto
        $sqlProyecto = "SELECT id_plan FROM proyecto_inversion WHERE id = ?";
        $stmtProyecto = $conexion->prepare($sqlProyecto);
        $stmtProyecto->bind_param("i", $id_proyecto);
        $stmtProyecto->execute();
        $resultadoProyecto = $stmtProyecto->get_result();

        if ($resultadoProyecto->num_rows === 0) {
            throw new Exception("No se encontró el proyecto de inversión con el ID proporcionado.");
        }

        $filaProyecto = $resultadoProyecto->fetch_assoc();
        $id_plan = $filaProyecto['id_plan'];

        // Paso 2: Consultar el id_ejercicio en la tabla plan_inversion usando id_plan
        $sqlPlan = "SELECT id_ejercicio FROM plan_inversion WHERE id = ?";
        $stmtPlan = $conexion->prepare($sqlPlan);
        $stmtPlan->bind_param("i", $id_plan);
        $stmtPlan->execute();
        $resultadoPlan = $stmtPlan->get_result();

        if ($resultadoPlan->num_rows === 0) {
            throw new Exception("No se encontró el plan de inversión con el ID proporcionado.");
        }

        $filaPlan = $resultadoPlan->fetch_assoc();
        $id_ejercicio = $filaPlan['id_ejercicio'];

        // Paso 3: Consultar todas las partidas del proyecto en proyecto_inversion_partidas
        $sqlPartidas = "SELECT id_partida, monto FROM proyecto_inversion_partidas WHERE id_proyecto = ?";
        $stmtPartidas = $conexion->prepare($sqlPartidas);
        $stmtPartidas->bind_param("i", $id_proyecto);
        $stmtPartidas->execute();
        $resultadoPartidas = $stmtPartidas->get_result();

        if ($resultadoPartidas->num_rows === 0) {
            throw new Exception("No se encontraron partidas asociadas al proyecto de inversión.");
        }

        // Iterar sobre todas las partidas y verificar la disponibilidad
        $partidasDisponibles = true;
        $partidas = []; // Almacenamos las partidas para actualizarlas después

        while ($filaPartida = $resultadoPartidas->fetch_assoc()) {
            $id_partida = $filaPartida['id_partida'];
            $monto = $filaPartida['monto'];

            // Verificar la disponibilidad de fondos en cada partida
            $disponible = consultarDisponibilidad($id_partida, $id_ejercicio, $monto);

            // Si alguna partida no tiene fondos suficientes, lanzar una excepción
            if (!$disponible['exito']) {
                throw new Exception("Una de las partidas seleccionadas en el proyecto no tiene los fondos suficientes para poder aceptar el proyecto.");
            }

            // Si todas tienen fondos suficientes, almacenamos los datos para actualizarlos después
            $partidas[] = [
                'id_partida' => $id_partida,
                'monto' => $monto,
                'monto_actual' => $disponible['monto_actual']
            ];
        }

        // Si todas las partidas tienen fondos suficientes, proceder a actualizar el monto_actual
        foreach ($partidas as $partida) {
            $nuevoMontoActual = $partida['monto_actual'] - $partida['monto'];

            $sqlUpdateDistribucion = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id_partida = ? AND id_ejercicio = ?";
            $stmtUpdateDistribucion = $conexion->prepare($sqlUpdateDistribucion);
            $stmtUpdateDistribucion->bind_param("dii", $nuevoMontoActual, $partida['id_partida'], $id_ejercicio);
            $stmtUpdateDistribucion->execute();

            if ($stmtUpdateDistribucion->affected_rows <= 0) {
                throw new Exception("No se pudo actualizar el monto actual de la partida con ID: " . $partida['id_partida']);
            }
        }
        $descripcion = "Proyecto de Inversion Aceptado";
        $resultadoCompromiso = registrarCompromiso($id_proyecto, 'proyecto_inversion', $descripcion);

        // Paso 4: Actualizar el estado del proyecto a 'ejecutado'
        $sqlUpdateProyecto = "UPDATE proyecto_inversion SET status = 1, comentario=? WHERE id = ?";
        $stmtUpdateProyecto = $conexion->prepare($sqlUpdateProyecto);
        $stmtUpdateProyecto->bind_param("si", $comentario, $id_proyecto);
        $stmtUpdateProyecto->execute();

        if ($stmtUpdateProyecto->affected_rows <= 0) {
            throw new Exception("Error al actualizar el estado del proyecto.");
        }

        // Confirmar la transacción
        $conexion->commit();

        return json_encode([
            "success" => "El proyecto ha sido ejecutado correctamente y el presupuesto actualizado."
        ]);

    } catch (Exception $e) {
        // En caso de error, revertir la transacción
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
    $accion = $data["accion"];
    if ($accion === "ejecutar_proyecto" && isset($data["id_proyecto"])) {
        echo ejecutarProyecto($data["comentario"], $data["id_proyecto"]);
    }else {
        echo json_encode(["error" => "Acción inválida o datos faltantes."]);
    }
} else {
    echo json_encode(["error" => "Acción no especificada."]);
}

 ?>