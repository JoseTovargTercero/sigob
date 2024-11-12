<?php
require_once '../sistema_global/conexion.php';

// Suponemos que recibimos $id_ejercicio y $ente de alguna manera, como parámetros GET
$id_ejercicio = $_GET['id_ejercicio'];

// Consultar registros de entes con sector 10
$sqlEntes = "SELECT id, ente_nombre FROM entes WHERE sector = 10";
$resultEntes = $conexion->query($sqlEntes);

$partidasArray = [];

if ($resultEntes && $resultEntes->num_rows > 0) {
    while ($rowEnte = $resultEntes->fetch_assoc()) {
        $enteId = $rowEnte['id'];
        $enteNombre = $rowEnte['ente_nombre'];

        // Consultar distribuciones del ente en la tabla distribucion_entes
        $sqlDistribuciones = "SELECT distribucion FROM distribucion_entes WHERE id_ente = ?";
        $stmtDistribuciones = $conexion->prepare($sqlDistribuciones);
        if (!$stmtDistribuciones) {
            die("Error en consulta de distribuciones del ente: " . mysqli_error($conexion));
        }
        $stmtDistribuciones->bind_param('i', $enteId);
        $stmtDistribuciones->execute();
        $resultDistribuciones = $stmtDistribuciones->get_result();

        while ($rowDistribucion = $resultDistribuciones->fetch_assoc()) {
            $distribuciones = json_decode($rowDistribucion['distribucion'], true);

            // Procesar cada item de la distribución
            foreach ($distribuciones as $distribucionItem) {
                $idDistribucion = $distribucionItem['id_distribucion'];
                $montoDistribucion = $distribucionItem['monto'];

                // Consultar distribucion_presupuestaria para obtener id_partida y monto_actual
                $sqlDistribucionPres = "SELECT id_partida, monto_actual FROM distribucion_presupuestaria WHERE id = ?";
                $stmtDistribucionPres = $conexion->prepare($sqlDistribucionPres);
                if (!$stmtDistribucionPres) {
                    die("Error en consulta de distribucion_presupuestaria: " . mysqli_error($conexion));
                }
                $stmtDistribucionPres->bind_param('i', $idDistribucion);
                $stmtDistribucionPres->execute();
                $resultDistribucionPres = $stmtDistribucionPres->get_result();
                $dataDistribucionPres = $resultDistribucionPres->fetch_assoc();

                if ($dataDistribucionPres) {
                    $idPartida = $dataDistribucionPres['id_partida'];
                    $montoActual = $dataDistribucionPres['monto_actual'];

                    // Consultar partida en partidas_presupuestarias para obtener el formato completo
                    $sqlPartida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
                    $stmtPartida = $conexion->prepare($sqlPartida);
                    if (!$stmtPartida) {
                        die("Error en consulta de partidas_presupuestarias: " . mysqli_error($conexion));
                    }
                    $stmtPartida->bind_param('i', $idPartida);
                    $stmtPartida->execute();
                    $resultPartida = $stmtPartida->get_result();
                    $dataPartida = $resultPartida->fetch_assoc();

                    if ($dataPartida) {
                        $partidaCompleta = $dataPartida['partida'];
                        $partidaArray = explode('.', $partidaCompleta);

                        // Desglose de partida en los componentes requeridos
                        $partidaInfo = [
                            'partida' => $partidaArray[0] ?? null,
                            'gen' => $partidaArray[1] ?? null,
                            'esp' => $partidaArray[2] ?? null,
                            'sub' => $partidaArray[3] ?? null,
                            'denominacion' => $enteNombre,
                            'monto' => $montoDistribucion // Monto específico de cada distribución
                        ];

                        // Añadir la partida al array principal
                        $partidasArray[] = $partidaInfo;
                    }

                    $stmtPartida->close();
                }

                $stmtDistribucionPres->close();
            }
        }

        $stmtDistribuciones->close();
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
        <th class="br">SECTOR</th>
        <th class="br">PARTIDA</th>
        <th class="br">GEN</th>
        <th class="br">ESP</th>
        <th class="br">SUB ESP</th>
        <th class="br">DENOMINACIÓN</th>
        <th class="br">CORRIENTE</th>
        <th class="br">CAPITAL</th>
        <th class="br">TOTAL</th>
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
            <td class="br">15</td>
            <td class="br"><?= $partida['partida'] ?></td>
            <td class="br"><?= $partida['gen'] ?></td>
            <td class="br"><?= $partida['esp'] ?></td>
            <td class="br"><?= $partida['sub'] ?></td>
            <td class="br subtitle"><?= $partida['denominacion'] ?></td>
            <td class="br"><?= number_format(0, 2) ?></td>
            <td class="br"><?= number_format(0, 2) ?></td>
            <td class="br"><?= number_format($partida['monto'], 2) ?></td>
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
        <td colspan="6" class="crim br">TOTAL GENERAL</td>
        <td class="crim br"></td>
        <td class="crim br"></td>
        <td class="crim br"><?= number_format($totalGeneral, 2) ?></td>
    </tr>
</table>






</body>

</html>