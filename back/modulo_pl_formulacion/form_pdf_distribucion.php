<?php
require_once '../sistema_global/conexion.php';

// Verificación de parámetros GET
if (!isset($_GET['id_ejercicio']) || !isset($_GET['ente'])) {
    die("Parámetros faltantes.");
}

$id_ejercicio = intval($_GET['id_ejercicio']);
$ente = intval($_GET['ente']);

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


// CONSULTAS
// Información del sector, programa y proyecto del ente
$stmt = mysqli_prepare($conexion, "SELECT entes.ente_nombre, ppy.proyecto_id, ppy.denominacion AS nombre_proyecto, 
                                        pp.programa, pp.denominacion AS nombre_programa, 
                                        ps.sector, ps.denominacion AS nombre_sector 
                                   FROM entes
                                   LEFT JOIN pl_sectores ps ON ps.id = entes.sector 
                                   LEFT JOIN pl_programas pp ON pp.id = entes.programa 
                                   LEFT JOIN pl_proyectos ppy ON ppy.id = entes.proyecto 
                                   WHERE entes.id = ? LIMIT 1");
if (!$stmt) {
    die("Error en consulta de sector y programa: " . mysqli_error($conexion));
}
$stmt->bind_param('i', $ente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sector_n = $row['sector'];
        $nombre_sector = $row['nombre_sector'];
        $programa_n = $row['programa'];
        $nombre_programa = $row['nombre_programa'];
        $proyecto_n = $row['proyecto_id'] ?? '00';
        $nombre_proyecto = ($proyecto_n == '00' ? '' : $row['nombre_proyecto']);
        $ue_n = ($row['sector'] == '15' ? 'DESPACHO DEL GOBERNADOR' : $row['ente_nombre']);
    }
} else {
    die("No se encontraron datos para el ente proporcionado.");
}
$stmt->close();

// Consultar distribuciones del ente en la tabla distribucion_entes
$sqlDistribuciones = "SELECT distribucion FROM distribucion_entes WHERE id_ente = ? AND id_ejercicio = ?";
$stmt = $conexion->prepare($sqlDistribuciones);
if (!$stmt) {
    die("Error en consulta de distribuciones del ente: " . mysqli_error($conexion));
}
$stmt->bind_param('ii', $ente, $id_ejercicio);
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

                $partidasData[$partidaCompleta . '.' . $actividad] = $partidaInfo;
            }
        }
    }
}

$stmt->close();


ksort($partidasData);


$actividades_nombre = [];

