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
                    corr.id = :id_correccion
            ";

            $stmt = $conexion->prepare($consulta);
            $stmt->bindParam(':id_correccion', $id_correccion, PDO::PARAM_INT);
            $stmt->execute();

            $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($movimiento) {
                $movimiento_id = $movimiento['movimiento_id'];
                $id_empleado = $movimiento['id_empleado'];
                $tabla = $movimiento['tabla'];
                $campo = $movimiento['campo'];
                $valor_anterior = $movimiento['valor_anterior'];

                $updateConsulta = "UPDATE $tabla SET $campo = :valor_anterior WHERE id = :id_empleado";

                $updateStmt = $conexion->prepare($updateConsulta);
                $updateStmt->bindParam(':valor_anterior', $valor_anterior, PDO::PARAM_STR);
                $updateStmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
                $updateStmt->execute();

                $deleteMovimientoConsulta = "DELETE FROM movimientos WHERE id = :movimiento_id";
                $deleteMovimientoStmt = $conexion->prepare($deleteMovimientoConsulta);
                $deleteMovimientoStmt->bindParam(':movimiento_id', $movimiento_id, PDO::PARAM_INT);
                $deleteMovimientoStmt->execute();

                $deleteCorreccionConsulta = "DELETE FROM correcciones WHERE id = :id_correccion";
                $deleteCorreccionStmt = $conexion->prepare($deleteCorreccionConsulta);
                $deleteCorreccionStmt->bindParam(':id_correccion', $id_correccion, PDO::PARAM_INT);
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
                    corr.id = :id_correccion
            ";

            $stmt = $conexion->prepare($consulta);
            $stmt->bindParam(':id_correccion', $id_correccion, PDO::PARAM_INT);
            $stmt->execute();

            $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($movimiento) {
                $movimiento_id = $movimiento['movimiento_id'];

                $updateConsulta = "UPDATE $tabla SET $campo = :nuevo_valor WHERE id = :id_empleado";

                $updateStmt = $conexion->prepare($updateConsulta);
                $updateStmt->bindParam(':nuevo_valor', $nuevo_valor, PDO::PARAM_STR);
                $updateStmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
                $updateStmt->execute();

                $updateMovimientoConsulta = "UPDATE movimientos SET status = 2 WHERE id = :movimiento_id";
                $updateMovimientoStmt = $conexion->prepare($updateMovimientoConsulta);
                $updateMovimientoStmt->bindParam(':movimiento_id', $movimiento_id, PDO::PARAM_INT);
                $updateMovimientoStmt->execute();

                $updateCorreccionConsulta = "UPDATE correcciones SET status = 1 WHERE id = :id_correccion";
                $updateCorreccionStmt = $conexion->prepare($updateCorreccionConsulta);
                $updateCorreccionStmt->bindParam(':id_correccion', $id_correccion, PDO::PARAM_INT);
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
