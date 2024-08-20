<?php
require_once '../sistema_global/conexion.php';

function procesarAccion($data, $conexion)
{
    $response = [];

    try {

        // Verificar si la acción es "revertir"
        if ($data['accion'] === 'revertir') {
            $id_movimiento = $data['id_movimiento'];

            // Realizar la consulta a la tabla movimientos para obtener los valores
            $consulta = "SELECT id_empleado, tabla, campo, valor_anterior, valor_nuevo FROM movimientos WHERE id = :id_movimiento";

            // Preparar la consulta
            $stmt = $conexion->prepare($consulta);
            $stmt->bindParam(':id_movimiento', $id_movimiento, PDO::PARAM_INT);
            $stmt->execute();

            // Obtener los resultados
            $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($movimiento) {
                $id_empleado = $movimiento['id_empleado'];
                $tabla = $movimiento['tabla'];
                $campo = $movimiento['campo'];
                $valor_anterior = $movimiento['valor_anterior'];

                // Construir la consulta UPDATE dinámica
                $updateConsulta = "UPDATE $tabla SET $campo = :valor_anterior WHERE id = :id_empleado";

                // Preparar la consulta UPDATE
                $updateStmt = $conexion->prepare($updateConsulta);
                $updateStmt->bindParam(':valor_anterior', $valor_anterior, PDO::PARAM_STR);
                $updateStmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
                $updateStmt->execute();

                // Borrar el registro de la tabla movimientos
                $deleteConsulta = "DELETE FROM movimientos WHERE id = :id_movimiento";
                $deleteStmt = $conexion->prepare($deleteConsulta);
                $deleteStmt->bindParam(':id_movimiento', $id_movimiento, PDO::PARAM_INT);
                $deleteStmt->execute();

                $response = json_encode(["success" => "Movimiento revertido y registro eliminado correctamente."]);
            } else {
                $response = json_encode(["error" => "No se encontró el movimiento con el ID proporcionado."]);
            }
        } else {
            json_encode(["Error" => "Acción no reconocida."]);
        }

    } catch (\Exception $e) {
        $response = json_encode(["error" => $e->getMessage()]);
    }


}

$data = json_decode(file_get_contents('php://input'), true);



procesarAccion($data, $conexion);

echo $response;
?>