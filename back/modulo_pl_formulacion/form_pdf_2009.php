<?php
require_once '../sistema_global/conexion.php';

// Suponemos que recibimos $id_ejercicio de alguna manera, como parámetro GET
$id_ejercicio = $_GET['id_ejercicio'];

// Consultar registros de la tabla proyecto_inversion_partidas
$sqlProyectos = "SELECT sector_id, programa_id, partida, monto, id_proyecto FROM proyecto_inversion_partidas";
$stmtProyectos = $conexion->prepare($sqlProyectos);
$stmtProyectos->execute();
$resultProyectos = $stmtProyectos->get_result();

$partidasArray = [];

while ($rowProyecto = $resultProyectos->fetch_assoc()) {
    $sectorId = $rowProyecto['sector_id'];
    $programaId = $rowProyecto['programa_id'];
    $partidaId = $rowProyecto['partida'];
    $monto = $rowProyecto['monto'];
    $idProyecto = $rowProyecto['id_proyecto'];

    // Consultar la tabla proyecto_inversion para obtener id_plan usando id_proyecto
    $sqlProyectoInversion = "SELECT id_plan FROM proyecto_inversion WHERE id = ?";
    $stmtProyectoInversion = $conexion->prepare($sqlProyectoInversion);
    $stmtProyectoInversion->bind_param('i', $idProyecto);
    $stmtProyectoInversion->execute();
    $resultProyectoInversion = $stmtProyectoInversion->get_result();
    $dataProyectoInversion = $resultProyectoInversion->fetch_assoc();
    $idPlan = $dataProyectoInversion['id_plan'] ?? null;

    // Consultar la tabla plan_inversion para obtener id_ejercicio usando id_plan
    $sqlPlanInversion = "SELECT id_ejercicio FROM plan_inversion WHERE id = ?";
    $stmtPlanInversion = $conexion->prepare($sqlPlanInversion);
    $stmtPlanInversion->bind_param('i', $idPlan);
    $stmtPlanInversion->execute();
    $resultPlanInversion = $stmtPlanInversion->get_result();
    $dataPlanInversion = $resultPlanInversion->fetch_assoc();
    $planEjercicioId = $dataPlanInversion['id_ejercicio'] ?? null;

    // Verificar si id_ejercicio de plan_inversion coincide con el recibido
    if ($planEjercicioId != $id_ejercicio) {
        continue; // Saltar este registro si no coincide
    }

    // Consultar la tabla pl_sectores para obtener el valor de sector
    $sqlSector = "SELECT sector FROM pl_sectores WHERE id = ?";
    $stmtSector = $conexion->prepare($sqlSector);
    $stmtSector->bind_param('i', $sectorId);
    $stmtSector->execute();
    $resultSector = $stmtSector->get_result();
    $dataSector = $resultSector->fetch_assoc();
    $sector = $dataSector['sector'] ?? null;

    // Consultar la tabla pl_programas para obtener el valor de programa
    $sqlPrograma = "SELECT programa FROM pl_programas WHERE id = ?";
    $stmtPrograma = $conexion->prepare($sqlPrograma);
    $stmtPrograma->bind_param('i', $programaId);
    $stmtPrograma->execute();
    $resultPrograma = $stmtPrograma->get_result();
    $dataPrograma = $resultPrograma->fetch_assoc();
    $programa = $dataPrograma['programa'] ?? null;

    // Consultar partida en partidas_presupuestarias para obtener el formato completo y la descripción
    $sqlPartida = "SELECT partida, descripcion FROM partidas_presupuestarias WHERE id = ?";
    $stmtPartida = $conexion->prepare($sqlPartida);
    $stmtPartida->bind_param('i', $partidaId);
    $stmtPartida->execute();
    $resultPartida = $stmtPartida->get_result();
    $dataPartida = $resultPartida->fetch_assoc();

    if ($dataPartida) {
        $partidaCompleta = $dataPartida['partida'];
        $descripcion = $dataPartida['descripcion'];

        // Desglose de partida en los componentes requeridos
        $partidaArray = explode('.', $partidaCompleta);
        $partidaInfo = [
            'sector' => $sector,
            'programa' => $programa,
            'partida' => $partidaArray[0] ?? null,
            'gen' => $partidaArray[1] ?? null,
            'esp' => $partidaArray[2] ?? null,
            'sub' => $partidaArray[3] ?? null,
            'denominacion' => $descripcion,
            'monto' => $monto
        ];

        // Añadir la partida al array principal
        $partidasArray[] = $partidaInfo;
    }

    // Cerrar consultas de cada iteración
    $stmtSector->close();
    $stmtPrograma->close();
    $stmtPartida->close();
    $stmtProyectoInversion->close();
    $stmtPlanInversion->close();
}

$stmtProyectos->close();

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
        <th class="br">PROGRAMA</th>
        <th class="br">PARTIDA</th>
        <th class="br">GEN</th>
        <th class="br">ESP</th>
        <th class="br">SUB ESP</th>
        <th class="br">DENOMINACIÓN</th>
        <th class="br">ASIGNACION PRESUPUESTARIA</th>
        <th class="br">OBSERVACION</th>
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
            <td class="br"><?= $partida['sector'] ?></td>
            <td class="br"><?= $partida['programa'] ?></td>
            <td class="br"><?= $partida['partida'] ?></td>
            <td class="br"><?= $partida['gen'] ?></td>
            <td class="br"><?= $partida['esp'] ?></td>
            <td class="br"><?= $partida['sub'] ?></td>
            <td class="br subtitle"><?= $partida['denominacion'] ?></td>
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
        <td colspan="6" class="crim br">TOTAL GENERAL</td>
        <td class="crim br"></td>
        <td class="crim br"><?= number_format($totalGeneral, 2) ?></td>
    </tr>
</table>






</body>

</html>