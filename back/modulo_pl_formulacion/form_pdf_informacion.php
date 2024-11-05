<?php
require_once '../sistema_global/conexion.php';

try {
    $conexion->begin_transaction();

    // Consulta a la tabla informacion_gobernacion
    $sqlGobernacion = "SELECT * FROM informacion_gobernacion";
    $resultadoGobernacion = $conexion->query($sqlGobernacion);
    $gobernacionData = [];
    if ($resultadoGobernacion && $resultadoGobernacion->num_rows > 0) {
        while ($row = $resultadoGobernacion->fetch_assoc()) {
            $gobernacionData[] = $row;
        }
    }

    // Consulta a la tabla informacion_contraloria
    $sqlContraloria = "SELECT * FROM informacion_contraloria";
    $resultadoContraloria = $conexion->query($sqlContraloria);
    $contraloriaData = [];
    if ($resultadoContraloria && $resultadoContraloria->num_rows > 0) {
        while ($row = $resultadoContraloria->fetch_assoc()) {
            $contraloriaData[] = $row;
        }
    }

    // Consulta a la tabla informacion_consejo
    $sqlConsejo = "SELECT * FROM informacion_consejo";
    $resultadoConsejo = $conexion->query($sqlConsejo);
    $consejoData = [];
    if ($resultadoConsejo && $resultadoConsejo->num_rows > 0) {
        while ($row = $resultadoConsejo->fetch_assoc()) {
            $consejoData[] = $row;
        }
    }

    // Consulta a la tabla personal_directivo
    $sqlDirectivo = "SELECT * FROM personal_directivo";
    $resultadoDirectivo = $conexion->query($sqlDirectivo);
    $directivoData = [];
    if ($resultadoDirectivo && $resultadoDirectivo->num_rows > 0) {
        while ($row = $resultadoDirectivo->fetch_assoc()) {
            $directivoData[] = $row;
        }
    }

    $conexion->commit();
} catch (Exception $e) {
    $conexion->rollback();
    die("Error en la consulta: " . $e->getMessage());
}

// Array final con todos los datos
$data = [
    "gobernacion" => $gobernacionData,
    "contraloria" => $contraloriaData,
    "consejo" => $consejoData,
    "directivo" => $directivoData
];

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
            width: 80px;
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

        .p-32 {
            padding: 3px 0 20px 3px;
        }

        .p-1 {
            padding: 2px;
        }
    </style>
</head>

