<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';


$id = $_GET["id"];

// INFORMACION GENERAR DE LA ASIGNACION
$stmt = $conexion->prepare("SELECT * FROM `asignacion_ente` AS AE 
LEFT JOIN entes ON entes.id = AE.id_ente
WHERE AE.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$info_asignacion = $stmt->get_result()->fetch_assoc();
$stmt->close();


// INFORMACION DE LA DISTRIBUCION POIR ACTIVIDADES
$stmt = mysqli_prepare($conexion, 'SELECT * FROM `distribucion_entes` AS DE
LEFT JOIN entes_dependencias AS ED ON ED.id = DE.actividad_id
WHERE DE.id_asignacion = ? ORDER BY ED.actividad');
$stmt->bind_param('s', $id);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $row['distribucion'] = json_decode($row['distribucion'], true);
    $data[$row['actividad']] = $row;
    $id_ejercicio = $row['id_ejercicio'];
  }
}
$stmt->close();



// INFORMACION COMPLEMENTARIA DE LAS PARTIDAS USADAS


$sql = "SELECT dp.*, 
p.partida,
pl_proyectos.proyecto_id,
ps.sector AS sector_nombre, 
ps.denominacion AS sector_nombre_completo, 
pp.programa AS programa_nombre, 
pp.denominacion AS programa_nombre_completo
FROM distribucion_presupuestaria dp
JOIN pl_sectores ps ON dp.id_sector = ps.id
JOIN pl_programas pp ON dp.id_programa = pp.id
LEFT JOIN pl_proyectos ON dp.id_proyecto = pl_proyectos.id
JOIN partidas_presupuestarias p ON dp.id_partida = p.id
WHERE dp.id_ejercicio=$id_ejercicio
";

$result = $conexion->query($sql);

$distribuciones = [];

while ($row = $result->fetch_assoc()) {
  $distribuciones[$row['id']] = $row;
}




//[{"id_distribucion":"9","monto":200},{"id_distribucion":"10","monto":200}]
$titulo = 'Distribución por entes';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title><?php echo $titulo ?></title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <style>
    table tr td:nth-child(1),
    table tr td:nth-child(2),
    table tr th:nth-child(1),
    table tr th:nth-child(2) {
      text-align: center !important;
      /* Alineación al centro, puedes cambiarla a 'left' o 'right' */
    }
  </style>

</head>
<?php require_once '../includes/header.php' ?>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  <?php require_once '../includes/menu.php' ?>
  <!-- [ MENU ] -->

  <?php require_once '../includes/top-bar.php' ?>
  <!-- [ top bar ] -->
  <!-- [ Main Content ] start -->
  <div class="pc-container">
    <div class="pc-content">
      <div class=" d-flex justify-content-between">
        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> <?php echo $titulo ?>
        </h4>
      </div>
      <div class="row ">



        <div class="col-lg-12">


          <div class=" d-flex justify-content-between">
            <div></div>

            <ul class="nav nav-pills mb-3 text-end" id="pills-tab" role="tablist">
            </ul>
          </div>


          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto ">
                  <h5 class="mb-0"><?php echo $info_asignacion['ente_nombre'] ?></h5>
                  <p id="actividad"></p>
                </div>
                <div class="mt-2 card-body">

                  <table class="table" id="table">
                    <thead>
                      <tr>
                        <th style="width: 5%;">#</th>
                        <th>S/P/P</th>
                        <th>Partida</th>
                        <th>Asignación</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- [ Main Content ] -->
      <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
      <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
      <script src="../../src/assets/js/pcoded.js"></script>
      <script src="../../src/assets/js/plugins/feather.min.js"></script>
      <script src="../../src/assets/js/notificaciones.js"></script>
      <script src="../../src/assets/js/main.js"></script>
      <script src="../../src/assets/js/ajax_class.js"></script>

      <script src="../../src/assets/js/amcharts5/index.js"></script>
      <script src="../../src/assets/js/amcharts5/themes/Animated.js"></script>
      <script src="../../src/assets/js/amcharts5/xy.js"></script>

      <script>
        var DataTable = $("#table").DataTable({
          language: lenguaje_datat
        });

        const distribucionData = <?php echo json_encode($data); ?>;
        const distribucionPartida = <?php echo json_encode($distribuciones); ?>;


        // CREAR BOTONES PILLS
        let ul_pills = document.getElementById('pills-tab')
        for (const key in distribucionData) {
          if (Object.prototype.hasOwnProperty.call(distribucionData, key)) {
            const item = distribucionData[key];
            ul_pills.innerHTML += ` <li class="nav-item pointer"  data-id-actividad="${item.actividad}" ><a class="nav-link ${item.actividad == '51' ? 'active' : ''}" data-bs-toggle="pill" role="tab">Actividad ${item.actividad}</a></li>`
            if (item.actividad == '51') {
              info_actividades('51')
            }
          }
        }

        // Intercambiar entre las tablas
        document.addEventListener('click', function(event) {
          if (event.target.closest('.nav-item')) {
            const id = event.target.closest('.nav-item').getAttribute('data-id-actividad');
            info_actividades(id)
          }
        });



        // CARGAR LA INFO DE LA ACTIVIDAD, TABLA Y NOMBRE DEL ENTE
        function info_actividades(actividad) {
          const ente_nombre = distribucionData[actividad].ente_nombre
          document.getElementById('actividad').innerHTML = `<b class="text-info">Actividad ${actividad}</b> - ${ente_nombre}`

          let info_tabla = []
          let count = 1;
          if (Array.isArray(distribucionData[actividad].distribucion)) {
            DataTable.clear()
            distribucionData[actividad].distribucion.forEach(distrib => {
              let proyecto = distribucionPartida[distrib.id_distribucion]['proyecto_id'] ?? '00'
              let spp = distribucionPartida[distrib.id_distribucion]['sector_nombre'] + '.' + distribucionPartida[distrib.id_distribucion]['programa_nombre'] + '.' + proyecto

              info_tabla.push([count++, spp, distribucionPartida[distrib.id_distribucion]['partida'], distrib.monto + 'Bs'])
            });
            DataTable.rows.add(info_tabla).draw()
          }
        }


        const url_back = '../../back/modulo_pl_formulacion/form_programas_back.php'
      </script>

</body>

</html>