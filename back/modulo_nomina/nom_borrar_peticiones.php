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
            WHERE peticion_id = :id_peticion
        ";

        $stmtMovimientos = $conexion->prepare($consultaMovimientos);
        $stmtMovimientos->bindParam(':id_peticion', $id_peticion, PDO::PARAM_INT);
        $stmtMovimientos->execute();
        $movimiento_ids = $stmtMovimientos->fetchAll(PDO::FETCH_COLUMN);

        if ($movimiento_ids) {
            // Eliminar registros de la tabla movimientos
            $deleteMovimientos = "
                DELETE FROM movimientos 
                WHERE id IN (" . implode(',', array_map('intval', $movimiento_ids)) . ")
            ";
            $conexion->exec($deleteMovimientos);

            // Eliminar registros de la tabla correcciones
            $deleteCorrecciones = "
                DELETE FROM correcciones 
                WHERE peticion_id = :id_peticion
            ";
            $stmtDeleteCorrecciones = $conexion->prepare($deleteCorrecciones);
            $stmtDeleteCorrecciones->bindParam(':id_peticion', $id_peticion, PDO::PARAM_INT);
            $stmtDeleteCorrecciones->execute();
        }

        // Eliminar registros de las tablas recibo_pago, peticiones, txt, informacion_pdf donde correlativo coincida
        $tablas = ['recibo_pago', 'peticiones', 'txt', 'informacion_pdf'];
        foreach ($tablas as $tabla) {
            $deleteConsulta = "
                DELETE FROM $tabla 
                WHERE correlativo = :correlativo
            ";
            $stmtDelete = $conexion->prepare($deleteConsulta);
            $stmtDelete->bindParam(':correlativo', $correlativo, PDO::PARAM_STR);
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