<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';
global $conexion;
global $remote_db;

$id_ejercicio = $_GET['id_ejercicio'];
$trimestre = $_GET['trimestre'];
$trimestres_text = [
    1 => 'PRIMER TRIMESTRE',
    2 => 'SEGUNDO TRIMESTRE',
    3 => 'TERCER TRIMESTRE',
    4 => 'CUARTO TRIMESTRE',
];

// Inicializar todos los identificadores con valores por defecto
$query_sectores = "SELECT id, sector FROM pl_sectores";
$query_programas = "SELECT id, programa, denominacion, sector AS id_sector FROM pl_programas WHERE sector = ?"; // Agregar el alias id_sector

// Preparar la primera consulta para los sectores
$stmt_sectores = $conexion->prepare($query_sectores);
$stmt_sectores->execute();
$sectores_result = $stmt_sectores->get_result();
$sectores = $sectores_result->fetch_all(MYSQLI_ASSOC);

// Liberar el resultado de la primera consulta
$sectores_result->free();

// Ahora recorrer cada sector y ejecutar la consulta para obtener los programas
$sectores_con_programas = [];

foreach ($sectores as $sector) {
    // Obtener el id del sector
    $id_sector = $sector['id'];
    
    // Preparar la consulta para los programas con el id del sector
    $stmt_programas = $conexion->prepare($query_programas);
    $stmt_programas->bind_param('i', $id_sector);
    $stmt_programas->execute();
    $programas_result = $stmt_programas->get_result();
    $programas = $programas_result->fetch_all(MYSQLI_ASSOC);
    
    // Liberar el resultado de la consulta de programas
    $programas_result->free();
    
    // Almacenar los programas encontrados bajo cada sector
    $sectores_con_programas[$sector['id']] = [
        'sector' => $sector['sector'],  // Nombre del sector
        'programas' => $programas      // Programas asociados a este sector
    ];
}

// Inicializar $data con todos los sectores y programas con valores en 0
$data = [];
$identificadores = [];

foreach ($sectores_con_programas as $id_sector => $sector_con_programas) {
    foreach ($sector_con_programas['programas'] as $programa) {
        // Asegurarse de que id_sector está presente en el arreglo $programa
        if (isset($programa['id_sector']) && $id_sector == $programa['id_sector']) {
            // Formatear identificador como sector-programa
            $identificador = sprintf("%s-%s", $sector_con_programas['sector'], $programa['programa']);
            
            // Inicializar valores en 0
            $data[$identificador] = [
                $identificador,  // Sector y programa combinados
                $programa['denominacion'],   // Denominación del programa
                0,               // Sumatoria de monto_inicial
                0,               // Sumatoria comprometido
                0,               // Sumatoria causado
                0,               // Sumatoria disponible (monto_actual de distribucion_presupuestaria)
                0                // Sumatoria de monto_actual (de las distribuciones)
            ];
            if (!in_array($identificador, $identificadores)) {
                $identificadores[] = $identificador;
            }
        }
    }
}

// Consultar monto_inicial desde distribucion_presupuestaria filtrado por id_ejercicio y agrupado por id_sector e id_programa
$query_distribucion = "SELECT id_sector, id_programa, monto_inicial FROM distribucion_presupuestaria WHERE id_ejercicio = ?";
$stmt_distribucion = $conexion->prepare($query_distribucion);
$stmt_distribucion->bind_param('i', $id_ejercicio);
$stmt_distribucion->execute();
$result_distribucion = $stmt_distribucion->get_result();

while ($row = $result_distribucion->fetch_assoc()) {
    $id_sector = $row['id_sector'];
    $id_programa = $row['id_programa'];
    $monto_inicial = $row['monto_inicial'];
    
    // Formatear identificador como sector-programa
    foreach ($sectores_con_programas[$id_sector]['programas'] as $programa) {
        if ($programa['id'] == $id_programa) {
            $identificador = sprintf("%s-%s", $sectores_con_programas[$id_sector]['sector'], $programa['programa']);
            
            // Sumar monto_inicial al identificador correspondiente
            if (isset($data[$identificador])) {
                $data[$identificador][2] += $monto_inicial;
            }
        }
    }
}




// Consultar ejercicio fiscal
$query_ejercicio = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_ejercicio);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$resultado = $result->fetch_assoc();

if (!$resultado) {
    die("No se encontró el ejercicio fiscal para el ID proporcionado.");
}

