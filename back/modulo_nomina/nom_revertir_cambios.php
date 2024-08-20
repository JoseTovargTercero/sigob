<?php
require_once '../sistema_global/conexion.php';

function procesarAccion($data, $conexion)
{
    $response = [];

    try {
        // Acción revertir
        if ($data['accion'] === 'revertir') {
            $movimiento_id = $data['movimiento_id'];

            $consulta = "
                SELECT 
                    mov.id_empleado, mov.tabla, mov.campo, mov.valor_anterior, mov.valor_nuevo, mov.status, 
                    corr.* 
                FROM 
                    correcciones corr
                JOIN 
                    movimientos mov ON mov.id = corr.movimiento_id
                WHERE 
                    corr.movimiento_id = :movimiento_id
            ";

            $stmt = $conexion->prepare($consulta);
            $stmt->bindParam(':movimiento_id', $movimiento_id, PDO::PARAM_INT);
            $stmt->execute();

            $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($movimiento) {
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

                $deleteCorreccionConsulta = "DELETE FROM correcciones WHERE movimiento_id = :movimiento_id";
                $deleteCorreccionStmt = $conexion->prepare($deleteCorreccionConsulta);
                $deleteCorreccionStmt->bindParam(':movimiento_id', $movimiento_id, PDO::PARAM_INT);
                $deleteCorreccionStmt->execute();

                $response = json_encode(["success" => "Movimiento revertido y registros eliminados correctamente."]);
            } else {
                $response = json_encode(["error" => "No se encontró el movimiento con el ID proporcionado."]);
            }

        // Acción manual
        } elseif ($data['accion'] === 'manual') {
            foreach ($data['correcciones'] as $correccion) {
                $id_correccion = $correccion['id_correccion'];
                $id_empleado = $correccion['id_empleado'];
                $tabla = $correccion['tabla'];
                $campo = $correccion['campo'];
                $nuevo_valor = $correccion['nuevo_valor'];

                // Realizar la consulta para obtener los datos necesarios de correcciones y movimientos
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

                    // Actualizar la tabla con el nuevo valor
                    $updateConsulta = "UPDATE $tabla SET $campo = :nuevo_valor WHERE id = :id_empleado";

                    $updateStmt = $conexion->prepare($updateConsulta);
                    $updateStmt->bindParam(':nuevo_valor', $nuevo_valor, PDO::PARAM_STR);
                    $updateStmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
                    $updateStmt->execute();

                    // Actualizar el status en la tabla movimientos
                    $updateMovimientoConsulta = "UPDATE movimientos SET status = 2 WHERE id = :movimiento_id";
                    $updateMovimientoStmt = $conexion->prepare($updateMovimientoConsulta);
                    $updateMovimientoStmt->bindParam(':movimiento_id', $movimiento_id, PDO::PARAM_INT);
                    $updateMovimientoStmt->execute();

                    // Actualizar el status en la tabla correcciones
                    $updateCorreccionConsulta = "UPDATE correcciones SET status = 1 WHERE id = :id_correccion";
                    $updateCorreccionStmt = $conexion->prepare($updateCorreccionConsulta);
                    $updateCorreccionStmt->bindParam(':id_correccion', $id_correccion, PDO::PARAM_INT);
                    $updateCorreccionStmt->execute();
                } else {
                    $response = json_encode(["error" => "No se encontró la corrección con el ID proporcionado."]);
                }
            }

            $response = json_encode(["success" => "Modificaciones manuales aplicadas correctamente."]);
        } else {
            $response = json_encode(["Error" => "Acción no reconocida."]);
        }

    } catch (\Exception $e) {
        $response = json_encode(["error" => $e->getMessage()]);
    }

    return $response;
}

$data = json_decode(file_get_contents('php://input'), true);
$response = procesarAccion($data, $conexion);

echo $response;
?>
