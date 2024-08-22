<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

$data = json_decode(file_get_contents('php://input'), true);

$response;


$conexion->autocommit(false);
$conexion->begin_transaction();

try {
    if (!isset($data)) {
        throw new Exception('No se recibió la información necesaria');
    }

    if (isset($data["peticion"])) {

        $peticion = $data["peticion"];
        $id_peticion = $peticion["id"];
        $correccion = $peticion["correccion"];

        $sql = "UPDATE peticiones SET status = 2, correccion = ? WHERE id = ?";
        $peticion_stmt = $conexion->prepare($sql);
        if (!$peticion_stmt) {
            throw new Exception("Error al preparar consulta de rechazo: $peticion_stmt->error");
        }
        $peticion_stmt->bind_param("si", $correccion, $id_peticion);

        if ($peticion_stmt->execute()) {
            $peticion_stmt->close();
        } else {
            throw new Exception("Error al cambiar status de peticion $peticion_stmt->error");
        }

        if (isset($data["correcciones"])) {

            foreach ($data["correcciones"] as $item) {
                if (empty($item[0]) || empty($item[1])) {
                    throw new Exception("Error: el campo 'id' o 'value' no puede estar vacío.");
                }

                $usuario = $_SESSION['u_id'];
                $movimiento_id = $item[0];
                $descripcion = $item[1];
                $peticionId = $item[2];
                $fecha_actual = date('Y-m-d H:i:s');
                $status = 0;

                $sql = 'INSERT INTO correcciones (usuario_id, movimiento_id, descripcion, fecha_correccion, status, peticion_id) VALUES (?,?,?,?,0,?)';
                $correccion_stmt = $conexion->prepare($sql);
                if (!$correccion_stmt) {
                    throw new Exception("Error al preparar consulta de correccion $correccion_stmt->error");
                }
                ;
                $correccion_stmt->bind_param('iissi', $usuario, $movimiento_id, $descripcion, $fecha_actual, $peticionId);
                if ($correccion_stmt->execute()) {
                    $correccion_stmt->close();
                } else {
                    throw new Exception("Error al ejecutar consulta de correccion: $correccion_stmt->error");
                }

            }

        }

        if (isset($data["movimientos"])) {
            foreach ($data["movimientos"] as $item) {
                if (empty($item)) {
                    throw new Exception("No se han recibido movimientos");
                }

                $sql = "UPDATE movimientos SET status = 1 WHERE id = $item";
                $correccion_stmt = $conexion->prepare($sql);
                if (!$correccion_stmt) {
                    throw new Exception("Error al preparar consulta de movimientos $correccion_stmt->error");
                }
                ;

                if ($correccion_stmt->execute()) {
                    $correccion_stmt->close();
                } else {
                    throw new Exception("Error al ejecutar consulta de movimientos: $correccion_stmt->error");
                }

            }

        }


    }
    $conexion->commit();
    notificar(['nomina'], 8);
    $response = json_encode(["success" => "Petición rechazada. Correcciones enviadas con éxito."]);

} catch (\Exception $e) {
    $conexion->rollback();

    $response = json_encode(["error" => $e->getMessage()]);
}

echo $response;


