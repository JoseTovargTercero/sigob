<?php
require_once '../sistema_global/conexion.php';

$id_ejercicio = $_GET['id_ejercicio'];

// Consultar ejercicio fiscal
$query_sector = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$resultado = $result->fetch_assoc();

if (!$resultado) {
    die("No se encontró el ejercicio fiscal para el ID proporcionado.");
}

$ano = $resultado['ano'];
$situado = $resultado['situado'];
$stmt->close();

// Nueva consulta a la tabla gastos
$query_gastos = "SELECT * FROM gastos WHERE id_ejercicio = ?";
$stmt_gastos = $conexion->prepare($query_gastos);
$stmt_gastos->bind_param('i', $id_ejercicio);
$stmt_gastos->execute();
$result_gastos = $stmt_gastos->get_result();

$gastos = $result_gastos->fetch_all(MYSQLI_ASSOC);

// Procesar distribuciones en los registros de gastos
$data = [];

foreach ($gastos as $gasto) {
    $distribuciones_json = $gasto['distribuciones'];
    $distribuciones_array = json_decode($distribuciones_json, true);

    if (!is_array($distribuciones_array)) {
        echo "Error al decodificar el JSON de distribuciones para el gasto con ID: " . $gasto['id'] . "<br>";
        continue;
    }

    foreach ($distribuciones_array as $distribucion) {
        $id_distribucion = $distribucion['id_distribucion'];
        $monto_actual = $distribucion['monto'];

        // Consultar distribucion_presupuestaria
        $query_distribucion = "SELECT * FROM distribucion_presupuestaria WHERE id = ? AND id_ejercicio = ?";
        $stmt_distribucion = $conexion->prepare($query_distribucion);
        $stmt_distribucion->bind_param('ii', $id_distribucion, $id_ejercicio);
        $stmt_distribucion->execute();
        $result_distribucion = $stmt_distribucion->get_result();
        $distribucion_presupuestaria = $result_distribucion->fetch_assoc();

        if (!$distribucion_presupuestaria) {
            echo "No se encontró distribucion_presupuestaria para id_distribucion: $id_distribucion y id_ejercicio: $id_ejercicio<br>";
            continue;
        }

        $monto_inicial = $distribucion_presupuestaria['monto_inicial'] ?? 0;
        $monto_disponible = $distribucion_presupuestaria['monto_actual'] ?? 0; // Monto disponible desde distribucion_presupuestaria
        $id_sector = $distribucion_presupuestaria['id_sector'] ?? 0;

        // Consultar sector y denominación en pl_sectores
        $query_sector = "SELECT sector, denominacion FROM pl_sectores WHERE id = ?";
        $stmt_sector = $conexion->prepare($query_sector);
        $stmt_sector->bind_param('i', $id_sector);
        $stmt_sector->execute();
        $result_sector = $stmt_sector->get_result();
        $sector_data = $result_sector->fetch_assoc();

        if (!$sector_data) {
            echo "No se encontró registro en pl_sectores para id_sector: $id_sector<br>";
            continue;
        }

        $sector = $sector_data['sector'] ?? 'N/A';
        $denominacion = $sector_data['denominacion'] ?? 'N/A';

        // Agrupar datos por id_sector
        if (!isset($data[$id_sector])) {
            $data[$id_sector] = [
                $sector,        // Sector
                $denominacion,  // Denominación
                0,              // Sumatoria de monto_inicial
                0,              // Sumatoria comprometido
                0,              // Sumatoria causado
                0,              // Sumatoria disponible (monto_actual de distribucion_presupuestaria)
                0               // Sumatoria de monto_actual (de las distribuciones)
            ];
        }

        // Sumar montos al agrupamiento
        $data[$id_sector][2] += $monto_inicial;      // Sumar monto_inicial
        $data[$id_sector][6] += $monto_disponible;   // Sumar monto_actual (disponibilidad)

        // Sumar comprometido o causado según el status del gasto
        if ($gasto['status'] == 0) { // Comprometido
            $data[$id_sector][4] += $monto_actual;
        } elseif ($gasto['status'] == 1) { // Causado
            $data[$id_sector][5] += $monto_actual;
        }
    }
}

// Imprimir resultados
print_r(array_values($data));
?>











<!DOCTYPE html>
<html>

<head>
    <title>RESUMEN GENERAL A NIVEL DE SECTORES</title>
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
            <th class="bt bl bb p-15" style="width: 10%">Codigo del Sector</th>
            <th class="bt bl bb p-15">Denominación</th>
            <th class="bt bl bb p-15">Asignación Inicial</th>
            <th class="bt bl bb p-15">Modificación</th>
            <th class="bt bl bb p-15">Compromiso</th>
            <th class="bt bl bb p-15">Causado</th>
            <th class="bt bl bb p-15">Disponibilidad</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_asignacion_inicial = 0;
        $total_modificacion = 0;
        $total_compromiso = 0;
        $total_causado = 0;
        $total_disponibilidad = 0;

        foreach ($data as $info_partida) {
            // Asignar los valores usando índices numéricos
            $codigo_partida = $info_partida[0] ?? 'N/A';
            $denominacion = $info_partida[1] ?? 'N/A';
            $asignacion_inicial = $info_partida[2] ?? 0;
            $modificacion = $info_partida[3] ?? 0; // Si corresponde al índice [3]
            $compromiso = $info_partida[4] ?? 0;   // Si corresponde al índice [4]
            $causado = $info_partida[5] ?? 0;     // Si corresponde al índice [5]
            $disponibilidad = $info_partida[6] ?? 0;

            // Acumular totales
            $total_asignacion_inicial += $asignacion_inicial;
            $total_modificacion += $modificacion;
            $total_compromiso += $compromiso;
            $total_causado += $causado;
            $total_disponibilidad += $disponibilidad;

            echo "<tr>
                <td class='fz-8 bl'>{$codigo_partida}</td>
                <td class='fz-8 bl text-left'>{$denominacion}</td>
                <td class='fz-8 bl'>" . number_format($asignacion_inicial, 2, ',', '.') . "</td>
                <td class='fz-8 bl'>" . number_format($modificacion, 2, ',', '.') . "</td>
                <td class='fz-8 bl'>" . number_format($compromiso, 2, ',', '.') . "</td>
                <td class='fz-8 bl'>" . number_format($causado, 2, ',', '.') . "</td>
                <td class='fz-8 bl br'>" . number_format($disponibilidad, 2, ',', '.') . "</td>
            </tr>";
        }

        // Totales generales
        echo "<tr>
            <td class='bl bb'></td>
            <td class='bl bb fw-bold'>TOTALES</td>
            <td class='bl bb fw-bold'>" . number_format($total_asignacion_inicial, 2, ',', '.') . "</td>
            <td class='bl bb fw-bold'>" . number_format($total_modificacion, 2, ',', '.') . "</td>
            <td class='bl bb fw-bold'>" . number_format($total_compromiso, 2, ',', '.') . "</td>
            <td class='bl bb fw-bold'>" . number_format($total_causado, 2, ',', '.') . "</td>
            <td class='bl br bb fw-bold'>" . number_format($total_disponibilidad, 2, ',', '.') . "</td>
        </tr>";
        ?>
    </tbody>
</table>







</body>

</html>