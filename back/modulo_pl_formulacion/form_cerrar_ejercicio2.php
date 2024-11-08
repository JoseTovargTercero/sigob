<?php
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función cerrarejerciciotodo
function cerrarejerciciotodo($id_ejercicio, $clave)
{
    global $conexion;

    try {
        // Consulta para obtener la contraseña del usuario con u_oficina_id = 4 y u_nivel = 1
        $sqlUsuario = "SELECT u_contrasena FROM system_users WHERE u_oficina_id = 4 AND u_nivel = 1 LIMIT 1";
        $stmtUsuario = $conexion->prepare($sqlUsuario);
        $stmtUsuario->execute();
        $resultadoUsuario = $stmtUsuario->get_result();

        // Verificar si el usuario existe
        if ($resultadoUsuario->num_rows === 0) {
            throw new Exception("Usuario no encontrado.");
        }

        $row = $resultadoUsuario->fetch_assoc();
        $stmtUsuario->close();

        // Verificar la clave proporcionada usando password_verify
        if (!password_verify($clave, $row['u_contrasena'])) {
            throw new Exception("La clave no es correcta.");
        }

        // Iniciar una transacción para asegurar que todas las actualizaciones se realicen correctamente
        $conexion->begin_transaction();

        // Actualizar el estado en la tabla ejercicio_fiscal
        $sqlEjercicioFiscal = "UPDATE ejercicio_fiscal SET status = 2 WHERE id = ?";
        $stmtEjercicioFiscal = $conexion->prepare($sqlEjercicioFiscal);
        $stmtEjercicioFiscal->bind_param("i", $id_ejercicio);
        $stmtEjercicioFiscal->execute();
        $stmtEjercicioFiscal->close();

        // Actualizar el campo status_cerrar en la tabla distribucion_presupuestaria
        $sqlDistribucionPresupuestaria = "UPDATE distribucion_presupuestaria SET status_cerrar = 1 WHERE id_ejercicio = ?";
        $stmtDistribucionPresupuestaria = $conexion->prepare($sqlDistribucionPresupuestaria);
        $stmtDistribucionPresupuestaria->bind_param("i", $id_ejercicio);
        $stmtDistribucionPresupuestaria->execute();
        $stmtDistribucionPresupuestaria->close();

        // Actualizar el campo status_cerrar en la tabla distribucion_entes
        $sqlDistribucionEntes = "UPDATE distribucion_entes SET status_cerrar = 1 WHERE id_ejercicio = ?";
        $stmtDistribucionEntes = $conexion->prepare($sqlDistribucionEntes);
        $stmtDistribucionEntes->bind_param("i", $id_ejercicio);
        $stmtDistribucionEntes->execute();
        $stmtDistribucionEntes->close();

        // Actualizar el campo status_cerrar en la tabla asignacion_ente
        $sqlAsignacionEnte = "UPDATE asignacion_ente SET status_cerrar = 1 WHERE id_ejercicio = ?";
        $stmtAsignacionEnte = $conexion->prepare($sqlAsignacionEnte);
        $stmtAsignacionEnte->bind_param("i", $id_ejercicio);
        $stmtAsignacionEnte->execute();
        $stmtAsignacionEnte->close();

        // Confirmar todas las actualizaciones
        $conexion->commit();

        return json_encode(['success' => 'Ejercicio cerrado correctamente.']);
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();

        // Registrar el error en la tabla error_log
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
    case "cerrar_ejercicio":
        $id_ejercicio = isset($data["id_ejercicio"]) ? $data["id_ejercicio"] : null;
        $clave = isset($data["clave"]) ? $data["clave"] : null;
        if ($id_ejercicio && $clave) {
            $response = cerrarejerciciotodo($id_ejercicio, $clave);
        } else {
            $response = json_encode(["error" => "ID de ejercicio o clave faltante."]);
        }
        break;
    default:
        $response = json_encode(["error" => "Acción inválida."]);
}

echo $response;

?>