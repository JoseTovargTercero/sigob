<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para dividir el total en 4 partes y generar el valor de distribución
function calcularDistribucion($total) {
    $parte = $total / 4;
    return "$parte - $parte - $parte - $parte";
}

// Función para insertar una nueva actividad en poa_actividades
function registrarActividad($actividades, $responsable, $unidad_medida, $total, $id_ente) {
    global $conexion;

    try {
        $fecha = date('d-m-Y'); // Fecha actual
        $distribucion = calcularDistribucion($total); // Calcular distribución

        $sql = "INSERT INTO poa_actividades (actividades, responsable, unidad_medida, distribucion, total, id_ente, fecha) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssdss", $actividades, $responsable, $unidad_medida, $distribucion, $total, $id_ente, $fecha);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Actividad registrada correctamente"]);
        } else {
            throw new Exception("No se pudo registrar la actividad");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar una actividad en poa_actividades
function actualizarActividad($id, $actividades, $responsable, $unidad_medida, $total, $id_ente) {
    global $conexion;

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para actualizar la actividad"]);
        }

        $distribucion = calcularDistribucion($total); // Calcular nueva distribución si cambia el total

        $sql = "UPDATE poa_actividades 
                SET actividades = ?, responsable = ?, unidad_medida = ?, total = ?, distribucion = ?, id_ente = ?
                WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssdsdi", $actividades, $responsable, $unidad_medida, $total, $distribucion, $id_ente, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Actividad actualizada correctamente"]);
        } else {
            throw new Exception("No se pudo actualizar la actividad");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

function eliminarActividad($id) {
    global $conexion;
    $user_id = $_SESSION['u_id']; // Obtener el user_id de la sesión actual

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para eliminar la actividad"]);
        }

        // Iniciar transacción
        $conexion->begin_transaction();

        // Eliminar la actividad en poa_actividades
        $sql = "DELETE FROM poa_actividades WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Registrar en audit_logs
            $sqlAudit = "INSERT INTO audit_logs (action_type, table_name, situation, affected_rows, user_id, timestamp) 
                         VALUES (?, ?, ?, ?, ?, NOW())";
            $stmtAudit = $conexion->prepare($sqlAudit);
            $action_type = 'DELETE';
            $table_name = 'poa_actividades';
            $situation = "id_actividad=$id";
            $affected_rows = $stmt->affected_rows;
            $stmtAudit->bind_param("sssii", $action_type, $table_name, $situation, $affected_rows, $user_id);
            $stmtAudit->execute();
            $stmtAudit->close();

            // Confirmar la transacción
            $conexion->commit();
            return json_encode(["success" => "Actividad eliminada correctamente"]);
        } else {
            throw new Exception("No se pudo eliminar la actividad");
        }
    } catch (Exception $e) {
        $conexion->rollback(); // Revertir en caso de error
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Procesar la petición
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];
    $id = $data["id"] ?? null;
    $actividades = $data["actividades"] ?? '';
    $responsable = $data["responsable"] ?? '';
    $unidad_medida = $data["unidad_medida"] ?? '';
    $total = $data["total"] ?? 0;
    $id_ente = $data["id_ente"] ?? '';

    if ($accion === "insert") {
        $response = registrarActividad($actividades, $responsable, $unidad_medida, $total, $id_ente);
    } elseif ($accion === "update") {
        $response = actualizarActividad($id, $actividades, $responsable, $unidad_medida, $total, $id_ente);
    } elseif ($accion === "delete") {
        $response = eliminarActividad($id);
    } else {
        $response = json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    $response = json_encode(['error' => "No se especificó ninguna acción"]);
}

echo $response;

?>
