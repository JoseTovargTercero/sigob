<?php
require_once '../sistema_global/conexion.php';

// Suponemos que recibimos $id y $id_ejercicio de alguna manera, como parámetros GET

//$id_ejercicio = $_GET['id_ejercicio'];
$id_ejercicio = '1';



// Consultar datos del sector
$query_sector = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$ano = $data['ano'];
$situado = $data['situado'];
$stmt->close();


$data = [];


//Cargar partidas
$stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_partidas`");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $denominacion = $row['denominacion'] == '' ? 'Sin denominación' : $row['denominacion'];

        $data[$row['partida']] = [
            'denominacion' => $denominacion,
            '01' => 0,
            '02' => 0,
            '03' => 0,
            '04' => 0,
            '05' => 0,
            '06' => 0,
            '07' => 0,
            '08' => 0,
            '09' => 0,
            '10' => 0,
            '11' => 0,
            '12' => 0,
            '13' => 0,
            '14' => 0,
            '15' => 0
        ];
    }
}
$stmt->close();





$stmt = mysqli_prepare($conexion, "SELECT DP.monto_inicial, PP.partida, PSP.sector FROM `distribucion_presupuestaria` AS DP
LEFT JOIN partidas_presupuestarias AS PP ON PP.id = DP.id_partida
LEFT JOIN pl_sectores AS PSP ON PSP.id = DP.id_sector
WHERE DP.id_ejercicio = ?");
$stmt->bind_param('s', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sector = $row['sector'];
        $partida = substr($row['partida'], 0, 3);

        if (!@$data[$partida]) {
            $data[$partida] = [
                'denominacion' => '<span style="color: red">Sin denominación</span>',
                '01' => 0,
                '02' => 0,
                '03' => 0,
                '04' => 0,
                '05' => 0,
                '06' => 0,
                '07' => 0,
                '08' => 0,
                '09' => 0,
                '10' => 0,
                '11' => 0,
                '12' => 0,
                '13' => 0,
                '14' => 0,
                '15' => 0
            ];
        }

        $data[$partida][$sector] += $row['monto_inicial'];
    }
}
$stmt->close();




// VALORES ADICIONALES DEL FCI

$stmt = mysqli_prepare($conexion, "SELECT DP.monto, PP.partida, PSP.sector FROM `proyecto_inversion_partidas` AS DP
LEFT JOIN partidas_presupuestarias AS PP ON PP.id = DP.partida
LEFT JOIN pl_sectores AS PSP ON PSP.id = DP.sector_id
");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sector = $row['sector'];
        $partida = substr($row['partida'], 0, 3);
        if (!@$data[$partida]) {
            $data[$partida] = [
                'denominacion' => '<span style="color: red">Sin denominación</span>',
                '01' => 0,
                '02' => 0,
                '03' => 0,
                '04' => 0,
                '05' => 0,
                '06' => 0,
                '07' => 0,
                '08' => 0,
                '09' => 0,
                '10' => 0,
                '11' => 0,
                '12' => 0,
                '13' => 0,
                '14' => 0,
                '15' => 0
            ];
        }
        $data[$partida][$sector] += $row['monto'];
    }
}
$stmt->close();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Créditos Presupuestarios del Sector por Programa</title>
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
            border-color: lightgray;
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
            border-left: 1px solid black;
        }

        .br {
            border-right: 1px solid black;
        }

        .bb {
            border-bottom: 1px solid black;
        }

        .bt {
            border-top: 1px solid black;
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
    </style>
</head>

<body>


    <div class="fz-10">
        <table class='b-1 bc-lightgray'>
            <tr>
                <td class="text-left pt-1 pb-0" colspan="2">
                    <b>GOBERNACION DEL ESTADO INDIGENA DE AMAZONAS</b>
                </td>
                <td class="text-right pt-1 pb-0">
                    <b>Pagina: 1 de 1</b>
                </td>
            </tr>
            <tr>
                <td class="text-left py-0" colspan="2">
                    <b>CODIGO PRESUPUESTARIO: E5100 </b>
                </td>
                <td class="text-right py-0">
                    <b>Fecha: <?php echo date('d/m/Y') ?></b>
                </td>
            </tr>

            <tr>
                <td class="pt-1">
                    <b>
                        PRESUPUESTO: <?php echo $ano ?>
                    </b>
                </td>
                <td class="pt-1">
                    <h2> RESUMEN DE LOS CREDITOS PRESUPUESTARIOS POR PARTIDAS A NIVEL DE SECTORES</h2>
                </td>
                <td class="pt-1">
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
            </tr>
        </table>

        <table cellpadding="5" cellspacing="0" class="t-content bc-gray" style="height: 100%;">
            <thead class="fz-10">
                <tr>
                    <th class="bl bb bt" rowspan="2" style="width: 4%;">PARTIDA</th>
                    <th class="bl bb bt" rowspan="2" style="width: 8%;">DENOMINACION</th>
                    <th class="bl bb bt" colspan="15">SECTORES</th>
                    <th class="bl bb bt br" rowspan="2">TOTAL</th>
                </tr>
                <tr>
                    <!-- Cabecera con los números de los sectores -->
                    <th class="bl bb">1</th>
                    <th class="bl bb">2</th>
                    <th class="bl bb">3</th>
                    <th class="bl bb">4</th>
                    <th class="bl bb">5</th>
                    <th class="bl bb">6</th>
                    <th class="bl bb">7</th>
                    <th class="bl bb">8</th>
                    <th class="bl bb">9</th>
                    <th class="bl bb">10</th>
                    <th class="bl bb">11</th>
                    <th class="bl bb">12</th>
                    <th class="bl bb">13</th>
                    <th class="bl bb">14</th>
                    <th class="bl bb">15</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $totales_sectores = array_fill(1, 15, 0); // Inicia array de totales con 0 para cada sector

                foreach ($data as $key => $item) {
                    $total_partida = 0;

                    for ($i = 1; $i <= 15; $i++) {
                        $sector_key = str_pad($i, 2, '0', STR_PAD_LEFT); // '01', '02', etc.
                        $sector_valor = $item[$sector_key];
                        $total_partida += $sector_valor;
                        $totales_sectores[$i] += $sector_valor;
                    }

                    echo "<tr class='fz-6'>
                        <td class='bl'>{$key}</td>
                        <td class='bl text-left'>{$item['denominacion']}</td>";

                    for ($i = 1; $i <= 15; $i++) {
                        echo "<td class='bl text-right pt-1'>" . number_format($item[str_pad($i, 2, '0', STR_PAD_LEFT)], '2', ',', '.') . "</td>";
                    }
                    echo "<td class='bl br text-right'>" . number_format($total_partida, '2', ',', '.') . "</td></tr>";
                }
                ?>

            </tbody>
            <tfoot>
                <tr class='fz-6'>
                    <td class="bt bb bl" colspan="2">TOTALES:</td>
                    <?php
                    foreach ($totales_sectores as $total_sector) {
                        echo "<td class='bt bb bl text-right'><b>" . number_format($total_sector, '2', ',', '.') . "</b></td>";
                    }
                    $total_general = array_sum($totales_sectores);
                    ?>
                    <td class="bt bb bl br text-right"><b><?php echo number_format($total_general, 2, ',', '.') ?></b></td>
                </tr>
            </tfoot>

        </table>


</body>

</html>