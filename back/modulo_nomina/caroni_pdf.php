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
        <h1 style="text-align:center;">Relacion Deposito Banco</h1>
        <table style="width: 100%;" border="1">
            <thead>
                <tr>
                    <th>Cedula</th>
                    <th>Nombre del Empleado</th>
                    <th>Cuenta Bancaria</th>
                    <th>Monto a Depositar</th>
                </tr>
            </thead>
            <tbody>  
                
                <?php
                $correlativo = $_GET['correlativo'];
                $identificador = $_GET['identificador'];

                // Conexión a la base de datos
                $conexion = mysqli_connect('localhost', 'root', '', 'sigob');

                if (!$conexion) {
                    die("Error de conexión: " . mysqli_connect_error());
                }

                // Consulta para obtener los registros de informacion_pdf
                $sql4 = "SELECT * FROM informacion_pdf WHERE correlativo='$correlativo' AND identificador='$identificador' AND banco='0128'";
                $result4 = mysqli_query($conexion, $sql4);

                if ($result4) {
                    $total_deposito = 0;
                    $cantidad_empleados = 0;

                    while ($mostrar4 = mysqli_fetch_array($result4)) {
                        $cedula = $mostrar4['cedula'];
                        $total_pagar = $mostrar4['total_pagar'];

                        // Limpiar la cadena de cédulas
                        $cedula = trim($cedula, '[]"');
                        $cedulas = explode(',', $cedula);

                        // Limpiar y convertir total_pagar en un array
                        $total_pagar = str_replace(['[', ']', '"'], '', $total_pagar);
                        $total_pagar_array = explode(',', $total_pagar);

                        // Iterar sobre cada cédula para realizar la consulta
                        foreach ($cedulas as $key => $cedula_individual) {
                            $cedula_individual = trim($cedula_individual, ' "'); // Eliminar espacios y comillas adicionales

                            // Obtener el total_pagar correspondiente
                            $total_pagar_individual = isset($total_pagar_array[$key]) ? $total_pagar_array[$key] : 0;

                            // Consulta para obtener los nombres y cuentas bancarias de cada empleado
                            $sql_empleados = "
                                SELECT e.cedula, e.nombres, e.cuenta_bancaria
                                FROM empleados e
                                WHERE e.cedula = '$cedula_individual'
                            ";

                            $result_empleados = mysqli_query($conexion, $sql_empleados);
                            if ($result_empleados) {
                                if (mysqli_num_rows($result_empleados) > 0) {
                                    while ($empleado = mysqli_fetch_array($result_empleados)) {
                                        $cantidad_empleados++;
                                        $total_deposito += $total_pagar_individual;
                                        ?>
                                        <tr>
                                            <td style="" align="right"><?php echo $empleado['cedula']; ?></td>
                                            <td style=""><?php echo $empleado['nombres']; ?></td>
                                            <td style=""><?php echo $empleado['cuenta_bancaria']; ?></td>
                                            <td style="" align="right"><?php echo $total_pagar_individual; ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No se encontraron empleados para la cédula $cedula_individual</td></tr>";
                                }
                            } else {
                                echo "Error en la consulta de empleados: " . mysqli_error($conexion);
                            }
                        }
                    }

                    // Mostrar la suma total y la cantidad de empleados
                    ?>
                    <tr>
                        <td colspan="3" align="right"><strong>Total:</strong></td>
                        <td align="right"><strong><?php echo $total_deposito; ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right"><strong>Cantidad de Empleados:</strong></td>
                        <td align="right"><strong><?php echo $cantidad_empleados; ?></strong></td>
                    </tr>
                    <?php

                } else {
                    echo "Error en la consulta de informacion_pdf: " . mysqli_error($conexion);
                }

                // Cerrar la conexión
                mysqli_close($conexion);
                ?>
 
            </tbody>
        </table>
    </div>
</body>
</html>
