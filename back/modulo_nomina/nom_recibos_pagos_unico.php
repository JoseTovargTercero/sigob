<?php require_once '../sistema_global/conexion.php'; ?>
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

        .w-5 {
            width: 5% !important;
        }

        .w-10 {
            width: 10% !important;
        }

        .text-left {
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

        th {
            font-size: 9px !important;
        }

        .b-tb {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        .my-1 {
            margin-top: 4px !important;
            margin-bottom: 4px !important;
        }

        .bt {
            border-top: 1px solid black;
        }

        .bb {
            border-bottom: 1px solid black;
        }

        .text-center {
            text-align: center !important;
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


// Función para calcular la fecha de pago
function calcularFechaPagar($row, $conexion) {
    $identificador = $row['identificador'];
    $fecha_pagar = $row['fecha_pagar']; // Formato esperado: m-Y
    $nombre_nomina = $row['nombre_nomina'];

    $fechaInicio = null;
    $fechaFin = null;
    
    // Consulta para obtener las fechas de aplicar
    $stmt_conceptos = mysqli_prepare($conexion, "SELECT fecha_aplicar FROM `conceptos_aplicados` WHERE nombre_nomina = ?");
    $stmt_conceptos->bind_param('s', $nombre_nomina);
    $stmt_conceptos->execute();
    $result_conceptos = $stmt_conceptos->get_result();

    $concepto_valor_max = 0; // Valor máximo para dividir el mes

    if ($result_conceptos->num_rows > 0) {
        while ($row_conceptos = $result_conceptos->fetch_assoc()) {
            // Decodificar el array de fecha_aplicar
            $fechas = json_decode($row_conceptos['fecha_aplicar'], true);

            if ($fechas && is_array($fechas)) {
                // Tomar el valor más alto de las fechas, sin la 'p'
                foreach ($fechas as $fecha) {
                    $valor = intval(str_replace('p', '', $fecha));
                    if ($valor > $concepto_valor_max) {
                        $concepto_valor_max = $valor;
                    }
                }
            }
        }
    }
    $stmt_conceptos->close();

    if (preg_match('/^s(\d+)$/', $identificador, $matches)) {
        // Identificador semanal (s1, s2, s3, ...)
        $semanaNumero = (int) $matches[1];

        // Calcular la fecha de inicio de la semana del año
        $primerDiaAno = new DateTime("first day of January " . date('Y'));
        $primerDiaAno->modify('+' . ($semanaNumero - 1) . ' weeks');

        // Calcular el primer día de la semana (Lunes) y último día (Domingo)
        $fechaInicio = clone $primerDiaAno;
        $fechaInicio->modify('Monday this week');
        $fechaFin = clone $fechaInicio;
        $fechaFin->modify('Sunday this week');
    } elseif (preg_match('/^q(\d+)$/', $identificador, $matches)) {
        // Identificador quincenal (q1, q2)
        $quincenaNumero = (int) $matches[1];

        // Crear la fecha inicial del mes dado
        $primerDiaMes = DateTime::createFromFormat('m-Y', $fecha_pagar);
        $primerDiaMes->setDate($primerDiaMes->format('Y'), $primerDiaMes->format('m'), 1);

        if ($quincenaNumero === 1) {
            $fechaInicio = clone $primerDiaMes;
            $fechaFin = clone $fechaInicio;
            $fechaFin->modify('+14 days');
        } elseif ($quincenaNumero === 2) {
            $fechaInicio = clone $primerDiaMes;
            $fechaInicio->modify('+15 days');
            $fechaFin = (clone $fechaInicio)->modify('last day of this month');
        }
    } elseif ($identificador === 'fecha_unica') {
        // Fecha única (todo el mes)
        $fechaInicio = DateTime::createFromFormat('m-Y', $fecha_pagar);
        $fechaInicio->setDate($fechaInicio->format('Y'), $fechaInicio->format('m'), 1);
        $fechaFin = (clone $fechaInicio)->modify('last day of this month');
    } elseif (preg_match('/^p(\d+)$/', $identificador, $matches)) {
        // Identificador personalizado (p1, p2, p3, ...)
        $periodoNumero = (int) $matches[1];

        // Crear la fecha inicial del mes dado
        $primerDiaMes = DateTime::createFromFormat('m-Y', $fecha_pagar);
        $primerDiaMes->setDate($primerDiaMes->format('Y'), $primerDiaMes->format('m'), 1);
        $ultimoDiaMes = (clone $primerDiaMes)->modify('last day of this month');

        if ($concepto_valor_max > 0) {
            // Dividir el mes en partes según el valor máximo de fechas de aplicación
            $intervaloDias = (int) ceil($ultimoDiaMes->diff($primerDiaMes)->days / $concepto_valor_max);

            $fechaInicio = clone $primerDiaMes;
            $fechaFin = clone $fechaInicio;
            $fechaFin->modify('+' . ($periodoNumero * $intervaloDias - 1) . ' days');

            if ($fechaFin > $ultimoDiaMes) {
                $fechaFin = $ultimoDiaMes;
            }
        }
    }

    // Formatear fechas para mostrar el rango
    if ($fechaInicio && $fechaFin) {
        return $fechaInicio->format('d-m-Y') . ' hasta ' . $fechaFin->format('d-m-Y');
    } else {
        return null; // Correlativo no reconocido
    }
}





// Obtener la cédula desde el parámetro GET
$cedula = $_GET['cedula'];

// Preparar la consulta SQL para evitar inyecciones SQL
$stmt = $conexion->prepare("SELECT id FROM empleados WHERE cedula = ?");
$stmt->bind_param("s", $cedula);

// Ejecutar la consulta
$stmt->execute();

// Obtener el resultado
$resultado = $stmt->get_result();

// Verificar si se encontró un registro
if ($resultado->num_rows > 0) {
    // Obtener el valor del campo id
    $fila = $resultado->fetch_assoc();
    $id_empleado = $fila['id'];
} else {
    echo "No se encontró el empleado con la cédula proporcionada.";
}


$fecha_inicio = $_GET['fecha_inicio'];
$fecha_fin = $_GET['fecha_fin'];
$nombre_nomina = $_GET['nombre_nomina'];

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
        rp.fecha_pagar,
        e.banco AS Centro_de_pago,
        e.cuenta_bancaria AS Cuenta_Bancaria,
        rp.correlativo,
        rp.identificador,
        rp.nombre_nomina,
        rp.fecha_inicio,
        rp.fecha_fin,
        c.categoria,
        c.categoria_nombre,
        e.id_dependencia,
        d.dependencia,
        d.cod_dependencia
    FROM
        recibo_pago rp
    JOIN
        empleados e ON rp.id_empleado = e.id
    JOIN
        cargos_grados cg ON e.cod_cargo = cg.cod_cargo
    JOIN
        categorias c ON e.id_categoria = c.id
    JOIN
        dependencias d ON e.id_dependencia = d.id_dependencia
    WHERE
        rp.id_empleado = ?
        AND rp.fecha_inicio >= ?
        AND rp.fecha_fin <= ?
        AND rp.nombre_nomina = ?
    ORDER BY rp.fecha_inicio, rp.fecha_fin
";


// Preparar la consulta
$stmt = $conexion->prepare($query);

if ($stmt === false) {
    // Error en la preparación de la consulta
    die('Error en la consulta SQL: ' . $conexion->error);
}

// Asignar los valores a los parámetros
$stmt->bind_param('ssss', $id_empleado, $fecha_inicio, $fecha_fin, $nombre_nomina);

// Ejecutar la consulta
$stmt->execute();

// Obtener los resultados
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


    // Verifica si hay resultados
    if (count($results) > 0) {

        $row2 = $results[0];  // Obtener el primer resultado
        $correlativo = $row2['correlativo'];

        $row3 = [
            'identificador' => $row2['identificador'], // Puede ser 's1', 'q1', 'fecha_unica', etc.
            'fecha_pagar' => $row2['fecha_pagar'], // Formato m-Y
            'nombre_nomina' => $nombre_nomina,
        ];

        // Calcular el periodo de pago
        $fechaPagar2 = calcularFechaPagar($row3, $conexion);

        // Agrupar empleados por unidad organizacional y categoría
        $groupedEmployees = [];
        $uniqueEmployees = []; // Para almacenar empleados únicos

        foreach ($results as $row) {
            $employeeKey = $row['Cédula']; // Usar la cédula como identificador único

            // Verificar si el empleado ya ha sido agregado
            if (!isset($uniqueEmployees[$employeeKey])) {
                $headerKey = $row['id_dependencia'] . '|' . $row['dependencia'] . '|' . $row['cod_dependencia'] . '|' . $row['categoria'] . '|' . $row['categoria_nombre'] . '|' . $fechaPagar2 . '|' . $row['nombre_nomina'];

                if (!isset($groupedEmployees[$headerKey])) {
                    $groupedEmployees[$headerKey] = [
                        'categoria_nombre' => $row['categoria_nombre'],
                        'employees' => []
                    ];
                }
                $groupedEmployees[$headerKey]['employees'][] = $row;
                $uniqueEmployees[$employeeKey] = true; // Marcar el empleado como agregado
            }
        }

        // Definir la función obtenerCodPartida antes de usarla
function obtenerCodPartida($concepto, $conexion)
{
    // Preparar la consulta para obtener el código de partida
    $query = "SELECT codigo_concepto FROM conceptos WHERE nom_concepto = ?";
    
    // Preparar la consulta
    if ($stmt = $conexion->prepare($query)) {
        // Vincular el parámetro
        $stmt->bind_param("s", $concepto);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener el resultado
        $result = $stmt->get_result()->fetch_assoc();
        
        // Cerrar el statement
        $stmt->close();
        
        // Retornar el resultado
        return $result ? $result['codigo_concepto'] : '';
    } else {
        // Manejar el error si la preparación falla
        return '';
    }
}

        foreach ($groupedEmployees as $headerKey => $data) {
            list($id_dependencia, $dependencia, $cod_dependencia, $categoria, $categoria_nombre, $fechaPagar2, $nombre_nomina) = explode('|', $headerKey);
            $employees = $data['employees'];

            // Imprimir el encabezado
            echo "
        <div style='font-size: 10px;'>
            <table>
                <tr>
                    <td class='w-50'>
                        <img src='../../img/logo.jpg' width='100px'>
                    </td>
                    <td class='text-right w-50'>
                        Fecha: " . date('d/m/Y') . " <br>
                        Correlativo Sigob: " . htmlspecialchars($correlativo) . "
                    </td>
                </tr>
            </table>

            <h2 class='mb-0' align='center'>
                Nomina de Pago por Nivel Organizacional
            </h2>

            <hr>

            <table class='mb-0'>
                <tr>
                    <td class='w-50 fw-bold'>
                        NOMINA: {$nombre_nomina}
                    </td>
                    <td class='w-50 fw-bold'>
                        Periodo del: {$fechaPagar2}
                    </td>
                </tr>
                <tr>
                    <td class='w-50 fw-bold'>
                        UNIDAD: {$cod_dependencia} {$dependencia}
                    </td>
                    <td class='w-50 fw-bold'>
                        CATEGORÍA: {$categoria} {$categoria_nombre}
                    </td>
                </tr>
            </table>
        ";


            foreach ($employees as $index => $row) {

                echo "<table cellspacing='10'>";
                echo "<thead>
                <tr>
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

                echo "<tr >
                <th class='bt bb w-10 text-left'>Codigo</th>
                <th class='bt bb text-left'>Nombre de Concepto</th>
                <th class='bt bb text-center'>Cantidad</th>
                <th class='bt bb text-center'>Asignación</th>
                <th class='bt bb text-center'>Deducción</th>
                <th class='bt bb text-center'>Aportes</th>
                <th class='bt bb text-center'>Saldo</th>
                </tr>";

                $neto = 0;
                $saldo = 0;

                $totalAsignaciones = 0;
                foreach ($asignaciones as $concepto => $valor) {
                    $codigo_concepto = obtenerCodPartida($concepto, $conexion);
                    echo "<tr>
                        <td>{$codigo_concepto}</td>
                        <td>{$concepto}</td>
                        <td class='text-center'></td>
                        <td class='text-center'>" . number_format($valor, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format(0, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format(0, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format($valor, 2, '.', ',') . "</td>
                    </tr>";
                    $totalAsignaciones += $valor;
                    $saldo += $valor;
                    $neto += $valor;
                }

                $totalDeducciones = 0;
                foreach ($deducciones as $concepto => $valor) {
                    $codigo_concepto = obtenerCodPartida($concepto, $conexion);
                    echo "<tr>
                        <td>{$codigo_concepto}</td>
                        <td>{$concepto}</td>
                        <td class='text-center'></td>
                        <td class='text-center'>" . number_format(0, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format($valor, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format(0, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format(-$valor, 2, '.', ',') . "</td>
                    </tr>";
                    $totalDeducciones += $valor;
                    $saldo -= $valor;
                    $neto -= $valor;
                }

                $totalAportes = 0;
                foreach ($aportes as $concepto => $valor) {
                    $codigo_concepto = obtenerCodPartida($concepto, $conexion);
                    echo "<tr>
                        <td>{$codigo_concepto}</td>
                        <td>{$concepto}</td>
                        <td class='text-center'></td>
                        <td class='text-center'>" . number_format(0, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format(0, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format($valor, 2, '.', ',') . "</td>
                        <td class='text-center'>" . number_format($valor, 2, '.', ',') . "</td>
                    </tr>";
                    $totalAportes += $valor;
                    $saldo += $valor;
                    $neto += $valor;
                }

                echo "<tr >
                <th class='bt bb w-10 text-left'>Total</th>
                <th class='bt bb text-left'></th>
                <th class='bt bb text-center'></th>
                <th class='bt bb text-center'>" . number_format($totalAsignaciones, 2, '.', ',') . "</th>
                <th class='bt bb text-center'>" . number_format($totalDeducciones, 2, '.', ',') . "</th>
                <th class='bt bb text-center'>" . number_format($totalAportes, 2, '.', ',') . "</th>
                <th class='bt bb text-center'>" . number_format($saldo, 2, '.', ',') . "</th>
                </tr>
            </tbody>
        </table>";
            }
        }
    } else {
        echo "No se encontraron resultados para el correlativo dado.";
    }
    ?>
</body>

</html>