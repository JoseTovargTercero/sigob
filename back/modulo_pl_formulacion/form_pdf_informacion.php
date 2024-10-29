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
                    <h2 align='center'>INFORMACIÓN GENERAL DE LA ENTIDAD FEDERAL</h2>
                </td>
            </tr>
        </table>
    "; 
    ?>

    <!-- Tabla principal -->
    <table>
        <?php foreach ($data['gobernacion'] as $gobernacion) : ?>
            <tr>
                <th class="bl bt bb" colspan="4">Base Legal</th>
            </tr>
             <tr>
                <th class="bl bt bb" colspan="4">IDENTIFICACIÓN DE LOS ÓRGANOS DEL PODER PÚBLICO ESTADAL:</th>
            </tr>
            <tr>
                <th class="bl bt bb" colspan="4"><?= $gobernacion['identificacion'] ?></th>
            </tr>
            <tr>
                <th class="bl bt bb" colspan="4">Domicilio Legal: <?= $gobernacion['domicilio'] ?> </th>
            </tr>
            <tr>
                <th class="bl bt bb">Telefono(s)</th>
                <th class="bl bt bb br">Pagina Web</th>
                <th class="bl bt bb br">Fax(s)</th>
                <th class="bl bt bb br">Codigo Postal</th>
            </tr>
            <tr>
                <th class="bl bt bb"><?= $gobernacion['telefono'] ?></th>
                <th class="bl bt bb br"><?= $gobernacion['pagina_web'] ?></th>
                <th class="bl bt bb br"><?= $gobernacion['fax'] ?></th>
                <th class="bl bt bb br"><?= $gobernacion['codigo_postal'] ?></th>
            </tr>
            <tr>
                <th class="bl bt bb" colspan="4">NOMBRES Y APELLIDOS DEL GOBERNADOR (RA)</th>        
            </tr>
            <tr>
                <td class="bl bt bb" colspan="4"><?= $gobernacion['nombre_apellido_gobernador'] ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <th class="bl bt bb" colspan="4">PERSONAL DIRECTIVO DE LA GOBERNACIÓN Y ÓRGANOS AUXILIARES:</th>
            </tr>
            
            <tr>
                        <th class="bl bt bb">DIRECCIÓN ADMINISTRATIVA</th>
                        <th class="bl bt bb br">NOMBRES Y APELLIDOS</th>
                        <th class="bl bt bb br">CORREO ELECTRÓNICO</th>
                        <th class="bl bt bb br">TELÉFONO (S)</th>
            </tr>
            <?php foreach ($data['directivo'] as $directivo) : ?>
            <tr>
                        <th class="bl bt bb"><?= $directivo['direccion'] ?></th>
                        <th class="bl bt bb"><?= $directivo['nombre_apellido'] ?></th>
                        <th class="bl bt bb"><?= $directivo['email'] ?></th>
                        <th class="bl bt bb"><?= $directivo['telefono'] ?></th>
            </tr>
            <?php endforeach; ?>
            <?php foreach ($data['contraloria'] as $contraloria) : ?>
            <tr>
                        <th class="bl bt bb" colspan="4">CONTRALORÍA ESTADAL</th>
            </tr>
            <tr>
                        <th class="bl bt bb" colspan="4">NOMBRES Y APELLIDOS DEL CONTRALOR (A)</th>
            </tr>
            <tr>
                        <th class="bl bt bb" colspan="4"><?= $contraloria['nombre_apellido_contralor'] ?></th>
            </tr>
            <tr>
                        <th class="bl bt bb" colspan="4">DOMICILIO LEGAL:</th>
            </tr>
            <tr>
                        <th class="bl bt bb" colspan="4"><?= $contraloria['domicilio'] ?></th>
            </tr>
            <tr>
                        <th class="bl bt bb">TELÉFONO (S)</th>
                        <th class="bl bt bb br">PÁGINA WEB</th>
                        <th class="bl bt bb br" colspan="2">CORREO ELECTRÓNICO</th>
            </tr>
            <tr>
                        <th class="bl bt bb"><?= $contraloria['telefono'] ?></th>
                        <th class="bl bt bb br"><?= $contraloria['pagina_web'] ?></th>
                        <th class="bl bt bb br" colspan="2"><?= $contraloria['email'] ?></th>
            </tr>
            <?php endforeach; ?>
            <?php foreach ($data['consejo'] as $consejo) : ?>
            <tr>
                        <th class="bl bt bb" colspan="4">CONCEJO LEGISLATIVO:</th>
            </tr>
            <tr>
                        <th class="bl bt bb" colspan="4">NOMBRES Y APELLIDOS DEL PRESIDENTE (A): <?= $consejo['nombre_apellido_presidente'] ?> </th>
            </tr>
            <tr>
                        <th class="bl bt bb" colspan="4">NOMBRES Y APELLIDOS DEL SECRETARIO (A): <?= $consejo['nombre_apellido_secretario'] ?></th>
            </tr>
            <tr>
                        <th class="bl bt bb" colspan="4">DOMICILIO LEGAL: <?= $consejo['domicilio'] ?></th>
            </tr>
            <tr>
                        <th class="bl bt bb">TELÉFONO (S)</th>
                        <th class="bl bt bb br">PÁGINA WEB</th>
                        <th class="bl bt bb br" colspan="2">CORREO ELECTRÓNICO</th>
            </tr>
            <tr>
                        <th class="bl bt bb"><?= $consejo['telefono'] ?></th>
                        <th class="bl bt bb br"><?= $consejo['pagina_web'] ?></th>
                        <th class="bl bt bb br" colspan="2"><?= $consejo['email'] ?></th>
            </tr>

            <tr>
                        <th class="bl bt bb">CONSEJO LOCAL DE PLANIFICACIÓN PÚBLICA :</th>
                        <th class="bl bt bb br"></th>
                        <th class="bl bt bb br" colspan="2"></th>
            </tr>
            <tr>
                        <th class="bl bt bb">NOMBRES Y APELLIDOS DE LOS CONSEJEROS (AS) :</th>
                        <th class="bl bt bb br" colspan="4"><?= $consejo['consejo_local'] ?></th>
            </tr>
            <?php endforeach; ?>
    </table>
</body>
</html>