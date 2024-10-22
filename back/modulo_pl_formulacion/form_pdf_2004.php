<?php 
require_once '../sistema_global/conexion.php'; 

$id_ejercicio = $_GET['id_ejercicio'];

// Inicializar array para almacenar los datos por sector y programa
$data = [];

// Consultar datos del sector y programa
$query_programa = "SELECT p.id AS id_programa, p.programa, p.sector, p.denominacion 
                   FROM pl_programas p
                   JOIN pl_sectores_presupuestarios s ON p.sector = s.sector AND p.programa = s.programa";

$stmt_programa = $conexion->prepare($query_programa);
if ($stmt_programa === false) {
    die('Error en la consulta SQL (pl_programas): ' . $conexion->error);
}

$stmt_programa->execute();
$result_programa = $stmt_programa->get_result();

// Agrupar los programas por sector y programa
$programas = []; // Array para agrupar programas
while ($programa_data = $result_programa->fetch_assoc()) {
    $sector = $programa_data['sector'];
    $programa = $programa_data['programa'];
    $denominacion = $programa_data['denominacion'];
    $id_programa = $programa_data['id_programa'];

    // Asegurarse de que el sector y programa existen en el array
    if (!isset($programas[$sector][$programa])) {
        $programas[$sector][$programa] = [
            'denominacion' => $denominacion,
            'ids' => [],
        ];
    }

    // Agregar el ID del programa al sector y programa correspondiente
    $programas[$sector][$programa]['ids'][] = $id_programa;
}

// Verificamos si hay programas disponibles
if (empty($programas)) {
    die('No se encontraron programas.');
}

// Iterar sobre los sectores y programas
foreach ($programas as $sector => $programas_info) {
    foreach ($programas_info as $programa => $info) {
        $id_list = implode(',', $info['ids']); // Convertimos el array de IDs a una lista separada por comas

        // Hacer la consulta a distribucion_presupuestaria
        if (!empty($id_list)) {
            $query_distribucion = "SELECT 
                                        SUM(0) AS total_ingresos_propios, 
                                        SUM(monto_inicial) AS total_situado_estadal, 
                                        SUM(0) AS total_fci, 
                                        SUM(0) AS total_otras_fuentes 
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

            // Extraer los montos o asignar 0 si no hay datos
            $ingresos_propios = $distribucion_data['total_ingresos_propios'] ?? 0;
            $situado_estadal = $distribucion_data['total_situado_estadal'] ?? 0;
            $fci = $distribucion_data['total_fci'] ?? 0;
            $otras_fuentes = $distribucion_data['total_otras_fuentes'] ?? 0;
        } else {
            // Si no hay IDs, asignar valores en 0
            $ingresos_propios = $situado_estadal = $fci = $otras_fuentes = 0;
        }
        
        // Calcular el total
        $total = $ingresos_propios + $situado_estadal + $fci + $otras_fuentes;

        // Organizar datos para la tabla final
        $data[] = [$sector, $programa, $info['denominacion'], $ingresos_propios, $situado_estadal, $fci, $otras_fuentes, $total];
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

        <h2 align='center'>RESUMEN DE LOS CREDITOS PRESUPUESTARIOS A NIVEL DE SECTORES Y PROGRAMAS Y FUENTES DE FINANCIAMIENTO</h2>
    ";

    // Inicio de tabla principal
    echo "
        <table>
            <thead>
                <tr>
                    <th class='text-left'>Sector</th>
                    <th class='text-left'>Programa</th>
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
            <td class='text-left'>{$row[2]}</td>
            <td>{$row[3]}</td>
            <td>{$row[4]}</td>
            <td>{$row[5]}</td>
            <td>{$row[6]}</td>
            <td>{$row[7]}</td>
        </tr>";
    }

    echo "
            </tbody>
        </table>
    ";
    ?>

</body>
</html>
