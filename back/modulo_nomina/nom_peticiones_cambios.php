<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
function procesarPeticion($id_peticion, $conexion)
{
    $response = [];


    try {
        $status = null;

        // Verificar el status de la petición
        $consultaPeticion = "SELECT status FROM peticiones WHERE id = ?";


        $stmtPeticion = $conexion->prepare($consultaPeticion);
        $stmtPeticion->bind_param('i', $id_peticion);
        $stmtPeticion->execute();
        $stmtPeticion->store_result();
        $stmtPeticion->bind_result($status);
        $stmtPeticion->fetch();

        if ($stmtPeticion->num_rows > 0 && $status == 2) {
            // Consultar correcciones
            $consultaCorrecciones = "SELECT 
                    corr.*, 
                    mov.id AS movimiento_id, mov.id_empleado, mov.tabla, mov.campo, mov.valor_anterior, mov.valor_nuevo, mov.descripcion AS movimiento_descripcion,mov.status AS movimiento_status 
                FROM 
                    correcciones corr
                JOIN 
                    movimientos mov ON mov.id = corr.movimiento_id
                WHERE 
                    corr.peticion_id = ? 
                AND 
                    mov.status = 1
            ";

            $stmtCorrecciones = $conexion->prepare($consultaCorrecciones);
            $stmtCorrecciones->bind_param('i', $id_peticion);
            $stmtCorrecciones->execute();
            $resultCorrecciones = $stmtCorrecciones->get_result();

            if ($resultCorrecciones->num_rows > 0) {
                $correcciones = $resultCorrecciones->fetch_all(MYSQLI_ASSOC);
                $response = json_encode(["success" => $correcciones]);
            } else {
                throw new Exception("No se encontraron correcciones con el ID de petición proporcionado.");
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