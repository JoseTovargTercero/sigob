<?php
require_once '../sistema_global/conexion.php';

// Verificación de parámetros GET
if (!isset($_GET['id_ejercicio']) || !isset($_GET['ente'])) {
    die("Parámetros faltantes.");
}

$id_ejercicio = intval($_GET['id_ejercicio']); // Asegurarse de que es entero
$ente = intval($_GET['ente']); // Asegurarse de que es entero

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

// Verificar si $denominacion se llenó correctamente
// Uncomment the following line for debugging
// var_dump($denominacion);

// CONSULTAS
// Información del sector, programa y proyecto del ente
$stmt = mysqli_prepare($conexion, "SELECT entes.ente_nombre, ppy.proyecto_id, ppy.denominacion AS nombre_proyecto, 
                                        pp.programa, pp.denominacion AS nombre_programa, 
                                        ps.sector, ps.denominacion AS nombre_sector 
                                   FROM entes
                                   LEFT JOIN pl_sectores ps ON ps.id = entes.sector 
                                   LEFT JOIN pl_programas pp ON pp.id = entes.programa 
                                   LEFT JOIN pl_proyectos ppy ON ppy.id = entes.proyecto 
                                   WHERE entes.id = ?");
if (!$stmt) {
    die("Error en consulta de sector y programa: " . mysqli_error($conexion));
}
$stmt->bind_param('i', $ente); // Cambiado de 's' a 'i' si 'ente' es entero
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sector_n = $row['sector'];
        $nombre_sector = $row['nombre_sector'];
        $programa_n = $row['programa'];
        $nombre_programa = $row['nombre_programa'];
        $proyecto_n = $row['proyecto_id'] ?? '00';
        $nombre_proyecto = ($proyecto_n == '00' ? $nombre_programa : $row['nombre_proyecto']);
        $ue_n = $row['ente_nombre'];
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
$maxActividad = 51;  // Inicializamos con el mínimo de las actividades

while ($rowDistribucion = $resultDistribuciones->fetch_assoc()) {
    $distribuciones = json_decode($rowDistribucion['distribucion'], true); // Decodificar el JSON

    if (!is_array($distribuciones)) {
        continue; // Si no es un array válido, saltar
    }

    foreach ($distribuciones as $distribucion) {
        if (!isset($distribucion['id_distribucion']) || !isset($distribucion['monto'])) {
            continue; // Saltar si faltan datos
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

            // Actualizar maxActividad
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

                // Desglosar la partida en part, gen, esp, sub_esp, cod_ordi
                $partidaArray = explode('.', $partidaCompleta);
                $partidaInfo = [
                    'part' => $partidaArray[0] ?? null,
                    'gen' => $partidaArray[1] ?? null,
                    'esp' => $partidaArray[2] ?? null,
                    'sub_esp' => $partidaArray[3] ?? null,
                    'cod_ordi' => $partidaArray[4] ?? null,
                    'denominacion' => $descripcion,
                    'monto' => $monto,
                    'actividad' => $actividad // Usar la actividad desde distribucion_presupuestaria
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

    if (!isset($partidasAgrupadas[$partKey])) {
        $partidasAgrupadas[$partKey] = [
            'part' => $partida['part'],
            'gen' => $partida['gen'],
            'esp' => $partida['esp'],
            'sub_esp' => $partida['sub_esp'],
            'cod_ordi' => $partida['cod_ordi'],
            'denominacion' => $partida['denominacion'],
            'total_programa' => 0,
            'actividades' => array_fill($inicioActividad, $finActividad - $inicioActividad + 1, 0)
        ];
    }

    // Acumular el monto en el total del programa
    $partidasAgrupadas[$partKey]['total_programa'] += $partida['monto'];

    // Acumular el monto en la actividad correspondiente
    if ($actividad >= $inicioActividad && $actividad <= $finActividad) {
        $partidasAgrupadas[$partKey]['actividades'][$actividad] += $partida['monto'];
    }
}


echo "<pre>";
var_dump($partidasAgrupadas);
echo "</pre>";


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
            <b>SECTOR</b> <br>
            <b>PROGRAMA</b> <br>
            <b>PROYECTO</b> <br>
            <b>UNIDAD EJECUTORA</b>
        </td>
        <td class="text-left">
            <b>: <?php echo $sector_n . ' ' . $nombre_sector ?></b> <br>
            <b>: <?php echo $programa_n . ' ' . $nombre_programa ?> </b> <br>
            <b>: <?php echo $proyecto_n . ' ' . $nombre_proyecto ?></b> <br>
            <b>: <?php echo $ue_n ?> </b>
        </td>
    </tr>
</table>

<table>
    <!-- Encabezado de la tabla -->
    <tr>
        <td colspan="6" class="header"></td>
        <td colspan="<?= $finActividad - $inicioActividad + 2 ?>" class="title">ACTIVIDADES</td>
    </tr>

    <!-- Encabezado de columnas -->
    <tr>
        <th class="br bb bt bl">PART</th>
        <th class="br bb bt">GEN</th>
        <th class="br bb bt">ESP</th>
        <th class="br bb bt">SUB ESP</th>
        <th class="br bb bt">COD ORDI</th>
        <th class="br bb bt">DENOMINACIÓN</th>
        <th class="br bb bt">TOTAL PROGRAMA</th>
        <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
            <th class="br bb bt">ACTIVIDAD <?= $actividad ?></th>
        <?php endfor; ?>
        <th class="br bb bt">MONTO DE LA OBRA</th>
    </tr>

    <?php
    $totalProgramaGeneral = 0;
    $totalActividad = array_fill($inicioActividad, $finActividad - $inicioActividad + 1, 0);
    $totalMontoObra = 0;
    $totalPartida = 0;
    $partAnterior = null;

    foreach ($partidasAgrupadas as $partidaKey => $partida):
        // Verificar si la partida ha cambiado para mostrar el total del grupo anterior
        if ($partAnterior !== null && $partAnterior !== $partida['part']) {
    ?>
        <!-- Fila de total por PART -->
        <tr>
            <td colspan="<?= 6 + ($finActividad - $inicioActividad + 2) ?>" class="br bb bl text-end underline p10">
                TOTAL POR PARTIDA <?php echo $partAnterior ?>
            </td>
            <td class="crim br bb"><?= number_format($totalPartida, 2) ?></td>
        </tr>
    <?php
        // Reiniciar el total para la nueva partida
        $totalPartida = 0;
    }

    // Acumular el total de la partida actual y de cada actividad
    $totalPartida += $partida['total_programa'];
    $totalProgramaGeneral += $partida['total_programa'];
    $partAnterior = $partida['part'];

    foreach ($partida['actividades'] as $actividad => $monto) {
        $totalActividad[$actividad] += $monto;
    }

    $totalMontoObra += $partida['total_programa'];
    ?>
    <!-- Fila de datos consolidada por partida -->
    <tr>
        <td class="br bb bl"><?= $partida['part'] ?></td>
        <td class="br bb"><?= $partida['gen'] ?></td>
        <td class="br bb"><?= $partida['esp'] ?></td>
        <td class="br bb"><?= $partida['sub_esp'] ?></td>
        <td class="br bb"><?= $partida['cod_ordi'] ?></td>
        <td class="br bb text-start"><?= $partida['denominacion'] ?></td>
        <td class="br bb"><?= number_format($partida['total_programa'], 2) ?></td>

        <!-- Actividades dinámicas -->
        <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
            <td class="br bb bl"><?= ($partida['actividades'][$actividad] > 0 ? number_format($partida['actividades'][$actividad], 2) : '0,00') ?></td>
        <?php endfor; ?>

        <!-- Monto de la obra -->
        <td class="br bb bl"><?= number_format($partida['total_programa'], 2) ?></td>
    </tr>
    <?php endforeach; ?>

    <!-- Fila de total de la última partida -->
    <tr>
       <td colspan="<?= 6 + ($finActividad - $inicioActividad + 2) ?>" class="br bb bl text-end underline p10">
            TOTAL POR PARTIDA <?php echo $partAnterior  ?>
       </td>
        <td class="crim br bb"><?= number_format($totalPartida, 2) ?></td>
    </tr>

    <!-- Fila de totales generales -->
    <tr>
        <td colspan="6" class="br bb bl text-end underline p10">TOTAL GENERAL</td>
        <td class="crim br bb"><?= number_format($totalProgramaGeneral, 2) ?></td>

        <!-- Totales por actividad -->
        <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
            <td class="crim br bb"><?= number_format($totalActividad[$actividad], 2) ?></td>
        <?php endfor; ?>

        <!-- Total monto de la obra -->
        <td class="crim br bb"><?= number_format($totalMontoObra, 2) ?></td>
    </tr>
</table>



</body>

</html>