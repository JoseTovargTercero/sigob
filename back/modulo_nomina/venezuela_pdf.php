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
      border-left: none !important;
      border-right: none !important;
    }

    tr {
      border: none !important;
      border-bottom: 1px solid black !important;
      border-top: 1px solid black !important;
    }

    th {
      font-size: 9px !important;
      border-left: none !important;
      border-right: none !important;
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

    .text-blue {
      color: #3d3dcf !important;
    }
  </style>
</head>

<body>
  <div style="font-size: 10px;">
    <table>
      <tr style="border: none !important; border-bottom: none !important">
        <td class="w-50">
          <img src="../../img/logo.jpg" width="100px">
        </td>
        <td class="text-right w-50">
          Fecha: 00/00/0000 <br>
        </td>
      </tr>
    </table>
    <h2 class="mb-0" align="center"> RELACION DEPOSITO BANCO</h2>
    <hr>
    <table class="mb-0" style="margin-bottom: 10px !important;">
      <tr style="border: none !important; border-bottom: none !important">
        <td class="fw-bold">
          Tipo de nómina: <span> NOMBRE DE LA NOMINA </span> - BANCO VENEZUELA
        </td>
      </tr>
    </table>
    <table style="width: 100%;">
      <thead>
        <tr>
          <th class="text-center">Cedula</th>
          <th class="text-left">Nombre del Empleado</th>
          <th class="text-center">Cuenta Bancaria</th>
          <th class="text-center">Monto a Depositar</th>
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
        $sql4 = "SELECT * FROM informacion_pdf WHERE correlativo='$correlativo' AND identificador='$identificador' AND banco='0102'";
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
                    <tr class="text-blue">
                      <td class="text-center"><?php echo $empleado['cedula']; ?></td>
                      <td class="text-left"><?php echo $empleado['nombres']; ?></td>
                      <td class="text-center"><?php echo $empleado['cuenta_bancaria']; ?></td>
                      <td class="text-center"><?php echo $total_pagar_individual; ?></td>
                    </tr>
          <?php
                  }
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
            <td class="text-center"><strong><?php echo $total_deposito; ?></strong></td>
          </tr>
          <tr>
            <td colspan="3" align="right"><strong>Cantidad de Empleados:</strong></td>
            <td class="text-center"><strong><?php echo $cantidad_empleados; ?></strong></td>
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