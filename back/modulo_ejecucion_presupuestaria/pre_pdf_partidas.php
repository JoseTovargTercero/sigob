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

// Inicializar todos los códigos de partida permitidos
$codigos_partida_permitidos = ['401', '402', '403', '404', '407', '408', '411', '498'];

// Inicializar data para cada código de partida permitido
$data = [];
foreach ($codigos_partida_permitidos as $codigo_partida) {
    $data[$codigo_partida] = [
        "$codigo_partida", // Código partida
        "$codigo_partida", // Denominación (temporalmente como código partida)
        0, // Sumatoria de monto_inicial
        0, // Sumatoria comprometido
        0, // Sumatoria causado
        0, // Sumatoria disponible (monto_actual de distribucion_presupuestaria)
        0  // Sumatoria de monto_actual (de las distribuciones)
    ];
}

// Consultar denominación de las partidas desde pl_partidas
$query_partidas = "SELECT partida, denominacion FROM pl_partidas WHERE partida IN ('" . implode("','", $codigos_partida_permitidos) . "')";
$result_partidas = $conexion->query($query_partidas);

while ($row = $result_partidas->fetch_assoc()) {
    $codigo_partida = $row['partida'];
    $denominacion = $row['denominacion'];
    
    // Actualizar denominación de cada partida
    if (isset($data[$codigo_partida])) {
        $data[$codigo_partida][1] = $denominacion;
    }
}

// Consultar monto_inicial desde distribucion_presupuestaria filtrado por id_ejercicio
$query_distribucion = "SELECT dp.id_partida, dp.monto_inicial, pp.partida 
                      FROM distribucion_presupuestaria dp
                      JOIN partidas_presupuestarias pp ON dp.id_partida = pp.id
                      WHERE dp.id_ejercicio = ?";
$stmt_distribucion = $conexion->prepare($query_distribucion);
$stmt_distribucion->bind_param('i', $id_ejercicio);
$stmt_distribucion->execute();
$result_distribucion = $stmt_distribucion->get_result();

while ($row = $result_distribucion->fetch_assoc()) {
    $codigo_partida = substr($row['partida'], 0, 3); // Tomar los primeros 3 dígitos
    $monto_inicial = $row['monto_inicial'];
    
    // Sumar monto_inicial a la partida correspondiente
    if (isset($data[$codigo_partida])) {
        $data[$codigo_partida][2] += $monto_inicial;
    }
}



// Consultar ejercicio fiscal
$query_sector = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$resultado = $result->fetch_assoc();
$stmt->close();
$ano = $resultado['ano'];
$situado = $resultado['situado'];
if (!$resultado) {
    die("No se encontró el ejercicio fiscal para el ID proporcionado.");
}

// Consultar gastos
$query_gastos = "SELECT * FROM gastos WHERE id_ejercicio = ? AND status != 2";
$stmt_gastos = $conexion->prepare($query_gastos);
$stmt_gastos->bind_param('i', $id_ejercicio);
$stmt_gastos->execute();
$result_gastos = $stmt_gastos->get_result();
$gastos = $result_gastos->fetch_all(MYSQLI_ASSOC);

foreach ($gastos as $gasto) {
    $mes = (int)date('n', strtotime($gasto['fecha']));
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

    $distribuciones_array = json_decode($gasto['distribuciones'], true);
    if (!is_array($distribuciones_array)) {
        continue;
    }

    foreach ($distribuciones_array as $distribucion) {
        $id_distribucion = $distribucion['id_distribucion'];
        $monto_actual = $distribucion['monto'];

        // Consultar distribucion_presupuestaria
        $query_distribucion = "SELECT id_partida, monto_inicial FROM distribucion_presupuestaria WHERE id = ? AND id_ejercicio = ?";
        $stmt_distribucion = $conexion->prepare($query_distribucion);
        $stmt_distribucion->bind_param('ii', $id_distribucion, $id_ejercicio);
        $stmt_distribucion->execute();
        $result_distribucion = $stmt_distribucion->get_result();
        $distribucion_presupuestaria = $result_distribucion->fetch_assoc();

        if ($distribucion_presupuestaria) {
            $id_partida = $distribucion_presupuestaria['id_partida'];
            $monto_inicial = $distribucion_presupuestaria['monto_inicial'] ?? 0;

            // Consultar el código de partida
            $query_partida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
            $stmt_partida = $conexion->prepare($query_partida);
            $stmt_partida->bind_param('i', $id_partida);
            $stmt_partida->execute();
            $result_partida = $stmt_partida->get_result();
            $partida_data = $result_partida->fetch_assoc();
            $codigo_partida = substr($partida_data['partida'], 0, 3);

            // Solo agregar al data si el código de partida está en los permitidos
            if (in_array($codigo_partida, $codigos_partida_permitidos)) {
                $data[$codigo_partida][6] += $monto_actual;
                $data[$codigo_partida][4] += $monto_actual;
                if ($gasto['status'] == 1) { // Causado
                    $data[$codigo_partida][5] += $monto_actual;
                }
            }
        }
    }
}

// Consultar traspasos
$sql = "SELECT id, fecha FROM traspasos WHERE id_ejercicio = ?";
$stmt = $remote_db->prepare($sql);
$stmt->bind_param("i", $id_ejercicio);
$stmt->execute();
$resultado = $stmt->get_result();
$traspasos = $resultado->fetch_all(MYSQLI_ASSOC);

