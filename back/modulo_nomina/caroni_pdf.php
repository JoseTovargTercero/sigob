<?php require_once '../sistema_global/conexion.php'; ?>

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
<?php
$correlativo = $_GET['correlativo'];
$identificador = $_GET['identificador'];
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

        // Crear la fecha inicial del mes dado
        $primerDiaMes = DateTime::createFromFormat('m-Y', $fecha_pagar);
        $primerDiaMes->setDate($primerDiaMes->format('Y'), $primerDiaMes->format('m'), 1);

        // Calcular el primer día de la semana (Lunes) y último día (Domingo)
        $fechaInicio = clone $primerDiaMes;
        $fechaInicio->modify('+' . ($semanaNumero - 1) . ' weeks')->modify('Monday this week');
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


// Consultar la tabla peticiones para obtener el nombre_nomina y creacion
$sql = "SELECT nombre_nomina, creacion, identificador FROM peticiones WHERE correlativo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $correlativo);
$stmt->execute();
$stmt->bind_result($nombre_nomina, $creacion, $identificador);
$stmt->fetch();
$stmt->close();

$row3 = [
        'identificador' => $identificador, // Puede ser 's1', 'q1', 'fecha_unica', etc.
        'fecha_pagar' => $creacion, // Formato m-Y
        'nombre_nomina' => $nombre_nomina // Formato m-Y
    ];
    $fechaPagar2 = calcularFechaPagar($row3, $conexion);
?>
<div style="font-size: 10px;">
  <table>
    <tr style="border: none !important; border-bottom: none !important">
      <td class="w-50">
        <img src="../../img/logo.jpg" width="100px">
      </td>
      <td class="text-right w-50">
        Fecha: <?php echo $fechaPagar2; ?> <br>
      </td>
    </tr>
  </table>
  <h2 class="mb-0" align="center"> RELACION DEPOSITO BANCO</h2>
  <hr>
  <table class="mb-0" style="margin-bottom: 10px !important;">
    <tr style="border: none !important; border-bottom: none !important">
      <td class="fw-bold">
        Tipo de nómina: <span> <?php echo htmlspecialchars($nombre_nomina); ?> </span> - BANCO CARONI
      </td>
    </tr>
  </table>
</div>
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