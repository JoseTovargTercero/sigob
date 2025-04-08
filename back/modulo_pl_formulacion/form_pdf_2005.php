<?php
require_once '../sistema_global/conexion.php';

$id_ejercicio = $_GET['id_ejercicio'];


$query_sector = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$resultado = $result->fetch_assoc();

$ano = $resultado['ano'];
$situado = $resultado['situado'];
$stmt->close();





// Plan de inversión
$stmt = $conexion->prepare("SELECT * FROM plan_inversion WHERE id_ejercicio = ?");
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$resultado = $result->fetch_assoc();
$id_plan = $resultado['id'];
$stmt->close();


$fci = [];

$stmt = mysqli_prepare($conexion, "SELECT PIP.monto, PA.partida AS partida_presupuestaria FROM `proyecto_inversion`  AS PI
RIGHT JOIN proyecto_inversion_partidas AS PIP ON PIP.id_proyecto=PI.id
LEFT JOIN partidas_presupuestarias AS PA ON PA.id=PIP.partida 
WHERE PI.id_plan=?");
$stmt->bind_param('i', $id_plan);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        if (@$fci[$row['partida_presupuestaria']]) {
            $fci[$row['partida_presupuestaria']] += (int) $row['monto'];
        } else {
            $fci[$row['partida_presupuestaria']] = (int) $row['monto'];
        }
    }
}
$stmt->close();
// Plan de inversión


// Consultar distribuciones presupuestarias
$query_distribucion = "SELECT DP.monto_inicial, DP.id_partida, PP.partida, PP.descripcion FROM distribucion_presupuestaria AS DP LEFT JOIN partidas_presupuestarias PP ON PP.id = DP.id_partida WHERE id_ejercicio = ?";
$stmt_distribucion = $conexion->prepare($query_distribucion);
$stmt_distribucion->bind_param('i', $id_ejercicio);
$stmt_distribucion->execute();
$result_distribucion = $stmt_distribucion->get_result();

// Verificar si hay resultados
if ($result_distribucion->num_rows === 0) {
    echo 'No se encontraron registros en distribucion_presupuestaria para el id_ejercicio: ' . $id_ejercicio . "<br>";
}

$distribuciones = $result_distribucion->fetch_all(MYSQLI_ASSOC);

$data = [];


// Crear array de codigos de partidas para las distribuciones
$solo_partidas = array_column($distribuciones, 'partida');
$codigos = array_map(function ($partida) {
    return substr($partida, 0, 3);
}, $solo_partidas);


$partidas_a_agrupadas = array_unique($codigos);

// extraer primeros 3 caracteres de las claves de $fci
foreach (array_keys($fci) as $clave_partida) {
    $codigo = substr($clave_partida, 0, 3);
    if (!in_array($codigo, $partidas_a_agrupadas)) {
        $partidas_a_agrupadas[] = $codigo;
    }
}

array_values($partidas_a_agrupadas);

$totales_por_partida = array_fill_keys($partidas_a_agrupadas, 0);




foreach ($distribuciones as $distribucion) {
    $monto_inicial = $distribucion['monto_inicial'];
    $id_partida = $distribucion['id_partida'];


    if (!$distribucion) {
        echo 'No se encontraron registros en partidas_presupuestarias para el id_partida: ' . $id_partida . "<br>";
        continue; // Continúa al siguiente registro
    }

    $partida = $distribucion['partida'] ?? 'N/A';
    $descripcion = $distribucion['descripcion'] ?? 'N/A';

    // Extraer el código de partida (los primeros 3 caracteres)
    $codigo_partida = substr($partida, 0, 3);


    // Agrupar datos por código de partida
    if (in_array($codigo_partida, $partidas_a_agrupadas)) {


        if (@$data[$codigo_partida][$partida]) {
            $data[$codigo_partida][$partida][3] = intval($data[$codigo_partida][$partida][3]) +  $monto_inicial;
        } else {
            $data[$codigo_partida][$partida] = [
                $partida,
                $descripcion,
                0,
                $monto_inicial,
                0, // [4] FCI
                0,
                $monto_inicial
            ];
        }

        $totales_por_partida[$codigo_partida] += intval($monto_inicial);
    }
}


// Preparar el statement UNA VEZ
$stmt = $conexion->prepare("SELECT descripcion FROM `partidas_presupuestarias` WHERE partida = ?");



function obtenerDescripcionPartida($stmt, $id_partida)
{
    $descripcion = 'N/A';
    $stmt->bind_param('s', $id_partida);
    $stmt->execute();
    $stmt->bind_result($descripcion);
    $stmt->fetch();
    return $descripcion;
}

// Asignar valores del plan de inversion 

