<?php
require_once '../sistema_global/conexion.php';
 ?>
<!DOCTYPE html>
<html>
<head>
    <title>REINTEGRO</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div style="font-size: 10px;">
        <img src="../../img/logo.jpg" style="height: 110px; width: 250px;">

<?php
$id_empleado = $_GET['id_empleado'];
$sql = "SELECT e.nombres AS nombre, e.cedula, e.fecha_ingreso, cg.cargo AS nombre_cargo
        FROM empleados e
        LEFT JOIN cargos_grados cg ON e.cod_cargo = cg.cod_cargo
        WHERE e.id = '$id_empleado'";

$result = mysqli_query($conexion, $sql);

// Verificación y llenado de los datos obtenidos
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // Obtener los datos del empleado
    $nombre = $row['nombre'];
    $cedula = $row['cedula'];
    $cargo = $row['nombre_cargo'];
    $fecha_ingreso = $row['fecha_ingreso'];

    echo "
    <center>
    <p>PAGO DE DIFERENCIA DE AGUINALDOS (pasivos)</p>
    <p>{$nombre} {$cedula} ({$cargo}) </p>
    <p>Fecha de Ingreso: {$fecha_ingreso}</p>
    </center>
    ";
}
 ?>

<?php
// Variable para guardar nombre_nomina
$nombre_nomina = '';
// Consulta a la base de datos para obtener el último registro con 'regional' en nombre_nomina
// Consulta a la base de datos para obtener el último registro con 'regional' en nombre_nomina
$sql5 = "SELECT * FROM historico_reintegros WHERE id_empleado='$id_empleado' AND LOWER(nombre_nomina) LIKE '%regional%' ORDER BY fecha DESC LIMIT 1";
$result5 = mysqli_query($conexion, $sql5);

// Variable para almacenar los datos del último registro
$ultimo_registro = null;

// Verificación y llenado de los datos obtenidos
if ($result5 && mysqli_num_rows($result5) > 0) {
    $mostrar5 = mysqli_fetch_assoc($result5);
    // Decodificación de los arrays JSON
    $asignaciones2 = json_decode($mostrar5['asignaciones'], true);
    $deducciones2 = json_decode($mostrar5['deducciones'], true);
    $aportes2 = json_decode($mostrar5['aportes'], true);
    $total_pagar2 = $mostrar5['total_pagar'];
    $nombre_nomina2 = strtolower($mostrar5['nombre_nomina']);
    $fecha2 = $mostrar5['fecha'];
    $sueldo_base_quincenal = $mostrar5['sueldo_base'] / 2; // Monto quincenal es la mitad
    $sueldo_base_mensual = $mostrar5['sueldo_base']; // Monto mensual es el completo

    // Convertir los arrays a texto formateado
    $asignaciones_texto = '';
    foreach ($asignaciones2 as $key => $value) {
        $monto_quincena = $value / 2; // Monto quincenal es la mitad del completo
        $monto_completo = $value; // Monto mensual es el completo
        $asignaciones_texto .= "<tr><td>$key</td><td>{$monto_quincena}</td><td>{$monto_completo}</td></tr>";
    }

    // Calcular el total integral
    $total_integral_quincenal = $sueldo_base_quincenal;
    $total_integral_mensual = $sueldo_base_mensual;

    foreach ($asignaciones2 as $value) {
        $total_integral_quincenal += $value / 2; // Suma los montos quincenales (mitad)
        $total_integral_mensual += $value; // Suma los montos mensuales (completos)
    }

    // Almacenar los datos en el array
    $ultimo_registro = [
        'asignaciones' => $asignaciones_texto,
        'total_pagar' => $total_pagar2,
        'nombre_nomina' => $nombre_nomina2,
        'fecha' => $fecha2,
        'sueldo_base' => [
            'quincenal' => $sueldo_base_quincenal,
            'mensual' => $sueldo_base_mensual
        ],
        'total_integral' => [
            'quincenal' => $total_integral_quincenal,
            'mensual' => $total_integral_mensual
        ]
    ];
} else {
    echo "<p>No se encontraron Reintegros</p>";
}

