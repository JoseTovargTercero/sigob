<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
function gestionarPlanOperativo($data)
{
    global $conexion;

    try {
        if (!isset($data['accion'])) {
            return json_encode(["error" => "No se ha especificado acción."]);
        }

        $accion = $data['accion'];

        // Acción: Consultar todos los registros
        if ($accion === 'consulta') {
            return consultarPlanesOperativos($data);
        }
        if ($accion === 'consulta_todos') {
            return consultarTodosPlanesOperativos($data);
        }

        // Acción: Consultar un registro por ID
        if ($accion === 'consulta_id') {
            return consultarPlanOperativoPorId($data);
        }

        // Acción: Registrar una nueva solicitud
        if ($accion === 'registrar') {
            return registrarPlanOperativo($data);
        }

        // Acción: Actualizar un registro
        if ($accion === 'update') {
            return actualizarPlanOperativo($data);
        }

        // Acción: Eliminar un registro (rechazar)
        if ($accion === 'delete') {
            return eliminarPlanOperativo($data);
        }


    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}





function registrarPlanOperativo($data)
{
    global $conexion;

    try {
        if (!isset($data['objetivo_general']) || !isset($data['id_ejercicio']) || !isset($data['id_ente'])) {
            return json_encode(["error" => "Faltan datos obligatorios para registrar el plan operativo."]);
        }

        $idEnte = $data['id_ente'];
        $idEjercicio = $data['id_ejercicio'];

        // Verificar si ya existe un registro para este id_ente y id_ejercicio
        $sqlVerificar = "SELECT COUNT(*) AS total FROM plan_operativo WHERE id_ente = ? AND id_ejercicio = ?";
        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bind_param("ii", $idEnte, $idEjercicio);
        $stmtVerificar->execute();
        $resultadoVerificar = $stmtVerificar->get_result();
        $filaVerificar = $resultadoVerificar->fetch_assoc();

        if ($filaVerificar['total'] > 0) {
            return json_encode(["error" => "Ya existe un plan operativo registrado para este ente en el ejercicio fiscal indicado."]);
        }

        // Obtener el año del ejercicio fiscal
        $sqlEjercicio = "SELECT ano FROM ejercicio_fiscal WHERE id = ?";
        $stmtEjercicio = $conexion->prepare($sqlEjercicio);
        $stmtEjercicio->bind_param("i", $idEjercicio);
        $stmtEjercicio->execute();
        $resultadoEjercicio = $stmtEjercicio->get_result();
        $filaEjercicio = $resultadoEjercicio->fetch_assoc();

        if (!$filaEjercicio) {
            return json_encode(["error" => "El id_ejercicio proporcionado no es válido."]);
        }

        $ano = $filaEjercicio['ano'];



        // Iniciar transacción
        $conexion->begin_transaction();

        // Insertar en plan_operativo
        $sqlInsertar = "INSERT INTO plan_operativo (id_ente, objetivo_general, objetivos_especificos, estrategias, acciones, dimensiones, id_ejercicio, status, metas_actividades) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)";

        $stmtInsertar = $conexion->prepare($sqlInsertar);

        // Convertir arrays a JSON
        $objetivosEspecificos = json_encode($data['objetivos_especificos']);
        $estrategias = json_encode($data['estrategias']);
        $acciones = json_encode($data['acciones']);
        $dimensiones = json_encode($data['dimensiones']);
        $metas_actividades = json_encode($data['metas_actividades']);

        $stmtInsertar->bind_param("isssssis", $idEnte, $data['objetivo_general'], $objetivosEspecificos, $estrategias, $acciones, $dimensiones, $idEjercicio, $metas_actividades);
        $stmtInsertar->execute();

        if ($stmtInsertar->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Registro exitoso"]);
        } else {
            $conexion->rollback();
            return json_encode(["error" => "No se pudo registrar el plan operativo."]);
        }
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}



function consultarPlanesOperativos($data)
{
    global $conexion;

    if (!isset($data['id_ejercicio'])) {
        return json_encode(["error" => "No se ha especificado el ID del ejercicio."]);
    }

    $idEnte = $_SESSION["id_ente"];
    $idEjercicio = $data['id_ejercicio'];

    try {
        $conexion->begin_transaction();

        $sql = "SELECT * FROM plan_operativo WHERE id_ente = ? AND id_ejercicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $idEnte, $idEjercicio);
        $stmt->execute();
        $result = $stmt->get_result();

        $informacion = [];

        if ($result->num_rows < 1) {
            $conexion->rollback();
            return json_encode(["success" => null]);
        }

        $result = $result->fetch_assoc();

        $result['dimensiones'] = json_decode($result['dimensiones']);
        $result['acciones'] = json_decode($result['acciones']);
        $result['estrategias'] = json_decode($result['estrategias']);
        $result['objetivos_especificos'] = json_decode($result['objetivos_especificos']);
        $result['metas_actividades'] = json_decode($result['metas_actividades']);

        $informacion['plan_operativo'] = $result;





        // Consultar la información del ente
        $sqlEnte = "SELECT * FROM entes WHERE id = ?";
        $stmtEnte = $conexion->prepare($sqlEnte);
        $stmtEnte->bind_param("i", $idEnte);
        $stmtEnte->execute();
        $resultEnte = $stmtEnte->get_result();
        $ente = $resultEnte->fetch_assoc();
        $informacion['ente'] = $ente ?: null; // Si no se encuentra, se asigna como null

        $conexion->commit();
        return json_encode(["success" => $informacion]);
    } catch (Exception $e) {
        $conexion->rollback();
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}

function consultarTodosPlanesOperativos($data)
{
    global $conexion;
    global $remote_db;

    $conexion = $remote_db;

    if (!isset($data['id_ejercicio'])) {
        return json_encode(["error" => "No se ha especificado el ID del ejercicio."]);
    }

    $idEjercicio = $data['id_ejercicio'];

    try {
        $conexion->begin_transaction();

        // Consultar los planes operativos, ordenados por id
        $sql = "SELECT * FROM plan_operativo WHERE id_ejercicio = ? ORDER BY id ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idEjercicio);
        $stmt->execute();
        $result = $stmt->get_result();

        $planesOperativos = [];

        while ($row = $result->fetch_assoc()) {
            $row['dimensiones'] = json_decode($row['dimensiones']);
            $row['acciones'] = json_decode($row['acciones']);
            $row['estrategias'] = json_decode($row['estrategias']);
            $row['objetivos_especificos'] = json_decode($row['objetivos_especificos']);
            $row['metas_actividades'] = json_decode($row['metas_actividades']);

            // Consultar la información del ente asociado al plan operativo
            $sqlEnte = "SELECT * FROM entes WHERE id = ?";
            $stmtEnte = $conexion->prepare($sqlEnte);
            $stmtEnte->bind_param("i", $row['id_ente']);
            $stmtEnte->execute();
            $resultEnte = $stmtEnte->get_result();
            $ente = $resultEnte->fetch_assoc();

            // Agregar la información del ente al plan operativo
            $row['ente'] = $ente;

            $planesOperativos[] = $row;
        }

        $conexion->commit();

        return json_encode([
            "success" =>
                $planesOperativos

        ]);

    } catch (Exception $e) {
        $conexion->rollback();
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}

function consultarPlanOperativoPorId($data)
{
    global $conexion;
    global $remote_db;
    $conexion = $remote_db;


    if (!isset($data['id'])) {
        return json_encode(["error" => "No se ha especificado ID o para la consulta."]);
    }

    $id = $data['id'];



    try {
        $conexion->begin_transaction();

        $sql = "SELECT * FROM plan_operativo WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id, );
        $stmt->execute();
        $result = $stmt->get_result();

        $informacion = [];

        if ($result->num_rows < 1) {
            $conexion->rollback();
            return json_encode(["success" => null]);
        }

        $result = $result->fetch_assoc();

        $idEnte = $result['id_ente'];

        $result['dimensiones'] = json_decode($result['dimensiones']);
        $result['acciones'] = json_decode($result['acciones']);
        $result['estrategias'] = json_decode($result['estrategias']);
        $result['objetivos_especificos'] = json_decode($result['objetivos_especificos']);
        $result['metas_actividades'] = json_decode($result['metas_actividades']);

        $informacion['plan_operativo'] = $result;





        // Consultar la información del ente
        $sqlEnte = "SELECT * FROM entes WHERE id = ?";
        $stmtEnte = $conexion->prepare($sqlEnte);
        $stmtEnte->bind_param("i", $idEnte);
        $stmtEnte->execute();
        $resultEnte = $stmtEnte->get_result();
        $ente = $resultEnte->fetch_assoc();
        $informacion['ente'] = $ente ?: null; // Si no se encuentra, se asigna como null

        $conexion->commit();
        return json_encode(["success" => $informacion]);
    } catch (Exception $e) {
        $conexion->rollback();
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}


function actualizarPlanOperativo($data)
{
    global $conexion;

    if (!isset($data['id'], $data['objetivo_general'], $data['objetivos_especificos'], $data['estrategias'], $data['acciones'], $data['dimensiones'], $data['id_ejercicio'])) {
        return json_encode(["error" => "Faltan datos o el ID para actualizar el plan operativo."]);
    }

    $idEnte = $data['id_ente'];
    $idPlan = $data['id'];

    try {
        // Verificar el estado del plan operativo
        $sqlVerificar = "SELECT status FROM plan_operativo WHERE id = ? AND id_ente = ?";
        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bind_param("ii", $idPlan, $idEnte);
        $stmtVerificar->execute();
        $resultadoVerificar = $stmtVerificar->get_result();
        $filaVerificar = $resultadoVerificar->fetch_assoc();

        if (!$filaVerificar) {
            return json_encode(["error" => "El plan operativo no existe."]);
        }

        if ($filaVerificar['status'] == 1) {
            return json_encode(["error" => "No se puede modificar el plan operativo porque está en estado aprobado (status = 1)."]);
        }


        $conexion->begin_transaction();

        // Convertir arrays a JSON
        $objetivosEspecificos = json_encode($data['objetivos_especificos']);
        $estrategias = json_encode($data['estrategias']);
        $acciones = json_encode($data['acciones']);
        $dimensiones = json_encode($data['dimensiones']);
        $metas_actividades = json_encode($data['metas_actividades']);

        $sql = "UPDATE plan_operativo SET objetivo_general = ?, objetivos_especificos = ?, estrategias = ?, acciones = ?, dimensiones = ?, id_ejercicio = ?, status = ?, metas_actividades = ? WHERE id = ? AND id_ente = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssssss", $data['objetivo_general'], $objetivosEspecificos, $estrategias, $acciones, $dimensiones, $data['id_ejercicio'], $data['status'], $metas_actividades, $data['id'], $idEnte);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Plan operativo actualizado con éxito."]);
        } else {
            $conexion->rollback();
            return json_encode(["error" => "No se pudo actualizar el plan operativo o no hubo cambios."]);
        }
    } catch (Exception $e) {
        $conexion->rollback();
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}

function eliminarPlanOperativo($data)
{
    global $conexion;

    if (!isset($data['id'])) {
        return json_encode(["error" => "No se ha especificado ID para eliminar."]);
    }

    $idEnte = $data['id_ente'];
    $idPlan = $data['id'];

    try {
        // Verificar el estado del plan operativo
        $sqlVerificar = "SELECT status FROM plan_operativo WHERE id = ? AND id_ente = ?";
        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bind_param("ii", $idPlan, $idEnte);
        $stmtVerificar->execute();
        $resultadoVerificar = $stmtVerificar->get_result();
        $filaVerificar = $resultadoVerificar->fetch_assoc();

        if (!$filaVerificar) {
            return json_encode(["error" => "El plan operativo no existe."]);
        }

        if ($filaVerificar['status'] == 1) {
            return json_encode(["error" => "No se puede eliminar el plan operativo porque está en estado aprobado (status = 1)."]);
        }

        $conexion->begin_transaction();

        $sql = "DELETE FROM plan_operativo WHERE id = ? AND id_ente = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $idPlan, $idEnte);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Plan operativo eliminado con éxito."]);
        } else {
            $conexion->rollback();
            return json_encode(["error" => "No se pudo eliminar el plan operativo."]);
        }
    } catch (Exception $e) {
        $conexion->rollback();
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}




$data = json_decode(file_get_contents("php://input"), true);
echo gestionarPlanOperativo($data);

?>