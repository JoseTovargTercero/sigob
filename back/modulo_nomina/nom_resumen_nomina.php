<?php require_once '../sistema_global/conexion.php'; ?>
<!DOCTYPE html>
<html>

<head>
  <title>RESUMEN</title>
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
      border: none;
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
      padding: 2px 2px;
      font-size: 8px !important;
      border: none;
    }

    th {
      font-size: 9px !important;
      border: none;
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

    h2 {
      font-size: 14px !important;
    }

    .text-crimsom {
      color: crimson;
    }

    .text-left {
      text-align: left !important;
    }
  </style>
</head>

<body>
  <?php $correlativo = $_GET['correlativo']; ?>
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


    // Consulta a la base de datos para obtener el nombre de la nómina y la fecha de creación
    $sql4 = "SELECT nombre_nomina, creacion, identificador FROM peticiones WHERE correlativo='$correlativo'";
    $result4 = mysqli_query($conexion, $sql4);

    // Verificación y obtención del nombre de la nómina y la fecha de creación
    if ($result4 && mysqli_num_rows($result4) > 0) {
        $mostrar4 = mysqli_fetch_array($result4);
        $nombre_nomina = $mostrar4['nombre_nomina'];
        $creacion = $mostrar4['creacion'];
        $identificador = $mostrar4['identificador'];

        // Consulta para obtener el emp_cantidad para "Sueldo Base" usando JOIN
        $sql5 = "SELECT c.emp_cantidad
                 FROM conceptos_aplicados AS c
                 JOIN peticiones AS p ON c.nombre_nomina = p.nombre_nomina
                 WHERE c.nom_concepto = 'Sueldo Base' AND p.nombre_nomina = '$nombre_nomina'";
        $result5 = mysqli_query($conexion, $sql5);

        // Verificación y obtención del valor de emp_cantidad
        if ($result5 && mysqli_num_rows($result5) > 0) {
            $mostrar5 = mysqli_fetch_array($result5);
            $emp_cantidad = $mostrar5['emp_cantidad'];
        } else {
            $emp_cantidad = 0; // Valor por defecto si no se encuentra el registro
        }
    } else {
        // Manejar el caso en el que no se encuentra el registro en la tabla peticiones
        $nombre_nomina = '';
        $creacion = '';
        $emp_cantidad = 0; // Valor por defecto si no se encuentra el registro
    }


    $row3 = [
        'identificador' => $identificador, // Puede ser 's1', 'q1', 'fecha_unica', etc.
        'fecha_pagar' => $creacion, // Formato m-Y
        'nombre_nomina' => $nombre_nomina // Formato m-Y
    ];
    $fechaPagar2 = calcularFechaPagar($row3,$conexion);
?>


  <table>
    <tr>
      <td class="w-50">
        <img src="../../img/logo.jpg" width="100px">
      </td>
      <td class="text-right w-50">
        Fecha: <?php echo $creacion ?> <br>
        Correlativo Sigob: <?php echo htmlspecialchars($correlativo); ?>
      </td>
    </tr>
  </table>



  <h2 class="mb-0" align="center">
    RELACION NOMINA CONCEPTO
  </h2>


  <hr>

  <table class="mb-0">
    <tr>
      <td class="w-50 fw-bold">
        <span class="text-crimsom">NOMINA:</span>
        <span><?php echo $nombre_nomina ?></span>
      </td>
      <td class=" w-50 fw-bold">
        <span class="text-crimsom">Periodos de: <?php echo $fechaPagar2 ?></span>
      </td>
    </tr>
    <tr>
      <td class="w-50 fw-bold">
        <span class="text-crimsom">Nro trabajadores:</span>

        <span><?php
    if ($emp_cantidad >= 0 && $emp_cantidad <= 9) {
        echo str_pad($emp_cantidad, 4, '0', STR_PAD_LEFT);
    } elseif ($emp_cantidad >= 10 && $emp_cantidad <= 99) {
        echo str_pad($emp_cantidad, 4, '0', STR_PAD_LEFT);
    } elseif ($emp_cantidad >= 100 && $emp_cantidad <= 999) {
        echo str_pad($emp_cantidad, 4, '0', STR_PAD_LEFT);
    } elseif ($emp_cantidad >= 1000 && $emp_cantidad <= 9999) {
        echo $emp_cantidad;
    }
?>

          
        </span>

      </td>
      <td class=" w-50">
      </td>
    </tr>
  </table>
  <hr>









  <div>
    <table style="width: 100%;" border="1">
      <thead>
        <tr>
          <th class='text-left'>Cant</th>
          <th class='text-left'>Concepto</th>
          <th class='text-left'>Nombre del Concepto</th>
          <th class='text-center'>Partida Presupuestaria</th>
          <th class='text-center'>Monto Asignación</th>
          <th class='text-center'>Monto Deducción</th>
          <th class='text-center'>Monto Aporte</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Variables para sumar los totales
        $total_asignaciones = 0;
        $total_deducciones = 0;
        $total_aportes = 0;
        $total_total_pagar = 0;



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


          
            }

            // Función para obtener datos de conceptos y conceptos_aplicados
            function obtener_datos_conceptos($conexion, $concepto, $nombre_nomina)
            {
              if ($concepto == "SALARIO BASE") {
                $concepto == "Sueldo base";
              }
              $sql_conceptos = "SELECT nom_concepto, codigo_concepto, cod_partida FROM conceptos WHERE nom_concepto='$concepto'";
              $result_conceptos = mysqli_query($conexion, $sql_conceptos);
              $datos_conceptos = mysqli_fetch_assoc($result_conceptos);
               if ($concepto == "SALARIO BASE") {
                $sql_conceptos_aplicados = "SELECT emp_cantidad FROM conceptos_aplicados WHERE nom_concepto='Sueldo Base' AND nombre_nomina='$nombre_nomina'";
                $result_conceptos_aplicados = mysqli_query($conexion, $sql_conceptos_aplicados);
                $datos_conceptos_aplicados = mysqli_fetch_assoc($result_conceptos_aplicados);
              }else{
                $sql_conceptos_aplicados = "SELECT emp_cantidad FROM conceptos_aplicados WHERE nom_concepto='$concepto' AND nombre_nomina='$nombre_nomina'";
                $result_conceptos_aplicados = mysqli_query($conexion, $sql_conceptos_aplicados);
                $datos_conceptos_aplicados = mysqli_fetch_assoc($result_conceptos_aplicados);
              }
              

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
                echo "<tr>
                        <td class='text-left'>" . ($datos_conceptos['emp_cantidad'] ?? '') . "</td>
                        <td class='text-left'>" . ($datos_conceptos['codigo_concepto'] ?? '') . "</td>
                        <td class='text-left'>" . ($datos_conceptos['nom_concepto'] ?? $key) . "</td>
                        <td class='text-center'>" . ($datos_conceptos['cod_partida'] ?? '') . "</td>
                        <td class='text-center'>" . round($value, 2) . "</td>
                        <td class='text-center'></td>
                        <td class='text-center'></td>
                        </tr>";
              }
            }

            // Suma de deducciones
            if (!empty($deducciones)) {
              foreach ($deducciones as $key => $value) {
                $total_deducciones += $value;
                $datos_conceptos = obtener_datos_conceptos($conexion, $key, $nombre_nomina);
                echo "<tr>
                    <td class='text-left'>" . ($datos_conceptos['emp_cantidad'] ?? '') . "</td>
                    <td class='text-left'>" . ($datos_conceptos['codigo_concepto'] ?? '') . "</td>
                    <td class='text-left'>" . ($datos_conceptos['nom_concepto'] ?? $key) . "</td>
                    <td class='text-center'>" . ($datos_conceptos['cod_partida'] ?? $key) . "</td>
                    <td class='text-center'></td>
                    <td class='text-center'>" . round($value, 2) . "</td>
                    <td class='text-center'></td>
                  </tr>";
              }
            }

            // Suma de aportes
            if (!empty($aportes)) {
              foreach ($aportes as $key => $value) {
                $total_aportes += $value;
                $datos_conceptos = obtener_datos_conceptos($conexion, $key, $nombre_nomina);
                echo "<tr>
                  <td class='text-left'>" . ($datos_conceptos['emp_cantidad'] ?? '') . "</td>
                  <td class='text-left'>" . ($datos_conceptos['codigo_concepto'] ?? '') . "</td>
                  <td class='text-left'>" . ($datos_conceptos['nom_concepto'] ?? $key) . "</td>
                        <td class='text-center'>" . ($datos_conceptos['cod_partida'] ?? $key) . "</td>
                  <td class='text-center'></td>
                  <td class='text-center'></td>
                  <td class='text-center'>" . round($value, 2) . "</td>
                  </tr>";
              }
            }
          }
        } else {
          echo "<tr><td colspan='6'>No se encontraron peticiones</td></tr>";
        }

        // Mostrar total de asignaciones, deducciones y aportes
        $total_total =  $total_total_pagar;
        echo "<tr>
    <td class='text-left' colspan='4'>TOTAL DE ASIGNACIONES  DEDUCCIONES  Y APORTES:</td>
    <td class='text-center'><b>" . (empty($total_asignaciones) || $total_asignaciones == 0 ? "0.00" : round($total_asignaciones, 2)) . "</b></td>
    <td class='text-center'><b>" . (empty($total_deducciones) || $total_deducciones == 0 ? "0.00" : round($total_deducciones, 2)) . "</b></td>
    <td class='text-center'><b>" . (empty($total_aportes) || $total_aportes == 0 ? "0.00" : round($total_aportes, 2)) . "</b></td>
</tr>";


        // Cierre de la conexión
        mysqli_close($conexion);
        ?>
      </tbody>
    </table>

  </div>
</body>

</html>