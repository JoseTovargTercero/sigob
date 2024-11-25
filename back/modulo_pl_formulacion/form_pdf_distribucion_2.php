<?php
require_once '../sistema_global/conexion.php';

// Verificación de parámetros GET
if (!isset($_GET['id_ejercicio'])) {
    die("Parámetros faltantes.");
}

$id_ejercicio = intval($_GET['id_ejercicio']);


// CONSULTAS
// Obtener denominaciones desde pl_partidas
$denominacion = [];
$stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_partidas`");
if (!$stmt) {
    die("Error en consulta de pl_partidas: " . mysqli_error($conexion));
}
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $denominacion[$row['partida']] = $row['denominacion'];
    }
}
$stmt->close();




$sector_n = '15';
$ue_n = 'DESPACHO DEL GOBERNADOR';
$proyecto_n = '00';
$nombre_proyecto = '';


$stmt = mysqli_prepare($conexion, "SELECT sec.id AS secId, plp.id AS proId, sec.denominacion AS nombre_sector, plp.programa, plp.denominacion AS nombre_programa FROM `pl_sectores` AS sec
LEFT JOIN pl_programas plp ON plp.sector = sec.id
 WHERE sec.sector = ?");
$stmt->bind_param('s', $sector_n);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nombre_sector = $row['nombre_sector'];
        $nombre_programa = $row['nombre_programa'];
        $programa_n = $row['programa'];
        $sector_id = $row['secId'];
        $programa_id = $row['proId'];
    }
}
$stmt->close();
// INFO SECCION SUPERIOR




$stmt = mysqli_prepare($conexion, "SELECT entes.id, pp.partida FROM `entes`
JOIN partidas_presupuestarias pp ON pp.id=entes.partida 
 WHERE tipo_ente = 'D'
 ORDER BY pp.partida");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $idsEntes[] = $row['id']; // Almacena cada ID
    }
}
$stmt->close();


if (empty($idsEntes)) {
    die('No hay entes asociados');
}

$placeholders = implode(',', array_fill(0, count($idsEntes), '?'));


// Consultar distribuciones del ente en la tabla distribucion_entes
$sqlDistribuciones = "SELECT distribucion FROM distribucion_entes 
                          WHERE id_ente IN ($placeholders) AND id_ejercicio = ?";
$stmt = $conexion->prepare($sqlDistribuciones);
if (!$stmt) {
    die("Error en consulta de distribuciones del ente: " . mysqli_error($conexion));
}

// Vincula los parámetros: primero los IDs de los entes, luego el id_ejercicio
$types = str_repeat('i', count($idsEntes)) . 'i'; // 'i' para cada entero
$params = array_merge($idsEntes, [$id_ejercicio]); // Junta los IDs y el id_ejercicio

$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultDistribuciones = $stmt->get_result();


$partidasData = [];
$maxActividad = 51;

while ($rowDistribucion = $resultDistribuciones->fetch_assoc()) {
    $distribuciones = json_decode($rowDistribucion['distribucion'], true);

    if (!is_array($distribuciones)) {
        continue;
    }

    foreach ($distribuciones as $distribucion) {
        if (!isset($distribucion['id_distribucion']) || !isset($distribucion['monto'])) {
            continue;
        }

        $id_distribucion = intval($distribucion['id_distribucion']);
        $monto = floatval($distribucion['monto']);

        // Consultar distribucion_presupuestaria para obtener id_partida, id_actividad y monto_actual
        $sqlDistribucionPres = "SELECT id_partida, id_actividad AS actividad, monto_actual FROM distribucion_presupuestaria WHERE id = ? AND id_ejercicio = ?";
        $stmtPres = $conexion->prepare($sqlDistribucionPres);
        if (!$stmtPres) {
            die("Error en consulta de distribucion_presupuestaria: " . mysqli_error($conexion));
        }
        $stmtPres->bind_param('ii', $id_distribucion, $id_ejercicio);
        $stmtPres->execute();
        $resultDistribucionPres = $stmtPres->get_result();
        $dataDistribucionPres = $resultDistribucionPres->fetch_assoc();
        $stmtPres->close();

        if ($dataDistribucionPres) {
            $id_partida = $dataDistribucionPres['id_partida'];
            $actividad = $dataDistribucionPres['actividad'];
            $monto_actual = $dataDistribucionPres['monto_actual'];

            if ($actividad > $maxActividad) {
                $maxActividad = $actividad;
            }

            // Consultar partida y descripcion en partidas_presupuestarias
            $sqlPartida = "SELECT partida, descripcion FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $conexion->prepare($sqlPartida);
            if (!$stmtPartida) {
                die("Error en consulta de partidas_presupuestarias: " . mysqli_error($conexion));
            }
            $stmtPartida->bind_param('i', $id_partida);
            $stmtPartida->execute();
            $resultPartida = $stmtPartida->get_result();
            $dataPartida = $resultPartida->fetch_assoc();
            $stmtPartida->close();

            if ($dataPartida) {
                $partidaCompleta = $dataPartida['partida'];
                $descripcion = $dataPartida['descripcion'];

                // Separar la partida completa en sus componentes
                $partidaArray = explode('.', $partidaCompleta);

                $part = $partidaArray[0] ?? null;
                $gen = $partidaArray[1] ?? null;
                $esp = $partidaArray[2] ?? null;
                $sub_esp = $partidaArray[3] ?? null;
                $cod_ordi = $partidaArray[4] ?? '0000';  // Definir cod_ordi si no existe

                // Modificar la partida para que los últimos 4 dígitos sean 0000
                $partidaModificada = implode('.', array_slice($partidaArray, 0, 4)) . '.0000';


                // Verificar el valor de cod_ordi
                if ($cod_ordi !== '0000') {
                    // Si cod_ordi es diferente de '0000', buscar la descripcion principal
                    // Usar la partida modificada con los últimos 4 dígitos en '0000'
                    $sqlDenominacionPrincipal = "SELECT descripcion 
                                 FROM partidas_presupuestarias 
                                 WHERE partida = ?";
                    $stmtDenominacionPrincipal = $conexion->prepare($sqlDenominacionPrincipal);

                    if (!$stmtDenominacionPrincipal) {
                        die("Error en consulta de denominación principal: " . mysqli_error($conexion));
                    }

                    // Pasar la partida modificada a la consulta
                    $stmtDenominacionPrincipal->bind_param('s', $partidaModificada);
                    $stmtDenominacionPrincipal->execute();
                    $resultDenominacionPrincipal = $stmtDenominacionPrincipal->get_result();
                    $dataDenominacionPrincipal = $resultDenominacionPrincipal->fetch_assoc();

                    $denominacion_principal = $dataDenominacionPrincipal['descripcion'] ?? $descripcion;
                } else {
                    $denominacion_principal = $descripcion;
                }
                // Desglosar la denominación principal
                $partidaPrincipalArray = explode('.', $partidaModificada);
                $part_principal = $partidaPrincipalArray[0] ?? null;
                $gen_principal = $partidaPrincipalArray[1] ?? null;
                $esp_principal = $partidaPrincipalArray[2] ?? null;
                $sub_esp_principal = $partidaPrincipalArray[3] ?? null;
                $cod_ordi_principal = $partidaPrincipalArray[4] ?? '0000'; // Este será siempre 0000

                $partidaInfo = [
                    'part' => $part,
                    'gen' => $gen,
                    'esp' => $esp,
                    'sub_esp' => $sub_esp,
                    'cod_ordi' => $cod_ordi,
                    'denominacion' => $descripcion,
                    'denominacion_principal' => $denominacion_principal,
                    'part_principal' => $part_principal,
                    'gen_principal' => $gen_principal,
                    'esp_principal' => $esp_principal,
                    'sub_esp_principal' => $sub_esp_principal,
                    'cod_ordi_principal' => $cod_ordi_principal,
                    'monto' => $monto,
                    'actividad' => $actividad
                ];

                $partidasData[] = $partidaInfo;
            }
        }
    }
}

$stmt->close();


// Determinar el rango de actividades
$inicioActividad = 51;
$finActividad = ($maxActividad > $inicioActividad) ? $maxActividad : $inicioActividad;

// Paso 1: Consolidar datos en una sola entrada por denominación y actividad
$partidasAgrupadas = [];
foreach ($partidasData as $partida) {
    $partKey = $partida['part'] . '-' . $partida['denominacion'];
    $actividad = intval($partida['actividad']);

    // Estructura para almacenar información consolidada de la partida
    $partidaInfo = [
        'part' => $partida['part'],
        'gen' => $partida['gen'],
        'esp' => $partida['esp'],
        'sub_esp' => $partida['sub_esp'],
        'cod_ordi' => $partida['cod_ordi'],
        'denominacion' => $partida['denominacion'],
        'denominacion_principal' => $partida['denominacion_principal'],
        'part_principal' => $partida['part_principal'],
        'gen_principal' => $partida['gen_principal'],
        'esp_principal' => $partida['esp_principal'],
        'sub_esp_principal' => $partida['sub_esp_principal'],
        'cod_ordi_principal' => $partida['cod_ordi_principal'],
        'monto' => $partida['monto'],
        'actividad' => $partida['actividad']
    ];

    // Agrupando las partidas por la clave (part-denominacion)
    if (!isset($partidasAgrupadas[$partKey])) {
        $partidasAgrupadas[$partKey] = [
            'part' => $partida['part'],
            'gen' => $partida['gen'],
            'esp' => $partida['esp'],
            'sub_esp' => $partida['sub_esp'],
            'cod_ordi' => $partida['cod_ordi'],
            'denominacion' => $partida['denominacion'],
            'denominacion_principal' => $partida['denominacion_principal'],
            'part_principal' => $partida['part_principal'],
            'gen_principal' => $partida['gen_principal'],
            'esp_principal' => $partida['esp_principal'],
            'sub_esp_principal' => $partida['sub_esp_principal'],
            'cod_ordi_principal' => $partida['cod_ordi_principal'],
            'total_programa' => 0,
            'actividades' => array_fill($inicioActividad, $finActividad - $inicioActividad + 1, 0)
        ];
    }

    // Acumulando el monto en el total del programa
    $partidasAgrupadas[$partKey]['total_programa'] += $partida['monto'];

    // Acumulando el monto en la actividad correspondiente
    if ($actividad >= $inicioActividad && $actividad <= $finActividad) {
        $partidasAgrupadas[$partKey]['actividades'][$actividad] += $partida['monto'];
    }
}


// Consultar datos del ejercicio fiscal
$query_sector = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
if (!$stmt) {
    die("Error en consulta de ejercicio_fiscal: " . mysqli_error($conexion));
}
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

$ano = $data['ano'] ?? 'Desconocido';
$situado = $data['situado'] ?? 'Desconocido';
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
            font-size: 14px;
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
            width: 100px;
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

        .text-end {
            text-align: right;
        }

        .underline {
            text-decoration: underline;
        }

        .p10 {
            padding: 10px;
        }

        .text-start {
            text-align: left;
        }
    </style>
</head>

<body>

    <?php
    $totalProgramaGeneral = 0;
    $totalActividad = array_fill($inicioActividad, $finActividad - $inicioActividad + 1, 0);
    $totalPartida = 0;
    $partAnterior = null;
    usort($partidasAgrupadas, function ($a, $b) {
        // Primero, manejar los casos donde 'cod_ordi' es vacío o '0000'
        if ($a['cod_ordi'] === '0000' && $b['cod_ordi'] !== '0000') {
            return 1;  // Poner el '0000' al final
        }
        if ($a['cod_ordi'] !== '0000' && $b['cod_ordi'] === '0000') {
            return -1;  // Poner el '0000' al final
        }

        // Si ambos son diferentes de '0000', ordenar numéricamente
        return (int)$a['cod_ordi'] <=> (int)$b['cod_ordi'];
    });
    // Ordenar el array $partidasAgrupadas por 'part'
    usort($partidasAgrupadas, function ($a, $b) {
        return $a['part'] <=> $b['part'];
    });

    ?>

    <table class='header-table bt br bb bl bc-lightgray'>
        <tr>
            <td class='text-left' colspan='2' style='vertical-align: top;'>
                <b>REPUBLICA BOLIVARIANA DE VENEZUELA <br>
                    GOBERNACIÓN DEL ESTADO AMAZONAS <br>
                    SECRETARIA DE PLANIFICACION, <br>
                    PROYECTOS Y PRESUPUESTO
                </b>
            </td>
            <td class='text-right' style='vertical-align: top;'>
                <b>
                    Fecha: <?php echo date('d/m/Y') ?>
                    <br>
                    <img src='../../img/logo.jpg' class='logo'>
                </b>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <h2 align='center'>CREDITOS PRESUPUESTARIOS DEL PROGRAMA Y SUS ACTIVIDADES <br>
                    A NIVEL DE PARTIDAS, SUB-PARTIDAS ESPECIFICAS Y ORDINALES</h2>
                <b>PRESUPUESTO <?php echo $ano ?></b>
            </td>
        </tr>
        <tr style="font-size: 12px;" class="crim">
            <td class="text-left" style="width: 15%;">
                <b class="crim">SECTOR</b> <br>
                <b class="crim">PROGRAMA</b> <br>
                <b class="crim">PROYECTO</b> <br>
                <b class="crim">UNIDAD EJECUTORA</b>
            </td>
            <td class="text-left">
                <b class="crim">: <?php echo $sector_n . ' ' . $nombre_sector ?></b> <br>
                <b class="crim">: <?php echo $programa_n . ' ' . $nombre_programa ?> </b> <br>
                <b class="crim">: <?php echo $proyecto_n . ' ' . $nombre_proyecto ?></b> <br>
                <b class="crim">: <?php echo $ue_n ?> </b>
            </td>
        </tr>
    </table>


    <div style="padding: 0 0 10px 0;">
        <b>51: CREDITOS ADMINISTRADOS POR LA DIRECCIÓN EJECUTIVA</b><br>
    </div>



    <table>

        <tr>
            <th class="br bt bb bl">PART</th>
            <th class="br bt bb">GEN</th>
            <th class="br bt bb">ESP</th>
            <th class="br bt bb">SUB ESP</th>
            <th class="br bt bb">COD ORDI</th>
            <th class="br bt bb">COD OBR</th>
            <th class="br bt bb">DENOMINACIÓN</th>
            <th class="br bt bb">TOTAL PROGRAMA</th>
            <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                <th class="br bt bb">ACTIVIDAD <?= $actividad ?></th>
            <?php endfor; ?>
            <th class="br bt bb">MONTO DE LA OBRA</th>
        </tr>
        <?php
        $totalPartida = 0;
        $codigoPartidaAnterior = '';
        $totalActividad = array_fill($inicioActividad, $finActividad - $inicioActividad + 1, 0);
        $totalProgramaGeneral = 0;
        $ultimaDenominacionPrincipal = '';

        // Consulta previa para optimizar las denominaciones
        $denominacionesPartidas = [];
        $query = "SELECT partida, denominacion FROM pl_partidas";
        $result = mysqli_query($conexion, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $denominacionesPartidas[$row['partida']] = $row['denominacion'];
        }

        foreach ($partidasAgrupadas as $partidaKey => $partida):
            if ($ultimaDenominacionPrincipal !== $partida['denominacion_principal']) {
                echo "<tr>
                  <th class=' crim br bl'>" . $partida['part'] . "</th>
                <th class=' crim br '>" . $partida['gen'] . "</th>
                <th class=' crim br '>" . $partida['esp'] . "</th>
                <th class=' crim br '>" . $partida['sub_esp'] . "</th>
                <th class=' crim br '></th>
                <th class=' crim br '></th>
                <td class=' crim br text-left'><u>{$partida['denominacion_principal']}</u></td>
                <th class=' crim br '></th>
                <th class=' crim br '></th>
                <th class=' crim br '></th>
                </tr>";
                $ultimaDenominacionPrincipal = $partida['denominacion_principal'];
            }

            if ($partida['part'] !== $codigoPartidaAnterior && $codigoPartidaAnterior !== ''):
                $denominacionPartida = $denominacionesPartidas[$codigoPartidaAnterior] ?? '';
        ?>
                <tr>
                    <td colspan="6" class="br  bl text-end underline ">TOTAL POR PARTIDA <?= $codigoPartidaAnterior ?></td>
                    <td class="br  text-left"><?= $denominacionPartida ?></td>
                    <td class="br "><?= number_format($totalPartida, 2) ?></td>
                    <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                        <td class="br  bl"><?= number_format($totalActividad[$actividad], 2) ?></td>
                    <?php endfor; ?>
                    <td class="br  bl"><?= number_format($totalPartida, 2) ?></td>
                </tr>
            <?php
                $totalPartida = 0;
            endif;
            ?>
            <tr>
                <td class="br bl"><?= $partida['part'] ?></td>
                <td class="br"><?= $partida['gen'] ?></td>
                <td class="br"><?= $partida['esp'] ?></td>
                <td class="br"><?= $partida['sub_esp'] ?></td>
                <td class="br "><?= $partida['cod_ordi'] ?></td>
                <td class="br "></td>
                <td class="br  text-left"><?= $partida['denominacion'] ?></td>
                <td class="br "><?= number_format($partida['total_programa'], 2) ?></td>
                <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                    <td class="br  bl"><?= $partida['actividades'][$actividad] > 0 ? number_format($partida['actividades'][$actividad], 2) : '' ?></td>
                    <?php $totalActividad[$actividad] += $partida['actividades'][$actividad]; ?>
                <?php endfor; ?>
                <td class="br  bl"></td>
                <?php
                $totalPartida += $partida['total_programa'];
                $totalProgramaGeneral += $partida['total_programa'];
                $codigoPartidaAnterior = $partida['part'];
                ?>
            </tr>
        <?php endforeach; ?>
        <tr>
            <th colspan="6" class="br bt bb  bl text-end underline p10 ">TOTAL POR PARTIDA <?= $codigoPartidaAnterior ?></th>
            <th class="br bt bb  underline p10 text-left"><?= $denominacionesPartidas[$codigoPartidaAnterior] ?? '' ?></th>
            <th class="br bt bb  underline p10"><?= number_format($totalPartida, 2) ?></th>
            <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                <th class="br bt bb  bl underline p10"><?= number_format($totalActividad[$actividad], 2) ?></th>
            <?php endfor; ?>
            <th class="br    bl underline p10"></th>
        </tr>
        <tr style="    font-weight: bold;">
            <th colspan="7" class="br bt bb bl text-end underline  p10 crim ">TOTAL GENERAL</th>
            <th class="br bt bb underline  p10 crim"><?= number_format($totalProgramaGeneral, 2) ?></th>
            <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                <th class="br bt bb underline  p10 crim"><?= number_format($totalActividad[$actividad], 2) ?></th>
            <?php endfor; ?>
            <th class="br bt bb underline  p10 crim"></th>
        </tr>
    </table>





</body>

</html>