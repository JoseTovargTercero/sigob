<?php
require_once '../sistema_global/conexion.php';


// Suponemos que recibimos $id y $id_ejercicio de alguna manera, como parámetros GET
$id_ejercicio = $_GET['id_ejercicio'];
$ente = $_GET['ente'];



// INFORMACION DEL SECTOR Y PROGRAMA
$stmt = mysqli_prepare($conexion, "SELECT entes.ente_nombre, ppy.proyecto_id, ppy.denominacion AS nombre_proyecto, pp.programa, pp.denominacion AS nombre_programa, ps.sector, ps.denominacion AS nombre_sector FROM `entes`
LEFT JOIN pl_sectores ps ON ps.id = entes.sector 
LEFT JOIN pl_programas pp ON pp.id = entes.programa 
LEFT JOIN pl_proyectos ppy ON ppy.id = entes.proyecto 

 WHERE entes.id = ?");
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
            <td colspan="6" class="title">ACTIVIDADES</td>
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
            <th class="br">ACTIVIDAD 51</th>
            <th class="br">ACTIVIDAD 52</th>
            <th class="br">ACTIVIDAD 53</th>
            <th class="br">ACTIVIDAD 54</th>
            <th class="br">ACTIVIDAD 55</th>
            <th class="br">MONTO DE LA OBRA</th>
        </tr>

        <!-- Ejemplo de fila de datos -->
        <tr>
            <td class="br">401</td>
            <td class="br">01</td>
            <td class="br">01</td>
            <td class="br">00</td>
            <td class="br">000</td>
            <td class="br subtitle">SUELDOS BÁSICOS PERSONAL FIJO A TIEMPO COMPLETO</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
        </tr>

        <!-- Ejemplo de fila total por partida -->
        <tr>
            <td colspan="6" class="crim br">TOTAL POR PARTIDA 401</td>
            <td class="crim br">Ejemplo</td>
            <td class="crim br">Ejemplo</td>
            <td class="crim br">Ejemplo</td>
            <td class="crim br">Ejemplo</td>
            <td class="crim br">Ejemplo</td>
            <td class="crim br">Ejemplo</td>
            <td class="crim br">Ejemplo</td>
        </tr>

        <!-- Fila de separación para diferentes partidas -->
        <tr>
            <td class="br">402</td>
            <td class="br">01</td>
            <td class="br">01</td>
            <td class="br">00</td>
            <td class="br">000</td>
            <td class="subtitle br">ALIMENTOS Y BEBIDAS PARA PERSONAS</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
            <td class="br">Ejemplo</td>
        </tr>
    </table>


</body>

</html>