$stmt = mysqli_prepare($conexion, "SELECT * FROM `entes_dependencias` WHERE ue = ?");
$stmt->bind_param('s', $ente);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $actividades_nombre[$row['actividad']] = $row['ente_nombre'];
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
    $totalMontoObra = 0;
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
        <?php


        $stmt = mysqli_prepare($conexion, "SELECT * FROM `entes_dependencias` WHERE ue = ? ORDER BY actividad");
        $stmt->bind_param('s', $ente);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (isset($totalActividad[$row['actividad']])) {
                    echo "<b>{$row['actividad']}: {$row['ente_nombre']}</b><br>";
                }
            }
        }
        $stmt->close();

        ?>
    </div>



    <table>



        <!-- Encabezado de columnas -->
        <tr>
            <th class="br bb bt bl">PART</th>
            <th class="br bb bt">GEN</th>
            <th class="br bb bt">ESP</th>
            <th class="br bb bt">SUB ESP</th>
            <th class="br bb bt">COD ORDI</th>
            <th class="br bb bt">COD OBR</th>
            <th class="br bb bt">DENOMINACIÓN</th>
            <th class="br bb bt">TOTAL PROGRAMA</th>
            <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                <th class="br bb bt">
                    <small style="font-weight: lighter;">
                        <?php echo $actividades_nombre[$actividad] ?? 'SIN DENOMINACION'; ?>
                    </small>
                    <br>
                    ACTIVIDAD <?= $actividad ?>
                </th>
            <?php endfor; ?>
            <th class="br bb bt">MONTO DE LA OBRA</th>
        </tr>

        <!-- Fila para los encabezados principales (part, gen, esp, sub-esp) -->
        <?php
        $totalPartida = 0;
        $codigoPartidaAnterior = ''; // Para verificar cuando cambie el código de partida
        ?>

        <?php foreach ($partidasAgrupadas as $partidaKey => $partida): ?>
            <?php
            // Verificar si el código de la partida cambió
            if ($partida['part'] !== $codigoPartidaAnterior && $codigoPartidaAnterior !== ''):
                // Realizar la consulta para obtener la denominación de la partida
                $denominacionPartida = '';
                $queryDenominacion = "SELECT denominacion FROM pl_partidas WHERE partida = '" . $codigoPartidaAnterior . "'";
                $resultDenominacion = mysqli_query($conexion, $queryDenominacion);
                if ($row = mysqli_fetch_assoc($resultDenominacion)) {
                    $denominacionPartida = $row['denominacion'];
                }
            ?>
                <tr>
                    <th colspan="6" class="br bb bt bl text-end underline p10">
                        TOTAL POR PARTIDA <?= $codigoPartidaAnterior ?>
                    </th>
                    <th class="br bb bt text-left underline"><?= $denominacionPartida ?></th>
                    <th class="br bb bt"><?= number_format($totalPartida, 2) ?></th>

                    <!-- Totales por actividad -->
                    <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                        <th class="br bb bt bl"><?= number_format($totalActividad[$actividad], 2) ?></th>
                    <?php endfor; ?>

                    <th class="br   bl"></th>
                </tr>
            <?php
                // Reiniciar el acumulado para la nueva partida
                $totalPartida = 0;
            endif;
            ?>
            <!-- Fila para los encabezados normales (part, gen, esp, sub-esp) -->
            <tr>
                <td class="crim br bl"><?= $partida['part_principal'] ?></td>
                <td class="crim br"><?= $partida['gen_principal'] ?></td>
                <td class="crim br"><?= $partida['esp_principal'] ?></td>
                <td class="crim br"><?= $partida['sub_esp_principal'] ?></td>
                <td class="br "></td> <!-- Espacio vacío para COD ORDI -->
                <td class="br "></td> <!-- Espacio vacío para COD OBR -->
                <td class="crim br  text-left"><u><?= $partida['denominacion_principal'] ?></u></td>
                <td class="br "></td>

                <!-- Actividades dinámicas -->
                <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                    <td class="br  bl"></td>
                <?php endfor; ?>

                <!-- Monto de la obra -->
                <td class="br  bl"></td>
            </tr>

            <!-- Fila para los encabezados normales (part, gen, esp, sub-esp) -->
            <tr>
                <td class="br  bl"><?= $partida['part'] ?></td>
                <td class="br "><?= $partida['gen'] ?></td>
                <td class="br "><?= $partida['esp'] ?></td>
                <td class="br "><?= $partida['sub_esp'] ?></td>
                <td class="br "><?= $partida['cod_ordi'] ?></td> <!-- COD ORDI -->
                <td class="br "></td> <!-- Espacio vacío para COD OBR -->
                <td class="br  text-left"><?= $partida['denominacion'] ?></td>
                <td class="br "><?= number_format($partida['total_programa'], 2) ?></td>

                <!-- Actividades dinámicas -->
                <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                    <td class="br  bl"><?= ($partida['actividades'][$actividad] > 0 ? number_format($partida['actividades'][$actividad], 2) : '0,00') ?></td>
                    <?php
                    // Acumulando el total de actividades
                    $totalActividad[$actividad] += $partida['actividades'][$actividad];
                    ?>
                <?php endfor; ?>

                <td class="br bl"></td>
                <?php
                // Acumulando el total de la obra
                $totalMontoObra += $partida['total_programa'];
                // Acumulando el total de la partida
                $totalPartida += $partida['total_programa'];
                // Acumulando el total general del programa
                $totalProgramaGeneral += $partida['total_programa'];
                $codigoPartidaAnterior = $partida['part']; // Guardar el código de la partida actual
                ?>
            </tr>
        <?php endforeach; ?>

        <!-- Fila de total de la última partida -->
        <?php
        // Obtener la denominación de la última partida
        $denominacionPartida = '';
        $queryDenominacion = "SELECT denominacion FROM pl_partidas WHERE partida = '" . $codigoPartidaAnterior . "'";
        $resultDenominacion = mysqli_query($conexion, $queryDenominacion);
        if ($row = mysqli_fetch_assoc($resultDenominacion)) {
            $denominacionPartida = $row['denominacion'];
        }
        ?>


        <tr>
            <th colspan="6" class="br bb bt bl text-end underline p10">
                TOTAL POR PARTIDA <?= $codigoPartidaAnterior ?>
            </th>
            <th class="br bb bt"><?= $denominacionPartida ?></th>
            <th class=" br bb bt"><?= number_format($totalPartida, 2) ?></th>
            <!-- Totales por actividad -->
            <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                <th class="br bb bt bl"><?= number_format($totalActividad[$actividad], 2) ?></th>
            <?php endfor; ?>
            <th class="br bb "></th>
        </tr>

        <!-- Fila de totales generales -->
        <tr>
            <th colspan="7" class="br bb bl text-end underline p10 crim">TOTAL GENERAL</th>
            <th class="crim br bb"><?= number_format($totalProgramaGeneral, 2) ?></th>
            <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                <th class="crim br bb"><?= number_format($totalActividad[$actividad], 2) ?></th>
            <?php endfor; ?>
            <th class="crim br bb"></th>
        </tr>
    </table>
</body>

</html>