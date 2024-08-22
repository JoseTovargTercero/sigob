<?php
require_once '../sistema_global/conexion.php';

function revertirAccion($revertirArray, $conexion)
{
    foreach ($revertirArray as $id_correccion) {
        try {
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
                $updateStmt->execute();

                $deleteMovimientoConsulta = "DELETE FROM movimientos WHERE id = ?";
                $deleteMovimientoStmt = $conexion->prepare($deleteMovimientoConsulta);
                $deleteMovimientoStmt->bind_param('i', $movimiento_id);
                $deleteMovimientoStmt->execute();

                $deleteCorreccionConsulta = "DELETE FROM correcciones WHERE id = ?";
                $deleteCorreccionStmt = $conexion->prepare($deleteCorreccionConsulta);
                $deleteCorreccionStmt->bind_param('i', $id_correccion);
                $deleteCorreccionStmt->execute();
            } else {
                return json_encode(["error" => "No se encontró el movimiento con el ID proporcionado."]);
            }
        } catch (\Exception $e) {
            return json_encode(["error" => $e->getMessage()]);
        }
    }

    return json_encode(["success" => "Movimiento revertido y registros eliminados correctamente."]);
}

function manualAccion($data, $conexion)
{
    foreach ($data['manual'] as $correccion) {
        try {
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
                $updateStmt = $conexion->prepare($updateConsulta);
                $updateStmt->bind_param('si', $nuevo_valor, $id_empleado);
                $updateStmt->execute();

                $updateMovimientoConsulta = "UPDATE movimientos SET status = 2 WHERE id = ?";
                $updateMovimientoStmt = $conexion->prepare($updateMovimientoConsulta);
                $updateMovimientoStmt->bind_param('i', $movimiento_id);
                $updateMovimientoStmt->execute();

                $updateCorreccionConsulta = "UPDATE correcciones SET status = 1 WHERE id = ?";
                $updateCorreccionStmt = $conexion->prepare($updateCorreccionConsulta);
                $updateCorreccionStmt->bind_param('i', $id_correccion);
                $updateCorreccionStmt->execute();
            } else {
                return json_encode(["error" => "No se encontró la corrección con el ID proporcionado."]);
            }
        } catch (\Exception $e) {
            return json_encode(["error" => $e->getMessage()]);
        }
    }

    return json_encode(["success" => "Modificaciones manuales aplicadas correctamente."]);
}

function procesarAccion($data, $conexion)
{
    $response = [];

    if (!empty($data['revertir'])) {
        $response[] = revertirAccion($data, $conexion);
    }

    if (!empty($data['manual'])) {
        $response[] = manualAccion($data, $conexion);
    }

    return json_encode($response);
}

$data = json_decode(file_get_contents('php://input'), true);
$response = procesarAccion($data, $conexion);

echo $response;
?>
