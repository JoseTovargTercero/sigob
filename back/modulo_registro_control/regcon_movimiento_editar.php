<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
// Verificar si se envió la acción
$response;

try {

    if (!isset($data["informacion"])) {
        throw new Exception("No se ha inticado la información a actualizar");
    }
    if (!isset($data["accion"])) {
        throw new Exception("Acción no especificada.");
    }

    $id = $data['id'];

    if ($data["accion"] === 'status') {
        foreach ($data['informacion'] as $id) {

            $stmt = mysqli_prepare($conexion, "UPDATE empleados SET status = ? WHERE id = $id");

            $sql = "UPDATE movimiento SET status = 1 WHERE id = $id";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Error al momento de preparar la consulta");
            }

            if ($stmt->execute()) {
                $stmt->close();
                $response = json_encode(['success' => "Movimiento actualizado"]);

            } else {
                throw new Exception("Error al actualizar movimiento");
            }
        }


    }

    $response = json_encode(["success" => "No se ha especificado acción"]);


} catch (\Exception $e) {
    // En caso de error, revertir la transacción
    $conexion->rollback();
    // Devolver una respuesta de error al cliente
    $response = json_encode(['error' => $e->getMessage()]);
}

echo $response;