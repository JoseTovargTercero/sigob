<?php
require_once '../sistema_global/conexion.php';

// Obtiene los parámetros id_programa e id_ejercicio desde GET
$id_programa = $_GET['id_programa'];
$id_ejercicio = $_GET['id_ejercicio'];

// Arrays para almacenar la información final
$array1 = [];
$array2 = [];

if (isset($id_programa) && isset($id_ejercicio)) {
    // Consulta a pl_programas para obtener sector, programa y denominacion de id_programa
    $queryPrograma = "SELECT sector, programa, denominacion FROM pl_programas WHERE id = ?";
    $stmtPrograma = $conexion->prepare($queryPrograma);
    $stmtPrograma->bind_param("i", $id_programa);
    $stmtPrograma->execute();
    $resultPrograma = $stmtPrograma->get_result();

    if ($rowPrograma = $resultPrograma->fetch_assoc()) {
        $sector = $rowPrograma['sector'];
        $programa = $rowPrograma['programa'];
        $denominacion_programa = $rowPrograma['denominacion'];

        // Consulta a pl_sectores para obtener la denominacion de sector
        $querySector = "SELECT sector, denominacion FROM pl_sectores WHERE id = ?";
        $stmtSector = $conexion->prepare($querySector);
        $stmtSector->bind_param("i", $sector);
        $stmtSector->execute();
        $resultSector = $stmtSector->get_result();

        if ($rowSector = $resultSector->fetch_assoc()) {
            $sector2 = $rowSector['sector'];
            $denominacion_sector = $rowSector['denominacion'];

            // Almacenar los datos en array1
            $array1 = [
                'programa' => $programa,
                'denominacion_programa' => $denominacion_programa,
                'sector' => $sector2,
                'denominacion_sector' => $denominacion_sector,
            ];
        }
        $stmtSector->close();
    }
    $stmtPrograma->close();

    // Consulta a pl_metas para obtener todos los registros con meta, unidad_medida, cantidad, costo
    $queryMeta = "SELECT meta, unidad_medida, cantidad, costo FROM pl_metas WHERE programa = ? AND id_ejercicio = ?";
    $stmtMeta = $conexion->prepare($queryMeta);
    $stmtMeta->bind_param("ii", $id_programa, $id_ejercicio);
    $stmtMeta->execute();
    $resultMeta = $stmtMeta->get_result();

    // Almacena todos los registros en array2
    while ($rowMeta = $resultMeta->fetch_assoc()) {
        $array2[] = [
            'meta' => $rowMeta['meta'],
            'unidad_medida' => $rowMeta['unidad_medida'],
            'cantidad' => $rowMeta['cantidad'],
            'costo' => $rowMeta['costo'],
        ];
    }
    $stmtMeta->close();
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
            width: 90px;
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
                   GOBERNACIÓN DEL ESTADO AMAZONAS 
                   <br>
                          CÓDIGO PRESUPUESTARIO: E5100
                   </b>
                </td>
                <td class='text-right'>
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
            </tr>
            <tr >
            <td colspan='3' class='text-center'>
            <h3>
        
            DESCRIPCIÓN DEL PROGRAMA,  SUB - PROGRAMA Y PROYECTO
        
            </h3>
            </td>
            </tr>

        </table>
    ";
    ?>


<!-- Tabla principal -->
<table>
    <tr>
        <th class="bl bt bb"></th>
        <th class="bl bt bb">CÓDIGO</th>
        <th class="bl bt bb br">DENOMINACIÓN</th>
    </tr>
    <tr>
        <td class="bl bb">SECTOR</td>
        <td class="bl bb"><?php echo $array1['sector']; ?></td>
        <td class="bl bb br"><?php echo $array1['denominacion_sector']; ?></td>
    </tr>
    <tr>
        <td class="bl bb">PROGRAMA</td>
        <td class="bl bb"><?php echo $array1['programa']; ?></td>
        <td class="bl bb br"><?php echo $array1['denominacion_programa']; ?></td>
    </tr>
    <tr>
        <td class="bl bb">SUB-PROGRAMA</td>
        <td class="bl bb"></td>
        <td class="bl bb br"></td>
    </tr>
    <tr>
        <td class="bl bb">PROYECTO</td>
        <td class="bl bb"></td>
        <td class="bl bb br"></td>
    </tr>
</table>

<!-- Tabla secundaria para los detalles de array2 -->
<table>
    <tr>
        <td class="bl bb" colspan="3">DESCRIPCIÓN</td>
        <td class="bl bb" colspan="3">UNIDAD DE MEDIDA</td>
        <td class="bl bb" colspan="3">CANTIDADES PROGRAMADAS</td>
        <td class="bl bb" colspan="3">COSTO FINANCIERO</td>
    </tr>

    <?php foreach ($array2 as $item) : ?>
        <tr>
            <td class="bl bb br" colspan="3"><?php echo $item['meta']; ?></td>
            <td class="bl bb br" colspan="3"><?php echo $item['unidad_medida']; ?></td>
            <td class="bl bb br" colspan="3"><?php echo $item['cantidad']; ?></td>
            <td class="bl bb br" colspan="3"><?= number_format($item['costo'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>


</body>

</html>