<?php
require_once '../sistema_global/conexion.php';

$id_ejercicio = $_GET['id_ejercicio'];

// Consulta a la tabla ejercicio_fiscal para obtener año y situado
$query_ejercicio = "SELECT ano, situado FROM ejercicio_fiscal WHERE id = ?";
$stmt = $conexion->prepare($query_ejercicio);
$stmt->bind_param('i', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$ano = $data['ano'];
$situado = $data['situado'];
$stmt->close();

// Inicializar array para almacenar los datos por sector
$data = [];

// Consultar datos del sector y su denominación en la tabla pl_sectores
$query_sector = "SELECT id, sector, denominacion FROM pl_sectores";
$stmt_sector = $conexion->prepare($query_sector);
if ($stmt_sector === false) {
    die('Error en la consulta SQL (pl_sectores): ' . $conexion->error);
}
$stmt_sector->execute();
$result_sector = $stmt_sector->get_result();

// Agrupar los sectores y sus denominaciones
$sectores = []; // Array para agrupar IDs por sector
while ($sector_data = $result_sector->fetch_assoc()) {
    $sector = $sector_data['sector'];
    $id = $sector_data['id'];

    // Asegurarse de que el sector existe en el array
    if (!isset($sectores[$sector])) {
        $sectores[$sector] = [];
    }

    // Agregar el ID al sector correspondiente
    $sectores[$sector][] = $id;

    // También almacenamos la denominación en el array $data
    $data[$sector] = [
        'denominacion' => $sector_data['denominacion'],
        'monto_ordinario' => 0, // Inicializamos con 0
        'monto_coordinado' => 0, // Inicializamos con 0
        'monto_proyecto' => 0 // Inicializamos con 0 para el cuarto valor
    ];
}

// Sectores a procesar
$sectores_a_procesar = ['01', '02', '06', '08', '09', '11', '12', '13', '14', '15'];

foreach ($sectores_a_procesar as $sector) {
    if (isset($sectores[$sector])) {
        $id_list = implode(',', $sectores[$sector]); // Convertimos el array en una lista separada por comas

        // Consulta para obtener el monto ordinario de distribucion_presupuestaria
        if (!empty($id_list)) {
            $query_distribucion = "SELECT 
                                        SUM(monto_inicial) AS total_monto_inicial, 
                                        SUM(0) AS total_coordinado 
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
            $monto_inicial_total = $distribucion_data['total_monto_inicial'] ?? 0;
            $data[$sector]['monto_ordinario'] = $monto_inicial_total;

            // Consulta para obtener el monto de proyectos en proyecto_inversion_partidas
            $query_proyecto = "SELECT SUM(monto) AS total_monto_proyecto FROM proyecto_inversion_partidas WHERE sector_id IN ($id_list)";
            $stmt_proyecto = $conexion->prepare($query_proyecto);
            if ($stmt_proyecto === false) {
                die('Error en la consulta SQL (proyecto_inversion_partidas): ' . $conexion->error);
            }
            $stmt_proyecto->execute();
            $result_proyecto = $stmt_proyecto->get_result();
            $proyecto_data = $result_proyecto->fetch_assoc();
            $monto_proyecto_total = $proyecto_data['total_monto_proyecto'] ?? 0;
            $data[$sector]['monto_proyecto'] = $monto_proyecto_total;
        }
    }
}

// Puedes continuar procesando o mostrando los datos almacenados en $data aquí
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

    <?php
    // Imprimir el encabezado
    echo "
    <div style='font-size: 9px;'>
        <table class='header-table bt br bb bl bc-lightgray'>
            <tr>
                <td class='text-left' style='width: 20px'>
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
                <td class='text-left' style='vertical-align: top;padding-top: 13px;'>
                    <b>
                    REPÚBLICA BOLIVARIANA DE VENEZUELA <br>
                    GOBERNACIÓN DEL ESTADO AMAZONAS 
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
                <h2 align='center'>Resumen de los Creditos Presupuestarios a Nivel de Sectores</h2>
                </td>
            </tr>

              <tr>
                <td class='text-left'>
                <b>PRESUPUESTO " . $ano . "</b>
                </td>
            </tr>
        </table>

        
    "; ?>

    <table>
        <thead>
            <tr>
                <th class="bl bt bb" rowspan="2">Sector</th>
                <th class="bl bt bb br p-2 text-left" style="width: 30%;" rowspan="2">Denominación</th>
                <th class="bl bt bb br" colspan='3'>Asignación Presupuestaria</th>
            </tr>
            <tr>
                <th class="bb br">Ordinario</th>
                <th class="bb br">Coordinado</th>
                <th class="bb br">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $t_ordinario = 0;
            $t_coordinado = 0;
            $t_totales = 0;

            // Iterar sobre los datos formateados para crear la tabla
            foreach ($data as $sector => $row) {
                $monto_total = $row['monto_ordinario'] + $row['monto_coordinado'] + $row['monto_proyecto'];
                $ordinario = $row['monto_ordinario'];
                $coordinado = $row['monto_proyecto'];
                $t_ordinario += $ordinario;
                $t_coordinado += $coordinado;
                $t_totales += $monto_total;

                echo "<tr>
                    <td class='p-2 bl'>{$sector}</td>
                    <td class='p-2 bl text-left'>{$row['denominacion']}</td>
                    <td class='p-2 bl'>" . number_format($ordinario, 2, ',', '.') . "</td>
                    <td class='p-2 bl'>" . number_format($coordinado, 2, ',', '.') . "</td>
                    <td class='p-2 bl br'>" . number_format($monto_total, 2, ',', '.') . "</td>
                </tr>";
            }
            ?>

        </tbody>
        <tfoot>
            <tr>
                <td class="bt bl bb" colspan='2'><b>TOTALES</b></td>
                <td class="bt bl bb"><?php echo number_format($t_ordinario, 2, ',', '.') ?></td>
                <td class="bt bl bb"><?php echo number_format($t_coordinado, 2, ',', '.') ?></td>
                <td class="bt bl bb br"><?php echo number_format($t_totales, 2, ',', '.') ?></td>
        </tfoot>
    </table>

</body>


</html>