foreach ($fci as $key => $item) {
    $codigo_partida = substr($key, 0, 3);

    if (@$data[$codigo_partida][$key]) {
        $data[$codigo_partida][$key][4] = intval($item);
    } else {
        $data[$codigo_partida][$partida] = [
            $key,
            obtenerDescripcionPartida($stmt, $key),
            0,
            0,
            intval($item), // [4] FCI
            0,
            intval($item)
        ];
    }

    $totales_por_partida[$codigo_partida] += intval($item);
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
            <h2 align='center'>RESUMEN DE LOS CREDITOS PRESUPUESTARIOS A NIVEL DE PARTIDAS Y FUENTES DE FINANCIAMIENTO</h2>
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
                <th class="bt bl bb p-15" rowspan="3" style="width: 10%">Partida</th>
                <th class="bt bl bb p-15" rowspan="3">Denominación</th>
                <th class="bt bl bb br p-1" colspan="5">ASIGNACION PRESUPUESTARIA</th>

            </tr>

            <tr>
                <th class="bb bl" rowspan="2" style="width: 10%">Ingresos Propios</th>
                <th class="bb bl " colspan="2">Aporte legal</th>

                <th class="bb br bl" rowspan="2" style="width: 10%">Otras Fuentes</th>
                <th class="bb br" rowspan="2" style="width: 10%">Total</th>
            </tr>

            <tr>
                <th class="bb bl" style="width: 10%;">Situado Estadal</th>
                <th class="bb bl" style="width: 10%;">FCI</th>
            </tr>
        </thead>




        <tbody>
            <?php


            $tt_ingreso_propio = 0;
            $tt_situado_estada = 0;
            $tt_fci = 0;
            $tt_otras_fuentes = 0;
            $tt_total = 0;




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
                        $t_total = $situado_estada + $fci;



                        $tt_ingreso_propio += $ingreso_propio;
                        $tt_situado_estada += $situado_estada;
                        $tt_fci += $fci;
                        $tt_otras_fuentes += $otras_fuentes;

                        echo "<tr>
                            <td class='fz-8 bl'>{$row[0]}</td>
                            <td class='fz-8 bl text-left'>{$row[1]}</td>
                            <td class='fz-8 bl'>" .  number_format($ingreso_propio, 2, ',', '.') . "</td>
                            <td class='fz-8 bl'>" .  number_format($situado_estada, 2, ',', '.') . "</td>
                            <td class='fz-8 bl'>" .  number_format($fci, 2, ',', '.') . "</td>
                            <td class='fz-8 bl'>" .  number_format($otras_fuentes, 2, ',', '.') . "</td>
                            <td class='fz-8 bl br'>" .  number_format($t_total, 2, ',', '.') . "</td>
                        </tr>";
                    }

                    // Imprimir total por partida
                    $monto_total = $totales_por_partida[$codigo_agrupado];
                    $tt_total += $monto_total;

                    echo "<tr>
                            <td class='bl bb'></td>
                            <td class='bl bb fw-bold total_text'>TOTAL POR PARTIDA $codigo_agrupado</td>
                            <td class='bl bb fw-bold total_text'>" . number_format($t_ingreso_propio, 2, ',', '.') . "</td>
                            <td class='bl bb fw-bold total_text'>" . number_format($t_situado_estada, 2, ',', '.') . "</td>
                            <td class='bl bb fw-bold total_text'>" . number_format($t_fci, 2, ',', '.') . "</td>
                            <td class='bl bb fw-bold total_text'>" . number_format($t_otras_fuentes, 2, ',', '.') . "</td>
                            <td class='bl br bb fw-bold total_text'>" . number_format($monto_total, 2, ',', '.') . "</td>
                    </tr >";
                }
            }


            echo "<tr>
            <td class='bl bb'></td>
            <td class='bl bb fw-bold'>TOTALES</td>
            <td class='bl bb fw-bold'>" . number_format($tt_ingreso_propio, 2, ',', '.') . "</td>
            <td class='bl bb fw-bold'>" . number_format($tt_situado_estada, 2, ',', '.') . "</td>
            <td class='bl bb fw-bold'>" . number_format($tt_fci, 2, ',', '.') . "</td>
            <td class='bl bb fw-bold'>" . number_format($tt_otras_fuentes, 2, ',', '.') . "</td>
            <td class='bl br bb fw-bold'>" . number_format($tt_total, 2, ',', '.') . "</td>
    </tr >";
            echo "</tbody >
        </table>";


            /*
            $totalll = 0;
            foreach ($data as $key => $value) {
                //  print_r(array_keys($value));;
                foreach ($value as $value2) {
                    $totalll += $value2[4];
                }
            }

            echo number_format($totalll, 0, '.', ',');
*/
            ?>




</body>

</html>