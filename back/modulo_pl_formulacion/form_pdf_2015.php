<?php
require_once '../sistema_global/conexion.php';

// Suponemos que recibimos $id y $id_ejercicio de alguna manera, como parámetros GET
$id_sector = $_GET['id_sector'];
$id_programa = $_GET['id_programa'];
$id_ejercicio = $_GET['id_ejercicio'];

// Consultar datos del ejercicio fiscal
$query_sector = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$ano = $data['ano'];
$situado = $data['situado'];
$stmt->close();


// Consultar denominación del sector y valor de sector desde pl_sectores
$query_denominacion_sector = "SELECT denominacion, sector FROM pl_sectores WHERE id = ?";
$stmt_denominacion_sector = $conexion->prepare($query_denominacion_sector);
$stmt_denominacion_sector->bind_param('s', $id_sector);
$stmt_denominacion_sector->execute();
$result_denominacion_sector = $stmt_denominacion_sector->get_result();
$sector_data = $result_denominacion_sector->fetch_assoc();

$denominacion_sector = $sector_data['denominacion'];
$sector = $sector_data['sector'];

// Consultar denominación del programa y valor de programa desde pl_programas
$query_denominacion_programa = "SELECT denominacion, programa FROM pl_programas WHERE sector = ?";
$stmt_denominacion_programa = $conexion->prepare($query_denominacion_programa);
$stmt_denominacion_programa->bind_param('s', $id_sector);
$stmt_denominacion_programa->execute();
$result_denominacion_programa = $stmt_denominacion_programa->get_result();
$programa_data = $result_denominacion_programa->fetch_assoc();

$denominacion_programa = $programa_data['denominacion'];
$programa = $programa_data['programa'];


// Consultar distribuciones presupuestarias
$query_distribucion = "SELECT monto_inicial, id_partida FROM distribucion_presupuestaria WHERE id_sector = ? AND id_programa = ? AND id_ejercicio = ?";
$stmt_distribucion = $conexion->prepare($query_distribucion);
$stmt_distribucion->bind_param('iii', $id_sector, $id_programa, $id_ejercicio);
$stmt_distribucion->execute();
$result_distribucion = $stmt_distribucion->get_result();
$distribuciones = $result_distribucion->fetch_all(MYSQLI_ASSOC);

