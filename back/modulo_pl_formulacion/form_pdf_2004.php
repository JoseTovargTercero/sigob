<?php
require_once '../sistema_global/conexion.php';

$id_ejercicio = $_GET['id_ejercicio'];

// Inicializar array para almacenar los datos por sector y programa


$query_sector = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$ano = $data['ano'];
$situado = $data['situado'];
$stmt->close();

$data = [];

// Consultar datos del sector y programa
$query_programa = "SELECT p.id AS id_programa, p.programa, p.sector, p.denominacion 
                   FROM pl_programas p
                   JOIN pl_sectores_presupuestarios s ON p.sector = s.sector AND p.programa = s.programa";

$stmt_programa = $conexion->prepare($query_programa);
if ($stmt_programa === false) {
    die('Error en la consulta SQL (pl_programas): ' . $conexion->error);
}

$stmt_programa->execute();
$result_programa = $stmt_programa->get_result();

// Agrupar los programas por sector y programa
$programas = []; // Array para agrupar programas
while ($programa_data = $result_programa->fetch_assoc()) {
    $sector = $programa_data['sector'];
    $programa = $programa_data['programa'];
    $denominacion = $programa_data['denominacion'];
    $id_programa = $programa_data['id_programa'];

    // Asegurarse de que el sector y programa existen en el array
    if (!isset($programas[$sector][$programa])) {
        $programas[$sector][$programa] = [
            'denominacion' => $denominacion,
            'ids' => [],
        ];
    }

    // Agregar el ID del programa al sector y programa correspondiente
    $programas[$sector][$programa]['ids'][] = $id_programa;
}

// Verificamos si hay programas disponibles
if (empty($programas)) {
    die('No se encontraron programas.');
}

// Iterar sobre los sectores y programas
foreach ($programas as $sector => $programas_info) {
    foreach ($programas_info as $programa => $info) {
        $id_list = implode(',', $info['ids']); // Convertimos el array de IDs a una lista separada por comas

        // Hacer la consulta a distribucion_presupuestaria
        if (!empty($id_list)) {
            $query_distribucion = "SELECT 
                                        SUM(0) AS total_ingresos_propios, 
                                        SUM(monto_inicial) AS total_situado_estadal, 
                                        SUM(0) AS total_fci, 
                                        SUM(0) AS total_otras_fuentes 
                                    FROM distribucion_presupuestaria 
                                    WHERE id_sector IN ($id_list) AND id_ejercicio = ?";

            $stmt_distribucion = $conexion->prepare($query_distribucion);
            if ($stmt_distribucion === false) {
                die('Error en la consulta SQL (distribucion_presupuestaria): ' . $conexion->error);
            }

            $stmt_distribucion->bind_param('i', $id_ejercicio);
            $stmt_distribucion->execute();
            $result_distribucion = $stmt_distribucion->get_result();
            $distribucion_data = $result_distribucion->fetch_assoc();

            // Extraer los montos o asignar 0 si no hay datos
            $ingresos_propios = $distribucion_data['total_ingresos_propios'] ?? 0;
            $situado_estadal = $distribucion_data['total_situado_estadal'] ?? 0;
            $fci = $distribucion_data['total_fci'] ?? 0;
            $otras_fuentes = $distribucion_data['total_otras_fuentes'] ?? 0;
        } else {
            // Si no hay IDs, asignar valores en 0
            $ingresos_propios = $situado_estadal = $fci = $otras_fuentes = 0;
        }

        // Calcular el total
        $total = $ingresos_propios + $situado_estadal + $fci + $otras_fuentes;

        // Organizar datos para la tabla final
        $data[] = [$sector, $programa, $info['denominacion'], $ingresos_propios, $situado_estadal, $fci, $otras_fuentes, $total];
    }
}

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

        .p-15 {
            padding: 15px;
        }

        .p-5 {
            padding: 5px;
        }
    </style>
    </style>
</head>

<body>
    <?php
    // Imprimir el encabezado
    echo "
    <div style='font-size: 9px;'>
        <table class='header-table bt br bb bl bc-lightgray' >
            <tr>
                <td class='text-left' style='width: 20px'>
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
                <td class='text-left' style='vertical-align: top;padding-top: 13px;'>
                    <b>
                    REPÚBLICA BOLIVARIANA DE VENEZUELA <br>
                    GOBERNACIÓN DEL ESTADO AMAZONAS  <br>
                    CODIGO PRESUPUESTARIO:  E5100
                    </b>
                    </div>
                    <td class='text-right' style='vertical-align: top;padding: 13px 10px 0 0; '>
                    <b>
                    Página: 1 de 1 <br>
                    Fecha: " . date('d/m/Y') . " 
                    </b>
                </td>
            </tr>
               <tr >
                <td colspan='3'>
               <h2 align='center'>RESUMEN DE LOS CREDITOS PRESUPUESTARIOS A NIVEL DE SECTORES Y PROGRAMAS Y FUENTES DE FINANCIAMIENTO</h2>
                </td>
            </tr>

              <tr>
                <td class='text-left'>
                <b>PRESUPUESTO " . $ano . "</b>
                </td>
            </tr>
        </table>

        
    "; ?>


    <table class="bb">
        <thead>
            <tr>
                <th class="bt bl bb p-15" rowspan="3" style="width: 10%">Sector</th>
                <th class="bt bl bb p-15" rowspan="3" style="width: 10%">Programa</th>
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

            $t_ingresosPropios = 0;
            $t_situado = 0;
            $t_fci = 0;
            $t_otras_fuentes = 0;
            $t_total = 0;

            foreach ($data as $row) {
                $secto = $row[0];
                $programa = $row[1];
                $denominacion = $row[2];
                $t_ingresosPropios += $ingresosPropios = $row[3];
                $t_situado += $situado = $row[4];
                $t_fci += $fci = $row[5];
                $t_otras_fuentes += $otras_fuentes = $row[6];
                $t_total += $total = $row[7];

                echo "<tr>
                    <td class='fz-8 bl'>{$secto}</td>
                    <td class='fz-8 bl'>{$programa}</td>
                    <td class='fz-8 bl text-left'>{$denominacion}</td>
                    <td class='fz-8 bl'>" . number_format($ingresosPropios, 2, ',', '.') . "</td>
                    <td class='fz-8 bl'>" . number_format($situado, 2, ',', '.') . "</td>
                    <td class='fz-8 bl'>" . number_format($fci, 2, ',', '.') . "</td>
                    <td class='fz-8 bl'>" . number_format($otras_fuentes, 2, ',', '.') . "</td>
                    <td class='fz-8 bl br'>" . number_format($total, 2, ',', '.') . "</td>
                </tr>";
            }

            ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="bt bl bb" colspan="3"><b>TOTALES</b></td>
                <td class="bt bl bb" style="font-weight: bold;"><?php echo number_format($t_ingresosPropios, 2, ',', '.') ?></td>
                <td class="bt bl bb" style="font-weight: bold;"><?php echo number_format($t_situado, 2, ',', '.') ?></td>
                <td class="bt bl bb" style="font-weight: bold;"><?php echo number_format($t_fci, 2, ',', '.') ?></td>
                <td class="bt bl bb" style="font-weight: bold;"><?php echo number_format($t_otras_fuentes, 2, ',', '.') ?></td>
                <td class="bt bl br bb" style="font-weight: bold;"><?php echo number_format($t_total, 2, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>


</body>

</html>