<?php
require_once '../sistema_global/conexion.php';


$id_ejercicio = $_GET["id_ejercicio"];

// Consultar datos del sector
$query_sector = "SELECT ano, situado FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_sector);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$data_sector = $result->fetch_assoc();
$stmt->close();

$ano = $data_sector['ano'];



// Consulta para obtener datos de entes y entes_dependencias con denominaciones de sector y programa
$queryEntes = "
    SELECT 
        e.sector, 
        e.programa, 
        e.proyecto, 
        e.actividad,
        e.ente_nombre AS denominacion, 
        ps.sector as sector_sectores,
        pp.programa as programa_programas, 
        ed.ente_nombre AS denominacion_dependencia,
        IFNULL(ed.actividad, e.actividad) AS actividad_final,
        ps.denominacion AS sector_denominacion,
        pp.denominacion AS programa_denominacion
    FROM entes e
    LEFT JOIN entes_dependencias ed ON ed.sector = e.sector AND ed.programa = e.programa
    LEFT JOIN pl_sectores ps ON ps.id = e.sector
    LEFT JOIN pl_programas pp ON pp.id = e.programa AND pp.sector = ps.id
    ORDER BY ps.sector, pp.programa, e.actividad, ed.actividad
";

$resultEntes = $conexion->query($queryEntes);

if (!$resultEntes) {
    die("Error en la consulta de entes: " . $conexion->error);
}

// Almacenar los resultados en un solo arreglo, agrupando por sector y programa
$allData = [];

// Procesar los resultados de la consulta
while ($row = $resultEntes->fetch_assoc()) {
    $allData[] = [
        'sector' => $row['sector_sectores'],
        'programa' => $row['programa_programas'],
        'proyecto' => $row['proyecto'],
        'actividad' => $row['actividad_final'],
        'denominacion' => $row['denominacion'],
        'unidad_ejecutora' => $row['denominacion_dependencia'] ?? $row['denominacion'],
        'sector_denominacion' => $row['sector_denominacion'],
        'programa_denominacion' => $row['programa_denominacion']
    ];
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
           
                <td class='text-left' style='vertical-align: top; padding-top: 13px;'>
                    <b>
                    REPÚBLICA BOLIVARIANA DE VENEZUELA <br>
                    GOBERNACIÓN DEL ESTADO AMAZONAS  <br>
                    CÓDIGO PRESUPUESTARIO: E5100
                    <br>
                    <br>
                    PRESUPUESTO: $ano
                    </b>
                </td>
                <td class='text-right' style='vertical-align: top; padding: 13px 10px 0 0;'>
                    <b>
                    Fecha: " . date('d/m/Y') . " 
                    </b>
                    <br>
                    <img src='../../img/logo.jpg' class='logo'>

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
                <th class="bl bt bb">Sector</th>
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
                    <td class='bl bt bb'>{$row['sector']}</td>
                    <td class='bl bt bb br'>{$row['programa']}</td>
                    <td class='bl bt bb br'>" . ($row['proyecto'] == '0' ? '00' : $row['proyecto']) . "</td>
                    <td class='bl bt bb br'>{$row['actividad']}</td>
                    <td class='bl bt bb br'>{$row['denominacion']}</td>
                    <td class='bl bt bb br'>{$row['unidad_ejecutora']}</td>
                </tr>
            ";
            }
            $resultEntes->free();

            $conexion->close();
            ?>
        </tbody>
    </table>
</body>

</html>