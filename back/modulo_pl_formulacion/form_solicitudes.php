<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

function guardarSolicitudEnte($id_ente, $id_poa, $partidas, $monto_total)
{
    global $conexion;

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

        // Array para almacenar los IDs de partidas_entes
        $idPartidasArray = [];

        // Insertar en partidas_entes para cada partida y monto, incluyendo id_ente
        foreach ($partidas as $partida) {
            $sqlInsertPartida = "INSERT INTO partidas_entes (id_ente, id_partida, monto) VALUES (?, ?, ?)";
            $stmtInsertPartida = $conexion->prepare($sqlInsertPartida);
            $stmtInsertPartida->bind_param("iid", $id_ente, $partida["id_partida"], $partida["monto"]);
            $stmtInsertPartida->execute();
            
            if ($stmtInsertPartida->affected_rows > 0) {
                $idPartidasArray[] = $stmtInsertPartida->insert_id;
            } else {
                // Si ocurre un error, deshacer la transacción
                $conexion->rollback();
                throw new Exception("Error al insertar en partidas_entes");
            }
        }

        // Insertar en solicitudes_entes
        $partidasJSON = json_encode($idPartidasArray);
        $status = 0;
        $sqlInsertSolicitud = "INSERT INTO solicitudes_entes (id_ente, id_poa, partidas, monto_total, status) VALUES (?, ?, ?, ?, ?)";
        $stmtInsertSolicitud = $conexion->prepare($sqlInsertSolicitud);
        $stmtInsertSolicitud->bind_param("iisdi", $id_ente, $id_poa, $partidasJSON, $monto_total, $status);
        $stmtInsertSolicitud->execute();

        if ($stmtInsertSolicitud->affected_rows > 0) {
            // Confirmar la transacción
            $conexion->commit();
            return json_encode(["success" => "Solicitud registrada correctamente."]);
        } else {
            // Si ocurre un error, deshacer la transacción
            $conexion->rollback();
            throw new Exception("Error al registrar la solicitud.");
        }
    } catch (Exception $e) {
        // En caso de excepción, deshacer la transacción si aún no se ha revertido
        if ($conexion->in_transaction) {
            $conexion->rollback();
        }
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Función para consultar una solicitud por ID
function consultarSolicitudPorId($id)
{
    global $conexion;

    try {
        // Obtener la solicitud de solicitudes_entes
        $sqlSolicitud = "SELECT * FROM solicitudes_entes WHERE id = ?";
        $stmtSolicitud = $conexion->prepare($sqlSolicitud);
        $stmtSolicitud->bind_param("i", $id);
        $stmtSolicitud->execute();
        $resultSolicitud = $stmtSolicitud->get_result();
        $solicitud = $resultSolicitud->fetch_assoc();

        if (!$solicitud) {
            throw new Exception("No se encontró la solicitud con el ID especificado.");
        }

        // Obtener información del ente
        $sqlEnte = "SELECT ente_nombre, tipo_ente FROM entes WHERE id = ?";
        $stmtEnte = $conexion->prepare($sqlEnte);
        $stmtEnte->bind_param("i", $solicitud["id_ente"]);
        $stmtEnte->execute();
        $resultEnte = $stmtEnte->get_result();
        $ente = $resultEnte->fetch_assoc();

        // Obtener detalles de las partidas
        $partidasArray = [];
        $idPartidas = json_decode($solicitud["partidas"], true);

        foreach ($idPartidas as $idPartidaEnte) {
            $sqlPartidaEnte = "SELECT id_partida, monto FROM partidas_entes WHERE id = ?";
            $stmtPartidaEnte = $conexion->prepare($sqlPartidaEnte);
            $stmtPartidaEnte->bind_param("i", $idPartidaEnte);
            $stmtPartidaEnte->execute();
            $resultPartidaEnte = $stmtPartidaEnte->get_result();
            $partidaEnte = $resultPartidaEnte->fetch_assoc();

            // Obtener el nombre de la partida
            $sqlPartida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $conexion->prepare($sqlPartida);
            $stmtPartida->bind_param("i", $partidaEnte["id_partida"]);
            $stmtPartida->execute();
            $resultPartida = $stmtPartida->get_result();
            $partida = $resultPartida->fetch_assoc();

            $partidasArray[] = [
                'id_partida' => $partidaEnte["id_partida"],
                'partida' => $partida["partida"],
                'monto' => $partidaEnte["monto"]
            ];
        }

        // Determinar el status
        $status = $solicitud["status"] == 0 ? "Pendiente" : "Aprobado";

        // Crear el array de respuesta
        $resultado = [
            'id' => $solicitud["id"],
            'id_ente' => $solicitud["id_ente"],
            'tipo_ente' => $ente["tipo_ente"],
            'ente_nombre' => $ente["ente_nombre"],
            'id_poa' => $solicitud["id_poa"],
            'partidas' => $partidasArray,
            'monto_total' => $solicitud["monto_total"],
            'status' => $status
        ];

        return json_encode(["success" => $resultado]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para consultar todas las solicitudes
function consultarTodasSolicitudes()
{
    global $conexion;

    try {
        $sqlSolicitudes = "SELECT * FROM solicitudes_entes";
        $resultSolicitudes = $conexion->query($sqlSolicitudes);

        $solicitudesArray = [];

        while ($solicitud = $resultSolicitudes->fetch_assoc()) {
            // Obtener información del ente
            $sqlEnte = "SELECT ente_nombre, tipo_ente FROM entes WHERE id = ?";
            $stmtEnte = $conexion->prepare($sqlEnte);
            $stmtEnte->bind_param("i", $solicitud["id_ente"]);
            $stmtEnte->execute();
            $resultEnte = $stmtEnte->get_result();
            $ente = $resultEnte->fetch_assoc();

            // Obtener detalles de las partidas
            $partidasArray = [];
            $idPartidas = json_decode($solicitud["partidas"], true);

            foreach ($idPartidas as $idPartidaEnte) {
                $sqlPartidaEnte = "SELECT id_partida, monto FROM partidas_entes WHERE id = ?";
                $stmtPartidaEnte = $conexion->prepare($sqlPartidaEnte);
                $stmtPartidaEnte->bind_param("i", $idPartidaEnte);
                $stmtPartidaEnte->execute();
                $resultPartidaEnte = $stmtPartidaEnte->get_result();
                $partidaEnte = $resultPartidaEnte->fetch_assoc();

                $sqlPartida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $partidaEnte["id_partida"]);
                $stmtPartida->execute();
                $resultPartida = $stmtPartida->get_result();
                $partida = $resultPartida->fetch_assoc();

                $partidasArray[] = [
                    'id_partida' => $partidaEnte["id_partida"],
                    'partida' => $partida["partida"],
                    'monto' => $partidaEnte["monto"]
                ];
            }

            $status = $solicitud["status"] == 0 ? "Pendiente" : "Aprobado";

            $solicitudesArray[] = [
                'id' => $solicitud["id"],
                'id_ente' => $solicitud["id_ente"],
                'tipo_ente' => $ente["tipo_ente"],
                'ente_nombre' => $ente["ente_nombre"],
                'id_poa' => $solicitud["id_poa"],
                'partidas' => $partidasArray,
                'monto_total' => $solicitud["monto_total"],
                'status' => $status
            ];
        }

        return json_encode(["success" => $solicitudesArray]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar el status de una solicitud en solicitudes_entes
function actualizarStatusSolicitud($id, $status)
{
    global $conexion;

    try {
        $sqlUpdateStatus = "UPDATE solicitudes_entes SET status = ? WHERE id = ?";
        $stmtUpdateStatus = $conexion->prepare($sqlUpdateStatus);
        $stmtUpdateStatus->bind_param("ii", $status, $id);
        $stmtUpdateStatus->execute();

        if ($stmtUpdateStatus->affected_rows > 0) {
            return json_encode(["success" => "El status de la solicitud ha sido actualizado correctamente."]);
        } else {
        throw new Exception("Error al actualizar el status de la solicitud.");
    }
} catch (Exception $e) {
    registrarError($e->getMessage());
    return json_encode(['error' => $e->getMessage()]);
    }
}



// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    if ($accion === "guardar") {
        echo guardarSolicitudEnte($data["id_ente"], $data["id_poa"], $data["partidas"], $data["monto_total"]);
    } elseif ($accion === "obtener_por_id") {
        echo obtenerSolicitudPorId($data["id"]);
    } elseif ($accion === "obtener_todas") {
        echo obtenerTodasLasSolicitudes();
    } elseif ($accion === "actualizar_status") {
    echo actualizarStatusSolicitud($data["id"], $data["status"]);
    } else {
        echo json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    echo json_encode(['error' => "No se especificó ninguna acción"]);
}

?>
