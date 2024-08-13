<?php
require_once '../sistema_global/conexion.php';
?>
<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>35386bdf-4f85-4021-814b-8ac614b90a85</title>
  <meta name="author" content="SIGOB" />

  <style type="text/css">
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
  <?php
  function generarNetoInformacion($fecha_pagar, $id_empleado, $conn)
  {
    $query = "SELECT
                e.cedula AS cedula, 
                e.nombres AS nombres, 
                cg.cargo AS cargo, 
                e.fecha_ingreso AS fecha_de_ingreso, 
                '' AS fecha_de_egreso, 
                rp.asignaciones AS asignacion, 
                rp.deducciones AS deduccion, 
                rp.aportes AS aporte, 
                rp.total_pagar AS total_pagar, 
                rp.sueldo_base AS sueldo_base,
                rp.fecha_pagar AS fecha_pagar2,
                rp.nombre_nomina AS nombre_nomina,
                n.id AS id_nomina, 
                n.frecuencia AS frecuencia_nomina,
                e.banco AS centro_de_pago, 
                e.cod_cargo AS co_cargo, 
                e.cuenta_bancaria AS cuenta_bancaria 
            FROM 
                recibo_pago rp 
            JOIN 
                empleados e ON rp.id_empleado = e.id 
            JOIN 
                cargos_grados cg ON e.cod_cargo = cg.cod_cargo 
            LEFT JOIN 
                nominas n ON rp.nombre_nomina = n.nombre 
            WHERE 
                rp.fecha_pagar = :fecha_pagar 
                AND rp.id_empleado = :id_empleado";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':fecha_pagar', $fecha_pagar, PDO::PARAM_STR);
    $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



    $pagos = count($results);

    $datosRetornados = [];

    $conceptoQuery = "SELECT codigo_concepto FROM conceptos WHERE nom_concepto = :nom_concepto";
    $conceptoStmt = $conn->prepare($conceptoQuery);

    function obtenerCodigoConcepto($conceptoStmt, $nom_concepto)
    {
      $conceptoStmt->bindValue(':nom_concepto', $nom_concepto, PDO::PARAM_STR);
      $conceptoStmt->execute();
      $result = $conceptoStmt->fetch(PDO::FETCH_ASSOC);
      return $result ? $result['codigo_concepto'] : null;
    }

    $asignacionesTotales = [];
    $deduccionesTotales = [];
    $aportesTotales = [];
    $sueldoTotal = 0;

    foreach ($results as $row) {
      $sueldoTotal += $row['sueldo_base'];

      $asignaciones = json_decode($row['asignacion'], true);
      $deducciones = json_decode($row['deduccion'], true);
      $aportes = json_decode($row['aporte'], true);

      foreach ($asignaciones as $nom_concepto => $valor) {
        $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
        if (!isset($asignacionesTotales[$codigo_concepto])) {
          $asignacionesTotales[$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
        }
        $asignacionesTotales[$codigo_concepto]['valor'] += $valor;
      }

      foreach ($deducciones as $nom_concepto => $valor) {
        $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
        if (!isset($deduccionesTotales[$codigo_concepto])) {
          $deduccionesTotales[$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
        }
        $deduccionesTotales[$codigo_concepto]['valor'] += $valor;
      }

      foreach ($aportes as $nom_concepto => $valor) {
        $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
        if (!isset($aportesTotales[$codigo_concepto])) {
          $aportesTotales[$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
        }
        $aportesTotales[$codigo_concepto]['valor'] += $valor;
      }
    }

    $datosRetornados[] = [
      'cedula' => $results[0]['cedula'],
      'nombres' => $results[0]['nombres'],
      'cargo' => $results[0]['cargo'],
      'fecha_de_ingreso' => $results[0]['fecha_de_ingreso'],
      'fecha_de_egreso' => $results[0]['fecha_de_egreso'],
      'centro_de_pago' => $results[0]['centro_de_pago'],
      'cuenta_bancaria' => $results[0]['cuenta_bancaria'],
      'co_cargo' => $results[0]['co_cargo'],
      'id_nomina' => $results[0]['id_nomina'],
      'fecha_pagar2' => $results[0]['fecha_pagar2'],
      'frecuencia_nomina' => $results[0]['frecuencia_nomina'],
      'nombre_nomina' => $results[0]['nombre_nomina'],
      'sueldo' => $sueldoTotal,
      'asignaciones' => $asignacionesTotales,
      'deducciones' => $deduccionesTotales,
      'aportes' => $aportesTotales
    ];

    return ['datos' => $datosRetornados[0], 'pagos' => $pagos];
  }

  // Conexión a la base de datos usando PDO
  $conn = new PDO('mysql:host=localhost;dbname=sigob', 'root', '');
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Obtén los parámetros de la URL
  $cedula = isset($_GET['cedula']) ? $_GET['cedula'] : '';
  $fecha_pagar = isset($_GET['fecha_pagar']) ? $_GET['fecha_pagar'] : '';

  if ($cedula && $fecha_pagar) {
    $stmt = $conn->prepare("SELECT id FROM empleados WHERE cedula = :cedula");
    $stmt->bindValue(':cedula', $cedula, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
      $id_empleado = $result['id'];
      $netoDatos = generarNetoInformacion($fecha_pagar, $id_empleado, $conn);
    } else {
      echo "Empleado no encontrado.";
    }
  } else {
    echo "Parámetros 'cedula' y 'fecha_pagar' son requeridos.";
  }

  $conn = null;

  $nombres = $netoDatos['datos']['nombres'];
  $cedula = $netoDatos['datos']['cedula'];
  $cuenta_bancaria = $netoDatos['datos']['cuenta_bancaria'];
  $cargo = $netoDatos['datos']['cargo'];
  $centro_de_pago = $netoDatos['datos']['centro_de_pago'];
  $sueldo = $netoDatos['datos']['sueldo'];
  $co_cargo = $netoDatos['datos']['co_cargo'];
  $nombre_nomina = $netoDatos['datos']['nombre_nomina'];
  $id_nomina = $netoDatos['datos']['id_nomina'];
  $fecha_pagar2 = $netoDatos['datos']['fecha_pagar2'];
  $frecuencia_nomina = $netoDatos['datos']['frecuencia_nomina'];
  $pagos = $netoDatos['pagos'];

  $asignacionesTotal = $sueldo;
  $deduccionesTotal = 0;
  $aporteTotal = 0;
  ?>

  <p style="text-indent: 0pt;text-align: left;"><br /></p>
  <table style="width: 100%; padding: 5px; ">
    <tr>
      <td style="width: 20%; text-align: left; padding-left: 20px;">
        <img width="84" height="55" src="../../img/IMAGEN1.jpg" />
      </td>
      <td style="width: 60%; text-align: center;">
        <p class="s1">
          REPUBLICA BOLIVARIANA DE VENEZUELA<br> GOBERNACION DEL ESTADO INDIGENA DE AMAZONAS
        </p>
        <table style="width: 100%; margin-top: 10px;">
          <tr>
            <td style="width: 50%; text-align: left; padding-right: 55px;">
              <p class="st s1">CANCELACION A: <span class="s2"><?php echo $nombre_nomina ?></span></p>
              <p class="st s1">Nomina Nro : <span class="s2"><?php echo $id_nomina ?></span></p>
            </td>
            <td style="width: 50%; text-align: left;">
              <?php
              if ($frecuencia_nomina == "1") {
                ?>
                <p class="st s1"> SEMANAL / 2024</p>
                <?php
              } elseif ($frecuencia_nomina == "2") {
                ?>
                <p class="st s1"> QUINCENAL / 2024</p>
                <?php
              } else {
                ?>
                <p class="st s1"> MENSUAL / 2024</p>
                <?php
              }

              ?>
              <p class="st s1">Fecha Nomina: <span class="s2"><?php echo $fecha_pagar2 ?></span></p>
            </td>
          </tr>
        </table>
      </td>
      <td style="width: 20%; text-align: right; padding-right: 20px;">
        <img width="84" height="55" src="../../img/n_amazonas.jpeg" />
      </td>
    </tr>
  </table>



  <table class="w-100">
    <tr>
      <td class="b-t" colspan="4">
        <p class="s1" style="padding-top: 2pt;padding-left: 9pt;text-indent: 0pt;text-align: left;">FAVOR
          DIRIGIRSE PARA
          EL COBRO AL BANCO :</p>
      </td>
      <td class="b-t b-l" colspan="4" rowspan="2">
        <p><br /></p>
        <p class="s1" style="padding-left: 3pt;text-indent: 0pt;text-align: left;">FECHA DE EMISION : <span
            class="s2"><?php echo date('d-m-Y') ?></span></p>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <p class="s2" style="padding-left: 9pt;text-indent: 0pt;text-align: left;">
          <?php if ($centro_de_pago == "0102") {
            echo "VENEZUELA";
          } elseif ($centro_de_pago == "0175") {
            echo "BICENTENARIO";
          } elseif ($centro_de_pago == "0128") {
            echo "CARONI";
          } else {
            echo "TESORO";
          }
          ?>


        </p>
      </td>
      <td colspan="2">
        <p class="s2" style="padding-left: 9pt;text-indent: 0pt;text-align: left;"> <b>CTA :
          </b><?php echo $cuenta_bancaria ?>
        </p>
      </td>
    </tr>



    <!-- PRIMER CABEZAL -->

    <tr style="height:13pt">
      <td
        style="width:73pt;border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="2">
        <p class="s1 t-l">COD.CARGO:</p>
      </td>
      <td
        style="width:85pt;border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
        <p class="s1 t-l2">COD.TRABAJADOR</p>
      </td>
      <td
        style="width:83pt;border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
        <p class="s1  t-l">CEDULA . IDENTIDAD</p>
      </td>
      <td
        style="width:291pt;border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="3">
        <p class="s1  t-l">APELLIDOS Y NOMBRES</p>
      </td>
      <td class="b-t">
        <p class="s1" style="padding-top: 2pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">PAGOS:</p>
      </td>
    </tr>

    <!-- VALORES DE PRIMER CABEZAL -->
    <tr style="height:12pt">
      <td
        style="width:73pt;border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="2">
        <p class="s2" style="padding-top: 2pt;padding-left: 18pt;text-indent: 0pt;text-align: left;">
          <?php echo $co_cargo ?>
        </p>
      </td>
      <td
        style="width:85pt;border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
        <p class="s2 t-l2"><?php echo $cedula ?></p>
      </td>
      <td
        style="width:83pt;border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
        <p class="s2  t-l"><?php echo $cedula ?></p>
      </td>
      <td
        style="width:291pt;border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="3">
        <p class="s2" style="padding-top: 2pt;padding-left: 18pt;text-indent: 0pt;text-align: left;">
          <?php echo $nombres ?>
        </p>
      </td>
      <td class="b-t">
        <p class="s2" style="padding-top: 2pt;padding-right: 13pt;text-indent: 0pt;text-align: center;">
          <?php echo $pagos ?>
        </p>
      </td>
    </tr>

  </table>
  <table class="w-100">
    <tr>
      <th class="s1">ASIGNACIONES</th>
      <th class="s1">DEDUCCIONES</th>
    </tr>



    <tr>
      <td class="table-container w-50">
        <table>
          <tr>
            <th class="s1">COD.</th>
            <th class="s1">DESCRIPCION</th>
            <th class="s1">CUOTA</th>
            <th class="s1">SALDO</th>
          </tr>
          <?php
          $asignacionerow = "<tr>
    <td class='s2'>COD.</td>
    <td class='s2'>SUELDO</td>
    <td class='s2'>{$sueldo}</td>
    <td class='s2'></td>
</tr>";

          // Procesar asignaciones
          foreach ($netoDatos['datos']["asignaciones"] as $codigo => $asignacion) {
            $nom_concepto = $asignacion['nom_concepto'];
            $valor = $asignacion['valor'];

            $asignacionesTotal += $valor;

            $asignacionerow .= "<tr>
        <td class='s2'>{$codigo}</td>
        <td class='s2'>{$nom_concepto}</td>
        <td class='s2'>{$valor}</td>
        <td class='s2'></td>
    </tr>";
          }
          echo $asignacionerow;
          ?>

        </table>
      </td>
      <td class="table-container w-50">
        <table>
          <tr>
            <th class="s1">COD.</th>
            <th class="s1">DESCRIPCION</th>
            <th class="s1">CUOTA</th>
            <th class="s1">SALDO</th>
          </tr>

          <?php
          $deduccionesrow = '';
          foreach ($netoDatos['datos']["deducciones"] as $codigo => $deduccion) {
            $nom_concepto = $deduccion['nom_concepto'];
            $valor = $deduccion['valor'];

            $deduccionesTotal += $valor;

            $deduccionesrow .= "<tr>
        <td class='s2'>{$codigo}</td>
        <td class='s2'>{$nom_concepto}</td>
        <td class='s2'>{$valor}</td>
        <td class='s2'></td>
    </tr>";
          }
          echo $deduccionesrow;
          $aporterow = '';
          foreach ($netoDatos['datos']["aportes"] as $codigo => $aporte) {
            $nom_concepto = $aporte['nom_concepto'];
            $valor = $aporte['valor'];

            $aporteTotal += $valor;

            $aporterow .= "<tr>
        <td class='s2'>{$codigo}</td>
        <td class='s2'>{$nom_concepto}</td>
        <td class='s2'>{$valor}</td>
        <td class='s2'></td>
    </tr>";
          }
          echo $aporterow;
          ?>

        </table>
      </td>
    </tr>

  </table>



  <!-- SEGUNDO CABEZAL -->
  <table class="b-t">
    <tr>
      <th class="s1 w-50">TOTAL ASIGNACIONES <span class="s5"><?php echo $asignacionesTotal ?></span></th>
      <th class="s1 b-l">TOTAL DEDUCCIONES <span class="s5"><?php echo $deduccionesTotal ?></span></th>
      <th class="s1 b-l">NETO <span class="s5"><?php
      $netoTotal = $asignacionesTotal - $deduccionesTotal - $aporteTotal;

      echo round($netoTotal, 2) ?></span></th>
    </tr>

  </table>
  <table>

    <tr>
      <td class="b-t text-center" colspan="4">
        <p class="s1">UBICACION GEOGRAFICA</p>
      </td>
      <td class="b-t b-l text-center" colspan="4">
        <p class="s1">O B S E R V A C I O N E S</p>
      </td>
    </tr>
    <tr style="height:41pt">
      <td class="b-t b-r" colspan="4">
        <p class="s6 t-l">
          AMAZONAS
          <br>
          PTO AYACUCHO
        </p>
      </td>
      <td class="b-l b-t" colspan="4" rowspan="3">
        <p style="text-indent: 0pt;text-align: left;"><br /></p>
      </td>
    </tr>
    <tr style="height:13pt">
      <td
        style="border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="4">
        <p class="s1" style="padding-top: 2pt;padding-left: 91pt;text-indent: 0pt;text-align: left;">UBICACION
          ADMINISTRATIVA</p>
      </td>
    </tr>

    <tr style="height:41pt">
      <td
        style="border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="4">
        <p class="s6" style="padding-top: 1pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">SECRETARíA
          DE
          RECURSOS HUMANOS</p>
        <p class="s6"
          style="padding-left: 4pt;padding-right: 66pt;text-indent: 0pt;line-height: 14pt;text-align: left;">
          SECRETARIA
          Y COORDINACIóN RR-HH SECRETARíA DE RECURSOS HUMANOS</p>
      </td>
    </tr>
    <tr style="height:13pt">
      <td
        style="border-top-style:solid;border-top-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="4">
        <p class="s1  t-l">DENOMINACION DEL CARGO</p>
      </td>
      <td class=" b-t" colspan="4">
        <p class="s1 t-l2">DEPOSITADO EN:</p>
      </td>
    </tr>
    <tr style="height:27pt">
      <td colspan="4">
        <p class="s6 b-b" style="padding-top: 6pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">
          <?php echo $cargo ?>
        </p>
      </td>
      <td class="b-l b-t" colspan="4">
        <p style="text-indent: 0pt;text-align: left;"><br /></p>
      </td>
    </tr>
  </table>
  <p class="b-t" style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>

  <table class="w-100" cellspacing="0">
    <thead>

      <tr style="height:12pt">
        <th class="b-t w-50">
          <p class="s1 text-center">SECRETARIA EJECUTIVA GESTIÓN HUMANA</p>
        </th>
        <th class="b-t w-50">
          <p class="s1 text-center">FIRMA Y SELLO</p>
        </th>
      </tr>
    </thead>


    <tbody>
      <tr style="height:70pt">
        <td class="s1 text-center b-t">
          <p style="text-indent: 0pt;text-align: left;"><br /></p>

          LICDA. LEYDA BAUTISTA PONARE
        </td>
        <td class="b-t ">

        </td>
      </tr>
    </tbody>
  </table>
</body>

</html>
<style>
  .t-l {
    padding-top: 2pt;
    padding-left: 4pt;
    text-indent: 0pt;
    text-align: left;
  }

  .t-l2 {
    padding-top: 2pt;
    padding-left: 3pt;
    text-indent: 0pt;
    text-align: left;
  }
</style>