<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

$stmt = mysqli_prepare($conexion, "SELECT * FROM `backups` ORDER BY id DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ultima_Act = $row['fecha']; // formato: dd-mm-YY
  }
} else {
  $ultima_Act = 'Nunca';
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Inicio</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="mb-0">Inicio</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row">


        <!-- [ worldLow section ] start -->
        <div class="col-lg-6 col-md-6">
          <div class="card">
            <div class="card-header">
              <h5>Estatus del sistema</h5>
            </div>
            <div class="card-body d-flex">
              <div class="m-a">
                <div class="file-upload width-338p">
                  <div class="upload-area">
                    <div class="icon d-flex text-center w-100">
                      <i class='bx bx-cloud-upload'></i>
                    </div>
                    <p class="text-title">Ultima copia de seguridad</p>
                    <div class="mb-3">
                      <p class="mb-0" id="ultima_Act"><?php echo $ultima_Act ?></p>
                      <small class="text-muted" id="timeAgo"></small>
                    </div>


                    <!--      <button id="respaldar-btn" class="browse-btn">Respaldar</button>
                      -->
                  </div>
                </div>
                <p class="text-sm text-muted mt-3 text-center width-338p">
                  * Después de 7 días se realizar una copia de seguridad automática
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 col-md-6">
          <div class="card">
            <div class="card-header">
              <h5>Empleados sin unidad relacionada</h5>
            </div>
            <div class="card-body d-flex">
              <?php
              // contar cuantos empleados hay en la tabla 'empleados'
              $sql = "SELECT COUNT(*) FROM empleados WHERE id_dependencia = '0'";
              $result = $conexion->query($sql);
              $cantidad_sinDp = $result->fetch_row()[0];

              $sql = "SELECT COUNT(*) FROM empleados WHERE id_dependencia != '0'";
              $result = $conexion->query($sql);
              $cantidad_conDp = $result->fetch_row()[0];



              ?>
              <div id="chartdiv"></div>

            </div>
          </div>
        </div>
      </div>
      <!-- [ worldLow section ] end -->


    </div>
    <!-- [ Main Content ] end -->
  </div>
  </div>
  <!-- [ Main Content ] end -->
  <style>
    #chartdiv {
      width: 100%;
      height: 500px;
    }
  </style>
  <script>
    let textarea = 't_area-2';
  </script>
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script src="../../src/assets/js/ajax_class.js"></script>

  <!-- Resources -->
  <script src="../../src/assets/js/amcharts5//index.js"></script>
  <script src="../../src/assets/js/amcharts5/percent.js"></script>
  <script src="../../src/assets/js/amcharts5/themes/Animated.js"></script>

  <script>
    am5.ready(function() {

      // Create root element
      // https://www.amcharts.com/docs/v5/getting-started/#Root_element
      var root = am5.Root.new("chartdiv");


      // Set themes
      // https://www.amcharts.com/docs/v5/concepts/themes/
      root.setThemes([
        am5themes_Animated.new(root)
      ]);


      // Create chart
      // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/
      var chart = root.container.children.push(am5percent.PieChart.new(root, {
        layout: root.verticalLayout,
        innerRadius: am5.percent(50)
      }));


      // Create series
      // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Series
      var series = chart.series.push(am5percent.PieSeries.new(root, {
        valueField: "value",
        categoryField: "category",
        alignLabels: false
      }));

      series.labels.template.setAll({
        textType: "circular",
        centerX: 0,
        centerY: 0
      });


      // Set data
      // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Setting_data
      series.data.setAll([{
          value: <?php echo $cantidad_sinDp ?>,
          category: "Sin unidad relacionada"
        },
        {
          value: <?php echo $cantidad_conDp ?>,
          category: "Con unidad"
        }
      ]);


      // Create legend
      // https://www.amcharts.com/docs/v5/charts/percent-charts/legend-percent-series/
      var legend = chart.children.push(am5.Legend.new(root, {
        centerX: am5.percent(50),
        x: am5.percent(50),
        marginTop: 15,
        marginBottom: 15,
      }));

      legend.data.setAll(series.dataItems);


      // Play initial series animation
      // https://www.amcharts.com/docs/v5/concepts/animations/#Animation_of_series
      series.appear(1000, 100);

    }); // end am5.ready()






    var actualizados = "<?php echo $ultima_Act ?>"
    actualizados = actualizados.split(',')


    // cuando cargue la pagina, agrega 'pc-sidebar-hide' a .pc-sidebar
    document.addEventListener('DOMContentLoaded', function() {
      if ("<?php echo $ultima_Act ?>" == 'Nunca') {
        actualizados = []
        verificarTablasActualizadas('nuevo')
      } else {
        // verificar si han pasado mas de 7 dias desde la ultima actualizacion
        var fecha = "<?php echo $ultima_Act ?>";
        var fecha = fecha.split('-');
        var fecha = new Date(fecha[2], fecha[1] - 1, fecha[0]);
        var hoy = new Date();
        var dias = Math.floor((hoy - fecha) / (1000 * 60 * 60 * 24));
        if (dias >= 7) {
          actualizados = []
          verificarTablasActualizadas('nuevo')
        } else {
          verificarTablasActualizadas('existente')
        }
        $('#timeAgo').html('Hace ' + dias + ' dias')
      }

    });



    let updates = ['empleados', 'cargos_grados', 'empleados_por_grupo', 'nominas_grupos', 'dependencias']

    /**
     * This function is responsible for updating the data.
     * It hides the sidebar, prepares the data object for an AJAX request,
     * sends the AJAX request to add the employee, and handles the success and error responses.
     */
    function actualizar(tabla, condicion) {
      $('#cargando').show()

      document.querySelector('.pc-sidebar').classList.add('pc-sidebar-hide');
      document.querySelector('.pc-sidebar-collapse').classList.add('hide');
      // Prepare data object for AJAX request

      $.ajax({
        url: '../../back/modulo_nomina/copia_seguridad.php',
        type: 'POST',
        data: {
          tabla: tabla,
          condicion: condicion
        },
        success: function(response) {
          try {
            if (typeof response !== 'object') {
              response = JSON.parse(response);
            }
          } catch (e) {
            console.error('Error al parsear la respuesta JSON:', e);
            toast_s('error', 'Respuesta del servidor no válida');
            return;
          }

          if (response.status === 'ok') {
            if (typeof onSuccess === 'function') {
              onSuccess(response);
            }
          } else {
            console.log(response.message);
            toast_s('error', 'Error: ' + response.message);
            if (typeof onError === 'function') {
              onError(response);
            }
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
          try {
            const response = JSON.parse(jqXHR.responseText);
            console.error('Error del servidor:', response.mensaje, 'Archivo:', response.archivo, 'Línea:', response.linea);
            toast_s('error', 'Error del servidor: ' + response.mensaje);

          } catch (e) {
            console.error('Error al parsear la respuesta de error:', e);
            toast_s('error', 'Error en la solicitud: ' + textStatus);
          }
          if (typeof onError === 'function') {
            onError({
              textStatus,
              errorThrown
            });
          }
        },
        complete: function(response) {
          console.log(response)
        }
      });
    }




    function verificarTablasActualizadas(condicion) {
      /* updates.forEach(element => {
         // verificar si element esta en el array 'actualizados'
         if (actualizados.indexOf(element) != -1) {
           //eliminar element de updates
           actualizar(element, condicion)
         }
       });*/
    }


    //    document.getElementById('respaldar-btn').addEventListener('click', verificarTablasActualizadas)
  </script>


</body>

</html>