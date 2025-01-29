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


function obtenerSumatoriaPorTipoSPP($ejercicio, $tipo)
{
    global $conexion;

    try {
        if (empty($ejercicio) || empty($tipo)) {
            throw new Exception("Debe proporcionar un ejercicio y un tipo válido");
        }

        $datos_consultados = obtenerIndiceTipo($tipo);
        $columna = $datos_consultados[0];
        $tabla_join = $datos_consultados[1];

        $sql = "SELECT 
                    DP.id_sector, DP.id_programa, DP.id_actividad, DP.$columna, 
                    DP.monto_inicial, DP.monto_actual
                FROM distribucion_presupuestaria DP
                INNER JOIN distribucion_entes DE ON JSON_CONTAINS(DE.distribucion, CONCAT('{\"id_distribucion\":\"', DP.id, '\"}'))
                INNER JOIN entes_dependencias ED ON DE.actividad_id = ED.id
                WHERE DP.id_ejercicio = ? AND ED.juridico = 0";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ejercicio);
        $stmt->execute();
        $resultadoDistribucion = $stmt->get_result();

        $partidasDatos = [];

        while ($row = $resultadoDistribucion->fetch_assoc()) {
            $montoInicial = (float) $row['monto_inicial'];
            $montoActual = (float) $row['monto_actual'];
            $value = returnValue($tabla_join, $row[$columna]) ?? '00';

            if (isset($partidasDatos[$value])) {
                $partidasDatos[$value]['total_inicial'] += $montoInicial;
                $partidasDatos[$value]['total_restante'] += $montoActual;
            } else {
                $partidasDatos[$value] = [
                    'value' => $value,
                    'total_inicial' => $montoInicial,
                    'total_restante' => $montoActual
                ];
            }
        }

        return json_encode(array_values($partidasDatos));
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}




// Función para obtener la sumatoria agrupada por los primeros tres dígitos de la partida o por sector/programa/partida
function obtenerSumatoriaPorPartida($ejercicio, $tipo)
{
    global $conexion;

    try {
        if (empty($ejercicio)) {
            throw new Exception("Debe proporcionar un ejercicio válido");
        }

        // Consulta optimizada con JOIN para reducir consultas individuales
        $sql = "SELECT 
                    dp.id_partida, dp.monto_inicial, dp.monto_actual, 
                    dp.id_sector, dp.id_programa, dp.id_actividad, 
                    pp.partida,
                    ps.sector AS sector_valor, 
                    ppgr.programa AS programa_valor
                FROM distribucion_presupuestaria dp
                JOIN partidas_presupuestarias pp ON dp.id_partida = pp.id
                LEFT JOIN pl_sectores ps ON dp.id_sector = ps.id
                LEFT JOIN pl_programas ppgr ON dp.id_programa = ppgr.id
                WHERE dp.id_ejercicio = ?
                AND EXISTS (
                    SELECT * FROM entes_dependencias ed 
                    WHERE ed.sector = dp.id_sector 
                    AND ed.programa = dp.id_programa 
                    AND ed.actividad = dp.id_actividad 
                    AND ed.juridico = 0
                )";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ejercicio);
        $stmt->execute();
        $resultadoDistribucion = $stmt->get_result();

        $partidasDatos = [];

        while ($row = $resultadoDistribucion->fetch_assoc()) {
            $grupoPartida = substr($row['partida'], 0, 3);

            if ($tipo === "partida_programa") {
                $sectorValor = str_pad($row['sector_valor'] ?? '00', 2, '0', STR_PAD_LEFT);
                $programaValor = str_pad($row['programa_valor'] ?? '00', 2, '0', STR_PAD_LEFT);
                $grupoPartida = sprintf("%02s.%02s.%s", $sectorValor, $programaValor, $grupoPartida);
            }

            if (isset($partidasDatos[$grupoPartida])) {
                $partidasDatos[$grupoPartida]['total_inicial'] += (float)$row['monto_inicial'];
                $partidasDatos[$grupoPartida]['total_restante'] += (float)$row['monto_actual'];
            } else {
                $partidasDatos[$grupoPartida] = [
                    'value' => $grupoPartida,
                    'total_inicial' => (float)$row['monto_inicial'],
                    'total_restante' => (float)$row['monto_actual']
                ];
            }
        }

        return json_encode(array_values($partidasDatos));
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}





function obtenerSumatoriaPorActividad($ejercicio)
{
    global $conexion;

    try {
        if (empty($ejercicio)) {
            throw new Exception("Debe proporcionar un ejercicio válido");
        }

        // Consulta con JOIN para simplificar la obtención de datos
        $sql = "SELECT 
                    dp.id_actividad, 
                    dp.monto_inicial, 
                    dp.monto_actual, 
                    dp.id_sector, 
                    dp.id_programa, 
                    ed.juridico, 
                    ps.sector AS sector_valor
                FROM distribucion_entes de
                JOIN JSON_TABLE(de.distribucion, '$[*]' COLUMNS (id_distribucion INT PATH '$.id_distribucion')) AS dist_json
                    ON dist_json.id_distribucion = de.id
                JOIN distribucion_presupuestaria dp 
                    ON dp.id = dist_json.id_distribucion
                JOIN entes_dependencias ed 
                    ON de.actividad_id = ed.id
                LEFT JOIN pl_sectores ps 
                    ON dp.id_sector = ps.id
                WHERE dp.id_ejercicio = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ejercicio);
        $stmt->execute();
        $resultadoDistribucion = $stmt->get_result();

        $actividadesDatos = [];

        while ($row = $resultadoDistribucion->fetch_assoc()) {
            if ($row['juridico'] != 0) {
                continue; // Omitir registros donde jurídico no sea 0
            }

            $sectorValor = str_pad($row['sector_valor'] ?? '00', 2, '0', STR_PAD_LEFT);
            $grupoActividad = sprintf("%02s.%s", $sectorValor, $row['id_actividad']);

            if (isset($actividadesDatos[$grupoActividad])) {
                $actividadesDatos[$grupoActividad]['total_inicial'] += (float)$row['monto_inicial'];
                $actividadesDatos[$grupoActividad]['total_restante'] += (float)$row['monto_actual'];
            } else {
                $actividadesDatos[$grupoActividad] = [
                    'value' => $grupoActividad,
                    'total_inicial' => (float)$row['monto_inicial'],
                    'total_restante' => (float)$row['monto_actual']
                ];
            }
        }

        return json_encode(array_values($actividadesDatos));
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
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
        "partida_progama" => ['id_partida', 'partidas_presupuestarias', 0],
        "generica" => ['id_partida', 'partidas_presupuestarias', 0],
        "especifica" => ['id_partida', 'partidas_presupuestarias', 1],
        "subespecifica" => ['id_partida', 'partidas_presupuestarias', 2]
    ];

    return isset($tipos[$tipo]) ? $tipos[$tipo] : null;
}

// Procesar la solicitud
//echo obtenerSumatoriaPorPartida($ejercicio, $tipo);








$data = json_decode(file_get_contents("php://input"), true);
if (isset($data["ejercicio"]) && isset($data["tipo"])) {
    $ejercicio = $data["ejercicio"];
    $tipo = $data["tipo"]; // Recibimos solo un valor de tipo

    // Llamar a la función para obtener las sumatorias por tipo
    if ($tipo == "sector" || $tipo == "programa" || $tipo == "proyecto") {
        echo obtenerSumatoriaPorTipoSPP($ejercicio, $tipo);
    } elseif ($tipo == "actividad") {
        echo obtenerSumatoriaPorActividad($ejercicio, $tipo);
    } else {
        echo obtenerSumatoriaPorPartida($ejercicio, $tipo);
    }
} else {
    echo json_encode(['error' => "No se proporcionaron los datos necesarios (ejercicio y tipo)"]);
}
