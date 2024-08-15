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

    .table-title {
      display: block;
      padding: 1rem;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
    }



    * {
      margin: 0;
      padding: 0;
      text-indent: 0;
    }

    .s1 {
      color: black;
      font-family: Arial, sans-serif;
      font-style: normal;
      font-weight: bold;
      text-decoration: none;
      font-size: 8.5pt;
    }

    .s2 {
      color: black;
      font-family: Arial, sans-serif;
      font-style: normal;
      font-weight: normal;
      text-decoration: none;
      font-size: 6.5pt;
    }

    .s3 {
      color: black;
      font-family: Arial, sans-serif;
      font-style: normal;
      font-weight: normal;
      text-decoration: none;
      font-size: 6pt;
    }

    .s4 {
      color: black;
      font-family: Arial, sans-serif;
      font-style: normal;
      font-weight: bold;
      text-decoration: none;
      font-size: 9pt;
    }

    .s5 {
      color: black;
      font-family: Arial, sans-serif;
      font-style: normal;
      font-weight: bold;
      text-decoration: none;
      font-size: 9pt;
    }

    .s6 {
      color: black;
      font-family: Arial, sans-serif;
      font-style: normal;
      font-weight: normal;
      text-decoration: none;
      font-size: 8pt;
    }



    .d-flex {
      display: flex !important;
    }

    .justify-content-between {
      justify-content: space-between !important;
    }

    .p-3 {
      padding: 3rem;
    }

    .p-2 {
      padding: 2rem;
    }

    .p-1 {
      padding: 1rem;
    }

    .text-center {
      text-align: center;
    }

    .w-100 {
      width: 100%;
    }

    .b-t {
      border-top: 1px solid black;
    }

    .b-l {
      border-left: 1px solid black;
    }

    .w-50 {
      width: 50%;
    }

    .mt-3 {
      margin-top: 3rem;
    }

    .mb-3 {
      margin-bottom: 3rem;
    }

    .mt-2 {
      margin-top: 2rem;
    }

    .mb-2 {
      margin-bottom: 2rem;
    }

    .mt-1 {
      margin-top: 1rem;
    }

    .mb-1 {
      margin-bottom: 1rem;
    }

    .s1>.st {
      text-align: left !important;
      margin-bottom: 5px !important;
    }

    .mr-3 {
      margin-right: 3rem;
    }

    /* td {
            padding: 2px !important;
        } */

    .b-r {
      border-right-width: 1pt
    }

    .b-b {
      border-bottom-width: 1pt
    }

    .b-l {
      border-left-width: 1pt
    }
  </style>
</head>
<body>
    <div style="font-size: 10px;">
        <img src="../../img/logo.jpg" style="height: 110px; width: 250px;">
        <?php
        $correlativo = $_GET['correlativo'];
        ?>
        <h1 align="center">Resumen de Nomina <?php echo htmlspecialchars($correlativo, ENT_QUOTES, 'UTF-8'); ?></h1>
        <table style="width: 100%;" border="1">
    <thead>
        <tr>
            <th>Nombre del Concepto</th>
            <th>Asignaciones</th>
            <th>Deducciones</th>
            <th>Aportes</th>
            <th>Codigo de Concepto</th>
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

                    echo "<tr><td>Sueldo Base</td><td>". round($total_total_pagar,2) ." Bs</td><td></td><td></td><td></td><td>$sueldo_base_cantidad</td></tr>";
                }

                // Función para obtener datos de conceptos y conceptos_aplicados
                function obtener_datos_conceptos($conexion, $concepto, $nombre_nomina) {
                    $sql_conceptos = "SELECT nom_concepto, codigo_concepto FROM conceptos WHERE nom_concepto='$concepto'";
                    $result_conceptos = mysqli_query($conexion, $sql_conceptos);
                    $datos_conceptos = mysqli_fetch_assoc($result_conceptos);

                    $sql_conceptos_aplicados = "SELECT emp_cantidad FROM conceptos_aplicados WHERE nom_concepto='$concepto' AND nombre_nomina='$nombre_nomina'";
                    $result_conceptos_aplicados = mysqli_query($conexion, $sql_conceptos_aplicados);
                    $datos_conceptos_aplicados = mysqli_fetch_assoc($result_conceptos_aplicados);

                    // Comprobar si ambos resultados existen
                    if ($datos_conceptos && $datos_conceptos_aplicados) {
                        return array_merge($datos_conceptos, $datos_conceptos_aplicados);
                    } elseif ($datos_conceptos) {
                        return $datos_conceptos; // Solo devuelve datos_conceptos
                    } elseif ($datos_conceptos_aplicados) {
                        return $datos_conceptos_aplicados; // Solo devuelve datos_conceptos_aplicados
                    } else {
                        return []; // Retorna un array vacío si no hay resultados
                    }
                }

                // Suma de asignaciones
                if (!empty($asignaciones)) {
                    foreach ($asignaciones as $key => $value) {
                        $total_asignaciones += $value;
                        $datos_conceptos = obtener_datos_conceptos($conexion, $key, $nombre_nomina);
                        echo "<tr><td>" . ($datos_conceptos['nom_concepto'] ?? $key) . "</td><td>". round($value,2) ." Bs</td><td></td><td></td><td>" . ($datos_conceptos['codigo_concepto'] ?? '') . "</td><td>" . ($datos_conceptos['emp_cantidad'] ?? '') . "</td></tr>";
                    }
                }

                // Suma de deducciones
                if (!empty($deducciones)) {
                    foreach ($deducciones as $key => $value) {
                        $total_deducciones += $value;
                        $datos_conceptos = obtener_datos_conceptos($conexion, $key, $nombre_nomina);
                        echo "<tr><td>" . ($datos_conceptos['nom_concepto'] ?? $key) . "</td><td></td><td>". round($value,2) ." Bs</td><td></td><td>" . ($datos_conceptos['codigo_concepto'] ?? '') . "</td><td>" . ($datos_conceptos['emp_cantidad'] ?? '') . "</td></tr>";
                    }
                }

                // Suma de aportes
                if (!empty($aportes)) {
                    foreach ($aportes as $key => $value) {
                        $total_aportes += $value;
                        $datos_conceptos = obtener_datos_conceptos($conexion, $key, $nombre_nomina);
                        echo "<tr><td>" . ($datos_conceptos['nom_concepto'] ?? $key) . "</td><td></td><td></td><td>". round($value,2) ." Bs</td><td>" . ($datos_conceptos['codigo_concepto'] ?? '') . "</td><td>" . ($datos_conceptos['emp_cantidad'] ?? '') . "</td></tr>";
                    }
                }
            }
        } else {
            echo "<tr><td colspan='6'>No se encontraron peticiones</td></tr>";
        }

        // Mostrar total de asignaciones, deducciones y aportes
        $total_total = $total_asignaciones + $total_total_pagar;
        echo "<tr><td></td><td><b>Total Asignaciones:</b>". round($total_total,2) ." Bs</td><td><b>Total Deducciones:</b>". round($total_deducciones,2) ." Bs</td><td><b>Total Aportes:</b>". round($total_aportes,2) ."Bs</td><td></td><td></td></tr>";

        // Cierre de la conexión
        mysqli_close($conexion);
        ?>
    </tbody>
</table>

    </div>
</body>
</html>
