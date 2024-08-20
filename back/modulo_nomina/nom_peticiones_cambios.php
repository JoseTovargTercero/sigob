<?php
require_once '../sistema_global/conexion.php';

function procesarPeticion($id_peticion, $conexion)
{
    $response = [];

    try {
        // Verificar el status de la petición
        $consultaPeticion = "SELECT status FROM peticiones WHERE id = :id_peticion";
        $stmtPeticion = $conexion->prepare($consultaPeticion);
        $stmtPeticion->bindParam(':id_peticion', $id_peticion, PDO::PARAM_INT);
        $stmtPeticion->execute();

        $peticion = $stmtPeticion->fetch(PDO::FETCH_ASSOC);

        if ($peticion && $peticion['status'] == 2) {
            // Consultar correcciones
            $consultaCorrecciones = "
                SELECT 
                    corr.*, 
                    mov.id AS movimiento_id, mov.id_empleado, mov.tabla, mov.campo, mov.valor_anterior, mov.valor_nuevo, mov.status AS movimiento_status 
                FROM 
                    correcciones corr
                JOIN 
                    movimientos mov ON mov.id = corr.movimiento_id
                WHERE 
                    corr.peticion_id = :id_peticion 
                AND 
                    mov.status = 1
            ";

            $stmtCorrecciones = $conexion->prepare($consultaCorrecciones);
            $stmtCorrecciones->bindParam(':id_peticion', $id_peticion, PDO::PARAM_INT);
            $stmtCorrecciones->execute();

            $correcciones = $stmtCorrecciones->fetchAll(PDO::FETCH_ASSOC);

            if ($correcciones) {
                $response = json_encode(["success" => true, "correcciones" => $correcciones]);
            } else {
                $response = json_encode(["error" => "No se encontraron correcciones con el ID de petición proporcionado."]);
            }
        } else {
            $response = json_encode(["error" => "La petición no tiene un status de 2 o no se encontró."]);
        }

    } catch (\Exception $e) {
        $response = json_encode(["error" => $e->getMessage()]);
    }

    return $response;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_peticion = $data['id_peticion'] ?? null;

if ($id_peticion) {
    $response = procesarPeticion($id_peticion, $conexion);
    echo $response;
} else {
    echo json_encode(["error" => "No se proporcionó un ID de petición."]);
}
?>
