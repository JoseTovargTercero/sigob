<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';


// validar que el monto no supere el presupuesto
function validarPresupuesto($plan, $monto)
{

    return true;
}



// Función para insertar datos en plan_inversion y proyecto_inversion
function guardarEnte($proyectosArray)
{
    global $conexion;

    try {
        // Verificar que el array de proyectos no esté vacío
        if (empty($proyectosArray)) {
            throw new Exception("El array de proyectos está vacío");
        }

        // Insertar los proyectos en la tabla proyecto_inversion

        $sector = $proyectosArray['sector'];
        $programa = $proyectosArray['programa'];
        $proyecto = $proyectosArray['proyecto'];
        $actividad = $proyectosArray['actividad'];
        $nombre = $proyectosArray['nombre'];
        $tipo_ente = $proyectosArray['tipo_ente'];

        // verificar nombre
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `entes` WHERE ente_nombre = ?");
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Ya existe un ente con el mismo nombre");
        }
        $stmt->close();



        $sql = "INSERT INTO entes (
        sector,
        programa,
        proyecto,
        actividad,
        ente_nombre,
        tipo_ente
        ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssss", $sector, $programa, $proyecto, $actividad, $nombre, $tipo_ente);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            $error = $stmt->error;
            throw new Exception("Error al insertar en la tabla proyecto_inversion. $error");
        }



        $stmt->close();

        return json_encode(["success" => "Datos guardados correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar datos en plan_inversion
function actualizarPlanInversion($id_plan, $monto_total)
{
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
function actualizarProyectoInversion($proyecto)
{
    global $conexion;

    try {
        $id_proyecto = $proyecto['id'];
        $nombre_proyecto = $proyecto['nombre'];
        $monto_proyecto = $proyecto['monto'];
        $descripcion = $proyecto['descripcion'];
        $partidas_montos = $proyecto['partida'];

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

        $error = false;
        // Actualizar los datos del proyecto
        $sql = "UPDATE proyecto_inversion SET proyecto = ?, descripcion=?, monto_proyecto = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssdi", $nombre_proyecto, $descripcion, $monto_proyecto, $id_proyecto);
        if (!$stmt->execute()) {
            $error = true;
        }
        $stmt->close();
        $stmt_d = $conexion->prepare("DELETE FROM `proyecto_inversion_partidas` WHERE id_proyecto= ?");
        $stmt_d->bind_param("i", $id_proyecto);
        if (!$stmt_d->execute()) {
            $error = true;
        }
        $stmt_d->close();
        $stmt_o = $conexion->prepare("INSERT INTO proyecto_inversion_partidas (id_proyecto, partida, sector_id, monto) VALUES (?, ?, ?, ?)");
        foreach ($partidas_montos as $item) {
            $partida = $item['partida'];
            $monto = $item['monto'];
            $sector = $item['sector'];
            $stmt_o->bind_param("isss", $id_proyecto, $partida, $sector, $monto);
            if (!$stmt_o->execute()) {
                $error = true;
            }
        }
        $stmt_o->close();
        if ($error) {
            throw new Exception("Error al actualizar el proyecto de inversión.");
        }
        return json_encode(["success" => "Proyecto actualizado correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para modificar el estado de un proyecto a 1 (ejecutado)
function ejecutarProyecto($comentario, $id_proyecto)
{
    global $conexion;

    try {
        $sql = "UPDATE proyecto_inversion SET status = 1, comentario=? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $comentario, $id_proyecto);
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
function eliminarPlanInversion($id_plan)
{
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



// Obtener lista de entes
function get_unidades()
{
    global $conexion;
    $data = [];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `entes` ORDER BY tipo_ente DESC, ente_nombre ASC ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push(
                $data,
                $row
            );
        }
    }
    $stmt->close();
    return json_encode(['success' => $data]);
}






// Obtener lista de subEntes
function get_sub_unidades()
{
    global $conexion;
    $data = [];

    $stmt = mysqli_prepare($conexion, "SELECT entes_dependencias.*, entes.ente_nombre AS nombre_ente_p FROM `entes_dependencias`
    LEFT JOIN entes ON entes.id = entes_dependencias.ue
     ORDER BY entes_dependencias.ue ASC, entes_dependencias.ente_nombre ASC ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push(
                $data,
                $row
            );
        }
    }
    $stmt->close();
    return json_encode(['success' => $data]);
}



function eliminarProyecto($id)
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "SELECT status FROM `proyecto_inversion` WHERE id = ? ");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['status'] == 1) {
                echo json_encode(['error' => 'No se puedo eliminar un proyecto ejecutado']);
                exit;
            }
        }
    } else {
        echo json_encode(['error' => 'El proyecto no existe']);
        exit;
    }
    $stmt->close();


    $stmt = $conexion->prepare("DELETE FROM `proyecto_inversion` WHERE id = ? AND status='0'");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        $stmt_d = $conexion->prepare("DELETE FROM `proyecto_inversion_partidas` WHERE id_proyecto= ?");
        $stmt_d->bind_param("i", $id);
        $stmt_d->execute();
        $stmt_d->close();


        echo json_encode(['success' => 'Proyecto eliminado']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar el proyecto']);
    }
    $stmt->close();
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    // Nuevos proyectos
    if ($accion === "registrar_ente" && isset($data["unidad"])) {
        echo guardarEnte($data["unidad"]);
        // Actualizar plan de inversión
    } elseif ($accion === "update_plan" && isset($data["id_plan"]) && isset($data["monto_total"])) {
        echo actualizarPlanInversion($data["id_plan"], $data["monto_total"]);
        // Actualizar proyecto de inversión
    } elseif ($accion === "update_proyecto" && isset($data["proyecto"])) {
        echo actualizarProyectoInversion($data["proyecto"]);
        // Marcar proyecto como ejecutado
    } elseif ($accion === "ejecutar_proyecto" && isset($data["id_proyecto"])) {
        echo ejecutarProyecto($data["comentario"], $data["id_proyecto"]);
        // Eliminar plan de inversión
    } elseif ($accion === "delete" && isset($data["id_plan"])) {
        echo eliminarPlanInversion($data["id_plan"]);
    } elseif ($accion === 'eliminar_proyecto' && isset($data['id_proyecto'])) {
        echo eliminarProyecto($data['id_proyecto']);
    } elseif ($accion === "get_unidades") {
        echo get_unidades(); // TODO: LISTO

    } elseif ($accion === "get_sub_unidades") {
        echo get_sub_unidades(); // TODO: LISTO
    } else {
        echo json_encode(["error" => "Acción inválida o datos faltantes."]);
    }
} else {
    echo json_encode(["error" => "Acción no especificada."]);
}
