<?php
require_once '../sistema_global/conexion.php';

// Suponemos que recibimos $id_ejercicio de alguna manera, como parámetro GET
$id_ejercicio = $_GET['id_ejercicio'];







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
    </style>
</head>

<body>

    <div class="fz-10">
        <table class='b-1 bc-lightgray'>
            <tr>
                <td class="text-left pt-1 pb-0" colspan="2">
                    <b>GOBERNACIÓN DEL ESTADO INDÍGENA DE AMAZONAS</b> <br> <br>
                    <b>CODIGO PRESUPUESTARIO: E5100 </b> <br> <br>
                    <b>PRESUPUESTO: <?php echo $ano ?></b>

                </td>
                <td class="text-right ">
                    <b>Fecha: <?php echo date('d/m/Y') ?></b> <br> <br>
                    <img src='../../img/logo.jpg' class='logo'>

                </td>
            </tr>

            <tr>

                <td class="pt-1 text-center" colspan="3">
                    <h2>GASTOS DE INVERSION ESTIMADOS POR EL ESTADO </h2>
                </td>

            </tr>
        </table>

        <table>

            <!-- Encabezado de columnas -->
            <tr>
                <th class="bl bt bb br" rowspan="2">SECTOR</th>
                <th class="bt bb br" rowspan="2">PROGRAMA</th>
                <th class="bt bb br" rowspan="2">PARTIDA</th>
                <th class="bt bb br" colspan="3">GEN</th>
                <th class="bt bb br" rowspan="2">DENOMINACIÓN</th>
                <th class="bt bb br" rowspan="2">ASIGNACION PRESUPUESTARIA</th>
                <th class="bt bb br" rowspan="2">OBSERVACION</th>
            </tr>

            <tr>
                <th class="bt bb br">GEN</th>
                <th class="bt bb br">ESP</th>
                <th class="bt bb br">SUB</th>
            </tr>

            <?php
            $totalPartida = 0;
            $totalGeneral = 0;
            $partAnterior = null;

            $partidasPermitidas = [
                ['01', '06', '401.01.01.00.0000'],
                ['01', '06', '401.05.01.00.0000'],
                ['01', '06', '401.05.03.00.0000'],
                ['09', '04', '401.01.01.00.0000'],
                ['09', '04', '401.05.01.00.0000'],
                ['09', '04', '401.05.03.00.0000'],
                ['11', '02', '403.18.01.00.0000'],
                ['11', '02', '404.03.06.00.0000'],
                ['11', '02', '404.99.01.00.0000'],
                ['12', '01', '403.18.01.00.0000'],
                ['12', '01', '404.99.01.00.0000'],
            ];


            // Función para obtener el ID correspondiente usando mysqli
            function obtenerId($conexion, $tabla, $campo, $valor)
            {
                $query = "SELECT id FROM $tabla WHERE $campo = ?";
                $stmt = $conexion->prepare($query);
                if ($stmt) {
                    $stmt->bind_param("s", $valor);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    $row = $resultado->fetch_assoc();
                    $stmt->close();
                    return $row ? $row['id'] : null;
                } else {
                    die("Error en la consulta: " . $conexion->error);
                }
            }
            function obtenerPrograma($conexion, $programa, $sector)
            {
                $query = "SELECT id FROM pl_programas WHERE programa = ? AND sector = ?";
                $stmt = $conexion->prepare($query);
                if ($stmt) {
                    $stmt->bind_param("ss", $programa, $sector);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    $row = $resultado->fetch_assoc();
                    $stmt->close();
                    return $row ? $row['id'] : null;
                } else {
                    die("Error en la consulta: " . $conexion->error);
                }
            }




            // Array con los IDs
            $partidasConIds = [];
            foreach ($partidasPermitidas as $partida) {
                $sectorId = obtenerId($conexion, 'pl_sectores', 'sector', $partida[0]);
                $programaId = obtenerPrograma($conexion, $partida[1], $sectorId);
                $partidaId = obtenerId($conexion, 'partidas_presupuestarias', 'partida', $partida[2]);

                // Validar que se hayan encontrado todos los IDs
                if ($sectorId && $programaId && $partidaId) {
                    $partidasConIds[] = [$sectorId, $programaId, $partidaId];

                    // echo $partida[0] . '.' . $partida[1] . '.' . $partida[2] . '------' . $sectorId . '.' . $programaId . '.' . $partidaId . '<br>';
                } else {
                    // Manejar el caso en que no se encuentren los IDs
                    $partidasConIds[] = null; // O manejarlo según tu lógica
                }
            }


            // Construir las condiciones dinámicamente
            $condiciones = [];
            foreach ($partidasConIds as $partida) {
                if ($partida) { // Asegúrate de que la partida no sea nula
                    $condiciones[] = "(id_sector = {$partida[0]} AND id_programa = {$partida[1]} AND id_partida = {$partida[2]})";
                }
            }

            // Agregar la condición final para id_sector = 10
            $condiciones[] = "(id_sector = 10)";

            // Unir todas las condiciones con OR
            $whereClause = implode(' OR ', $condiciones);

            // Construir la consulta final
            $query = "SELECT dp.monto_inicial, pp.partida, ps.sector, pr.programa, pp.descripcion FROM distribucion_presupuestaria AS dp
                        LEFT JOIN partidas_presupuestarias pp ON pp.id = dp.id_partida
                        LEFT JOIN pl_sectores ps ON ps.id = dp.id_sector
                        LEFT JOIN pl_programas pr ON pr.id = dp.id_programa
                         WHERE $whereClause ORDER BY dp.id_sector";


            // Mostrar la consulta

            $resumenes = [];
            $total  = 0;
            $stmt = mysqli_prepare($conexion, $query);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $idKey = $row['sector'] . '.' . $row['programa'] . '.' . $row['partida'];

                    if (@$resumenes[$idKey]) {
                        $resumenes[$idKey]['monto_inicial'] += $row['monto_inicial'];
                    } else {
                        $resumenes[$idKey] = $row;
                    }
                }
            }
            $stmt->close();

            foreach ($resumenes as $row) {
                $partida = explode('.', $row['partida']);
                $total += $row['monto_inicial'];

                echo "<tr>
                <td class='bl br'>{$row['sector']}</td>
                <td class='bl br'>{$row['programa']}</td>
                <td class='br'>{$partida[0]}</td>
                <td class='br'>{$partida[1]}</td>
                <td class='br'>{$partida[2]}</td>
                <td class='br'>{$partida[3]}</td>
                <td class='br text-left'>{$row['descripcion']}</td>
                <td class='br'>" . number_format($row['monto_inicial'], 2) . "</td>
                <td class='br'></td>
                </tr>";
            }

            /*


            $sectores_programas_unicos = [];

            // Iterar por cada elemento del array
            foreach ($partidasPermitidas as $partida) {
                // Extraer los índices 0 y 1
                $par = [$partida[0], $partida[1]];
                // Evitar duplicados en el sectores_programas_unicos
                if (!in_array($par, $sectores_programas_unicos, true)) {
                    $sectores_programas_unicos[] = $par;
                }
            }
*/
            ?>

            <!-- Fila de total general -->
            <tr>
                <td colspan="7" class="bt bl br bb text-right"><b>TOTAL</b></td>
                <td class="bt br bb"><b><?= number_format($total, 2) ?></b></td>
                <td class="bt br bb"></td>
            </tr>
        </table>


        <style>
            .w-7 {
                width: 4%;
            }
        </style>




</body>

</html>