$data = [];
$totales_por_partida = [];
$partidas_a_agrupadas = ['301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '400', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '498'];

foreach ($distribuciones as $distribucion) {
    $monto_inicial = $distribucion['monto_inicial'];
    $id_partida = $distribucion['id_partida'];

    // Consultar partida y descripción
    $query_partida = "SELECT partida, descripcion FROM partidas_presupuestarias WHERE id = ?";
    $stmt_partida = $conexion->prepare($query_partida);
    $stmt_partida->bind_param('i', $id_partida);
    $stmt_partida->execute();
    $result_partida = $stmt_partida->get_result();
    $partida_data = $result_partida->fetch_assoc();

    if (!$partida_data) {
        echo 'No se encontraron registros en partidas_presupuestarias para el id_partida: ' . $id_partida . "<br>";
        continue; // Continúa al siguiente registro
    }

    $partida = $partida_data['partida'] ?? 'N/A';
    $descripcion = $partida_data['descripcion'] ?? 'N/A';

    // Extraer el código de partida (los primeros 3 caracteres)
    $codigo_partida = substr($partida, 0, 3);

    // Agrupar datos por código de partida
    if (in_array($codigo_partida, $partidas_a_agrupadas)) {
        $data[$codigo_partida][] = [$partida, $descripcion, 0, $monto_inicial, 0, 0, $monto_inicial];

        if (!isset($totales_por_partida[$codigo_partida])) {
            $totales_por_partida[$codigo_partida] = 0;
        }
        $totales_por_partida[$codigo_partida] += $monto_inicial;
    }
}

// Aquí podrías agregar un paso adicional para imprimir o utilizar $denominacion_sector y $denominacion_programa, si lo necesitas
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
        <table class='header-table bt br bb bl bc-lightgray'>
            <tr>
                <td class='text-left' style='width: 20px'>
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
                <td class='text-left' style='vertical-align: top;padding-top: 13px;'>
                    <b>
                        REPÚBLICA BOLIVARIANA DE VENEZUELA <br>
                        GOBERNACIÓN DEL ESTADO AMAZONAS <br>
                        CODIGO PRESUPUESTARIO: E5100
                    </b>
    </div>
    <td class='text-right' style='vertical-align: top;padding: 13px 10px 0 0; '>
        <b>
            Fecha: <?php echo date('d/m/Y') ?>
        </b>
    </td>
    </tr>
    <tr>
        <td colspan='3'>
            <h2 align='center'>CREDITOS PRESUPUESTARIOS DEL SECTOR POR PROGRAMA A NIVEL DE
                PARTIDAS Y FUENTES DE FINANCIAMIENTO</h2>
        </td>
    </tr>

    <tr>
        <td class='text-left'>
            <b>PRESUPUESTO <?php echo $ano ?></b>
        </td>
    </tr>
    </table>




    <table>
        <thead>
            <tr>
                <td class="bl bt bb"></td>
                <td class='bl bb bt text-center fw-bold' style="width: 10%;">CODIGO</td>
                <td class='bl bb bt br text-center fw-bold' colspan="6">DENOMINACION:</td>
            </tr>
            <tr>
                <td class='bl bb text-center fw-bold' style="width: 10%;">SECTOR:</td>
                <td class='bl bb text-center fw-bold'><?php echo $sector ?></td>
                <td class='bl bb br text-left fw-bold' colspan="6"><?php echo $denominacion_sector ?></td>

            </tr>
            <tr>
                <td class='bl bb text-center fw-bold' style="width: 10%;">PROGRAMA</td>
                <td class='bl bb text-center fw-bold'><?php echo $programa ?></td>
                <td class='bl bb br text-left fw-bold' colspan="6"><?php echo $denominacion_programa ?></td>

            </tr>


            <tr>
                <th class="bt bl bb p-15" rowspan="3" style="width: 10%">PARTIDA</th>
                <th class="bt bl bb p-15" rowspan="3" colspan="2" style="width: 25%">DENOMINACION</th>
                <th class="bt bl bb br p-1" colspan="5">ASIGNACION PRESUPUESTARIA</th>

            </tr>

            <tr>
                <th class="bb bl" rowspan="2" style="width: 10%">INGRESOS PROPIOS</th>
                <th class="bb bl " colspan="2">APORTE LEGAL</th>

                <th class="bb br bl" rowspan="2" style="width: 10%">OTRAS FUENTES</th>
                <th class="bb br" rowspan="2" style="width: 10%">TOTAL</th>
            </tr>

            <tr>
                <th class="bb bl" style="width: 10%;">SITUADO ESTADAL</th>
                <th class="bb bl" style="width: 10%;">FCI</th>
            </tr>
        </thead>
        <tbody>
            <?php

            // Imprimir los registros agrupados y sus totales
            foreach ($partidas_a_agrupadas as $codigo_agrupado) {
                if (isset($data[$codigo_agrupado])) {

                    $t_ingreso_propio = 0;
                    $t_situado_estada = 0;
                    $t_fci = 0;
                    $t_otras_fuentes = 0;
                    $t_total = 0;

                    foreach ($data[$codigo_agrupado] as $row) {

                        $t_ingreso_propio += $ingreso_propio = $row[2];
                        $t_situado_estada += $situado_estada = $row[3];
                        $t_fci += $fci = $row[4];
                        $t_otras_fuentes += $otras_fuentes = $row[5];
                        $t_total += $total = $row[6];

                        echo "<tr>
                            <td class='fz-8 bl'>{$row[0]}</td>
                            <td colspan='2' class='fz-8 bl text-left'>{$row[1]}</td>
                            <td class='fz-8 bl'>" .  number_format($ingreso_propio, 2, ',', '.') . "</td>
                            <td class='fz-8 bl'>" .  number_format($situado_estada, 2, ',', '.') . "</td>
                            <td class='fz-8 bl'>" .  number_format($fci, 2, ',', '.') . "</td>
                            <td class='fz-8 bl'>" .  number_format($otras_fuentes, 2, ',', '.') . "</td>
                            <td class='fz-8 bl br'>" .  number_format($total, 2, ',', '.') . "</td>
                        </tr>";
                    }

                    // Imprimir total por partida
                    $monto_total = $totales_por_partida[$codigo_agrupado];
                    echo "<tr>
                            <td class='bl bb'></td>
                            <td colspan='2' class='bl bb fw-bold total_text'>TOTAL POR PARTIDA $codigo_agrupado</td>
                            <td class='bl bb fw-bold total_text'>" . number_format($t_ingreso_propio, 2, ',', '.') . "</td>
                            <td class='bl bb fw-bold total_text'>" . number_format($t_situado_estada, 2, ',', '.') . "</td>
                            <td class='bl bb fw-bold total_text'>" . number_format($t_fci, 2, ',', '.') . "</td>
                            <td class='bl bb fw-bold total_text'>" . number_format($t_otras_fuentes, 2, ',', '.') . "</td>
                            <td class='bl br bb fw-bold total_text'>" . number_format($monto_total, 2, ',', '.') . "</td>
                    </tr >";
                }
            }

            echo "</tbody >
        </table>";
            ?>


</body>

</html>