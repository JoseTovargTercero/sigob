<?php
require_once '../sistema_global/conexion.php';

// Suponemos que recibimos $id_ejercicio de alguna manera, como parámetro GET
$id_ejercicio = $_GET['id_ejercicio'];



/*


*/

$partidasPermitidas = [
    ['01', '06', '401.01.01.00.0000'],
    ['01', '06', '401.05.01.00.0000'],
    ['01', '06', '401.05.03.00.0000'],
    ['09', '04', '401.01.01.00.0000'],
    ['09', '04', '401.05.01.00.0000'],
    ['09', '04', '401.05.03.00.0000'],
    ['11', '02', '403.18.01.00.0000'],
    ['11', '02', '404.03.06.00.0000'],
    ['11', '02', '404.99.01.00.0000'],
    ['12', '01', '403.18.01.00.0000'],
    ['12', '01', '404.99.01.00.0000']
];


$sectores_programas_unicos = [];

// Iterar por cada elemento del array
foreach ($partidasPermitidas as $partida) {
    // Extraer los índices 0 y 1
    $par = [$partida[0], $partida[1]];
    // Evitar duplicados en el sectores_programas_unicos
    if (!in_array($par, $sectores_programas_unicos, true)) {
        $sectores_programas_unicos[] = $par;
    }
}






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

        .crim {
            color: #942c2c;
            font-weight: bold;

        }
    </style>
</head>

<body>

    <div class="fz-10">
        <table class='b-1 bc-lightgray'>
            <tr>
                <td class="text-left pt-1 pb-0" colspan="2">
                    <b>GOBERNACIÓN DEL ESTADO INDÍGENA DE AMAZONAS</b> <br> <br>
                    <b>CODIGO PRESUPUESTARIO: E5100 </b> <br> <br>
                    <b>PRESUPUESTO: <?php echo $ano ?></b>

                </td>
                <td class="text-right ">
                    <b>Fecha: <?php echo date('d/m/Y') ?></b> <br> <br>
                    <img src='../../img/logo.jpg' class='logo'>

                </td>
            </tr>

            <tr>

                <td class="pt-1 text-center" colspan="3">
                    <h2>GASTOS DE INVERSION ESTIMADOS POR EL ESTADO </h2>
                </td>

            </tr>
        </table>

        <table>
            <!-- Encabezado de la tabla -->
            <tr>
                <td colspan="6" class="header"></td>
                <td colspan="3" class="title">DETALLE DE PARTIDAS</td>
            </tr>

            <!-- Encabezado de columnas -->
            <tr>
                <th class="bl bt bb br">SECTOR</th>
                <th class="bt bb br">PROGRAMA</th>
                <th class="bt bb br">PARTIDA</th>
                <th class="bt bb br">GEN</th>
                <th class="bt bb br">ESP</th>
                <th class="bt bb br">SUB ESP</th>
                <th class="bt bb br">DENOMINACIÓN</th>
                <th class="bt bb br">ASIGNACION PRESUPUESTARIA</th>
                <th class="bt bb br">OBSERVACION</th>
            </tr>

            <tr>

            </tr>

            <?php
            $totalPartida = 0;
            $totalGeneral = 0;
            $partAnterior = null;

            foreach ($partidasArray as $index => $partida):
                // Verificar si la partida ha cambiado para mostrar el total del grupo anterior
                if ($partAnterior !== null && $partAnterior !== $partida['partida']) {
                    // Actualizar el total general con el total acumulado de la partida anterior
                    $totalGeneral += $totalPartida;
                    $totalPartida = 0;  // Reiniciar el total de la partida actual
                }

                // Acumular el total de la partida actual
                $totalPartida += $partida['monto'];
                $partAnterior = $partida['partida'];

            ?>
                <!-- Fila de datos consolidada por partida -->
                <tr>
                    <td class="bl br w-7"><?= $partida['sector'] ?></td>
                    <td class="br w-7"><?= $partida['programa'] ?></td>
                    <td class="br w-7"><?= $partida['partida'] ?></td>
                    <td class="br w-7"><?= $partida['gen'] ?></td>
                    <td class="br w-7"><?= $partida['esp'] ?></td>
                    <td class="br w-7"><?= $partida['sub'] ?></td>
                    <td class="br"><?= $partida['denominacion'] ?></td>
                    <td class="br"><?= number_format($partida['monto'], 2) ?></td>
                    <td class="br"></td>
                </tr>

            <?php
                // Al final del último registro, sumamos el total de la última partida al total general
                if ($index === array_key_last($partidasArray)) {
                    $totalGeneral += $totalPartida;
                }
            endforeach;
            ?>

            <!-- Fila de total general -->
            <tr>
                <td colspan="7" class="bt bl bb text-right br"><b>TOTAL</b></td>
                <td class="bt br bb"><b><?= number_format($totalGeneral, 2) ?></b></td>
                <td class="bt br bb"></td>
            </tr>
        </table>


        <style>
            .w-7 {
                width: 4%;
            }
        </style>




</body>

</html>