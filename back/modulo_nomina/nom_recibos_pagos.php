<!DOCTYPE html>
<html>

<head>
    <title>Nomina de Pago por Nivel Organizacional</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            margin: 10px;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        hr {
            margin: 5px 0 !important;
            padding: 0 !important;
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

        .text-right {
            text-align: right !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .w-50 {
            width: 50% !important;
        }
        .w-5{
            width: 5% !important;
        }
        .w-10{
            width: 10% !important;
        }
        .text-left{
            text-align: left;
        }

        .fw-bold {
            font-weight: bold !important;
        }

        .bg-gray {
            background-color: #dddddd;
        }

        td {
            padding: 1px 2px;
            font-size: 8px !important;
        }
        th{
            font-size: 9px !important;
        }
        .b-tb{
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }
        .my-1{
            margin-top: 4px !important;
            margin-bottom: 4px !important;

        }
        .bt{
            border-top: 1px solid black;
        }
        .bb{
            border-bottom: 1px solid black;
        }
        .text-center{
            text-align: center !important;
        }
        /* Other existing styles */
    </style>
</head>

<body>
    <?php $correlativo = $_GET['correlativo']; ?>

    <div style="font-size: 10px;">

        <table>
            <tr>
                <td class="w-50">
                    <img src="../../img/logo.jpg" width="100px">
                </td>
                <td class="text-right w-50">
                    Fecha: 00/00/0000 <br>
                    Correlativo Sigob: <?php echo htmlspecialchars($correlativo); ?>
                </td>
            </tr>
        </table>

        <h2 class="mb-0" align="center">
            Nomina de Pago por Nivel Organizacional </h2>

        <hr>

        <table class="mb-0">
            <tr>
                <td class="w-50 fw-bold">
                    NOMINA: 003 - DIRECTORES (PERSONAL DE CONFIANZA)
                </td>
                <td class=" w-50 fw-bold">
                    Periodo del : 15/10/2023 Al: 31/10/2023
                </td>
            </tr>
            <tr>
                <td class="w-50">
                    UNIDAD: 01-06-51 Secretaria Ejecutiva de Recursos Humanos
                </td>
                <td class=" w-50">
                    CATEGORIA: 14-01-00-55 PERSONAL DE ALTO NIVEL Y JEFATURAS
                </td>
            </tr>
        </table>
        <hr>


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
        function obtenerCodPartida($concepto, $stmt)
        {
            $stmt->execute(['nom_concepto' => $concepto]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['codigo_concepto'] : '';
        }

        $tableCounter = 0;

        foreach ($results as $row) {
            echo "<table cellspacing='10' >";
            echo "<thead>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>";

            // Datos principales del empleado
            echo "<tr class='my-1'>
                <td COLSPAN=3 class='fw-bold bg-gray'>{$row['Cédula']} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$row['Nombres']}</td>
                <td COLSPAN=2><b>Cargo: </b>&nbsp;&nbsp;&nbsp; {$row['Cargo']}</td>";

            // Calcular el sueldo sumando Asignacion, Deduccion y Aporte
            $sueldo = $row['Total_Pagar'];
            $asignaciones = json_decode($row['Asignacion'], true);
            $deducciones = json_decode($row['Deduccion'], true);
            $aportes = json_decode($row['Aporte'], true);

            echo " <td></td>
            <td></td>
            </tr>
            <tr>
                <td COLSPAN=3><b>Fecha de Ingreso:</b> {$row['Fecha_de_Ingreso']}</td>
                <td COLSPAN=3><b>Fecha de Egreso:</b>{$row['Fecha_de_Egreso']}</td>
                <td><b>SUELDO: &nbsp;&nbsp;{$sueldo} </b></td>
                <td></td>
            </tr>
            <tr>
                <td COLSPAN=3><b>Centro de Pago:</b> {$row['Centro_de_pago']}</td>
                <td COLSPAN=3><b>Cuenta Bancaria:</b>{$row['Cuenta_Bancaria']}</td>
                <td></td>
                <td></td>
            </tr>";





            // Detalle de conceptos
            echo "<tr >
                <th class='bt bb w-10 text-left'>Codigo</th>
                <th class='bt bb text-left'>Nombre de Concepto</th>
                <th class='bt bb text-center'>Cantidad</th>
                <th class='bt bb text-center'>Asignación</th>
                <th class='bt bb text-center'>Deducción</th>
                <th class='bt bb text-center'>Aportes</th>
                <th class='bt bb text-center'>Saldo</th>
                </tr>";

                            // Detalle de conceptos
            echo "<tr>
                <td >001</td>
                <td >SUELDO</td>
                <td class='text-center'></td>
                <td class='text-center'>{$sueldo}</td>
                <td class='text-center'></td>
                <td class='text-center'></td>
                <td class='text-center'>{$sueldo}</td>
            </tr>";



            $neto = $sueldo;
            $saldo = 0;


            // Mostrar asignaciones
            $totalAsignaciones = 0;
            foreach ($asignaciones as $concepto => $valor) {
                $codigo_concepto = obtenerCodPartida($concepto, $codPartidaStmt);
                echo "<tr>
                        <td >{$codigo_concepto}</td>
                        <td>{$concepto}</td>
                        <td class='text-center'></td>
                        <td class='text-center'>".number_format($valor, 2, '.', ',')."</td>
                        <td class='text-center'>".number_format(0, 2, '.', ',')."</td>
                        <td class='text-center'>".number_format(0, 2, '.', ',')."</td>
                        <td class='text-center'>".number_format($valor, 2, '.', ',')."</td>
                    </tr>";
                $totalAsignaciones += $valor;
                $saldo += $valor;
                $neto += $valor;
            }

            // Mostrar deducciones
            $totalDeducciones = 0;
            foreach ($deducciones as $concepto => $valor) {
                $codigo_concepto = obtenerCodPartida($concepto, $codPartidaStmt);
                echo "<tr>
                        <td >{$codigo_concepto}</td>
                        <td>{$concepto}</td>
                        <td class='text-center'></td>
                        <td class='text-center'>".number_format(0, 2, '.', ',')."</td>
                        <td class='text-center'>".number_format($valor, 2, '.', ',')."</td>
                        <td class='text-center'>".number_format(0, 2, '.', ',')."</td>
                        <td class='text-center'>".number_format($valor, 2, '.', ',')."</td>
                    </tr>";
                $totalDeducciones += $valor;
                $saldo += $valor;
                $neto -= $valor;

            }

            // Mostrar aportes
            $totalAportes = 0;
            foreach ($aportes as $concepto => $valor) {
                $codigo_concepto = obtenerCodPartida($concepto, $codPartidaStmt);
                echo "<tr>
                        <td>{$codigo_concepto}</td>
                        <td>{$concepto}</td>
                        <td class='text-center'></td>
                        <td class='text-center'>".number_format(0, 2, '.', ',')." </td>
                        <td class='text-center'>".number_format(0, 2, '.', ',')." </td>
                        <td class='text-center'>".number_format($valor, 2, '.', ',')."</td>
                        <td class='text-center'>".number_format($valor, 2, '.', ',')."</td>
                    </tr>";
                $totalAportes += $valor;
                $saldo += $valor;
                $neto -= $valor;

            }

            // Mostrar totales
            echo "<tr>
                    <td class='text-center'></td>
                    <td class='text-center'></td>
                    <td class='bt text-center '><b>Total</b></td>
                    <td class='bt text-center'><strong>".number_format($totalAsignaciones, 2, '.', ',')."</strong></td>
                    <td class='bt text-center'><strong>".number_format($totalDeducciones, 2, '.', ',')."</strong></td>
                    <td class='bt text-center'><strong>".number_format($totalAportes, 2, '.', ',')."</strong></td>
                    <td class='bt text-center'><strong>".number_format($saldo, 2, '.', ',')."</strong></td>
                </tr>";

             // ROW CON LA INFORMACION DEL NETO
            echo "<tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class='bg-gray text-center'><strong>NETO: ".number_format($neto, 2, '.', ',')."</strong></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>";


            echo "</tbody></table> <hr>";

            $tableCounter++;
            if ($tableCounter % 4 == 0) {
                echo '<div class="page-break"></div>';
            }
        }
        ?>

    </div>

</body>

</html>