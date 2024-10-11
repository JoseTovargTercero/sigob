<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
$response = '';

function revertirAccion($data, $conexion)
{
    $conexion->autocommit(false);
    $conexion->begin_transaction();
    try {

        if (!empty($data['revertir'])) {
            foreach ($data["revertir"] as $id_correccion) {
                $consulta = "
                        SELECT 
                            mov.id_empleado, mov.tabla, mov.campo, mov.valor_anterior, mov.valor_nuevo, mov.status, 
                            corr.* 
                        FROM 
                            correcciones corr
                        JOIN 
                            movimientos mov ON mov.id = corr.movimiento_id
                        WHERE 
                            corr.id = ?
                    ";

                $stmt = $conexion->prepare($consulta);
                $stmt->bind_param('i', $id_correccion);
                $stmt->execute();

                $result = $stmt->get_result();
                $movimiento = $result->fetch_assoc();

                if ($movimiento) {
                    $movimiento_id = $movimiento['movimiento_id'];
                    $id_empleado = $movimiento['id_empleado'];
                    $tabla = $movimiento['tabla'];
                    $campo = $movimiento['campo'];
                    $valor_anterior = $movimiento['valor_anterior'];

                    $updateConsulta = "UPDATE $tabla SET $campo = ? WHERE id = ?";
                    $updateStmt = $conexion->prepare($updateConsulta);
                    $updateStmt->bind_param('si', $valor_anterior, $id_empleado);

                    if (!$updateStmt->execute()) {
                        throw new Exception("Error al momento de actualizar con nuevos cambios $updateStmt->error");
                    }

                    $deleteMovimientoConsulta = "DELETE FROM movimientos WHERE id = ?";
                    $deleteMovimientoStmt = $conexion->prepare($deleteMovimientoConsulta);
                    $deleteMovimientoStmt->bind_param('i', $movimiento_id);
                    $deleteMovimientoStmt->execute();

                    if (!$deleteMovimientoStmt->execute()) {
                        throw new Exception("Error al momento de borrar movimiento $updateStmt->error");
                    }

                    $deleteCorreccionConsulta = "DELETE FROM correcciones WHERE id = ?";
                    $deleteCorreccionStmt = $conexion->prepare($deleteCorreccionConsulta);
                    $deleteCorreccionStmt->bind_param('i', $id_correccion);
                    $deleteCorreccionStmt->execute();

                    if (!$deleteCorreccionStmt->execute()) {
                        throw new Exception("Error al momento de borrar corrección $updateStmt->error");
                    }

                } else {
                    throw new Exception("No se encontró el movimiento con el ID proporcionado.");
                }
            }
        }

        if (!empty($data['manual'])) {
            foreach ($data['manual'] as $correccion) {
                $id_correccion = $correccion['id_correccion'];
                $id_empleado = $correccion['id_empleado'];
                $tabla = $correccion['tabla'];
                $campo = $correccion['campo'];
                $nuevo_valor = $correccion['nuevo_valor'];

                $consulta = "
                        SELECT 
                            mov.id AS movimiento_id, mov.id_empleado, mov.tabla, mov.campo, mov.valor_anterior, mov.valor_nuevo, mov.status, 
                            corr.* 
                        FROM 
                            correcciones corr
                        JOIN 
                            movimientos mov ON mov.id = corr.movimiento_id
                        WHERE 
                            corr.id = ?
                    ";

                $stmt = $conexion->prepare($consulta);
                $stmt->bind_param('i', $id_correccion);
                $stmt->execute();

                $result = $stmt->get_result();
                $movimiento = $result->fetch_assoc();

                if ($movimiento) {
                    $movimiento_id = $movimiento['movimiento_id'];

                    $updateConsulta = "UPDATE $tabla SET $campo = ? WHERE id = ?";
                    $updateStmt2 = $conexion->prepare($updateConsulta);
                    $updateStmt2->bind_param('si', $nuevo_valor, $id_empleado);
                    if (!$updateStmt2->execute()) {
                        throw new Exception("Error al momento de actualizar con nuevos cambios $updateStmt2->error");
                    }


                    $updateMovimientoConsulta = "UPDATE movimientos SET status = 2 WHERE id = ?";
                    $updateMovimientoStmt = $conexion->prepare($updateMovimientoConsulta);
                    $updateMovimientoStmt->bind_param('i', $movimiento_id);
                    if (!$updateMovimientoStmt->execute()) {
                        throw new Exception("Error al momento de actualizar movimientos $updateStmt2->error");
                    }

                    $updateCorreccionConsulta = "UPDATE correcciones SET status = 1 WHERE id = ?";
                    $updateCorreccionStmt = $conexion->prepare($updateCorreccionConsulta);
                    $updateCorreccionStmt->bind_param('i', $id_correccion);
                    if (!$updateCorreccionStmt->execute()) {
                        throw new Exception("Error al momento de actualizar correciones $updateStmt2->error");
                    }

                } else {
                    throw new Exception("No se encontró la corrección con el ID proporcionado.");
                }
            }
        }


        $conexion->commit();
        return json_encode(["success" => "Correciones realizadas"]);

    } catch (\Exception $e) {
        $conexion->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }

}


$data = json_decode(file_get_contents('php://input'), true);
$response = revertirAccion($data, $conexion);

echo $response;
?>