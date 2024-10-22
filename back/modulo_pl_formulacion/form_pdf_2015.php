<?php 
require_once '../sistema_global/conexion.php'; 

// Suponemos que recibimos $id y $id_ejercicio de alguna manera, como parámetros GET
$id = $_GET['id'];
$id_ejercicio = $_GET['id_ejercicio'];

// Consultar datos del sector
$query_sector = "SELECT sector, programa, nombre FROM pl_sectores_presupuestarios WHERE id = ?";
$stmt_sector = $conexion->prepare($query_sector);
$stmt_sector->bind_param('i', $id);
$stmt_sector->execute();
$result_sector = $stmt_sector->get_result();
$sector_data = $result_sector->fetch_assoc();

$sector = $sector_data['sector'];
$programa = $sector_data['programa'];

// Consultar distribuciones presupuestarias
$query_distribucion = "SELECT monto_inicial, id_partida FROM distribucion_presupuestaria WHERE id_sector = ? AND id_ejercicio = ?";
$stmt_distribucion = $conexion->prepare($query_distribucion);
$stmt_distribucion->bind_param('ii', $id, $id_ejercicio);
$stmt_distribucion->execute();
$result_distribucion = $stmt_distribucion->get_result();
$distribuciones = $result_distribucion->fetch_all(MYSQLI_ASSOC);

// Crear un array para almacenar los datos de la tabla
$data = [];

foreach ($distribuciones as $distribucion) {
    $monto_inicial = $distribucion['monto_inicial'];
    $id_partida = $distribucion['id_partida'];

    // Consultar partida y descripción
    $query_partida = "SELECT partida, descripcion FROM partidas_presupuestarias WHERE id = ?";
    $stmt_partida = $conexion->prepare($query_partida);
    $stmt_partida->bind_param('i', $id_partida);
    $stmt_partida->execute();
    $result_partida = $stmt_partida->get_result();
    $partida_data = $result_partida->fetch_assoc();

    $partida = $partida_data['partida'];
    $descripcion = $partida_data['descripcion'];

    // Formatear los datos según el esquema solicitado
    $data[] = [$partida, $descripcion, '0,00', $monto_inicial, '0,00', '0,00', $monto_inicial];
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

        <h2 align='center'>CRÉDITOS PRESUPUESTARIOS DEL SECTOR POR PROGRAMA A NIVEL DE PARTIDAS Y FUENTES DE FINANCIAMIENTO</h2>

        <table class='table-title'>
            <tr>
                <td class='text-left fw-bold'>SECTOR:</td>
                <td class='text-left fw-bold'>PROGRAMA:</td>
            </tr>
            <tr>
                <td class='text-left'>{$sector}</td>
                <td class='text-left'>{$programa}</td>
            </tr>
        </table>
    ";

    // Inicio de tabla principal
    echo "
        <table>
            <thead>
                <tr>
                    <th class='text-left'>Partida</th>
                    <th class='text-left'>Denominación</th>
                    <th>Ingresos Propios</th>
                    <th>Situado Estadal</th>
                    <th>FCI</th>
                    <th>Otras Fuentes</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>";

    foreach ($data as $row) {
        echo "<tr>
            <td class='text-left'>{$row[0]}</td>
            <td class='text-left'>{$row[1]}</td>
            <td>{$row[2]}</td>
            <td>{$row[3]}</td>
            <td>{$row[4]}</td>
            <td>{$row[5]}</td>
            <td>{$row[6]}</td>
        </tr>";
    }

    echo "
            </tbody>
        </table>
    ";
    ?>

</body>
</html>
