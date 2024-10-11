<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para guardar en la tabla ejercicio_fiscal
function guardarEjercicioPlan($id, $monto)
{
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id) || empty($monto)) {
            throw new Exception("Faltaron uno o más valores (ano, situado)");
        }

        // Al guardar, el status siempre debe ser 1
        $status = 1;

        // Insertar los datos en la tabla
        $sql = "INSERT INTO plan_inversion (id_ejercicio, monto_total) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $id, $monto);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Datos del plan de inversión guardados correctamente"]);
        } else {
            throw new Exception("No se pudo guardar los datosl");
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

    if ($accion === "insert") {
        if (empty($data["id"]) || empty($data["monto"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (monto)"]);
        } else {
            echo guardarEjercicioPlan($data["id"], $data["monto"]);
        }
    }
} else {
    echo json_encode(['error' => "No se especificó ninguna acción"]);
}
