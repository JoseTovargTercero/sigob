<?php 
require_once '../sistema_global/conexion.php'; 

$id_ejercicio = $_GET['id_ejercicio'];


// Consultar distribuciones presupuestarias
$query_distribucion = "SELECT monto_inicial, id_partida FROM distribucion_presupuestaria WHERE id_ejercicio = ?";
$stmt_distribucion = $conexion->prepare($query_distribucion);
$stmt_distribucion->bind_param('i', $id_ejercicio);
$stmt_distribucion->execute();
$result_distribucion = $stmt_distribucion->get_result();

// Verificar si hay resultados
if ($result_distribucion->num_rows === 0) {
    echo 'No se encontraron registros en distribucion_presupuestaria para el id_ejercicio: ' . $id_ejercicio . "<br>";
}

$distribuciones = $result_distribucion->fetch_all(MYSQLI_ASSOC);

$data = [];
$totales_por_partida = [];
$partidas_a_agrupadas = ['401', '402', '403', '404', '407', '408', '411', '498'];



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


    if (!$partida_data) {
        echo 'No se encontraron registros en partidas_presupuestarias para el id_partida: ' . $id_partida . "<br>";
        continue; // Continúa al siguiente registro
    }

    $partida = $partida_data['partida'] ?? 'N/A';
    $descripcion = $partida_data['descripcion'] ?? 'N/A';
    
 

    // Extraer el código de partida (los primeros 3 caracteres)
    $codigo_partida = substr($partida, 0, 3);

    // Agrupar datos por código de partida
    if (in_array($codigo_partida, $partidas_a_agrupadas)) {
        $data[$codigo_partida][] = [$partida, $descripcion, '0,00', $monto_inicial, '0,00', '0,00', $monto_inicial];
        
        if (!isset($totales_por_partida[$codigo_partida])) {
            $totales_por_partida[$codigo_partida] = 0;
        }
        $totales_por_partida[$codigo_partida] += $monto_inicial;
    }
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

    // Imprimir los registros agrupados y sus totales
    foreach ($partidas_a_agrupadas as $codigo_agrupado) {
        if (isset($data[$codigo_agrupado])) {
            foreach ($data[$codigo_agrupado] as $row) {
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

            // Imprimir total por partida
            $monto_total = $totales_por_partida[$codigo_agrupado];
            echo "<tr>
                <td colspan='6' class='text-left fw-bold'>TOTAL POR PARTIDA $codigo_agrupado</td>
                <td class='fw-bold'>$monto_total</td>
            </tr>";
        }
    }

    echo "</tbody>
        </table>";
    ?>

</body>
</html>
