<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';
global $conexion;
global $remote_db;

$id_ejercicio = $_GET['id_ejercicio'];
$trimestre = $_GET['trimestre'];
$trimestres_text = [
    1 => 'PRIMER TRIMESTRE',
    2 => 'SEGUNDO TRIMESTRE',
    3 => 'TERCER TRIMESTRE',
    4 => 'CUARTO TRIMESTRE',
];

// Lista de identificadores fijos
$identificadores_fijos = [
    "01-01", "01-02", "01-03", "01-04", "01-05", "01-06", "01-07", "01-08", "01-09", "01-10", 
    "01-11", "01-12", "01-13", "02-01", "02-02", "02-03", "02-04", "02-05", "06-01", 
    "08-02", "08-03", "09-01", "09-02", "11-01", "11-02", "12-01", "13-01", "13-02", 
    "14-01", "15-01"
];
foreach ($identificadores_fijos as $identificador) {
    // Extraer el sector y el programa de la clave del identificador
    $id_sector = substr($identificador, 0, 2);  // El primer dígito es el sector
    $id_programa = substr($identificador, 3, 2);  // El segundo dígito es el programa
    
    // Consultar sector en pl_sectores
    $query_sector = "SELECT * FROM pl_sectores WHERE sector = ?";
    $stmt_sector = $conexion->prepare($query_sector);
    $stmt_sector->bind_param('i', $id_sector);
    $stmt_sector->execute();
    $result_sector = $stmt_sector->get_result();
    $sector_data = $result_sector->fetch_assoc();

    if (!$sector_data) {
        echo "No se encontró registro en pl_sectores para id_sector: $id_sector<br>";
        continue;
    }

    $id = $sector_data['id'] ?? 'N/A';

    // Consultar programa en pl_programas
    $query_programa = "SELECT programa, denominacion FROM pl_programas WHERE programa = ? AND sector = ?";
    $stmt_programa = $conexion->prepare($query_programa);
    $stmt_programa->bind_param('ii', $id_programa, $id);
    $stmt_programa->execute();
    $result_programa = $stmt_programa->get_result();
    $programa_data = $result_programa->fetch_assoc();

    if (!$programa_data) {
        echo "No se encontró registro en pl_programas para id_programa: $id_programa<br>";
        continue;
    }

    $programa = $programa_data['programa'] ?? 'N/A';
    $denominacion = $programa_data['denominacion'] ?? 'N/A';
    
    // Agrupar datos por identificador
    if (!isset($data[$identificador])) {
        $data[$identificador] = [
            $identificador,  // Sector y programa combinados
            $denominacion,   // Denominación del programa
            0,               // Sumatoria de monto_inicial
            0,               // Sumatoria comprometido
            0,               // Sumatoria causado
            0,               // Sumatoria disponible (monto_actual de distribucion_presupuestaria)
            0                // Sumatoria de monto_actual (de las distribuciones)
        ];
    }
}






// Imprimir resultados
print_r(array_values($data));
?>











<!DOCTYPE html>
<html>

<head>
    <title>RESUMEN GENERAL A NIVEL DE SECTORES Y PROGRAMAS</title>
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
        <h1 align='center'>RESUMEN GENERAL A NIVEL DE SECTORES Y PROGRAMAS (<?php echo $trimestres_text[$trimestre] . ' ' . $ano ?>)</h1>
        <table>
            <thead>
                <tr>
                    <th class="bt bb p-15" style="width: 10%; border-width: 3px;">COD.SECTOR-PROGRAMA</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">DENOMINACIÓN</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">ASIGNACIÓN INICIAL</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">MODIFICACIÓN(+/-)</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">ASIGNACIÓN AJUSTADA</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">COMPROMISO</th>
                    <th class="bt bb p-15" style=" border-width: 3px;">SALDO</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_asignacion_inicial = 0;
                $total_modificacion = 0;
                $total_asignacion_ajustada = 0;
                $total_compromiso = 0;
                $total_disponibilidad = 0;

                foreach ($data as $info_partida) {
                    // Asignar los valores usando índices numéricos
                    $codigo_partida = $info_partida[0] ?? 'N/A';
                    $denominacion = $info_partida[1] ?? 'N/A';
                       $modificacion = $info_partida[3] ?? 0; // Si corresponde al índice [3]
                       $compromiso = $info_partida[5] ?? 0;   // Si corresponde al índice [4]
                       $asignacion_inicial = $info_partida[2] ?? 0;
                       if ($modificacion > $compromiso) {
                        $asignacion_ajustada = $asignacion_inicial + $modificacion;
                       }else{
                        $asignacion_ajustada = $asignacion_inicial - $modificacion;
                    }
                    $disponibilidad = ($asignacion_inicial + $modificacion) - $compromiso;

                    // Acumular totales
                    $total_asignacion_inicial += $asignacion_inicial;
                    $total_modificacion += $modificacion;
                    $total_asignacion_ajustada += $asignacion_ajustada;
                    $total_compromiso += $compromiso;
                    $total_disponibilidad += $disponibilidad;

                    echo "<tr>
                <td class='fz-8'>{$codigo_partida}</td>
                <td class='fz-8 text-left'>{$denominacion}</td>
                <td class='fz-8'>" . number_format($asignacion_inicial, 2, ',', '.') . "</td>";

                           if ($modificacion > $compromiso) {
    echo "<td class='fz-8' style=''>" . number_format($modificacion, 2, ',', '.') . "</td>";
} else {
    if ($modificacion == 0) {
        echo "<td class='fz-8' style=''>" . number_format($modificacion, 2, ',', '.') . "</td>";
    }else{
        echo "<td class='fz-8' style=''>-" . number_format($modificacion, 2, ',', '.') . "</td>";
    }
    
}
                echo "
                <td class='fz-8'>" . number_format($asignacion_ajustada, 2, ',', '.') . "</td>
                <td class='fz-8'>" . number_format($compromiso, 2, ',', '.') . "</td>
                <td class='fz-8'>" . number_format($disponibilidad, 2, ',', '.') . "</td>
            </tr>";
                }

                // Totales generales
                echo "<tr>
            <td class='bt'  style='border-width: 3px;'></td>
            <td class='bt fw-bold' style='border-width: 3px;'>TOTAL RESUMEN POR SECTORES Y PROGRAMAS</td>
            <td class='bt fw-bold' style='border-width: 3px;'>" . number_format($total_asignacion_inicial, 2, ',', '.') . "</td>";
                    if ($total_modificacion > $total_compromiso) {
    echo "<td class='bt fw-bold' style='border-width: 3px;'>" . number_format($total_modificacion, 2, ',', '.') . "</td>";
} else {
        echo "<td class='bt fw-bold' style='border-width: 3px;'>" . number_format($total_modificacion, 2, ',', '.') . "</td>";  
}
        echo"
            <td class='bt fw-bold' style='border-width: 3px;'>" . number_format($total_asignacion_ajustada, 2, ',', '.') . "</td>
            <td class='bt fw-bold' style='border-width: 3px;'>" . number_format($total_compromiso, 2, ',', '.') . "</td>
            <td class='bt fw-bold' style='border-width: 3px;'>" . number_format($total_disponibilidad, 2, ',', '.') . "</td>
        </tr>";
                ?>
            </tbody>
        </table>







</body>

</html>