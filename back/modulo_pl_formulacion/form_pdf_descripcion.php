<?php
require_once '../sistema_global/conexion.php';

// Obtiene los parámetros id_sector e id_programa desde GET
$id_sector = $_GET['id_sector'];
$id_programa = $_GET['id_programa'];

// Array para almacenar la información final
$data = [];

if (isset($id_sector) && isset($id_programa)) {
    // Consulta a pl_sectores para obtener denominacion de id_sector
    $querySector = "SELECT denominacion FROM pl_sectores WHERE id = ?";
    $stmtSector = $conexion->prepare($querySector);
    $stmtSector->bind_param("i", $id_sector);
    $stmtSector->execute();
    $resultSector = $stmtSector->get_result();
    if ($rowSector = $resultSector->fetch_assoc()) {
        $data['denominacion_sector'] = $rowSector['denominacion'];
    }
    $stmtSector->close();

    // Consulta a pl_programas para obtener sector, programa y denominacion de id_programa
    $queryPrograma = "SELECT sector, programa, denominacion FROM pl_programas WHERE id = ?";
    $stmtPrograma = $conexion->prepare($queryPrograma);
    $stmtPrograma->bind_param("i", $id_programa);
    $stmtPrograma->execute();
    $resultPrograma = $stmtPrograma->get_result();
    if ($rowPrograma = $resultPrograma->fetch_assoc()) {
        $data['sector'] = $rowPrograma['sector'];
        $data['programa'] = $rowPrograma['programa'];
        $data['denominacion_programa'] = $rowPrograma['denominacion'];
    }
    $stmtPrograma->close();

    // Consulta a entes para obtener ente_nombre coincidente con sector y programa del programa
    $queryEnte = "SELECT ente_nombre FROM entes WHERE sector = ? AND programa = ?";
    $stmtEnte = $conexion->prepare($queryEnte);
    $stmtEnte->bind_param("ii", $data['sector'], $data['programa']);
    $stmtEnte->execute();
    $resultEnte = $stmtEnte->get_result();
    if ($rowEnte = $resultEnte->fetch_assoc()) {
        $data['ente_nombre'] = $rowEnte['ente_nombre'];
    }
    $stmtEnte->close();

    // Consulta a descripcion_programas para obtener descripcion coincidente con id_sector e id_programa
    $queryDescripcion = "SELECT descripcion FROM descripcion_programas WHERE id_sector = ? AND id_programa = ?";
    $stmtDescripcion = $conexion->prepare($queryDescripcion);
    $stmtDescripcion->bind_param("ii", $id_sector, $id_programa);
    $stmtDescripcion->execute();
    $resultDescripcion = $stmtDescripcion->get_result();
    if ($rowDescripcion = $resultDescripcion->fetch_assoc()) {
        $data['descripcion'] = $rowDescripcion['descripcion'];
    }
    $stmtDescripcion->close();
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
                    <h2 align='center'>DESCRIPCION DEL PROGRAMA,  SUB - PROGRAMA Y PROYECTO</h2>
                </td>
            </tr>

        </table>
    "; 
    ?>

    <!-- Tabla principal -->
    <table>
            <tr>
                <th class="bl bt bb"></th>
                <th class="bl bt bb br">Codigo</th>
                <th class="bl bt bb br">Denominacion</th>
            </tr>
             <tr>
                <th class="bl bt bb">Sector</th>
                <th class="bl bt bb br"><?php echo $data['sector']; ?></th>
                <th class="bl bt bb br"><?php echo $data['denominacion_sector']; ?></th>
            </tr>
            <tr>
                <th class="bl bt bb">Programa</th>
                <th class="bl bt bb br"><?php echo $data['programa']; ?></th>
                <th class="bl bt bb br"><?php echo $data['denominacion_programa']; ?></th>
            </tr>
            <tr>
                <th class="bl bt bb">Sub-Programa</th>
                <th class="bl bt bb br"></th>
                <th class="bl bt bb br"></th>
            </tr>
            <tr>
                <th class="bl bt bb">Proyecto</th>
                <th class="bl bt bb br"></th>
                <th class="bl bt bb br" rowspan="2"><?php echo $data['ente_nombre']; ?></th>
            </tr>
            <tr>
                <th class="bl bt bb">Unidad Ejecutora</th>
                <th class="bl bt bb br"></th>
            </tr>
            <tr>
                <th class="bl bt bb" colspan="3">Descripcion</th>
            </tr>
                    <tr>
                        <td class='bl bt bb br' colspan="3">
                            <?php echo $data['descripcion']; ?>
                        </td>
                    </tr>
    </table>
</body>
</html>