<body>




    <div style='font-size: 9px;'>
        <table class='header-table bt br bb bl bc-lightgray'>
            <tr>
                <td class='text-left' style='vertical-align: top; padding-top: 13px;'>
                    <b>
                        GOBERNACION DE AMAZONAS <br>
                        CÓDIGO PRESUPUESTARIO: E5100 <br>
                        PERÍODO PRESUPUESTARIO: AÑO 2020
                    </b>
                </td>
                <td>

                </td>
                <td class='text-right'>
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
            </tr>
            <tr>
                <td colspan='3'>
                    <h2 align='center'>INFORMACIÓN GENERAL DE LA ENTIDAD FEDERAL</h2>
                </td>
            </tr>
        </table>

        <!-- Tabla principal -->
        <table>
            <?php foreach ($data['gobernacion'] as $gobernacion) : ?>
                <tr>
                    <th class="bl bt bb br text-left p-32" colspan="4">
                        <b>BASE LEGAL:</b>
                    </th>
                </tr>

                <tr>
                    <th class="bl bb br text-left text-left p-32" colspan="4">IDENTIFICACIÓN DE LOS ÓRGANOS DEL PODER PÚBLICO ESTADAL:</th>
                </tr>
                <tr>
                    <td class="bl bb br text-left text-left p-32" colspan="4"><?= $gobernacion['identificacion'] ?></td>
                </tr>
                <tr>
                    <td class="bl bb br text-left text-left p-32" colspan="4">DOMICILIO LEGAL: <?= $gobernacion['domicilio'] ?> </td>
                </tr>
                <tr>
                    <th class="p-1 bl bb">Telefono (s)</th>
                    <th class="p-1 bl bb br">Pagina Web</th>
                    <th class="p-1 bl bb br">Fax(s)</th>
                    <th class="p-1 bl bb br">Codigo Postal</th>
                </tr>
                <tr>
                    <td class="bl bb"><?= $gobernacion['telefono'] ?></td>
                    <td class="bl bb br"><?= $gobernacion['pagina_web'] ?></td>
                    <td class="bl bb br"><?= $gobernacion['fax'] ?></td>
                    <td class="bl bb br"><?= $gobernacion['codigo_postal'] ?></td>
                </tr>
                <tr>
                    <td class="bl bb br p-32 text-left" colspan="4">
                        <b>NOMBRES Y APELLIDOS DEL GOBERNADOR (RA)</b> <br>
                        <?= $gobernacion['nombre_apellido_gobernador'] ?>
                    </td>
                </tr>

            <?php endforeach; ?>
            <tr>
                <th class="bl bb br p-2" colspan="4">PERSONAL DIRECTIVO DE LA GOBERNACIÓN Y ÓRGANOS AUXILIARES:</th>
            </tr>

            <tr>
                <th class="bl bb br">DIRECCIÓN ADMINISTRATIVA</th>
                <th class="bl bb br">NOMBRES Y APELLIDOS</th>
                <th class="bl bb br">CORREO ELECTRÓNICO</th>
                <th class="bl bb br">TELÉFONO (S)</th>
            </tr>
            <?php foreach ($data['directivo'] as $directivo) : ?>
                <tr>
                    <td class="bl br text-left"><?= $directivo['direccion'] ?></td>
                    <td class="bl br text-left"><?= $directivo['nombre_apellido'] ?></td>
                    <td class="bl br text-left"><?= $directivo['email'] ?></td>
                    <td class="bl br text-left"><?= $directivo['telefono'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($data['contraloria'] as $contraloria) : ?>
                <tr>
                    <th class="bl bb bt p1 text-left" colspan="4">CONTRALORÍA ESTADAL</th>
                </tr>
                <tr>
                    <th class="bl bb p1 text-left" colspan="4">NOMBRES Y APELLIDOS DEL CONTRALOR (A)</th>
                </tr>
                <tr>
                    <td class="bl bb p1 text-left" colspan="4"><?= $contraloria['nombre_apellido_contralor'] ?></td>
                </tr>
                <tr>
                    <th class="bl bb p1 text-left" colspan="4">DOMICILIO LEGAL:</th>
                </tr>
                <tr>
                    <td class="bl bb p1 text-left" colspan="4"><?= $contraloria['domicilio'] ?></td>
                </tr>
                <tr>
                    <th class="bl bb">TELÉFONO (S)</th>
                    <th class="bl bb br">PÁGINA WEB</th>
                    <th class="bl bb br" colspan="2">CORREO ELECTRÓNICO</th>
                </tr>
                <tr>
                    <td class="bl bb"><?= $contraloria['telefono'] ?></td>
                    <td class="bl bb br"><?= $contraloria['pagina_web'] ?></td>
                    <td class="bl bb br" colspan="2"><?= $contraloria['email'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($data['consejo'] as $consejo) : ?>
                <tr>
                    <th class="bl bb text-left" colspan="4">CONCEJO LEGISLATIVO:</th>
                </tr>
                <tr>
                    <th class="bl bb text-left" colspan="4">NOMBRES Y APELLIDOS DEL PRESIDENTE (A): <?= $consejo['nombre_apellido_presidente'] ?> </th>
                </tr>
                <tr>
                    <th class="bl bb text-left" colspan="4">NOMBRES Y APELLIDOS DEL SECRETARIO (A): <?= $consejo['nombre_apellido_secretario'] ?></th>
                </tr>
                <tr>
                    <td class="bl bb text-left" colspan="4">DOMICILIO LEGAL: <?= $consejo['domicilio'] ?></td>
                </tr>
                <tr>
                    <th class="bl bb">TELÉFONO (S)</th>
                    <th class="bl bb br">PÁGINA WEB</th>
                    <th class="bl bb br" colspan="2">CORREO ELECTRÓNICO</th>
                </tr>
                <tr>
                    <td class="bl bb"><?= $consejo['telefono'] ?></td>
                    <td class="bl bb br"><?= $consejo['pagina_web'] ?></td>
                    <td class="bl bb br" colspan="2"><?= $consejo['email'] ?></td>
                </tr>

                <tr>
                    <th class="bl bb">CONSEJO LOCAL DE PLANIFICACIÓN PÚBLICA :</th>
                    <th class="bl bb br"></th>
                    <th class="bl bb br" colspan="2"></th>
                </tr>
                <tr>
                    <td class="bl bb">NOMBRES Y APELLIDOS DE LOS CONSEJEROS (AS) :</td>
                    <td class="bl bb br" colspan="4"><?= $consejo['consejo_local'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
</body>

</html>