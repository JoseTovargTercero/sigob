<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para insertar datos en plan_inversion y proyecto_inversion
function guardarPlanInversion($id_ejercicio, $monto_total, $proyectosArray) {
    global $conexion;

    try {
        // Verificar que el array de proyectos no esté vacío
        if (empty($proyectosArray)) {
            throw new Exception("El array de proyectos está vacío");
        }

        // Verificar que la suma de los montos de proyectos sea igual al monto_total
        $sumaMontos = array_sum(array_column($proyectosArray, 1)); // Columna de montos
        if ($sumaMontos != $monto_total) {
            throw new Exception("La suma de los montos de los proyectos no es igual al monto total de la inversión.");
        }

        // Insertar en la tabla plan_inversion
        $fecha = date('Y-m-d'); // Fecha actual
        $sqlPlan = "INSERT INTO plan_inversion (id_ejercicio, monto_total, fecha) VALUES (?, ?, ?)";
        $stmtPlan = $conexion->prepare($sqlPlan);
        $stmtPlan->bind_param("ids", $id_ejercicio, $monto_total, $fecha);
        $stmtPlan->execute();

        if ($stmtPlan->affected_rows <= 0) {
            throw new Exception("Error al insertar en la tabla plan_inversion.");
        }

        // Obtener el ID generado para plan_inversion
        $id_plan = $stmtPlan->insert_id;
        $stmtPlan->close();

        // Insertar los proyectos en la tabla proyecto_inversion
        foreach ($proyectosArray as $proyecto) {
            $nombre_proyecto = $proyecto[0];
            $monto_proyecto = $proyecto[1];
            $id_partida = $proyecto[2];
            $status = 0; // Estado por defecto

            $sqlProyecto = "INSERT INTO proyecto_inversion (id_plan, proyecto, monto_proyecto, id_partida, status) VALUES (?, ?, ?, ?, ?)";
            $stmtProyecto = $conexion->prepare($sqlProyecto);
            $stmtProyecto->bind_param("isdis", $id_plan, $nombre_proyecto, $monto_proyecto, $id_partida, $status);
            $stmtProyecto->execute();

            if ($stmtProyecto->affected_rows <= 0) {
                throw new Exception("Error al insertar en la tabla proyecto_inversion.");
            }

            $stmtProyecto->close();
        }

        return json_encode(["success" => "Datos guardados correctamente."]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar datos en plan_inversion
function actualizarPlanInversion($id_plan, $monto_total) {
    global $conexion;

    try {
        $fecha = date('Y-m-d');
        $sql = "UPDATE plan_inversion SET monto_total = ?, fecha = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("dsi", $monto_total, $fecha, $id_plan);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al actualizar el plan de inversión.");
        }

        $stmt->close();
        return json_encode(["success" => "Plan de inversión actualizado correctamente."]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar datos en proyecto_inversion
function actualizarProyectoInversion($id_proyecto, $nombre_proyecto, $monto_proyecto, $id_partida) {
    global $conexion;

    try {
        // Verificar si el proyecto ya ha sido ejecutado
        $sqlCheckStatus = "SELECT status FROM proyecto_inversion WHERE id = ?";
        $stmtCheckStatus = $conexion->prepare($sqlCheckStatus);
        $stmtCheckStatus->bind_param("i", $id_proyecto);
        $stmtCheckStatus->execute();
        $stmtCheckStatus->bind_result($status);
        $stmtCheckStatus->fetch();
        $stmtCheckStatus->close();

        if ($status == 1) {
            throw new Exception("Este proyecto ya ha sido ejecutado y no se puede modificar.");
        }

        // Actualizar los datos del proyecto
        $sql = "UPDATE proyecto_inversion SET proyecto = ?, monto_proyecto = ?, id_partida = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sdii", $nombre_proyecto, $monto_proyecto, $id_partida, $id_proyecto);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al actualizar el proyecto de inversión.");
        }

        // Verificar que la suma de los montos de los proyectos con el mismo id_plan sea igual a monto_total en plan_inversion
        $sqlSuma = "SELECT SUM(monto_proyecto) FROM proyecto_inversion WHERE id_plan = (SELECT id_plan FROM proyecto_inversion WHERE id = ?)";
        $stmtSuma = $conexion->prepare($sqlSuma);
        $stmtSuma->bind_param("i", $id_proyecto);
        $stmtSuma->execute();
        $stmtSuma->bind_result($suma_montos);
        $stmtSuma->fetch();
        $stmtSuma->close();

        $sqlMontoTotal = "SELECT monto_total FROM plan_inversion WHERE id = (SELECT id_plan FROM proyecto_inversion WHERE id = ?)";
        $stmtMontoTotal = $conexion->prepare($sqlMontoTotal);
        $stmtMontoTotal->bind_param("i", $id_proyecto);
        $stmtMontoTotal->execute();
        $stmtMontoTotal->bind_result($monto_total);
        $stmtMontoTotal->fetch();
        $stmtMontoTotal->close();

        if ($suma_montos != $monto_total) {
            throw new Exception("La suma de los montos de los proyectos no coincide con el monto total del plan de inversión.");
        }

        return json_encode(["success" => "Proyecto actualizado correctamente."]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para modificar el estado de un proyecto a 1 (ejecutado)
function ejecutarProyecto($id_proyecto) {
    global $conexion;

    try {
        $sql = "UPDATE proyecto_inversion SET status = 1 WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_proyecto);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al actualizar el estado del proyecto.");
        }

        $stmt->close();
        return json_encode(["success" => "Proyecto marcado como ejecutado."]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar datos en plan_inversion
function eliminarPlanInversion($id_plan) {
    global $conexion;

    try {
        // Eliminar los proyectos relacionados
        $sqlDeleteProyectos = "DELETE FROM proyecto_inversion WHERE id_plan = ?";
        $stmtDeleteProyectos = $conexion->prepare($sqlDeleteProyectos);
        $stmtDeleteProyectos->bind_param("i", $id_plan);
        $stmtDeleteProyectos->execute();
        $stmtDeleteProyectos->close();

        // Eliminar el plan de inversión
        $sql = "DELETE FROM plan_inversion WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_plan);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al eliminar el plan de inversión.");
        }

        $stmt->close();
        return json_encode(["success" => "Plan de inversión y proyectos eliminados correctamente."]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    // Insertar datos
    if ($accion === "insert" && isset($data["id_ejercicio"]) && isset($data["monto_total"]) && isset($data["proyectos"])) {
        echo guardarPlanInversion($data["id_ejercicio"], $data["monto_total"], $data["proyectos"]);

    // Actualizar plan de inversión
    } elseif ($accion === "update_plan" && isset($data["id_plan"]) && isset($data["monto_total"])) {
        echo actualizarPlanInversion($data["id_plan"], $data["monto_total"]);

    // Actualizar proyecto de inversión
    } elseif ($accion === "update_proyecto" && isset($data["id_proyecto"]) && isset($data["proyecto"]) && isset($data["monto_proyecto"]) && isset($data["id_partida"])) {
        echo actualizarProyectoInversion($data["id_proyecto"], $data["proyecto"], $data["monto_proyecto"], $data["id_partida"]);

    // Marcar proyecto como ejecutado
    } elseif ($accion === "ejecutar_proyecto" && isset($data["id_proyecto"])) {
        echo ejecutarProyecto($data["id_proyecto"]);

    // Eliminar plan de inversión
    } elseif ($accion === "delete" && isset($data["id_plan"])) {
        echo eliminarPlanInversion($data["id_plan"]);

    } else {
        echo json_encode(["error" => "Acción inválida o datos faltantes."]);
    }
} else {
    echo json_encode(["error" => "Acción no especificada."]);
}

?>
