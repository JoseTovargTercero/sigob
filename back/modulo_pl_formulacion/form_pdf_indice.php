<?php
require_once '../sistema_global/conexion.php';

// Consulta para obtener datos de la tabla entes
$queryEntes = "
    SELECT 
        sector, 
        programa, 
        proyecto, 
        actividad, 
        ente_nombre AS denominacion, 
        'entes' AS tipo
    FROM entes
";
$resultEntes = $conexion->query($queryEntes);

if (!$resultEntes) {
    die("Error en la consulta de entes: " . $conexion->error);
}

// Consulta para obtener datos de la tabla entes_dependencias
$queryEntesDependencias = "
    SELECT 
        sector, 
        programa, 
        proyecto, 
        actividad, 
        ente_nombre AS denominacion, 
        'entes_dependencias' AS tipo
    FROM entes_dependencias
";
$resultEntesDependencias = $conexion->query($queryEntesDependencias);

if (!$resultEntesDependencias) {
    die("Error en la consulta de entes_dependencias: " . $conexion->error);
}

// Almacenar los resultados en un solo arreglo
$allData = [];
while ($row = $resultEntes->fetch_assoc()) {
    $allData[] = $row;
}
while ($row = $resultEntesDependencias->fetch_assoc()) {
    $allData[] = $row;
}

// Ordenar el arreglo por sector y programa
usort($allData, function($a, $b) {
    return $a['sector'] <=> $b['sector'] ?: $a['programa'] <=> $b['programa'];
});

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
            border-left: 1px solid;
        }

        .br {
            border-right: 1px solid;
        }

        .bb {
            border-bottom: 1px solid;
        }

        .bt {
            border-top: 1px solid;
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
    </style>
</head>

<body>
    <!-- Encabezado -->
    <?php
    echo "
    <div style='font-size: 9px;'>
        <table class='header-table bt br bb bl bc-lightgray'>
            <tr>
                <td class='text-left' style='width: 20px'>
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
                <td class='text-left' style='vertical-align: top; padding-top: 13px;'>
                    <b>
                    REPÚBLICA BOLIVARIANA DE VENEZUELA <br>
                    GOBERNACIÓN DEL ESTADO AMAZONAS 
                    </b>
                </td>
                <td class='text-right' style='vertical-align: top; padding: 13px 10px 0 0;'>
                    <b>
                    Página: 1 de 1 <br>
                    Fecha: " . date('d/m/Y') . " 
                    </b>
                </td>
            </tr>
            <tr>
                <td colspan='3'>
                    <h2 align='center'>ÍNDICE DE CATEGORÍAS PROGRAMÁTICAS</h2>
                </td>
            </tr>
  
        </table>
    "; 
    ?>

    <!-- Tabla principal -->
    <table>
        <thead>
            <tr>
                <th class="bl bt bb" rowspan="2">Sector</th>
                <th class="bl bt bb br">Programa</th>
                <th class="bl bt bb br">Proyecto</th>
                <th class="bl bt bb br">Actividad</th>
                <th class="bl bt bb br">Denominación</th>
                <th class="bl bt bb br">Unidad Ejecutora</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $currentSector = null;
            $currentPrograma = null;

            // Iterar y agrupar datos en la tabla
            foreach ($allData as $row) {
                // Revisar si el sector o programa cambió
                if ($currentSector !== $row['sector'] || $currentPrograma !== $row['programa']) {
                    $currentSector = $row['sector'];
                    $currentPrograma = $row['programa'];
                }

                echo "
                    <tr>
                        <td class='p-2 bl'>{$row['sector']}</td>
                        <td class='p-2 bl'>{$row['programa']}</td>
                        <td class='p-2 bl'>{$row['proyecto']}</td>
                        <td class='p-2 bl'>{$row['actividad']}</td>
                        <td class='p-2 bl'>{$row['denominacion']}</td>
                        <td class='p-2 bl br'>{$row['denominacion']}</td>
                    </tr>
                ";
            }
            $resultEntes->free();
            $resultEntesDependencias->free();
            $conexion->close();
            ?>
        </tbody>
    </table>
</body>
</html>