foreach ($traspasos as $traspaso) {
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

    $sqlInfo = "SELECT id_distribucion, monto, tipo FROM traspaso_informacion WHERE id_traspaso = ?";
    $stmtInfo = $remote_db->prepare($sqlInfo);
    $stmtInfo->bind_param("i", $traspaso['id']);
    $stmtInfo->execute();
    $resultadoInfo = $stmtInfo->get_result();
    $detalles = $resultadoInfo->fetch_all(MYSQLI_ASSOC);

    foreach ($detalles as $detalle) {
        $sqlDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
        $stmtDistribucion = $remote_db->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("i", $detalle['id_distribucion']);
        $stmtDistribucion->execute();
        $resultadoDistribucion = $stmtDistribucion->get_result();

        if ($distribucion_presupuestaria = $resultadoDistribucion->fetch_assoc()) {
            $id_partida = $distribucion_presupuestaria['id_partida'];

            // Consultar el código de partida
            $sqlPartida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $remote_db->prepare($sqlPartida);
            $stmtPartida->bind_param("i", $id_partida);
            $stmtPartida->execute();
            $resultadoPartida = $stmtPartida->get_result();
            $partida_data = $resultadoPartida->fetch_assoc();
            $codigo_partida2 = substr($partida_data['partida'], 0, 3);

            // Solo agregar al data si el código de partida está en los permitidos
            if (in_array($codigo_partida2, $codigos_partida_permitidos)) {
                $monto_traspaso = $detalle['monto'];

                // Dependiendo del tipo de traspaso, se guarda en la posición correcta
                if ($detalle['tipo'] == 'A') {
                    $data[$codigo_partida2][3] += $monto_traspaso;
                } elseif ($detalle['tipo'] == 'D') {
                    $data[$codigo_partida2][7] += $monto_traspaso;
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
    <title>RESUMEN GENERAL A NIVEL DE PARTIDAS</title>
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


    <h1 align='center'> RESUMEN GENERAL A NIVEL DE PARTIDAS (<?php echo $trimestres_text[$trimestre] . ' ' . $ano ?>)</h1>

    <table>
        <thead>
            <tr>
                <th class="bt bb p-15" style="width: 10%; border-width: 3px;">COD. PARTIDA</th>
                <th class="bt bb p-15" style=" border-width: 3px;">DENOMINACIÓN</th>
                <th class="bt bb p-15" style=" border-width: 3px;">ASIGNACIÓN INICIAL</th>
                <th class="bt bb p-15" style=" border-width: 3px;">MODIFICACIÓN (+/-)</th>
                <th class="bt bb p-15" style=" border-width: 3px;">COMPROMISO</th>
                <th class="bt bb p-15" style=" border-width: 3px;">CAUSADO</th>
                <th class="bt bb p-15" style=" border-width: 3px;">DISPONIBILIDAD</th>
            </tr>
        </thead>
<tbody>
    <?php
    $total_asignacion_inicial = 0;
    $total_modificacion = 0; // Ahora se usará una sola variable para modificaciones
    $total_compromiso = 0;
    $total_causado = 0;
    $total_disponibilidad = 0;

    foreach ($data as $info_partida) {
        // Asignar valores
        $codigo_partida = $info_partida[0] ?? 'N/A';
        $denominacion = $info_partida[1] ?? 'N/A';
        $asignacion_inicial = $info_partida[2] ?? 0;
        $modificacion_aumentada = $info_partida[3] ?? 0;
        $compromiso = $info_partida[4] ?? 0;
        $causado = $info_partida[5] ?? 0;
        $modificacion_restada = $info_partida[7] ?? 0;

        // Calcular modificación como un solo valor positivo o negativo
        $modificacion = $modificacion_aumentada - $modificacion_restada;

        // Calcular disponibilidad correctamente
        $disponibilidad = ($asignacion_inicial + $modificacion) - $compromiso;

        // Acumular valores
        $total_asignacion_inicial += $asignacion_inicial;
        $total_modificacion += $modificacion; // Se usa el valor neto de modificaciones
        $total_compromiso += $compromiso;
        $total_causado += $causado;
        $total_disponibilidad += $disponibilidad;

        // Imprimir filas
        echo "<tr>
            <td class='fz-8' style='border-width: 3px;'>{$codigo_partida}</td>
            <td class='fz-8 text-left' style='border-width: 3px;'>{$denominacion}</td>
            <td class='fz-8' style='border-width: 3px;'>" . number_format($asignacion_inicial, 2, ',', '.') . "</td>";

        // Imprimir modificación con su respectivo signo
        echo "<td class='fz-8' style='border-width: 3px;'>" . number_format($modificacion, 2, ',', '.') . "</td>";

        echo "<td class='fz-8' style='border-width: 3px;'>" . number_format($compromiso, 2, ',', '.') . "</td>
              <td class='fz-8' style='border-width: 3px;'>" . number_format($causado, 2, ',', '.') . "</td>
              <td class='fz-8' style='border-width: 3px;'>" . number_format($disponibilidad, 2, ',', '.') . "</td>
          </tr>";
    }

    // Imprimir totales generales
    echo "<tr>
        <td class='bt'></td>
        <td class='bt fw-bold'>TOTALES</td>
        <td class='bt fw-bold'>" . number_format($total_asignacion_inicial, 2, ',', '.') . "</td>
        <td class='bt fw-bold' style='border-width: 3px;'>" . number_format($total_modificacion, 2, ',', '.') . "</td>
        <td class='bt fw-bold'>" . number_format($total_compromiso, 2, ',', '.') . "</td>
        <td class='bt fw-bold'>" . number_format($total_causado, 2, ',', '.') . "</td>
        <td class='bt fw-bold'>" . number_format($total_disponibilidad, 2, ',', '.') . "</td>
    </tr>";
    ?>
</tbody>

    </table>







</body>

</html>