$ano = $resultado['ano'];
$situado = $resultado['situado'];
$stmt->close();

// Nueva consulta a la tabla gastos
$query_gastos = "SELECT * FROM gastos WHERE id_ejercicio = ? AND status != 2";
$stmt_gastos = $conexion->prepare($query_gastos);
$stmt_gastos->bind_param('i', $id_ejercicio);
$stmt_gastos->execute();
$result_gastos = $stmt_gastos->get_result();

$gastos = $result_gastos->fetch_all(MYSQLI_ASSOC);

// Procesar distribuciones en los registros de gastos
foreach ($gastos as $gasto) {
    $distribuciones_json = $gasto['distribuciones'];
    $distribuciones_array = json_decode($distribuciones_json, true);
    $mes = (int)date('n', strtotime($gasto['fecha']));
    

    if (!is_array($distribuciones_array)) {
        echo "Error al decodificar el JSON de distribuciones para el gasto con ID: " . $gasto['id'] . "<br>";
        continue;
    }

    foreach ($distribuciones_array as $distribucion) {
        $id_distribucion = $distribucion['id_distribucion'];
        $monto_actual = $distribucion['monto'];

        $sqlDistribucionEnte = "SELECT id, distribucion FROM distribucion_entes WHERE id_ejercicio = ? AND distribucion LIKE ?";
        $likePattern = '%"id_distribucion":"' . $id_distribucion . '"%';
        $stmtDistribucionEnte = $conexion->prepare($sqlDistribucionEnte);
        $stmtDistribucionEnte->bind_param("is", $id_ejercicio, $likePattern);
        $stmtDistribucionEnte->execute();
        $resultadoDistribucionEnte = $stmtDistribucionEnte->get_result();

        if ($distribucionEnte = $resultadoDistribucionEnte->fetch_assoc()) {
            $id_distribucion_ente = $distribucionEnte['id'];
            $distribucionData = json_decode($distribucionEnte['distribucion'], true);

            foreach ($distribucionData as $dist) {
                if ($dist['id_distribucion'] == $id_distribucion) {
                    $montoDistribucion = $dist['monto'];
                    break;
                }
            }

            // Consultar distribucion_presupuestaria
            $query_distribucion = "SELECT * FROM distribucion_presupuestaria WHERE id = ? AND id_ejercicio = ?";
            $stmt_distribucion = $conexion->prepare($query_distribucion);
            $stmt_distribucion->bind_param('ii', $id_distribucion, $id_ejercicio);
            $stmt_distribucion->execute();
            $result_distribucion = $stmt_distribucion->get_result();
            $distribucion_presupuestaria = $result_distribucion->fetch_assoc();

            if (!$distribucion_presupuestaria) {
                echo "No se encontró distribucion_presupuestaria para id_distribucion: $id_distribucion y id_ejercicio: $id_ejercicio<br>";
                continue;
            }

            $monto_inicial = $distribucion_presupuestaria['monto_inicial'] ?? 0;
            $monto_disponible = $montoDistribucion; // Monto disponible desde distribucion_presupuestaria entes
            $id_sector = $distribucion_presupuestaria['id_sector'] ?? 0;
            $id_programa = $distribucion_presupuestaria['id_programa'] ?? 0;

            // Consultar sector en pl_sectores
            $query_sector = "SELECT sector FROM pl_sectores WHERE id = ?";
            $stmt_sector = $conexion->prepare($query_sector);
            $stmt_sector->bind_param('i', $id_sector);
            $stmt_sector->execute();
            $result_sector = $stmt_sector->get_result();
            $sector_data = $result_sector->fetch_assoc();

            if (!$sector_data) {
                echo "No se encontró registro en pl_sectores para id_sector: $id_sector<br>";
                continue;
            }

            $sector = $sector_data['sector'] ?? 'N/A';

            // Consultar programa en pl_programas
            $query_programa = "SELECT programa, denominacion FROM pl_programas WHERE id = ?";
            $stmt_programa = $conexion->prepare($query_programa);
            $stmt_programa->bind_param('i', $id_programa);
            $stmt_programa->execute();
            $result_programa = $stmt_programa->get_result();
            $programa_data = $result_programa->fetch_assoc();

            if (!$programa_data) {
                echo "No se encontró registro en pl_programas para id_programa: $id_programa<br>";
                continue;
            }

             if ($trimestre == 1) {
                    $inicio_trimestre = 1;
                    $fin_trimestre = 3;
                }elseif ($trimestre == 2) {
                    $inicio_trimestre = 4;
                    $fin_trimestre = 6;
                }elseif ($trimestre == 3) {
                    $inicio_trimestre = 7;
                    $fin_trimestre = 9;
                }elseif ($trimestre == 4) {
                    $inicio_trimestre = 10;
                    $fin_trimestre = 12;
                }
                

                if ($mes < $inicio_trimestre || $mes > $fin_trimestre) {
                    continue;
                }

            $programa = $programa_data['programa'] ?? 'N/A';
            $denominacion = $programa_data['denominacion'] ?? 'N/A';

            // Formatear identificador como xx-xx
            $identificador = sprintf("%s-%s", $sector, $programa);
            
            // Agrupar datos por identificador
            if (isset($data[$identificador])) {
                $data[$identificador][6] += $monto_disponible;   // Sumar monto_actual (disponibilidad)
                    $data[$identificador][5] += $monto_actual;
            }
        }
    }
}
 


