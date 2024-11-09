<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);

function actualizar($info)
{
    global $conexion;

    $id = $info['id'];
    $monto_nuevo = $info['monto_nuevo'];
    $key = $info['key'];
    $fecha = date("Y-m-d");

    try {
        $conexion->begin_transaction();

        // Obtener la distribución actual
        $sqlDistribucion = "SELECT distribucion, id_asignacion FROM distribucion_entes WHERE id = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("i", $id);
        $stmtDistribucion->execute();
        $resultadoDistribucion = $stmtDistribucion->get_result();
        if ($resultadoDistribucion->num_rows === 0) {
            throw new Exception("Distribución no encontrada.");
        }

        $row = $resultadoDistribucion->fetch_assoc();
        $distribucionData = json_decode($row['distribucion'], true);
        $monto_anterior = $distribucionData[$key]['monto'];
        $id_distribucion = $distribucionData[$key]['id_distribucion'];
        $id_asignacion = $row['id_asignacion'];

        // Verificar la disponibilidad en distribucion_presupuestaria
        $sqlPresupuestaria = "SELECT monto_actual FROM distribucion_presupuestaria WHERE id = ?";
        $stmtPresupuestaria = $conexion->prepare($sqlPresupuestaria);
        $stmtPresupuestaria->bind_param("i", $id_distribucion);
        $stmtPresupuestaria->execute();
        $resultadoPresupuestaria = $stmtPresupuestaria->get_result();
        if ($resultadoPresupuestaria->num_rows === 0) {
            throw new Exception("Distribución presupuestaria no encontrada.");
        }

        $monto_actual = $resultadoPresupuestaria->fetch_assoc()['monto_actual'];
        $diferencia = $monto_nuevo - $monto_anterior;

        if ($monto_nuevo > $monto_anterior && $diferencia > $monto_actual) {
            throw new Exception("No hay disponibilidad presupuestaria suficiente.");
        }

        // Actualizar monto_actual en distribucion_presupuestaria
        $nuevoMontoActual = $monto_actual - $diferencia;
        $sqlUpdatePresupuestaria = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id = ?";
        $stmtUpdatePresupuestaria = $conexion->prepare($sqlUpdatePresupuestaria);
        $stmtUpdatePresupuestaria->bind_param("di", $nuevoMontoActual, $id_distribucion);
        $stmtUpdatePresupuestaria->execute();

        // Actualizar monto en distribucion_entes
        $distribucionData[$key]['monto'] = $monto_nuevo;
        $nuevaDistribucion = json_encode($distribucionData);
        $sqlUpdateDistribucion = "UPDATE distribucion_entes SET distribucion = ?, monto_total = monto_total + ? WHERE id = ?";
        $stmtUpdateDistribucion = $conexion->prepare($sqlUpdateDistribucion);
        $stmtUpdateDistribucion->bind_param("sdi", $nuevaDistribucion, $diferencia, $id);
        $stmtUpdateDistribucion->execute();

        // Actualizar monto_total en asignacion_ente
        $sqlUpdateAsignacion = "UPDATE asignacion_ente SET monto_total = monto_total + ? WHERE id = ?";
        $stmtUpdateAsignacion = $conexion->prepare($sqlUpdateAsignacion);
        $stmtUpdateAsignacion->bind_param("di", $diferencia, $id_asignacion);
        $stmtUpdateAsignacion->execute();

        $conexion->commit();
        return json_encode(["success" => "Distribución actualizada correctamente."]);
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

function eliminar($info)
{
    global $conexion;

    $id = $info['id'];
    $key = $info['key'];

    try {
        $conexion->begin_transaction();

        // Obtener la distribución actual
        $sqlDistribucion = "SELECT distribucion, monto_total, id_asignacion FROM distribucion_entes WHERE id = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("i", $id);
        $stmtDistribucion->execute();
        $resultadoDistribucion = $stmtDistribucion->get_result();
        if ($resultadoDistribucion->num_rows === 0) {
            throw new Exception("Distribución no encontrada.");
        }

        $row = $resultadoDistribucion->fetch_assoc();
        $distribucionData = json_decode($row['distribucion'], true);
        $monto_eliminar = $distribucionData[$key]['monto'];
        $id_distribucion = $distribucionData[$key]['id_distribucion'];
        $id_asignacion = $row['id_asignacion'];

        // Actualizar monto_actual en distribucion_presupuestaria
        $sqlPresupuestaria = "UPDATE distribucion_presupuestaria SET monto_actual = monto_actual + ? WHERE id = ?";
        $stmtPresupuestaria = $conexion->prepare($sqlPresupuestaria);
        $stmtPresupuestaria->bind_param("di", $monto_eliminar, $id_distribucion);
        $stmtPresupuestaria->execute();

        // Eliminar el monto de distribucion en distribucion_entes
        unset($distribucionData[$key]);
        $nuevaDistribucion = json_encode(array_values($distribucionData));
        $sqlUpdateDistribucion = "UPDATE distribucion_entes SET distribucion = ?, monto_total = monto_total - ? WHERE id = ?";
        $stmtUpdateDistribucion = $conexion->prepare($sqlUpdateDistribucion);
        $stmtUpdateDistribucion->bind_param("sdi", $nuevaDistribucion, $monto_eliminar, $id);
        $stmtUpdateDistribucion->execute();

        // Actualizar monto_total en asignacion_ente
        $sqlUpdateAsignacion = "UPDATE asignacion_ente SET monto_total = monto_total - ? WHERE id = ?";
        $stmtUpdateAsignacion = $conexion->prepare($sqlUpdateAsignacion);
        $stmtUpdateAsignacion->bind_param("di", $monto_eliminar, $id_asignacion);
        $stmtUpdateAsignacion->execute();

        $conexion->commit();
        return json_encode(["success" => "Distribución eliminada correctamente."]);
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// PROCESAR SOLICITUDES
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["accion"])) {
    echo json_encode(["error" => "Acción no especificada."]);
    exit;
}

$accion = $data["accion"];
$response = null;

switch ($accion) {
    case "actualizar":
        $response = actualizar($data);
        break;
    case "borrar":
        $response = eliminar($data);
        break;
    default:
        $response = ["error" => "Acción inválida."];
}

echo $response;

?>
