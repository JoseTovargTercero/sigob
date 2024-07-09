<!DOCTYPE html>
<html>
<head>
    <title>NOMINA BONO ESPECIAL</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
    <div style="font-size: 10px;">
        <img src="../../img/logo.jpg" style="height: 110px; width: 250px; ">
        <?php
        $correlativo = $_GET['correlativo'];
        ?>
        <h1 align="center">Resumen de Nomina <?php echo $correlativo ?></h1>
        <table style="width: 100%;" border="1">
    <thead>
        <tr>
            <th>Nombre del Concepto</th>
            <th>Asignaciones</th>
            <th>Deducciones</th>
            <th>Aportes</th>
            <th>Codigo de Partida</th>
            <th>Cantidad de Empleados</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Variables para sumar los totales
        $total_asignaciones = 0;
        $total_deducciones = 0;
        $total_aportes = 0;
        $total_total_pagar = 0;

        // Conexión a la base de datos
        $conexion = mysqli_connect('localhost', 'root', '', 'sigob');

        // Verificación de la conexión
        if (!$conexion) {
            die("Error de conexión: " . mysqli_connect_error());
        }

        // Consulta a la base de datos
        $sql4 = "SELECT * FROM peticiones WHERE correlativo='$correlativo'";
        $result4 = mysqli_query($conexion, $sql4);

        // Verificación y llenado de la tabla con los datos obtenidos
        if ($result4) {
            while ($mostrar4 = mysqli_fetch_array($result4)) {
                // Decodificación de los arrays JSON
                $asignaciones = json_decode($mostrar4['asignaciones'], true);
                $deducciones = json_decode($mostrar4['deducciones'], true);
                $aportes = json_decode($mostrar4['aportes'], true);
                $total_pagar = json_decode($mostrar4['total_pagar'], true);
                $nombre_nomina = $mostrar4['nombre_nomina'];

                // Suma de total_pagar (como parte de asignaciones)
                if (!empty($total_pagar)) {
                    foreach ($total_pagar as $key => $value) {
                        $total_total_pagar += $value;
                    }
                    // Buscar emp_cantidad igual a Sueldo_Base
                    $sql_sueldo_base = "SELECT emp_cantidad FROM conceptos_aplicados WHERE nom_concepto='Sueldo Base' AND nombre_nomina='$nombre_nomina'";
                    $result_sueldo_base = mysqli_query($conexion, $sql_sueldo_base);
                    $datos_sueldo_base = mysqli_fetch_assoc($result_sueldo_base);
                    $sueldo_base_cantidad = $datos_sueldo_base['emp_cantidad'] ?? 0;

                    echo "<tr><td>Sueldo Base</td><td>$total_total_pagar Bs</td><td></td><td></td><td></td><td>$sueldo_base_cantidad</td></tr>";
                }

                // Función para obtener datos de conceptos y conceptos_aplicados
                function obtener_datos_conceptos($conexion, $concepto, $nombre_nomina) {
                    $sql_conceptos = "SELECT nom_concepto, cod_partida FROM conceptos WHERE nom_concepto='$concepto'";
                    $result_conceptos = mysqli_query($conexion, $sql_conceptos);
                    $datos_conceptos = mysqli_fetch_assoc($result_conceptos);

                    $sql_conceptos_aplicados = "SELECT emp_cantidad FROM conceptos_aplicados WHERE nom_concepto='$concepto' AND nombre_nomina='$nombre_nomina'";
                    $result_conceptos_aplicados = mysqli_query($conexion, $sql_conceptos_aplicados);
                    $datos_conceptos_aplicados = mysqli_fetch_assoc($result_conceptos_aplicados);

                    return array_merge($datos_conceptos, $datos_conceptos_aplicados);
                }

                // Suma de asignaciones
                if (!empty($asignaciones)) {
                    foreach ($asignaciones as $key => $value) {
                        $total_asignaciones += $value;
                        $datos_conceptos = obtener_datos_conceptos($conexion, $key, $nombre_nomina);
                        echo "<tr><td>" . $datos_conceptos['nom_concepto'] . "</td><td>$value Bs</td><td></td><td></td><td>" . $datos_conceptos['cod_partida'] . "</td><td>" . $datos_conceptos['emp_cantidad'] . "</td></tr>";
                    }
                }

                // Suma de deducciones
                if (!empty($deducciones)) {
                    foreach ($deducciones as $key => $value) {
                        $total_deducciones += $value;
                        $datos_conceptos = obtener_datos_conceptos($conexion, $key, $nombre_nomina);
                        echo "<tr><td>" . $datos_conceptos['nom_concepto'] . "</td><td></td><td>$value Bs</td><td></td><td>" . $datos_conceptos['cod_partida'] . "</td><td>" . $datos_conceptos['emp_cantidad'] . "</td></tr>";
                    }
                }

                // Suma de aportes
                if (!empty($aportes)) {
                    foreach ($aportes as $key => $value) {
                        $total_aportes += $value;
                        $datos_conceptos = obtener_datos_conceptos($conexion, $key, $nombre_nomina);
                        echo "<tr><td>" . $datos_conceptos['nom_concepto'] . "</td><td></td><td></td><td>$value Bs</td><td>" . $datos_conceptos['cod_partida'] . "</td><td>" . $datos_conceptos['emp_cantidad'] . "</td></tr>";
                    }
                }
            }
        } else {
            echo "<tr><td colspan='6'>No se encontraron peticiones</td></tr>";
        }

        // Mostrar total de asignaciones, deducciones y aportes
        $total_total = $total_asignaciones + $total_total_pagar;
        echo "<tr><td></td><td><b>Total Asignaciones:</b> $total_total Bs</td><td><b>Total Deducciones:</b> $total_deducciones Bs</td><td><b>Total Aportes:</b> $total_aportes Bs</td><td></td><td></td></tr>";

        // Cierre de la conexión
        mysqli_close($conexion);
        ?>
    </tbody>
</table>

    </div>
</body>
</html>