// Mostrar los datos del último registro
if ($ultimo_registro) {
    echo "
    <table border='1'>
        <thead>
            <tr>
                <th colspan='3'>CUADRO DEMOSTRATIVO DEL SALARIO</th>
            </tr>
            <tr>
                <th>CONCEPTOS</th>
                <th>MONTO QUINCENAL</th>
                <th>MONTO MENSUAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>SUELDO Y SALARIO TABULADOR REGIONAL</td>
                <td>{$ultimo_registro['sueldo_base']['quincenal']}</td>
                <td>{$ultimo_registro['sueldo_base']['mensual']}</td>
            </tr>
            {$ultimo_registro['asignaciones']}
            <tr>
                <td>TOTAL INTEGRAL</td>
                <td>{$ultimo_registro['total_integral']['quincenal']}</td>
                <td>{$ultimo_registro['total_integral']['mensual']}</td>
            </tr>
        </tbody>
    </table>";
     $sueldo_diario = round($ultimo_registro['total_integral']['mensual']/30,2);
     $alicuota_vacacional = round(($sueldo_diario*179)/12,2);
     $aguinaldo_regional = round($sueldo_diario*153,2);
     $aguinaldo_nacional = round($sueldo_diario*30,2);
     $porcentaje_ince = 0.5 / 100;
     $descuento_ince = round($aguinaldo_regional * $porcentaje_ince,2);
     $aguinaldo_regional_total = round($aguinaldo_regional - $descuento_ince,2);
     $diferencia_aguinaldos = $aguinaldo_regional_total - $aguinaldo_nacional;
     $diferencia_dividida = round($diferencia_aguinaldos/4,2);
     $diferencia_25 = round($diferencia_dividida*0.25,2);

    echo "
    <table border='1'>
        <thead>
            <tr>
                <th colspan='4'>CUADRO DEMOSTRATIVO DE LA ALÍCUOTA DE BONO VACACIONAL</th>
            </tr>
            <tr>
                <th>SUELDO MENSUAL</th>
                <th>SUELDO DIARIO</th>
                <th>DIAS A CANCELAR DE BONO VACACIONAL</th>
                <th>ALÍCUOTA DE BONO VACACIONAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$ultimo_registro['total_integral']['mensual']}</td>
                <td>{$sueldo_diario}</td>
                <td>179</td>
                <td>{$alicuota_vacacional}</td>

            </tr>
        </tbody>
    </table>";


    echo "
    <table border='1'>
        <thead>
            <tr>
                <th colspan='6'>CUADRO DEMOSTRATIVO DEL AGUINALDO</th>
            </tr>
            <tr>
                <th>SUELDO MENSUAL + ALÍCUOTA DE BONO VACACIONAL</th>
                <th>SUELDO DIARIO</th>
                <th>DIAS DE AGUINALDO</th>
                <th>AGUINALDO TOTAL(100%)</th>
                <th>DESCUENTO DEL INCE(0,5%)</th>
                <th>TOTAL AGUINALDO(100%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$ultimo_registro['total_integral']['mensual']} + {$alicuota_vacacional} </td>
                <td>{$sueldo_diario}</td>
                <td>153</td>
                <td>{$aguinaldo_regional}</td>
                <td>{$descuento_ince}</td>
                <td>{$aguinaldo_regional_total}</td>

            </tr>
        </tbody>
    </table>
    <span>AGUINALDOS NACIONAL</span>
    </br>
    <span>{$aguinaldo_nacional}</span>
    </br>

    <span>AGUINALDOS REGIONAL</span>
    </br>
    <span>{$aguinaldo_regional_total}</span>
    </br>
    <span>{$aguinaldo_regional_total} - {$aguinaldo_nacional} =  {$diferencia_aguinaldos}</span>
    </br>
    <span>TOTAL DIFERENCIA = {$diferencia_aguinaldos} / 4 = {$diferencia_dividida} </span>

    <table border='1'>
        <thead>
            <tr>
                <th>PARTES</th>
                <th>CONCEPTOS</th>
                <th>SE LE DEBE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1ER 25%</td>
                <td>Diferencia de aguinaldo</td>
                <td>{$diferencia_25}</td>
            </tr>
            <tr>
                <td>2ER 25%</td>
                <td>Diferencia de aguinaldo</td>
                <td>{$diferencia_25}</td>
            </tr>
            <tr>
                <td>3ER 25%</td>
                <td>Diferencia de aguinaldo</td>
                <td>{$diferencia_25}</td>
            </tr>
            <tr>
                <td>4ER 25%</td>
                <td>Diferencia de aguinaldo</td>
                <td>{$diferencia_25}</td>
            </tr>

        </tbody>
    </table>

    ";


}










?>






