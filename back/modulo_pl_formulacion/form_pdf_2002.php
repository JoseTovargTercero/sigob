<?php 
require_once '../sistema_global/conexion.php'; 

$id_ejercicio = $_GET['id_ejercicio'];

// Inicializar array para almacenar los datos por sector
$data = [];

// Consultar datos del sector y su denominación junto con los IDs de pl_sectores_presupuestarios
$query_sector = "SELECT s.id, s.sector, s.denominacion 
                 FROM pl_sectores s
                 JOIN pl_sectores_presupuestarios ps ON s.sector = ps.sector";

$stmt_sector = $conexion->prepare($query_sector);
if ($stmt_sector === false) {
    die('Error en la consulta SQL (pl_sectores): ' . $conexion->error);
}
$stmt_sector->execute();
$result_sector = $stmt_sector->get_result();

// Agrupar los IDs por sector
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

// Tomemos un ejemplo para procesar los sectores 01, 02, 06, 08, 09, 11, 12, 13, 14, 15
$sectores_a_procesar = ['01', '02', '06', '08', '09', '11', '12', '13', '14', '15'];

foreach ($sectores_a_procesar as $sector) {
    if (isset($sectores[$sector])) {
        $id_list = implode(',', $sectores[$sector]); // Convertimos el array en una lista separada por comas

        // Hacer la consulta a distribucion_presupuestaria para el monto ordinario
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

            // Segunda consulta: Obtener la sumatoria de la tabla proyecto_inversion_partidas (para el monto de proyectos)
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

        th, td {
            border: 1px solid black;
            padding: 5px;
        }

        th {
            background-color: #dddddd;
            font-weight: bold;
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
            font-size: 14px;
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

        @media print {
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>

    <?php
    // Imprimir el encabezado
    echo "
    <div style='font-size: 9px;'>
        <table class='header-table'>
            <tr>
                <td class='w-50'>
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
                <td class='text-right w-50'>
                    <div class='fw-bold'>GOBERNACION DEL ESTADO INDÍGENA DE AMAZONAS</div>
                    <div>CÓDIGO PRESUPUESTARIO: E5100</div>
                    <div>PRESUPUESTO: 2020</div>
                    <div>Fecha: 27/12/2019</div>
                </td>
            </tr>
        </table>

        <h2 align='center'>Resumen de los Creditos Presupuestarios a Nivel de Sectores</h2>
    ";

    // Inicio de tabla principal
    echo "
        <table>
            <thead>
                <tr>
                    <th class='text-left'>Sector</th>
                    <th class='text-left'>Denominación</th>
                    <th>Ordinario</th>
                    <th>Coordinado</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>";

    // Iterar sobre los datos formateados para crear la tabla
    foreach ($data as $sector => $row) {
        $monto_total = $row['monto_ordinario'] + $row['monto_coordinado'] + $row['monto_proyecto'];
        echo "<tr>
            <td class='text-left'>{$sector}</td>
            <td class='text-left'>{$row['denominacion']}</td>
            <td>{$row['monto_ordinario']}</td>
            <td>{$row['monto_proyecto']}</td>
            <td>{$monto_total}</td>
        </tr>";
    }

    echo "
            </tbody>
        </table>
    ";
    ?>

</body>
</html>
