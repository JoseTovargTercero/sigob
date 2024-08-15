<!DOCTYPE html>
<html>
<head>
    <title>NOMINA BONO ESPECIAL</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            border: 1px solid black;
            margin: 10px;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.5;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-container {
            vertical-align: top;
            padding: 0;
            border-collapse: collapse;
        }

        .table-container td {
            padding: 2px;
            text-align: left;
            border-left: 1px solid black;
            border-right: 1px solid black;
        }

        .table-container th {
            border: 1px solid #000;
        }

        .page-break {
            page-break-after: always;
        }

        /* Other existing styles */

    </style>
</head>

<body>

<div style="font-size: 10px;">
    <img src="../../img/logo.jpg" style="height: 110px; width: 250px;">
    <?php
    $correlativo = $_GET['correlativo'];
    ?>
    <h1 align="center">Recibo de Pagos <?php echo htmlspecialchars($correlativo); ?></h1>
    <?php
    // Conexión a la base de datos
    $conn = new PDO('mysql:host=localhost;dbname=sigob', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Parámetro para paginación
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $registrosPorPagina = 175;

    $offset = ($pagina - 1) * $registrosPorPagina;

    $query = "
        SELECT
            e.cedula AS Cédula,
            e.nombres AS Nombres,
            cg.cargo AS Cargo,
            e.fecha_ingreso AS Fecha_de_Ingreso,
            '' AS Fecha_de_Egreso,
            rp.asignaciones AS Asignacion,
            rp.deducciones AS Deduccion,
            rp.aportes AS Aporte,
            rp.total_pagar AS Total_Pagar,
            e.banco AS Centro_de_pago,
            e.cuenta_bancaria AS Cuenta_Bancaria
        FROM
            recibo_pago rp
        JOIN
            empleados e ON rp.id_empleado = e.id
        JOIN
            cargos_grados cg ON e.cod_cargo = cg.cod_cargo
        WHERE
            rp.correlativo = :correlativo
        LIMIT :offset, :limit
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':correlativo', $correlativo, PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Preparamos la consulta para obtener el código de partida
    $codPartidaStmt = $conn->prepare("SELECT codigo_concepto FROM conceptos WHERE nom_concepto = :nom_concepto");

    // Función para obtener el código de partida
    function obtenerCodPartida($concepto, $stmt) {
        $stmt->execute(['nom_concepto' => $concepto]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['codigo_concepto'] : '';
    }

    $tableCounter = 0;

    foreach ($results as $row) {
        echo "<table border='1' cellspacing='0' cellpadding='5'>";
        echo "<thead>
            <tr>
                <th>Cédula</th>
                <th>Nombres</th>
                <th>Cargo</th>
                <th>Fecha de Ingreso</th>
                <th>Fecha de Egreso</th>
                <th>Sueldo</th>
                <th>Centro de Pago</th>
                <th>Cuenta Bancaria</th>
            </tr>
        </thead>
        <tbody>";

        // Datos principales del empleado
        echo "<tr>";
        echo "<td>{$row['Cédula']}</td>";
        echo "<td>{$row['Nombres']}</td>";
        echo "<td>{$row['Cargo']}</td>";
        echo "<td>{$row['Fecha_de_Ingreso']}</td>";
        echo "<td>{$row['Fecha_de_Egreso']}</td>";

        // Calcular el sueldo sumando Asignacion, Deduccion y Aporte
        $sueldo = $row['Total_Pagar'];
        $asignaciones = json_decode($row['Asignacion'], true);
        $deducciones = json_decode($row['Deduccion'], true);
        $aportes = json_decode($row['Aporte'], true);

        echo "<td>{$sueldo}</td>";
        echo "<td>{$row['Centro_de_pago']}</td>";
        echo "<td>{$row['Cuenta_Bancaria']}</td>";
        echo "</tr>";

        // Detalle de conceptos
        echo "<tr>";
        echo "<th colspan='2'>Codigo</th>";
        echo "<th colspan='2'>Nombre de Concepto</th>";
        echo "<th colspan='2'>Asignacion</th>";
        echo "<th>Deduccion</th>";
        echo "<th>Aportes</th>";
        echo "</tr>";

        // Mostrar asignaciones
        $totalAsignaciones = 0;
        foreach ($asignaciones as $concepto => $valor) {
            $codigo_concepto = obtenerCodPartida($concepto, $codPartidaStmt);
            echo "<tr>";
            echo "<td colspan='2'>{$codigo_concepto}</td>";
            echo "<td colspan='2'>{$concepto}</td>";
            echo "<td colspan='2'>{$valor} Bs</td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "</tr>";
            $totalAsignaciones += $valor;
        }

        // Mostrar deducciones
        $totalDeducciones = 0;
        foreach ($deducciones as $concepto => $valor) {
            $codigo_concepto = obtenerCodPartida($concepto, $codPartidaStmt);
            echo "<tr>";
            echo "<td colspan='2'>{$codigo_concepto}</td>";
            echo "<td colspan='2'>{$concepto}</td>";
            echo "<td colspan='2'></td>";
            echo "<td>{$valor} Bs</td>";
            echo "<td></td>";
            echo "</tr>";
            $totalDeducciones += $valor;
        }

        // Mostrar aportes
        $totalAportes = 0;
        foreach ($aportes as $concepto => $valor) {
            $codigo_concepto = obtenerCodPartida($concepto, $codPartidaStmt);
            echo "<tr>";
            echo "<td colspan='2'>{$codigo_concepto}</td>";
            echo "<td colspan='2'>{$concepto}</td>";
            echo "<td colspan='2'></td>";
            echo "<td></td>";
            echo "<td>{$valor}  Bs</td>";
            echo "</tr>";
            $totalAportes += $valor;
        }

        // Mostrar totales
        echo "<tr>";
        echo "<td colspan='4'><strong>Total</strong></td>";
        echo "<td colspan='2'><strong>{$totalAsignaciones} Bs</strong></td>";
        echo "<td><strong>{$totalDeducciones} Bs</strong></td>";
        echo "<td><strong>{$totalAportes} Bs</strong></td>";
        echo "</tr>";

        echo "</tbody></table>";

        $tableCounter++;
        if ($tableCounter % 2 == 0) {
            echo '<div class="page-break"></div>';
        }
    }
    ?>

</div>

</body>
</html>