// Consultar los traspasos principales filtrando por id_ejercicio
$sql = "SELECT t.id, t.n_orden, t.id_ejercicio, t.monto_total, t.fecha, t.status, t.tipo 
        FROM traspasos t
        WHERE t.id_ejercicio = ?";
$stmt = $remote_db->prepare($sql);
$stmt->bind_param("i", $id_ejercicio);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $traspasos = $resultado->fetch_all(MYSQLI_ASSOC);

    // Agregar la información de traspaso_informacion para cada traspaso
    foreach ($traspasos as &$traspaso) {
        $mes2 = (int)date('n', strtotime($traspaso['fecha']));

        if ($trimestre == 1) {
            $inicio_trimestre = 1;
            $fin_trimestre = 3;
        } elseif ($trimestre == 2) {
            $inicio_trimestre = 4;
            $fin_trimestre = 6;
        } elseif ($trimestre == 3) {
            $inicio_trimestre = 7;
            $fin_trimestre = 9;
        } elseif ($trimestre == 4) {
            $inicio_trimestre = 10;
            $fin_trimestre = 12;
        }

        if ($mes2 < $inicio_trimestre || $mes2 > $fin_trimestre) {
            continue;
        }

        $sqlInfo = "SELECT ti.id_distribucion, ti.monto, ti.tipo 
                    FROM traspaso_informacion ti 
                    WHERE ti.id_traspaso = ?";
        $stmtInfo = $remote_db->prepare($sqlInfo);
        $stmtInfo->bind_param("i", $traspaso['id']);
        $stmtInfo->execute();
        $resultadoInfo = $stmtInfo->get_result();

        if ($resultadoInfo->num_rows > 0) {
            $detalles = $resultadoInfo->fetch_all(MYSQLI_ASSOC);
            foreach ($detalles as &$detalle) {
                // Obtener la información de distribucion_presupuestaria
                $sqlDistribucion = "SELECT dp.* FROM distribucion_presupuestaria dp WHERE dp.id = ?";
                $stmtDistribucion = $remote_db->prepare($sqlDistribucion);
                $stmtDistribucion->bind_param("i", $detalle['id_distribucion']);
                $stmtDistribucion->execute();
                $resultadoDistribucion = $stmtDistribucion->get_result();

                if ($resultadoDistribucion->num_rows > 0) {
                    $distribucion_presupuestaria = $resultadoDistribucion->fetch_assoc();
                    $detalle['distribucion_presupuestaria'] = $distribucion_presupuestaria;

                    // Obtener la información de partidas_presupuestarias usando id_partida
                    $id_partida = $distribucion_presupuestaria['id_partida'];
                    $sqlPartida = "SELECT pp.* FROM partidas_presupuestarias pp WHERE pp.id = ?";
                    $stmtPartida = $remote_db->prepare($sqlPartida);
                    $stmtPartida->bind_param("i", $id_partida);
                    $stmtPartida->execute();
                    $resultadoPartida = $stmtPartida->get_result();

                    if ($resultadoPartida->num_rows > 0) {
                        $detalle['distribucion_presupuestaria']['partida_presupuestaria'] = $resultadoPartida->fetch_assoc();
                    } else {
                        $detalle['distribucion_presupuestaria']['partida_presupuestaria'] = [];
                    }

                    // Obtener el id_sector de distribucion_presupuestaria
                    $id_sector = $distribucion_presupuestaria['id_sector'] ?? 0;
                    $id_programa = $distribucion_presupuestaria['id_programa'] ?? 0;
                    $monto_traspaso = $detalle['monto'];

                    // Consultar sector en pl_sectores
                    $query_sector = "SELECT sector FROM pl_sectores WHERE id = ?";
                    $stmt_sector = $conexion->prepare($query_sector);
                    $stmt_sector->bind_param('i', $id_sector);
                    $stmt_sector->execute();
                    $result_sector = $stmt_sector->get_result();
                    $sector_data = $result_sector->fetch_assoc();

                    if (!$sector_data) {
                        echo "No se encontró registro en pl_sectores para id_sector: $id_sector<br>";
                        continue;
                    }

                    $sector = $sector_data['sector'] ?? 'N/A';

                    // Consultar programa en pl_programas
                    $query_programa = "SELECT programa, denominacion FROM pl_programas WHERE id = ?";
                    $stmt_programa = $conexion->prepare($query_programa);
                    $stmt_programa->bind_param('i', $id_programa);
                    $stmt_programa->execute();
                    $result_programa = $stmt_programa->get_result();
                    $programa_data = $result_programa->fetch_assoc();

                    if (!$programa_data) {
                        echo "No se encontró registro en pl_programas para id_programa: $id_programa<br>";
                        continue;
                    }

                    $programa = $programa_data['programa'] ?? 'N/A';
                    $denominacion = $programa_data['denominacion'] ?? 'N/A';

                    // Formatear identificador como xx-xx
                    $identificador2 = sprintf("%s-%s", $sector, $programa);

                    // Agrupar datos por identificador
                    if (in_array($identificador2, $identificadores)) {
                        if ($detalle['tipo'] == 'A') {
                            $data[$identificador2][3] = $monto_traspaso;  // Sumar monto del traspaso
                        } elseif ($detalle['tipo'] == 'D') {
                            $data[$identificador2][7] = $monto_traspaso;  // Restar monto del traspaso
                        }
                    }
                }
            }
        }
    }
}



