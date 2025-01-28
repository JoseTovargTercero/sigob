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
        $sql = "SELECT DP.id_sector, DP.id_programa, DP.id_actividad, DP.$columna, DP.monto_inicial, DP.monto_actual 
                FROM distribucion_presupuestaria AS DP 
                WHERE DP.id_ejercicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ejercicio);
        $stmt->execute();
        $resultadoDistribucion = $stmt->get_result();

        // Array para almacenar los datos de las partidas
        $partidasDatos = [];

        // Iterar sobre los registros obtenidos de distribucion_presupuestaria
        while ($row = $resultadoDistribucion->fetch_assoc()) {
            $idSector = $row['id_sector'];
            $idPrograma = $row['id_programa'];
            $idActividad = $row['id_actividad'];

            // Consultar entes_dependencias para verificar que el registro coincida y tenga juridico = 0
            $sqlEntes = "SELECT juridico FROM entes_dependencias 
                         WHERE sector = ? AND programa = ? AND actividad = ? AND juridico = 0";
            $stmtEntes = $conexion->prepare($sqlEntes);
            $stmtEntes->bind_param("iii", $idSector, $idPrograma, $idActividad);
            $stmtEntes->execute();
            $resultadoEntes = $stmtEntes->get_result();

            // Solo procesar si se encontró un registro con juridico = 0
            if ($resultadoEntes->num_rows > 0) {
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




// Función para obtener la sumatoria agrupada por los primeros tres dígitos de la partida o por sector/programa/partida
function obtenerSumatoriaPorPartida($ejercicio, $tipo)
{
    global $conexion;

    try {
        // Validar que el ejercicio no esté vacío
        if (empty($ejercicio)) {
            throw new Exception("Debe proporcionar un ejercicio válido");
        }

        // Consulta para obtener los registros de distribucion_presupuestaria con el id_ejercicio dado
        $sql = "SELECT DP.id_partida, DP.monto_inicial, DP.monto_actual, DP.id_sector, DP.id_programa, DP.id_actividad 
                FROM distribucion_presupuestaria AS DP 
                WHERE DP.id_ejercicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ejercicio);
        $stmt->execute();
        $resultadoDistribucion = $stmt->get_result();

        // Array para almacenar la sumatoria de los montos por los primeros tres dígitos de la partida
        $partidasDatos = [];

        // Iterar sobre los registros obtenidos de distribucion_presupuestaria
        while ($row = $resultadoDistribucion->fetch_assoc()) {
            $idPartida = $row['id_partida'];
            $montoInicial = isset($row['monto_inicial']) ? (float)$row['monto_inicial'] : 0;
            $montoActual = isset($row['monto_actual']) ? (float)$row['monto_actual'] : 0;

            // Verificar si existe un registro en entes_dependencias con los mismos id_sector, id_programa, y id_actividad, y que juridico sea 0
            $sqlDependencia = "SELECT * FROM entes_dependencias 
                               WHERE sector = ? AND programa = ? AND actividad = ? AND juridico = 0";
            $stmtDependencia = $conexion->prepare($sqlDependencia);
            $stmtDependencia->bind_param("iii", $row['id_sector'], $row['id_programa'], $row['id_actividad']);
            $stmtDependencia->execute();
            $resultadoDependencia = $stmtDependencia->get_result();

            if ($resultadoDependencia->num_rows > 0) {
                // Consultar la tabla partidas_presupuestarias para obtener el valor de partida
                $sqlPartida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $idPartida);
                $stmtPartida->execute();
                $resultadoPartida = $stmtPartida->get_result();

                if ($resultadoPartida->num_rows > 0) {
                    $partidaRow = $resultadoPartida->fetch_assoc();
                    $partidaValor = $partidaRow['partida'];

                    // Obtener los primeros tres dígitos de la partida
                    $grupoPartida = substr($partidaValor, 0, 3);

                    // Si el tipo es "partida_programa", incluir sector y programa en el identificador
                    if ($tipo === "partida_programa") {
                        // Obtener el valor de sector desde pl_sectores
                        $sqlSector = "SELECT sector FROM pl_sectores WHERE id = ?";
                        $stmtSector = $conexion->prepare($sqlSector);
                        $stmtSector->bind_param("i", $row['id_sector']);
                        $stmtSector->execute();
                        $resultadoSector = $stmtSector->get_result();
                        $sectorValor = $resultadoSector->fetch_assoc()['sector'] ?? '00';
                        $stmtSector->close();

                        // Obtener el valor de programa desde pl_programas
                        $sqlPrograma = "SELECT programa FROM pl_programas WHERE id = ?";
                        $stmtPrograma = $conexion->prepare($sqlPrograma);
                        $stmtPrograma->bind_param("i", $row['id_programa']);
                        $stmtPrograma->execute();
                        $resultadoPrograma = $stmtPrograma->get_result();
                        $programaValor = $resultadoPrograma->fetch_assoc()['programa'] ?? '00';
                        $stmtPrograma->close();

                        // Formar el identificador compuesto como "sector.programa.partida"
                        $grupoPartida = sprintf("%02s.%02s.%s", $sectorValor, $programaValor, $grupoPartida);
                    }

                    // Sumar montos en el array agrupado por el identificador adecuado
                    if (isset($partidasDatos[$grupoPartida])) {
                        $partidasDatos[$grupoPartida]['total_inicial'] += $montoInicial;
                        $partidasDatos[$grupoPartida]['total_restante'] += $montoActual;
                    } else {
                        // Si el grupo aún no existe, se agrega al array
                        $partidasDatos[$grupoPartida] = [
                            'value' => $grupoPartida,
                            'total_inicial' => $montoInicial,
                            'total_restante' => $montoActual
                        ];
                    }
                }

                $stmtPartida->close();
            }

            $stmtDependencia->close();
        }

        // Preparar el array final de resultados
        $resultados = [];
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





function obtenerSumatoriaPorActividad($ejercicio)
{
    global $conexion;

    try {
        // Validar que el ejercicio no esté vacío
        if (empty($ejercicio)) {
            throw new Exception("Debe proporcionar un ejercicio válido");
        }

        // Consulta para obtener los registros de distribucion_presupuestaria con el id_ejercicio dado
        $sql = "SELECT DP.id_actividad, DP.monto_inicial, DP.monto_actual, DP.id_sector, DP.id_programa
                FROM distribucion_presupuestaria AS DP
                WHERE DP.id_ejercicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ejercicio);
        $stmt->execute();
        $resultadoDistribucion = $stmt->get_result();

        // Array para almacenar la sumatoria de los montos por sector.actividad
        $actividadesDatos = [];

        // Iterar sobre los registros obtenidos de distribucion_presupuestaria
        while ($row = $resultadoDistribucion->fetch_assoc()) {
            $idActividad = $row['id_actividad'];
            $idSector = $row['id_sector'];
            $idPrograma = $row['id_programa'];
            $montoInicial = isset($row['monto_inicial']) ? (float)$row['monto_inicial'] : 0;
            $montoActual = isset($row['monto_actual']) ? (float)$row['monto_actual'] : 0;

            // Verificar en entes_dependencias si existe un registro con los mismos valores de sector, programa y actividad, y que juridico = 0
            $sqlEntes = "SELECT juridico 
                         FROM entes_dependencias 
                         WHERE sector = ? AND programa = ? AND actividad = ?";
            $stmtEntes = $conexion->prepare($sqlEntes);
            $stmtEntes->bind_param("iii", $idSector, $idPrograma, $idActividad);
            $stmtEntes->execute();
            $resultadoEntes = $stmtEntes->get_result();
            $registroEntes = $resultadoEntes->fetch_assoc();

            // Continuar solo si el campo juridico es igual a 0
            if ($registroEntes && $registroEntes['juridico'] == 0) {
                // Obtener el valor de sector desde pl_sectores
                $sqlSector = "SELECT sector FROM pl_sectores WHERE id = ?";
                $stmtSector = $conexion->prepare($sqlSector);
                $stmtSector->bind_param("i", $idSector);
                $stmtSector->execute();
                $resultadoSector = $stmtSector->get_result();
                $sectorValor = $resultadoSector->fetch_assoc()['sector'] ?? '00';
                $stmtSector->close();

                // Formar el identificador compuesto como "sector.actividad"
                $grupoActividad = sprintf("%02s.%s", $sectorValor, $idActividad);

                // Sumar montos en el array agrupado por el identificador adecuado
                if (isset($actividadesDatos[$grupoActividad])) {
                    $actividadesDatos[$grupoActividad]['total_inicial'] += $montoInicial;
                    $actividadesDatos[$grupoActividad]['total_restante'] += $montoActual;
                } else {
                    // Si el grupo aún no existe, se agrega al array
                    $actividadesDatos[$grupoActividad] = [
                        'value' => $grupoActividad,
                        'total_inicial' => $montoInicial,
                        'total_restante' => $montoActual
                    ];
                }
            }
        }

        // Preparar el array final de resultados
        $resultados = [];
        foreach ($actividadesDatos as $datos) {
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
