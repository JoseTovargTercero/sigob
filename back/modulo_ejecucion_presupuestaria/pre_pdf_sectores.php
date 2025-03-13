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

// Consultar ejercicio fiscal
$query_sector = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
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

// Consulta a la tabla gastos
$query_gastos = "SELECT * FROM gastos WHERE id_ejercicio = ? AND status != 2";
$stmt_gastos = $conexion->prepare($query_gastos);
$stmt_gastos->bind_param('i', $id_ejercicio);
$stmt_gastos->execute();
$result_gastos = $stmt_gastos->get_result();
$gastos = $result_gastos->fetch_all(MYSQLI_ASSOC);

// Procesar distribuciones en los registros de gastos
$data = [];
foreach ($gastos as $gasto) {
    $distribuciones_json = $gasto['distribuciones'];
    $distribuciones_array = json_decode($distribuciones_json, true);
    $mes = (int)date('n', strtotime($gasto['fecha']));

      $inicio_trimestre = ($trimestre - 1) * 3 + 1; // Mes inicial del trimestre
            $fin_trimestre = $inicio_trimestre + 2;       // Mes final del trimestre
            if ($mes < $inicio_trimestre or $mes > $fin_trimestre) {
                continue;
            }

    if (!is_array($distribuciones_array)) {
        continue;
    }

    foreach ($distribuciones_array as $distribucion) {
        $id_distribucion = $distribucion['id_distribucion'];
        $monto_actual = $distribucion['monto'];

        // Consultar distribucion_presupuestaria
        $query_distribucion = "SELECT * FROM distribucion_presupuestaria WHERE id = ? AND id_ejercicio = ?";
        $stmt_distribucion = $conexion->prepare($query_distribucion);
        $stmt_distribucion->bind_param('ii', $id_distribucion, $id_ejercicio);
        $stmt_distribucion->execute();
        $result_distribucion = $stmt_distribucion->get_result();
        $distribucion_presupuestaria = $result_distribucion->fetch_assoc();

        if (!$distribucion_presupuestaria) {
            continue;
        }

        $monto_inicial = $distribucion_presupuestaria['monto_inicial'] ?? 0;
        $id_sector = $distribucion_presupuestaria['id_sector'] ?? 0;

        // Consultar sector y denominación en pl_sectores
        $query_sector = "SELECT sector, denominacion FROM pl_sectores WHERE id = ?";
        $stmt_sector = $conexion->prepare($query_sector);
        $stmt_sector->bind_param('i', $id_sector);
        $stmt_sector->execute();
        $result_sector = $stmt_sector->get_result();
        $sector_data = $result_sector->fetch_assoc();

        if (!$sector_data) {
            continue;
        }

        $sector = $sector_data['sector'] ?? 'N/A';
        $denominacion = $sector_data['denominacion'] ?? 'N/A';

        if (!isset($data[$id_sector])) {
            $data[$id_sector] = [$sector, $denominacion, 0, 0, 0, 0, 0];
        }

        $data[$id_sector][2] += $monto_inicial;
        $data[$id_sector][4] += $monto_actual;

        if ($gasto['status'] == 1) {
            $data[$id_sector][5] += $monto_actual;
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
                    $sqlDistribucion = "SELECT dp.* 
                                        FROM distribucion_presupuestaria dp 
                                        WHERE dp.id = ?";
                    $stmtDistribucion = $remote_db->prepare($sqlDistribucion);
                    $stmtDistribucion->bind_param("i", $detalle['id_distribucion']);
                    $stmtDistribucion->execute();
                    $resultadoDistribucion = $stmtDistribucion->get_result();

                    if ($resultadoDistribucion->num_rows > 0) {
                        $detalle['distribucion_presupuestaria'] = $resultadoDistribucion->fetch_assoc();

                        // Obtener la información de partidas_presupuestarias usando id_partida
                        $id_partida = $detalle['distribucion_presupuestaria']['id_partida'];
                        $sqlPartida = "SELECT pp.* 
                                       FROM partidas_presupuestarias pp 
                                       WHERE pp.id = ?";
                        $stmtPartida = $remote_db->prepare($sqlPartida);
                        $stmtPartida->bind_param("i", $id_partida);
                        $stmtPartida->execute();
                        $resultadoPartida = $stmtPartida->get_result();

                        if ($resultadoPartida->num_rows > 0) {
                            $detalle['distribucion_presupuestaria']['partida_presupuestaria'] = $resultadoPartida->fetch_assoc();
                        } else {
                            $detalle['distribucion_presupuestaria']['partida_presupuestaria'] = [];
                        }
                    } else {
                        $detalle['distribucion_presupuestaria'] = [];
                    }

        if (!$distribucion_presupuestaria) {
            continue;
        }

        $id_sector = $distribucion_presupuestaria['id_sector'] ?? 0;

        if (isset($data[$id_sector])) {
            $data[$id_sector][3] += $monto_traspaso;
        }
    }


// Imprimir resultados
 print_r(array_values($data));
?>











<!DOCTYPE html>
<html>

<head>
    <title>RESUMEN GENERAL A NIVEL DE SECTORES</title>
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

    <h1 align='center'> RESUMEN GENERAL A NIVEL DE SECTORES (<?php echo $trimestres_text[$trimestre] . ' ' . $ano ?>)</h1>


    <table>
        <thead>
            <tr>
                <th class="bt bb p-15" style="width: 10%; border-width: 3px;">Codigo del Sector</th>
                <th class="bt bb p-15" style=" border-width: 3px;">Denominación</th>
                <th class="bt bb p-15" style=" border-width: 3px;">Asignación Inicial</th>
                <th class="bt bb p-15" style=" border-width: 3px;">Modificación</th>
                <th class="bt bb p-15" style=" border-width: 3px;">Compromiso</th>
                <th class="bt bb p-15" style=" border-width: 3px;">Causado</th>
                <th class="bt bb p-15" style=" border-width: 3px;">Disponibilidad</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_asignacion_inicial = 0;
            $total_modificacion = 0;
            $total_compromiso = 0;
            $total_causado = 0;
            $total_disponibilidad = 0;

            foreach ($data as $info_partida) {
                // Asignar los valores usando índices numéricos
                $codigo_partida = $info_partida[0] ?? 'N/A';
                $denominacion = $info_partida[1] ?? 'N/A';
                $asignacion_inicial = $info_partida[2] ?? 0;
                $modificacion = $info_partida[3] ?? 0; // Si corresponde al índice [3]
                $compromiso = $info_partida[4] ?? 0;   // Si corresponde al índice [4]
                $causado = $info_partida[5] ?? 0;     // Si corresponde al índice [5]
                $disponibilidad = $info_partida[6] ?? 0;

                // Acumular totales
                $total_asignacion_inicial += $asignacion_inicial;
                $total_modificacion += $modificacion;
                $total_compromiso += $compromiso;
                $total_causado += $causado;
                $total_disponibilidad += $disponibilidad;

                echo "<tr>
                <td class='fz-8' style='border-width: 3px;'>{$codigo_partida}</td>
                <td class='fz-8 text-left' style='border-width: 3px;'>{$denominacion}</td>
                <td class='fz-8' style='border-width: 3px;'>" . number_format($asignacion_inicial, 2, ',', '.') . "</td>
                <td class='fz-8' style='border-width: 3px;'>" . number_format($modificacion, 2, ',', '.') . "</td>
                <td class='fz-8' style='border-width: 3px;'>" . number_format($compromiso, 2, ',', '.') . "</td>
                <td class='fz-8' style='border-width: 3px;'>" . number_format($causado, 2, ',', '.') . "</td>
                <td class='fz-8' style='border-width: 3px;'>" . number_format($disponibilidad, 2, ',', '.') . "</td>
            </tr>";
            }

            // Totales generales
            echo "<tr>
            <td class='bt'></td>
            <td class='bt fw-bold'>TOTALES</td>
            <td class='bt fw-bold'>" . number_format($total_asignacion_inicial, 2, ',', '.') . "</td>
            <td class='bt fw-bold'>" . number_format($total_modificacion, 2, ',', '.') . "</td>
            <td class='bt fw-bold'>" . number_format($total_compromiso, 2, ',', '.') . "</td>
            <td class='bt fw-bold'>" . number_format($total_causado, 2, ',', '.') . "</td>
            <td class='bt fw-bold'>" . number_format($total_disponibilidad, 2, ',', '.') . "</td>
        </tr>";
            ?>
        </tbody>
    </table>







</body>

</html>