<?php
require_once '../sistema_global/conexion.php';
global $ano_ejercicio, $situado_ejercicio;
function procesarDatos($tipo, $tipo_fecha, $fecha, $local_db, $remote_db, $id_ejercicio) {
    global $ano_ejercicio, $situado_ejercicio;
    // Seleccionar la base de datos en función del tipo
    $db = ($tipo === 'gastos') ? $local_db : $remote_db;

    // Consultar el ejercicio fiscal
    $query_ejercicio = "SELECT * FROM ejercicio_fiscal WHERE id = ?";
    $stmt_ejercicio = $db->prepare($query_ejercicio);
    $stmt_ejercicio->bind_param('i', $id_ejercicio);
    $stmt_ejercicio->execute();
    $result_ejercicio = $stmt_ejercicio->get_result();
    $ejercicio = $result_ejercicio->fetch_assoc();
    $stmt_ejercicio->close();

    if (!$ejercicio) {
        die("No se encontró el ejercicio fiscal para el ID proporcionado.");
    }

    // Variables del ejercicio fiscal
    $ano_ejercicio = $ejercicio['ano'];
    $situado_ejercicio = $ejercicio['situado'];

    // Consultar la tabla compromisos
    $query_compromisos = "SELECT * FROM compromisos WHERE tabla_registro = ?";
    $stmt = $db->prepare($query_compromisos);
    $stmt->bind_param('s', $tipo);
    $stmt->execute();
    $result_compromisos = $stmt->get_result();
    $compromisos = $result_compromisos->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $data = [];

    foreach ($compromisos as $compromiso) {
        $id_registro = $compromiso['id_registro'];

        // Inicializar el mes como nulo
        $mes = null;

        if ($tipo === 'solicitud_dozavos') {
            // Consultar la tabla solicitud_dozavos
            $query_solicitud = "SELECT mes FROM solicitud_dozavos WHERE id = ?";
            $stmt_solicitud = $db->prepare($query_solicitud);
            $stmt_solicitud->bind_param('i', $id_registro);
            $stmt_solicitud->execute();
            $result_solicitud = $stmt_solicitud->get_result();
            $solicitud = $result_solicitud->fetch_assoc();
            $stmt_solicitud->close();

            if (!$solicitud) continue;

            $mes = (int)$solicitud['mes'];
        } else if ($tipo === 'gastos') {
            // Consultar la tabla gastos
            $query_gasto = "SELECT fecha FROM gastos WHERE id = ?";
            $stmt_gasto = $db->prepare($query_gasto);
            $stmt_gasto->bind_param('i', $id_registro);
            $stmt_gasto->execute();
            $result_gasto = $stmt_gasto->get_result();
            $gasto = $result_gasto->fetch_assoc();
            $stmt_gasto->close();

            if (!$gasto) continue;

            // Convertir la fecha al mes
            $mes = (int)date('n', strtotime($gasto['fecha'])) - 1; // Restar 1 para que sea 0-indexado
        }

        // Verificar condiciones de mes o trimestre
        if ($tipo_fecha === 'mensual' && $mes !== $fecha) {
            continue;
        }

        if ($tipo_fecha === 'trimestre') {
            $trimestre = (int)$fecha;
            $rango_trimestre = [
                1 => [0, 1, 2],
                2 => [3, 4, 5],
                3 => [6, 7, 8],
                4 => [9, 10, 11]
            ];

            if (!in_array($mes, $rango_trimestre[$trimestre])) {
                continue;
            }
        }

        // Agregar información completa del compromiso al array
        $data[] = [
            'id' => $compromiso['id'],
            'correlativo' => $compromiso['correlativo'],
            'descripcion' => $compromiso['descripcion'],
            'id_registro' => $compromiso['id_registro'],
            'id_ejercicio' => $compromiso['id_ejercicio'],
            'tabla_registro' => $compromiso['tabla_registro'],
            'numero_compromiso' => $compromiso['numero_compromiso'],
        ];
    }

    return $data;
}


// Variables de entrada
$tipo = $_GET['tipo']; // 'gastos' o 'solicitud_dozavos'
$tipo_fecha = $_GET['tipo_fecha']; // 'mensual' o 'trimestre'
$fecha = (int)$_GET['fecha']; // 0-11 para mensual o 1-4 para trimestre
$id_ejercicio = $_GET['id_ejercicio'];



// Procesar los datos
$data = procesarDatos($tipo, $tipo_fecha, $fecha, $local_db, $remote_db, $id_ejercicio);

?>








<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Compromisos</title>
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
            <h2 align='center'>Reporte de Compromisos</h2>
        </td>
    </tr>

    <tr>
        <td class='text-left'>
            <b>PRESUPUESTO <?php echo $ano_ejercicio ?></b>
        </td>
    </tr>
    </table>

<table>
    <thead>
        <tr>
            <th class="bt bl bb p-15">Correlativo</th>
            <th class="bt bl bb p-15">Descripción</th>
            <th class="bt bl bb p-15">ID Registro</th>
            <th class="bt bl bb p-15">Ejercicio Fiscal</th>
            <th class="bt bl bb p-15">Tabla Registro</th>
            <th class="bt bl bb p-15">Número de Compromiso</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($data)) {
            foreach ($data as $compromiso) {
                $correlativo = $compromiso['correlativo'];
                $descripcion = $compromiso['descripcion'];
                $id_registro = $compromiso['id_registro'];
                $id_ejercicio = $compromiso['id_ejercicio'];
                $tabla_registro = $compromiso['tabla_registro'];
                $numero_compromiso = $compromiso['numero_compromiso'];

                echo "<tr>
                    <td class='fz-8 bl'>{$correlativo}</td>
                    <td class='fz-8 bl text-left'>{$descripcion}</td>
                    <td class='fz-8 bl'>{$id_registro}</td>
                    <td class='fz-8 bl'>{$ano_ejercicio}</td>
                    <td class='fz-8 bl'>{$tabla_registro}</td>
                    <td class='fz-8 bl br'>{$numero_compromiso}</td>
                </tr>";
            }
        } else {
            echo "<tr>
                <td colspan='7' class='text-center fz-8 bl br'>No se encontraron datos</td>
            </tr>";
        }
        ?>
    </tbody>
</table>








</body>

</html>