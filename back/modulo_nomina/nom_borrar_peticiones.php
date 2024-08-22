<?php
require_once '../sistema_global/conexion.php';

function eliminarRegistros($data, $conexion)
{
    $correlativo = $data['correlativo'];
    $id_peticion = $data['id_peticion'];

    try {
        // Consulta para obtener los movimiento_id de la tabla correcciones
        $consultaMovimientos = "
            SELECT movimiento_id 
            FROM correcciones 
            WHERE peticion_id = ?
        ";

        $stmtMovimientos = $conexion->prepare($consultaMovimientos);
        $stmtMovimientos->bind_param('i', $id_peticion);
        $stmtMovimientos->execute();
        $resultMovimientos = $stmtMovimientos->get_result();
        $movimiento_ids = $resultMovimientos->fetch_all(MYSQLI_ASSOC);

        if ($movimiento_ids) {
            $movimiento_ids_array = array_column($movimiento_ids, 'movimiento_id');
            $ids_string = implode(',', array_map('intval', $movimiento_ids_array));

            // Eliminar registros de la tabla movimientos
            $deleteMovimientos = "
                DELETE FROM movimientos 
                WHERE id IN ($ids_string)
            ";
            $conexion->query($deleteMovimientos);

            // Eliminar registros de la tabla correcciones
            $deleteCorrecciones = "
                DELETE FROM correcciones 
                WHERE peticion_id = ?
            ";
            $stmtDeleteCorrecciones = $conexion->prepare($deleteCorrecciones);
            $stmtDeleteCorrecciones->bind_param('i', $id_peticion);
            $stmtDeleteCorrecciones->execute();
        }

        // Eliminar registros de las tablas recibo_pago, peticiones, txt, informacion_pdf donde correlativo coincida
        $tablas = ['recibo_pago', 'peticiones', 'txt', 'informacion_pdf'];
        foreach ($tablas as $tabla) {
            $deleteConsulta = "
                DELETE FROM $tabla 
                WHERE correlativo = ?
            ";
            $stmtDelete = $conexion->prepare($deleteConsulta);
            $stmtDelete->bind_param('s', $correlativo);
            $stmtDelete->execute();
        }

        return json_encode(["success" => "Registros eliminados correctamente."]);

    } catch (\Exception $e) {
        return json_encode(["error" => $e->getMessage()]);
    }
}

$data = json_decode(file_get_contents('php://input'), true);
$response = eliminarRegistros($data, $conexion);

echo $response;
?>