// Imprimir resultados
//print_r(array_values($data));
?>











<!DOCTYPE html>
<html>

<head>
    <title>RESUMEN GENERAL A NIVEL DE SECTORES Y PROGRAMAS</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            margin: 10px;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            text-align: center;
        }

        td {
            padding: 5px;
        }

        th {
            font-weight: bold;
            text-align: center;
        }

        .py-0 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .pb-0 {
            padding-bottom: 0 !important;
        }

        .pt-0 {
            padding-top: 0 !important;
        }

        .b-1 {
            border: 1px solid;
        }

        .bc-lightgray {
            border-color: lightgray !important;
        }

        .bc-gray {
            border-color: gray;
        }

        .pt-1 {
            padding-top: 1rem !important;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        h2 {
            font-size: 16px;
            margin: 0;
        }

        .header-table {
            margin-bottom: 20px;
        }

        .small-text {
            font-size: 8px;
        }

        .w-50 {
            width: 50%;
        }

        .table-title {
            font-size: 10px;
            margin-top: 10px;
        }

        .logo {
            width: 120px;
        }

        .t-border-0>tr>td {
            border: none !important;
        }

        .fz-6 {
            font-size: 5px !important;
        }

        .fz-8 {
            font-size: 8px !important;
        }

        .fz-9 {
            font-size: 9px !important;
        }

        .fz-10 {
            font-size: 10px !important;
        }

        .bl {
            border-left: 1px solid gray;
        }

        .br {
            border-right: 1px solid gray;
        }

        .bb {
            border-bottom: 1px solid gray;
        }

        .bt {
            border-top: 1px solid gray;
        }

        .dw-nw {
            white-space: nowrap !important
        }

        @media print {
            .page-break {
                page-break-after: always;
            }
        }

        .t-content {
            page-break-inside: avoid;
        }

        .p-2 {
            padding: 10px;
        }

        .total_text {
            color: #8e1e1e;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div style='font-size: 9px;'>
        <h1 align='center'>RESUMEN GENERAL A NIVEL DE SECTORES Y PROGRAMAS (<?php echo $trimestres_text[$trimestre] . ' ' . $ano ?>)</h1>
        <table>
            <thead>
                <tr>
                    <th class="bt bb p-15" style="width: 10%; border-width: 3px;">COD.SECTOR-PROGRAMA</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">DENOMINACIÓN</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">ASIGNACIÓN INICIAL</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">MODIFICACIÓN(+/-)</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">ASIGNACIÓN AJUSTADA</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">COMPROMISO</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">SALDO</th>
                </tr>
            </thead>
<tbody>
    <?php
    $total_asignacion_inicial = 0;
    $total_modificacion = 0; // Ahora se usará una sola variable para modificaciones
    $total_asignacion_ajustada = 0;
    $total_compromiso = 0;
    $total_disponibilidad = 0;

    foreach ($data as $info_partida) {
        // Asignar los valores usando índices numéricos
        $codigo_partida = $info_partida[0] ?? 'N/A';
        $denominacion = $info_partida[1] ?? 'N/A';
        $asignacion_inicial = $info_partida[2] ?? 0;
        $modificacion_aumentada = $info_partida[3] ?? 0;
        $compromiso = $info_partida[4] ?? 0;
        $causado = $info_partida[5] ?? 0;
        $modificacion_restada = $info_partida[7] ?? 0;
        if ($asignacion_inicial == 0) {
            continue;
        }

        // Calcular modificación como un solo valor positivo o negativo
        $modificacion = $modificacion_aumentada - $modificacion_restada;

        // Calcular asignación ajustada
        if ($modificacion > $compromiso) {
            $asignacion_ajustada = $asignacion_inicial + $modificacion;
        } else {
            $asignacion_ajustada = $asignacion_inicial - $modificacion;
        }

        // Calcular disponibilidad
        $disponibilidad = ($asignacion_inicial + $modificacion) - $compromiso;

        // Acumular totales
        $total_asignacion_inicial += $asignacion_inicial;
        $total_modificacion += $modificacion;
        $total_asignacion_ajustada += $asignacion_ajustada;
        $total_compromiso += $compromiso;
        $total_disponibilidad += $disponibilidad;

        

        // Imprimir filas
        echo "<tr>
            <td class='fz-8'>{$codigo_partida}</td>
            <td class='fz-8 text-left'>{$denominacion}</td>
            <td class='fz-8'>" . number_format($asignacion_inicial, 2, ',', '.') . "</td>";

        // Imprimir modificación con su respectivo signo
        if ($modificacion > 0) {
            echo "<td class='fz-8'>" . number_format($modificacion, 2, ',', '.') . "</td>";
        }elseif ($modificacion == 0) {
            echo "<td class='fz-8'>" . number_format($modificacion, 2, ',', '.') . "</td>";
        }else{
            echo "<td class='fz-8'>-" . number_format(abs($modificacion), 2, ',', '.') . "</td>";
        }

        echo "
            <td class='fz-8'>" . number_format($asignacion_ajustada, 2, ',', '.') . "</td>
            <td class='fz-8'>" . number_format($compromiso, 2, ',', '.') . "</td>
            <td class='fz-8'>" . number_format($disponibilidad, 2, ',', '.') . "</td>
        </tr>";
    }

    // Imprimir totales generales
    echo "<tr>
        <td class='bt'></td>
        <td class='bt fw-bold'>TOTAL RESUMEN POR SECTORES Y PROGRAMAS</td>
        <td class='bt fw-bold'>" . number_format($total_asignacion_inicial, 2, ',', '.') . "</td>";

    // Imprimir el total de modificaciones con su signo
    if ($total_modificacion > 0) {
        echo "<td class='bt fw-bold'>" . number_format($total_modificacion, 2, ',', '.') . "</td>";
    }elseif ($total_modificacion == 0) {
        echo "<td class='bt fw-bold'>" . number_format($total_modificacion, 2, ',', '.') . "</td>";
    } else {
        echo "<td class='bt fw-bold'>-" . number_format(abs($total_modificacion), 2, ',', '.') . "</td>";
    }

    echo "
        <td class='bt fw-bold'>" . number_format($total_asignacion_ajustada, 2, ',', '.') . "</td>
        <td class='bt fw-bold'>" . number_format($total_compromiso, 2, ',', '.') . "</td>
        <td class='bt fw-bold'>" . number_format($total_disponibilidad, 2, ',', '.') . "</td>
    </tr>";
    ?>
</tbody>
</table>







</body>

</html>