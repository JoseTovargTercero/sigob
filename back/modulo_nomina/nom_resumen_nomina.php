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
    RELACION NOMINA CONCEPTO
  </h2>


  <hr>

  <table class="mb-0">
    <tr>
      <td class="w-50 fw-bold">
        <span class="text-crimsom">NOMINA:</span>
        <span>003 - DIRECTORES (PERSONAL DE CONFIANZA)</span>
      </td>
      <td class=" w-50 fw-bold">
        <span class="text-crimsom">Periodos del: dd/mm/YY Al: dd/mm/YY</span>
      </td>
    </tr>
    <tr>
      <td class="w-50 fw-bold">
        <span class="text-crimsom">Nro trabajadores:</span>
        <span>0000</span>

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

              echo "<tr>
                      <td class='text-left'>$sueldo_base_cantidad</td>
                      <td class='text-left'>001</td>
                      <td class='text-left'>SUELDO BASE</td>
                      <td class='text-center'></td>
                      <td class='text-center'>" . round($total_total_pagar, 2) . "</td>
                      <td class='text-center'></td>
                      <td class='text-center'></td>
                    </tr>";
            }

            // Función para obtener datos de conceptos y conceptos_aplicados
            function obtener_datos_conceptos($conexion, $concepto, $nombre_nomina)
            {
              $sql_conceptos = "SELECT nom_concepto, codigo_concepto, cod_partida FROM conceptos WHERE nom_concepto='$concepto'";
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
                echo "<tr>
                        <td class='text-left'>" . ($datos_conceptos['emp_cantidad'] ?? '') . "</td>
                        <td class='text-left'>" . ($datos_conceptos['codigo_concepto'] ?? '') . "</td>
                        <td class='text-left'>" . ($datos_conceptos['nom_concepto'] ?? $key) . "</td>
                        <td class='text-center'>" . ($datos_conceptos['cod_partida'] ?? $key) . "</td>
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
        $total_total = $total_asignaciones + $total_total_pagar;
        echo "<tr>
          <td class='text-left' colspan='4'>TOTAL DE ASIGNACIONES  DEDUCCIONES  Y APORTES:</td>
          <td class='text-center'><b>" . round($total_total, 2) . "</b></td>
          <td class='text-center'><b>" . round($total_deducciones, 2) . "</b></td>
          <td class='text-center'><b>" . round($total_aportes, 2) . "</b></td>
          </tr>";

        // Cierre de la conexión
        mysqli_close($conexion);
        ?>
      </tbody>
    </table>

  </div>
</body>

</html>