<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';


function returnValue($tabla, $id)
{
    global $conexion;

    if ($tabla == 'pl_programas') {
        $stmt = mysqli_prepare($conexion, "SELECT pl_programas.*, pl_sectores.sector as sector_n FROM `pl_programas`
        LEFT JOIN pl_sectores ON pl_sectores.id = pl_programas.sector
         WHERE pl_programas.id = ?");
    } else {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `$tabla` WHERE id = ?");
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            switch ($tabla) {
                case 'pl_sectores':
                    return $row['sector'];
                    break;
                case 'pl_programas':
                    return $row['sector_n'] . '.' . $row['programa'];
                    break;
                case 'pl_proyectos':
                    return $row['proyecto_id'];
                    break;
            }
        }
    }
    $stmt->close();
}


// Función para obtener sumatoria según el tipo
function obtenerSumatoriaPorTipoSPP($ejercicio, $tipo)
{
    global $conexion;

    try {
        // Validar que ejercicio y tipo no estén vacíos
        if (empty($ejercicio) || empty($tipo)) {
            throw new Exception("Debe proporcionar un ejercicio y un tipo válido");
        }

        $datos_consultados = obtenerIndiceTipo($tipo);

        $columna = $datos_consultados[0];
        $tabla_join = $datos_consultados[1];
        $campo_join = $datos_consultados[2];


        // Crear un array para almacenar los resultados finales
        $resultados = [];

        // Consultar la tabla distribucion_presupuestaria para obtener los registros con el id_ejercicio dado
        $sql = "SELECT DP.$columna, DP.monto_inicial, DP.monto_actual FROM distribucion_presupuestaria AS DP 
        WHERE id_ejercicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ejercicio);
        $stmt->execute();
        $resultadoDistribucion = $stmt->get_result();

        // Array para almacenar los datos de las partidas
        $partidasDatos = [];

        // Iterar sobre los registros obtenidos de distribucion_presupuestaria
        while ($row = $resultadoDistribucion->fetch_assoc()) {
            $montoInicial = isset($row['monto_inicial']) ? (float)$row['monto_inicial'] : 0; // Asegurar que sea numérico
            $montoActual = isset($row['monto_actual']) ? (float)$row['monto_actual'] : 0;   // Asegurar que sea numérico
            $value = returnValue($tabla_join, $row[$columna]) ?? '00';

            // Si el valor ya existe en el array, sumamos los montos
            if (isset($partidasDatos[$value])) {
                $partidasDatos[$value]['total_inicial'] += $montoInicial;
                $partidasDatos[$value]['total_restante'] += $montoActual;
            } else {
                // Si no existe, lo agregamos
                $partidasDatos[$value] = [
                    'value' => $value,
                    'total_inicial' => $montoInicial,
                    'total_restante' => $montoActual
                ];
            }
        }

        // Preparar el array final de resultados
        foreach ($partidasDatos as $datos) {
            $resultados[] = [
                'value' => $datos['value'],
                'total_inicial' => $datos['total_inicial'],
                'total_restante' => $datos['total_restante']
            ];
        }

        // Devolver los resultados como JSON
        return json_encode($resultados);
    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}



function obtenerSumatoriaPorPartida(){

}


function obtenerSumatoriaPorActiviad(){

}






// Función auxiliar para obtener el índice del tipo en la partida
function obtenerIndiceTipo($tipo)
{
    $tipos = [
        "sector" => ['id_sector', 'pl_sectores', 'sector'],
        "programa" => ['id_programa', 'pl_programas', 'programa'],
        "proyecto" => ['id_proyecto', 'pl_proyectos', 'proyecto_id'],

        "actividad" => ['id_actividad', ''],
        "partida" => ['id_partida', 'partidas_presupuestarias', 0],
        "generica" => ['id_partida', 'partidas_presupuestarias', 0],
        "especifica" => ['id_partida', 'partidas_presupuestarias', 1],
        "subespecifica" => ['id_partida', 'partidas_presupuestarias', 2]
    ];

    return isset($tipos[$tipo]) ? $tipos[$tipo] : null;
}

// Procesar la solicitud
echo obtenerSumatoriaPorPartida($ejercicio, $tipo);



/*
echo obtenerSumatoriaPorTipo('1', 'programa');
*/
exit;
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data["ejercicio"]) && isset($data["tipo"])) {
    $ejercicio = $data["ejercicio"];
    $tipo = $data["tipo"]; // Recibimos solo un valor de tipo

    // Llamar a la función para obtener las sumatorias por tipo
    if ($tipo == "sector" || $tipo == "programa" || $tipo == "proyecto") {
        echo obtenerSumatoriaPorTipoSPP($ejercicio, $tipo);
    } elseif($tipo == "actividad"){
        echo obtenerSumatoriaPorActiviad($ejercicio, $tipo);
    }else{
        echo obtenerSumatoriaPorPartida($ejercicio, $tipo);
    }
} else {
    echo json_encode(['error' => "No se proporcionaron los datos necesarios (ejercicio y tipo)"]);
}
