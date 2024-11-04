<?php
require_once '../sistema_global/conexion.php';

// Suponemos que recibimos $id_ejercicio y $ente de alguna manera, como parámetros GET
$id_ejercicio = $_GET['id_ejercicio'];
$ente = $_GET['ente'];

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
$stmt->bind_param('s', $ente);
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
}
$stmt->close();

// Consultar distribuciones del ente en la tabla distribucion_entes
$sqlDistribuciones = "SELECT distribucion, actividad_id FROM distribucion_entes WHERE id_ente = ?";
$stmt = $conexion->prepare($sqlDistribuciones);
if (!$stmt) {
    die("Error en consulta de distribuciones del ente: " . mysqli_error($conexion));
}
$stmt->bind_param('i', $ente);
$stmt->execute();
$resultDistribuciones = $stmt->get_result();
$partidasData = [];
$maxActividad = 51;  // Inicializamos con el mínimo de las actividades

while ($rowDistribucion = $resultDistribuciones->fetch_assoc()) {
    $distribuciones = json_decode($rowDistribucion['distribucion'], true); // Decodificar el JSON
    $actividad_id = $rowDistribucion['actividad_id'];
    if ($actividad_id > $maxActividad) {
        $maxActividad = $actividad_id;
    }

    foreach ($distribuciones as $distribucion) {
        $id_distribucion = $distribucion['id_distribucion'];

        // Consultar distribucion_presupuestaria para obtener id_partida y monto_actual
        $sqlDistribucionPres = "SELECT id_partida, monto_actual FROM distribucion_presupuestaria WHERE id = ?";
        $stmtPres = $conexion->prepare($sqlDistribucionPres);
        if (!$stmtPres) {
            die("Error en consulta de distribucion_presupuestaria: " . mysqli_error($conexion));
        }
        $stmtPres->bind_param('i', $id_distribucion);
        $stmtPres->execute();
        $resultDistribucionPres = $stmtPres->get_result();
        $dataDistribucionPres = $resultDistribucionPres->fetch_assoc();

        if ($dataDistribucionPres) {
            $id_partida = $dataDistribucionPres['id_partida'];
            $monto_actual = $dataDistribucionPres['monto_actual'];

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
                    'monto' => $monto_actual,
                ];
               // Consultar actividad en la tabla ente_dependencias
                $sqlActividad = "SELECT actividad FROM entes_dependencias WHERE id = ?";
                $stmtActividad = $conexion->prepare($sqlActividad);
                if (!$stmtActividad) {
                    die("Error en consulta de actividad en ente_dependencias: " . mysqli_error($conexion));
                }
                $stmtActividad->bind_param('i', $actividad_id);
                $stmtActividad->execute();
                $resultActividad = $stmtActividad->get_result();
                $dataActividad = $resultActividad->fetch_assoc();

                if ($dataActividad) {
                    $partidaInfo['actividad'] = $dataActividad['actividad'];
                }

                $partidasData[] = $partidaInfo;
            }

            $stmtPartida->close();
        }

        $stmtPres->close();
    }
}
$stmt->close();

// Obtener el máximo valor de actividad desde entes_dependencias
$maxActividadQuery = "SELECT MAX(actividad) AS max_actividad FROM entes_dependencias WHERE id IN (SELECT actividad_id FROM distribucion_entes WHERE id_ente = ?)";
$stmtMaxActividad = $conexion->prepare($maxActividadQuery);
if (!$stmtMaxActividad) {
    die("Error en consulta del máximo de actividad: " . mysqli_error($conexion));
}
$stmtMaxActividad->bind_param('i', $ente);
$stmtMaxActividad->execute();
$resultMaxActividad = $stmtMaxActividad->get_result();
$rowMaxActividad = $resultMaxActividad->fetch_assoc();
$maxActividad = $rowMaxActividad['max_actividad'];
$stmtMaxActividad->close();


// Determinar el rango de actividades
$inicioActividad = 51;
$finActividad = $maxActividad;

// Paso 1: Consolidar datos en una sola entrada por denominación y actividad
$partidasAgrupadas = [];
foreach ($partidasData as $partida) {
    $partKey = $partida['part'] . '-' . $partida['denominacion'];
    $actividad = $partida['actividad'];
    
    // Si la partida no existe en el array, inicializarla
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
    if (isset($partidasAgrupadas[$partKey]['actividades'][$actividad])) {
        $partidasAgrupadas[$partKey]['actividades'][$actividad] += $partida['monto'];
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
        <th class="br">PART</th>
        <th class="br">GEN</th>
        <th class="br">ESP</th>
        <th class="br">SUB ESP</th>
        <th class="br">COD ORDI</th>
        <th class="br">DENOMINACIÓN</th>
        <th class="br">TOTAL PROGRAMA</th>
        <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
            <th class="br">ACTIVIDAD <?= $actividad ?></th>
        <?php endfor; ?>
        <th class="br">MONTO DE LA OBRA</th>
    </tr>

    <?php
    $totalPartida = 0;
    $partAnterior = null;
    foreach ($partidasAgrupadas as $partidaKey => $partida):
        // Verificar si la partida ha cambiado para mostrar el total del grupo anterior
        if ($partAnterior !== null && $partAnterior !== $partida['part']) {
            ?>
            <!-- Fila de total por PART -->
            <tr>
                <td colspan="6" class="crim br">TOTAL POR PARTIDA <?= $partAnterior ?></td>
                <td class="crim br"><?= number_format($totalPartida, 2) ?></td>
                <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                    <td class="crim br"></td>
                <?php endfor; ?>
                <td class="crim br"><?= number_format($totalPartida, 2) ?></td>
            </tr>
            <?php
            // Reiniciar el total para la nueva partida
            $totalPartida = 0;
        }

        // Acumular el total de la partida actual
        $totalPartida += $partida['total_programa'];
        $partAnterior = $partida['part'];
        ?>
        <!-- Fila de datos consolidada por partida -->
        <tr>
            <td class="br"><?= $partida['part'] ?></td>
            <td class="br"><?= $partida['gen'] ?></td>
            <td class="br"><?= $partida['esp'] ?></td>
            <td class="br"><?= $partida['sub_esp'] ?></td>
            <td class="br"><?= $partida['cod_ordi'] ?></td>
            <td class="br subtitle"><?= $partida['denominacion'] ?></td>
            <td class="br"><?= number_format($partida['total_programa'], 2) ?></td>

            <!-- Actividades dinámicas -->
            <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
                <td class="br"><?= ($partida['actividades'][$actividad] > 0 ? number_format($partida['actividades'][$actividad], 2) : '') ?></td>
            <?php endfor; ?>

            <!-- Monto de la obra -->
            <td class="br"><?= number_format($partida['total_programa'], 2) ?></td>
        </tr>
    <?php endforeach; ?>

    <!-- Fila de total de la última partida -->
    <tr>
        <td colspan="6" class="crim br">TOTAL POR PARTIDA <?= $partAnterior ?></td>
        <td class="crim br"><?= number_format($totalPartida, 2) ?></td>
        <?php for ($actividad = $inicioActividad; $actividad <= $finActividad; $actividad++): ?>
            <td class="crim br"></td>
        <?php endfor; ?>
        <td class="crim br"><?= number_format($totalPartida, 2) ?></td>
    </tr>
</table>




</body>

</html>