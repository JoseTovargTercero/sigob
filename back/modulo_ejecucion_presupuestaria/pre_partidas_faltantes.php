<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

function obtenerPartidasFaltantes($id_ejercicio)
{
    global $conexion;

    try {
        // Obtener todas las partidas presupuestarias
        $sqlPartidas = "SELECT id, partida, descripcion FROM partidas_presupuestarias";
        $stmtPartidas = $conexion->prepare($sqlPartidas);
        $stmtPartidas->execute();
        $resultPartidas = $stmtPartidas->get_result();

        $partidas = [];
        while ($row = $resultPartidas->fetch_assoc()) {
            $partidas[$row['id']] = [
                "partida" => $row['partida'],
                "descripcion" => $row['descripcion']
            ];
        }

        // Obtener todas las partidas que ya existen en distribucion_presupuestaria para el id_ejercicio
        $sqlDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id_ejercicio = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("i", $id_ejercicio);
        $stmtDistribucion->execute();
        $resultDistribucion = $stmtDistribucion->get_result();

        while ($row = $resultDistribucion->fetch_assoc()) {
            if (isset($partidas[$row['id_partida']])) {
                unset($partidas[$row['id_partida']]); // Eliminar las que ya existen en distribución
            }
        }

        return json_encode($partidas, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        return json_encode(["error" => $e->getMessage()]);
    }
}



function registrarDistribucionPresupuestaria($id_ejercicio, $actividad, $partida_incluir, $sector, $programa, $proyecto)
{
    global $conexion, $remote_db;

    // Iniciar transacciones en ambas bases de datos
    $conexion->begin_transaction();
    $remote_db->begin_transaction();

    try {
        // Realizar la inserción en distribucion_presupuestaria en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlInsertDistribucion = "INSERT INTO distribucion_presupuestaria 
                (id_partida, monto_inicial, id_ejercicio, monto_actual, id_sector, id_programa, id_proyecto, status, status_cerrar, id_actividad) 
                VALUES (?, 0, ?, 0, ?, ?, ?, 1, 0, ?)";

            $stmtDistribucion = $db->prepare($sqlInsertDistribucion);
            $stmtDistribucion->bind_param("iiiii", $partida_incluir, $id_ejercicio, $sector, $programa, $proyecto, $actividad);
            $stmtDistribucion->execute();

            if ($stmtDistribucion->affected_rows === 0) {
                throw new Exception("Error al insertar en distribucion_presupuestaria.");
            }

            // Guardar el id generado solo en la primera inserción (asumimos que es igual en ambas bases)
            if ($db === $conexion) {
                $id_distribucion = $stmtDistribucion->insert_id;
            }
        }

        // Buscar id_ente en la tabla entes
        $sqlEnte = "SELECT id FROM entes WHERE sector = ? AND programa = ? AND proyecto = ? AND actividad = ?";
        $stmtEnte = $conexion->prepare($sqlEnte);
        $stmtEnte->bind_param("iiii", $sector, $programa, $proyecto, $actividad);
        $stmtEnte->execute();
        $resultEnte = $stmtEnte->get_result();

        if ($resultEnte->num_rows === 0) {
            throw new Exception("No se encontró el ente correspondiente.");
        }
        $id_ente = $resultEnte->fetch_assoc()['id'];

        // Buscar actividad_id en entes_dependencias
        $sqlDependencia = "SELECT id FROM entes_dependencias WHERE ue = ?";
        $stmtDependencia = $conexion->prepare($sqlDependencia);
        $stmtDependencia->bind_param("i", $id_ente);
        $stmtDependencia->execute();
        $resultDependencia = $stmtDependencia->get_result();

        if ($resultDependencia->num_rows === 0) {
            throw new Exception("No se encontró la dependencia correspondiente.");
        }
        $actividad_id = $resultDependencia->fetch_assoc()['id'];

        // Buscar id_asignacion en asignacion_entes
        $sqlAsignacion = "SELECT id FROM asignacion_entes WHERE id_ente = ? AND id_ejercicio = ?";
        $stmtAsignacion = $conexion->prepare($sqlAsignacion);
        $stmtAsignacion->bind_param("ii", $id_ente, $id_ejercicio);
        $stmtAsignacion->execute();
        $resultAsignacion = $stmtAsignacion->get_result();

        if ($resultAsignacion->num_rows === 0) {
            throw new Exception("No se encontró la asignación correspondiente.");
        }
        $id_asignacion = $resultAsignacion->fetch_assoc()['id'];

        // Preparar el JSON para distribucion_entes
        $distribucion_json = json_encode([["id_distribucion" => $id_distribucion, "monto" => 0]]);
        $fecha_actual = date('Y-m-d');

        // Insertar en distribucion_entes en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlInsertDistribucionEntes = "INSERT INTO distribucion_entes 
                (id_ente, actividad_id, distribucion, monto_total, status, id_ejercicio, comentario, fecha, id_asignacion, status_cerrar, nuevo) 
                VALUES (?, ?, ?, 0, 1, ?, '', ?, ?, 0, 1)";

            $stmtDistribucionEntes = $db->prepare($sqlInsertDistribucionEntes);
            $stmtDistribucionEntes->bind_param("iisisi", $id_ente, $actividad_id, $distribucion_json, $id_ejercicio, $fecha_actual, $id_asignacion);
            $stmtDistribucionEntes->execute();

            if ($stmtDistribucionEntes->affected_rows === 0) {
                throw new Exception("Error al insertar en distribucion_entes.");
            }
        }

        // Confirmar las transacciones en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();

        return json_encode(["success" => "Registro exitoso en ambas bases de datos"]);

    } catch (Exception $e) {
        // Revertir las transacciones en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }
}





// Procesar la petición
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];
    

    if ($accion === "consulta") {
        $id_ejercicio = $data["id_ejercicio"] ?? '';
        $response = obtenerPartidasFaltantes($id_ejercicio);
    } 
    if ($accion === "registrar") {
        $actividad = $data["actividad"] ?? '';
        $id_ejercicio = $data["id_ejercicio"] ?? '';
        $partida_incluir = $data["partida_incluir"] ?? '';
        $sector = $data["sector"] ?? '';
        $programa = $data["programa"] ?? '';
        $proyecto = $data["proyecto"] ?? '';
        $response = registrarDistribucionPresupuestaria($id_ejercicio, $actividad, $partida_incluir, $sector, $programa, $proyecto);
    } 
} else {
    $response = json_encode(['error' => "No se especificó ninguna acción"]);
}

echo $response;





 ?>