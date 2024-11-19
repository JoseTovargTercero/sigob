<?php
require_once '../sistema_global/conexion.php';

// Suponemos que recibimos $id_ejercicio y $ente de alguna manera, como parámetros GET
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


        .subtitle {
            font-weight: bold;
            background-color: #f0f0f0;
            color: blue;
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
                    <h2> TRANSFERENCIAS Y DONACIONES OTORGADAS A ORGANISMOS DEL SECTOR PUBLICO Y PRIVADO</h2>
                </td>
                <td class="pt-1">
                    <img src='../../img/logo.jpg' class='logo'>
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
                <th class="br bt bb bl" rowspan="2">SECTOR</th>
                <th class="br bt bb" rowspan="2">PARTIDA</th>
                <th class="br bt bb" colspan="3">SUB - PARTIDAS</th>
                <th class="br bt bb" rowspan="2">DENOMINACIÓN</th>
                <th class="br bt bb" colspan="2">TIPO DE GASTO</th>
                <th class="br bt bb" rowspan="2">TOTAL</th>
            </tr>
            <tr>
                <th class="br bt bb">GEN</th>
                <th class="br bt bb">ESP</th>
                <th class="br bt bb">SUB ESP</th>
                <th class="br bt bb">CORRIENTE</th>
                <th class="br bt bb">CAPITAL</th>
            </tr>

            <?php
            $total = 0;



            $stmt = mysqli_prepare($conexion, "SELECT dp.monto_inicial, pp.partida, pp.descripcion FROM `distribucion_presupuestaria` AS dp LEFT JOIN partidas_presupuestarias pp ON pp.id = dp.id_partida WHERE id_sector='10'");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    $partida = explode('.', $row['partida']);
                    $total += $row['monto_inicial'];

                    echo '     <tr>
                    <td class="bl br">15</td>
                    <td class="br">' . $partida[0] . '</td>
                    <td class="br">' . $partida[1] . '</td>
                    <td class="br">' . $partida[2] . '</td>
                    <td class="br">' . $partida[3] . '</td>
                    <td class="br text-left">' . $row['descripcion'] . '</td>
                    <td class="br">' . number_format(0, 2) . '</td>
                    <td class="br">' . number_format(0, 2) . '</td>
                    <td class="br">' . number_format($row['monto_inicial'], 2) . '</td>
                </tr>';
                }
            }
            $stmt->close();


            ?>
            <!-- Fila de datos consolidada por partida -->



            <!-- Fila de total general -->
            <tr>
                <td colspan="6" class="br bt bb text-right">TOTAL GENERAL</td>
                <td class="br bt bb"><?= number_format(0, 2) ?></td>
                <td class="br bt bb"><?= number_format(0, 2) ?></td>
                <td class="br bt bb"><?= number_format($total, 2) ?></td>
            </tr>
        </table>






</body>

</html>