<?php

















        function obtener_mes_en_letras($fecha) {
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $fecha_parts = explode('-', $fecha);
            if (count($fecha_parts) == 2) {
                $mes_numero = (int)$fecha_parts[0];
                $año = $fecha_parts[1];
                return $meses[$mes_numero] . ' ' . $año;
            }
            return "Fecha inválida";
        }
        ?>
        <h1 align="center">Resumen de Reintegro <?php  echo $nombre?></h1>
        <?php


        // Consulta a la base de datos
        $sql4 = "SELECT * FROM historico_reintegros WHERE id_empleado='$id_empleado'";
        $result4 = mysqli_query($conexion, $sql4);

        // Arrays para almacenar los datos por fecha
        $datos_por_fecha = [];

        // Verificación y llenado de los datos obtenidos
        if ($result4 && mysqli_num_rows($result4) > 0) {
            while ($mostrar4 = mysqli_fetch_assoc($result4)) {
                // Decodificación de los arrays JSON
                $asignaciones = json_decode($mostrar4['asignaciones'], true);
                $deducciones = json_decode($mostrar4['deducciones'], true);
                $aportes = json_decode($mostrar4['aportes'], true);
                $total_pagar = $mostrar4['total_pagar'];
                $nombre_nomina = strtolower($mostrar4['nombre_nomina']);
                $fecha = $mostrar4['fecha'];

                // Almacenar los datos en el array según la fecha y el tipo de nómina
                if (!isset($datos_por_fecha[$fecha])) {
                    $datos_por_fecha[$fecha] = [
                        'nacional' => null,
                        'regional' => null
                    ];
                }

                if (strpos($nombre_nomina, 'nacional') !== false) {
                    $datos_por_fecha[$fecha]['nacional'] = [
                        'asignaciones' => $asignaciones,
                        'deducciones' => $deducciones,
                        'aportes' => $aportes,
                        'total_pagar' => $total_pagar
                    ];
                } elseif (strpos($nombre_nomina, 'regional') !== false) {
                    $datos_por_fecha[$fecha]['regional'] = [
                        'asignaciones' => $asignaciones,
                        'deducciones' => $deducciones,
                        'aportes' => $aportes,
                        'total_pagar' => $total_pagar
                    ];
                }
            }
        } else {
            echo "<p>No se encontraron Reintegros</p>";
        }

        // Mostrar los datos en tablas
        foreach ($datos_por_fecha as $fecha => $datos) {
            if ($datos['nacional'] && $datos['regional']) {
                $nacional = $datos['nacional'];
                $regional = $datos['regional'];

                // Calcular diferencias
                $diferencias = [
                    'asignaciones' => [],
                    'deducciones' => [],
                    'aportes' => []
                ];

                foreach ($nacional['asignaciones'] as $key => $value) {
                    $regional_value = $regional['asignaciones'][$key] ?? 0;
                    $diferencias['asignaciones'][$key] = abs($value - $regional_value);
                }

                foreach ($nacional['deducciones'] as $key => $value) {
                    $regional_value = $regional['deducciones'][$key] ?? 0;
                    $diferencias['deducciones'][$key] = abs($value - $regional_value);
                }

                foreach ($nacional['aportes'] as $key => $value) {
                    $regional_value = $regional['aportes'][$key] ?? 0;
                    $diferencias['aportes'][$key] = abs($value - $regional_value);
                }
        ?>
        <h1 align="center"><?php echo obtener_mes_en_letras($fecha); ?></h1>
        <table>
            <thead>
                <tr>
                    <th colspan="1">Nacional</th>
                    <th colspan="1">Regional</th>
                    <th rowspan="2">Diferencia</th>
                    <th colspan="2" style="text-align: center;">Total a Pagar</th>
                    <th rowspan="2">Fecha</th>
                </tr>
                <tr>
                    <th>Conceptos</th>
                    <th>Conceptos</th>
                    <th colspan="2" style="text-align: center;">Quincena / Mensual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Columna Nacional -->
                    <td>
                        <strong>Asignaciones:</strong><br>
                        <?php
                        foreach ($nacional['asignaciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Deducciones:</strong><br>
                        <?php
                        foreach ($nacional['deducciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Aportes:</strong><br>
                        <?php
                        foreach ($nacional['aportes'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                    </td>

                    <!-- Columna Regional -->
                    <td>
                        <strong>Asignaciones:</strong><br>
                        <?php
                        foreach ($regional['asignaciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Deducciones:</strong><br>
                        <?php
                        foreach ($regional['deducciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Aportes:</strong><br>
                        <?php
                        foreach ($regional['aportes'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                    </td>

                    <!-- Columna Diferencia -->
                    <td>
                        <strong>Asignaciones:</strong><br>
                        <?php
                        foreach ($diferencias['asignaciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Deducciones:</strong><br>
                        <?php
                        foreach ($diferencias['deducciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Aportes:</strong><br>
                        <?php
                        foreach ($diferencias['aportes'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                    </td>

                    <!-- Columna Total a Pagar -->
                    <td>
                        <strong>Total a Pagar Nacional:</strong> <?php echo $nacional['total_pagar']/2; ?><br>
                        <strong>Total a Pagar Regional:</strong> <?php echo $regional['total_pagar']/2; ?>
                    </td>
                    <td>
                        <strong>Total a Pagar Nacional:</strong> <?php echo $nacional['total_pagar']; ?><br>
                        <strong>Total a Pagar Regional:</strong> <?php echo $regional['total_pagar']; ?>
                    </td>

                    <!-- Columna Fecha -->
                    <td><?php echo $fecha; ?></td>
                </tr>
            </tbody>
        </table>
        <?php
            }
        }

        // Cierre de la conexión
        mysqli_close($conexion);
        ?>
    </div>
</body>
</html>
