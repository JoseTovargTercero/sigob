<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

function contar($table, $condicion)
{
  global $conexion;

  $stmt = $conexion->prepare("SELECT count(*) FROM $table WHERE $condicion");
  $stmt->execute();
  $row = $stmt->get_result()->fetch_row();
  $galTotal = $row[0];

  return $galTotal;
}


if (isset($_GET["ejercicio"])) {
  $annio = $_GET["ejercicio"];
} else {
  $annio = date('Y');
}


$stmt = mysqli_prepare($conexion, "SELECT * FROM `ejercicio_fiscal`  WHERE ano = ?");
$stmt->bind_param('s', $annio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ejercicio_fiscal = $row['id']; // formato: dd-mm-YY
    $situado = $row['situado']; // formato: dd-mm-YY
    $status = $row['status']; // formato: dd-mm-YY
  }
} else {
  $ejercicio_fiscal = 'No';
  $situado = 0; // formato: dd-mm-YY
}
$stmt->close();


$stmt = mysqli_prepare($conexion, "SELECT SUM(monto_inicial) AS total FROM distribucion_presupuestaria WHERE id_ejercicio=?");
$stmt->bind_param('i', $ejercicio_fiscal);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $distribuido = $row['total'];
  }
} else {
  $distribuido = 0;
}
$stmt->close();

$stmt = mysqli_prepare($conexion, "SELECT * FROM plan_inversion WHERE id_ejercicio=?");
$stmt->bind_param('i', $ejercicio_fiscal);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $plan_inversion_id = $row['id'];
    $plan_inversion = $row['monto_total'];
    $proyectos = contar("proyecto_inversion", 'id_plan=' . $plan_inversion_id);
  }
} else {
  $plan_inversion = 'no';
  $proyectos = 0;
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
  <script src="../../src/assets/js/checkRemoteDB.js"></script>



  <style>
    td {
      padding: 7px !important;
    }

    .h-15 {
      min-height: 15vh !important;
    }

    .img-ng {
      height: 54vh;
      width: min-content;
      margin: auto;
      opacity: 0.2;
    }

    .top-col>.card {
      height: 192px;
    }

    #situado_h2 {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-size: 3em;
      /* Tamaño inicial */
    }

    h5 {
      font-size: 1rem !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
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

  <style>
    #table td,
    #table th {
      text-align: center;
    }
  </style>


  <!-- [ Main Content ] start -->
  <div class="pc-container">
    <div class="pc-content">




      <div class=" d-flex justify-content-between">

        <?php
        $y_d = date('Y');
        $y_d1 = $y_d - 1;
        $y_d2 = date('Y') + 1;
        ?>
        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> Ejercicio fiscal <?php echo $y_d; ?>
        </h4>

        <div class="d-flex gap-1">
          <p> <a href="">Años anteriores</a>... </p>

          <p><a class="pointer <?php echo ($annio == $y_d1 ? 'text-decoration-underline text-primary' : 'text-dark') ?>"
              href="?ejercicio=<?php echo $y_d1 ?>"><?php echo $y_d1 ?></a></p>
          <p><a class="pointer <?php echo ($annio == $y_d ? 'text-decoration-underline text-primary' : 'text-dark') ?> "
              href="?ejercicio=<?php echo $y_d ?>"><?php echo $y_d ?></a></p>
          <p><a href="?ejercicio=<?php echo $y_d2 ?>"
              class="pointer <?php echo ($annio == $y_d2 ? 'text-decoration-underline text-primary' : 'text-dark') ?>"><?php echo $y_d2 ?></a>
          </p>

        </div>
      </div>



      <!-- CONTENIDO -->
      <div class="row ">


        <div class="col-lg-4">

          <div class="card bg-brand-color-3 bitcoin-wallet h-15">
            <div class="card-body ">
              <h5 class="text-white mb-2">Situado</h5>
              <h3 class="text-white mb-2 f-w-300" id="situado_h2">
                <?php echo number_format($situado, 0, '.', ',') ?> Bs
              </h3>

              <?php if ($ejercicio_fiscal != 'No') { ?>
                <span class="text-white d-block">

                  <?php echo number_format($distribuido, 0, '.', '.') ?> Bs
                  (<?php echo number_format($distribuido * 100 / $situado, 2, '.', '.') . '%'; ?> por partidas)
                </span> <i class="fab fa-btc f-70 text-white"></i>
              <?php } else {
                echo '<span class="text-white d-block">El ejercicio fiscal no fue creado</span>';
              } ?>
            </div>
          </div>



          <div id="card_dozavos" class="card bg-brand-color-1 visitor">
            <div class="card-body text-center">
              <h5 class="text-white m-0">DOZAVOS</h5>
              <h3 class="text-white m-t-20 f-w-300" id="contador_dozavos">0</h3><span id="mensaje_dozavo" class="text-white">Solicitudes dozavo pendientes</span>
            </div>
          </div>



          <div class="card mb-3">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto">
                  <h5 class="mb-3">Gastos por trimestre</h5>
                </div>
                <div id="grafico_gastos_trimestre" style="width: 100%; height: 60vh;"></div>
                <div class="overmark"></div>
              </div>
            </div>
          </div>



        </div>


        <div class="col-lg-8">

          <div class="card mb-3">
            <div class="card-body">
              <h5 class="mb-2">Gastos</h5>
              <div id="chartdiv_gastos" style="width: 100%; height: 36vh;"></div>
              <div class="overmark"></div>
            </div>
          </div>
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0">
                    <button id="btn-vista-graf_table" class="btn btn-icon btn-primary avtar-s mb-0 me-1"
                      style="border-radius: 5px;">
                      <i class='bx bx-bar-chart-alt-2'></i>
                    </button>
                    Disponibilidad presupuestaria
                  </h5>

                  <div style="width: 30%;">
                    <select class="form-control form-control-sm" id="select_tipo">
                      <option value="sector">Sector</option>
                      <option value="programa">Programa</option>
                      <option value="actividad">Actividad</option>
                      <option value="proyecto">Proyecto</option>
                      <option value="partida">Partida</option>
                      <option value="partida_programa">Partida (Por Sector y Programa)</option>
                    </select>
                  </div>
                </div>

                <?php
                if ($ejercicio_fiscal == 'No') {
                ?>

                  <img src="../../src/assets/images/icons-png/no_grafico.jpg" class="img-ng" alt="Sin grafico">

                <?php
                } else {

                ?>


                  <section id="vista-grafico">
                    <div id="grafico_2" style="width: 100%; height: 50vh;"></div>
                  </section>
                  <section id="vista-tabla" class="hide mt-2 card-body">

                    <table class="table" id="table">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Indicador</th>
                          <th>Total</th>
                          <th>Disponibilidad</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                      </tbody>
                    </table>
                  </section>

                <?php
                }
                ?>


              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="col-lg-12">
        <div class="card" style="min-height: 165px;">
          <div class="card-header">
            <div class="card-title mb-auto d-flex justify-content-between">
              <h5 class="mb-0">
                Disponibilidad presupuestaria por partidas
              </h5>
            </div>
          </div>
          <div class=" card-body">


            <table class="table" id="table-2">
              <thead>
                <tr>
                  <th>Partida</th>
                  <th>Asignación inicial</th>
                  <th>Disponibilidad</th>
                </tr>
              </thead>
              <tbody>

                <?php


                $stmt = mysqli_prepare($conexion, "SELECT * FROM `distribucion_presupuestaria` AS dp
                  LEFT JOIN partidas_presupuestarias AS pp ON pp.id = dp.id_partida
                  WHERE id_ejercicio = ? AND monto_actual > 0");
                $stmt->bind_param('s', $ejercicio_fiscal);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    echo '
                      <tr>
                      <td>' . $row['partida'] . '<br>
                      <small class="text-muted">' . substr($row['descripcion'], 0, 35) . '</small>...
                      </td>
                      <td class="text-center">' . number_format($row['monto_inicial'], 0, '.', '.') . ' Bs</td>
                      <td class="text-center">' . number_format($row['monto_actual'], 0, '.', '.') . ' Bs</td>
                      </tr>';
                  }
                }
                $stmt->close();


                ?>
              </tbody>
            </table>

          </div>
        </div>
      </div>




    </div>


    <?php
    if ($ejercicio_fiscal == 'No') {
    ?>

      <div class="dialogs">
        <div class="dialogs-content " style="width: 35%;">
          <span class="close-button">×</span>
          <h5 class="mb-1">Nuevo ejercicio fiscal</h5>
          <hr>
          <div class="card-body">
            <form id="dataEjercicio">
              <div class="mb-3">
                <label for="situado" class="form-label">Situado constitucional</label>
                <input type="number" id="situado" name="situado" class="form-control"
                  placeholder="Presupuesto asignado para el ejercicio fiscal <?php echo $annio ?>">
              </div>
              <div class="mb-2 text-end">
                <button type="submit" class="btn btn-primary">Registrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php
    } else {
    ?>
      <div class="dialogs">
        <div class="dialogs-content " style="width: 35%;">
          <span class="close-button">×</span>
          <h5 class="mb-1">Nuevo plan de inversión</h5>
          <hr>
          <div class="card-body">
            <form id="dataEjercicio_2">
              <div class="mb-3">
                <label for="monto" class="form-label">Monto del plan de inversión</label>
                <input type="number" id="monto" name="monto" class="form-control"
                  placeholder="Presupuesto asignado para el plan de inversión">
              </div>
              <div class="mb-2 text-end">
                <button type="submit" class="btn btn-primary">Registrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php
    }
    ?>
    <!-- [ Main Content ] end -->
    <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
    <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
    <script src="../../src/assets/js/pcoded.js"></script>
    <script src="../../src/assets/js/plugins/feather.min.js"></script>
    <script src="../../src/assets/js/notificaciones.js"></script>
    <script src="../../src/assets/js/main.js"></script>
    <script src="../../src/assets/js/ajax_class.js"></script>

    <script src="../../src/assets/js/amcharts5/index.js"></script>
    <script src="../../src/assets/js/amcharts5/percent.js"></script>
    <script src="../../src/assets/js/amcharts5/themes/Animated.js"></script>
    <script src="../../src/assets/js/amcharts5/xy.js"></script>



    <script>
      let id_ejercicio_fiscal = "<?php echo $ejercicio_fiscal ?>"

      am5.ready(function() {
        // Crear elemento raíz
        var root = am5.Root.new("grafico_gastos_trimestre");

        // Establecer temas
        root.setThemes([am5themes_Animated.new(root)]);

        // Crear gráfico
        var chart = root.container.children.push(
          am5xy.XYChart.new(root, {
            panX: false,
            panY: false,
            paddingLeft: 0,
            wheelX: "panX",
            wheelY: "zoomX",
            layout: root.verticalLayout,
          })
        );

        // Agregar leyenda
        var legend = chart.children.push(
          am5.Legend.new(root, {
            centerX: am5.p50,
            x: am5.p50,
          })
        );

        // Datos iniciales vacíos
        var data = [];

        // Crear ejes
        var xRenderer = am5xy.AxisRendererX.new(root, {
          cellStartLocation: 0.1,
          cellEndLocation: 0.9,
          minorGridEnabled: true,
        });

        var xAxis = chart.xAxes.push(
          am5xy.CategoryAxis.new(root, {
            categoryField: "year",
            renderer: xRenderer,
            tooltip: am5.Tooltip.new(root, {}),
          })
        );

        xRenderer.grid.template.setAll({
          location: 1,
        });

        var yAxis = chart.yAxes.push(
          am5xy.ValueAxis.new(root, {
            renderer: am5xy.AxisRendererY.new(root, {
              strokeOpacity: 0.1,
            }),
          })
        );

        // Función para agregar series
        function makeSeries(name, fieldName) {
          var series = chart.series.push(
            am5xy.ColumnSeries.new(root, {
              name: name,
              xAxis: xAxis,
              yAxis: yAxis,
              valueYField: fieldName,
              categoryXField: "year",
            })
          );

          series.columns.template.setAll({
            tooltipText: "{name}, {categoryX}: {valueY}",
            width: am5.percent(90),
            tooltipY: 0,
            strokeOpacity: 0,
          });

          series.appear();

          series.bullets.push(function() {
            return am5.Bullet.new(root, {
              locationY: 0,
              sprite: am5.Label.new(root, {
                text: "{valueY}",
                fill: root.interfaceColors.get("alternativeText"),
                centerY: 0,
                centerX: am5.p50,
                populateText: true,
              }),
            });
          });

          legend.data.push(series);
        }

        // Llamada AJAX para obtener datos
        function obtenerGastosTrimestre() {
          $.ajax({
              url: "../../back/modulo_ejecucion_presupuestaria/pre_gastos.php",
              type: "POST",
              dataType: "json",
              contentType: "application/json",
              data: JSON.stringify({
                accion: "obtener_trimestre",
                id_ejercicio: id_ejercicio_fiscal
              }),
            })
            .done(function(resultado) {
              const info = resultado.data;

              // Actualizar datos
              data.push({
                year: "Trimestres",
                europe: info.T1,
                namerica: info.T2,
                asia: info.T3,
                lamerica: info.T4,
              });

              // Establecer datos en los ejes y series
              xAxis.data.setAll(data);
              chart.series.each(function(series) {
                series.data.setAll(data);
              });
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
              console.error("Error en la solicitud:", textStatus, errorThrown);
            });
        }

        // Llamar a la función para obtener datos
        obtenerGastosTrimestre();

        // Crear las series
        makeSeries("T1", "europe");
        makeSeries("T2", "namerica");
        makeSeries("T3", "asia");
        makeSeries("T4", "lamerica");

        // Animación del gráfico
        chart.appear(1000, 100);
      });

      am5.ready(function() {
        // Crear el elemento raíz
        var root = am5.Root.new("chartdiv_gastos");

        // Establecer temas
        root.setThemes([
          am5themes_Animated.new(root)
        ]);

        // Crear gráfico
        var chart = root.container.children.push(am5xy.XYChart.new(root, {
          panX: true,
          panY: true,
          wheelX: "panX",
          wheelY: "zoomX",
          pinchZoomX: true,
          paddingLeft: 0
        }));

        // Agregar cursor
        var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
          behavior: "none"
        }));
        cursor.lineY.set("visible", false);

        // Crear ejes
        var xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
          baseInterval: {
            timeUnit: "day",
            count: 1
          },
          renderer: am5xy.AxisRendererX.new(root, {
            minorGridEnabled: true
          }),
          tooltip: am5.Tooltip.new(root, {})
        }));

        var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
          renderer: am5xy.AxisRendererY.new(root, {})
        }));

        // Crear serie
        var series = chart.series.push(am5xy.LineSeries.new(root, {
          name: "Gastos",
          xAxis: xAxis,
          yAxis: yAxis,
          valueYField: "value",
          valueXField: "date",
          tooltip: am5.Tooltip.new(root, {
            labelText: "{valueY}"
          })
        }));

        // Array de datos inicial
        var data = [];

        // Configurar la serie con el array vacío
        series.data.setAll(data);

        // Función para agregar datos al array
        function addData(fecha, gasto) {
          // Convertir la fecha recibida (formato "YYYY-MM-DD") a un timestamp
          var date = new Date(fecha).getTime();

          // Agregar el nuevo dato al array
          data.push({
            date: date + 86400000,
            value: parseFloat(gasto)
          });

          // Actualizar la serie con los nuevos datos
          series.data.setAll(data);
        }


        function obtenerGastos() {

          $.ajax({
              url: '../../back/modulo_ejecucion_presupuestaria/pre_gastos.php',
              type: 'POST',
              dataType: 'json', // Cambiado a 'json'
              contentType: 'application/json',
              data: JSON.stringify({
                accion: 'obtener',
                id_ejercicio: id_ejercicio_fiscal
              }),
            })
            .done(function(resultado) {
              console.log(resultado)

              try {
                var data = [];
                // Procesar el resultado
                resultado.forEach(element => {
                  addData(element.fecha, element.monto_gasto);
                });
              } catch (error) {
                console.error('Error procesando los datos:', error);
              }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
              console.error('Error en la solicitud:', textStatus, errorThrown);
              //  alert('Hubo un problema al obtener los datos. Por favor, inténtalo de nuevo.');
            })
            .always(function(res) {});
        }

        obtenerGastos()
        series.appear(1000);
        chart.appear(1000, 100);
      }); // end am5.ready()




      $.ajax({
          url: "../../back/modulo_ejecucion_presupuestaria/pre_solicitud_dozavos_api.php", // Ruta al endpoint
          type: "GET", // Tipo de solicitud
          dataType: "json", // Automáticamente parsea el JSON
          data: {
            id_ejercicio: id_ejercicio_fiscal, // Parámetros de la solicitud
          },
        })
        .done(function(response) {

          console.log(response)
          // Verifica si el estado de la respuesta es 200
          if (response.status === 200) {
            // Calcula el total de elementos en el array "success"
            const total = response.success?.length || 0;
            document.getElementById('contador_dozavos').innerHTML = total;
          } else {
            document.getElementById('mensaje_dozavo').innerHTML = 'Respuesta inesperada del servidor'
            document.getElementById('card_dozavos').classList.remove('bg-brand-color-1');
            document.getElementById('card_dozavos').classList.add('bg-brand-color-2');
          }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
          document.getElementById('mensaje_dozavo').innerHTML = 'Error en la solicitud'
          document.getElementById('card_dozavos').classList.remove('bg-brand-color-1');
          document.getElementById('card_dozavos').classList.add('bg-brand-color-2');
        });



      function verificarMonto(monto) {
        // verifica que situado sea un numero
        if (isNaN(monto)) {
          toast_s('error', 'El campo debe ser un número.');
          return false;
        }
        // verifica que situado sea mayor a 0
        if (monto <= 0) {
          toast_s('error', 'El campo debe ser mayor a 0.');
          return false;
        }
        // verifica que situado sea un número entero
        if (monto % 1 !== 0) {
          toast_s('error', 'El campo debe ser un número entero.');
          return false;
        }

        return true
      }




      // Ajustar el tamaño del texto con la cantidad del situado para el card con el bg-info
      function adjustFontSize() {
        const situado = document.getElementById('situado_h2');
        let fontSize = parseFloat(window.getComputedStyle(situado, null).getPropertyValue('font-size'));

        // Mientras el h2 se desborde, reduce el tamaño de fuente
        while (situado.scrollWidth > situado.offsetWidth && fontSize > 10) { // Evitar reducir mucho el tamaño
          fontSize -= 1; // Ajusta el decremento según lo necesites
          situado.style.fontSize = fontSize + 'px';
        }
      }
      document.addEventListener('DOMContentLoaded', adjustFontSize);
      // Si el contenido cambia, volver a ajustar
      window.onresize = adjustFontSize;
      // End: Ajustar el tamaño del texto con la cantidad del situado para el card con el bg-info




      // DATA TABLE
      var DataTable = $("#table").DataTable({
        language: lenguaje_datat
      });
      var DataTable_2 = $("#table-2").DataTable({
        language: lenguaje_datat
      });
      // DATA TABLE






      // MOSTAR OCULTAR tabla y grafico principal
      function setVistaGT() {
        // Obtener los elementos de la vista del gráfico y de la tabla
        const vistaGrafico = document.getElementById('vista-grafico');
        const vistaTabla = document.getElementById('vista-tabla');
        const botonIcono = document.querySelector('#btn-vista-graf_table i');

        // Alternar la clase 'hide' entre la vista de gráfico y tabla
        if (vistaGrafico.classList.contains('hide')) {
          vistaGrafico.classList.remove('hide');
          vistaTabla.classList.add('hide');
          // Cambiar el icono a gráfico
          botonIcono.className = 'bx bx-bar-chart-alt-2';
        } else {
          vistaGrafico.classList.add('hide');
          vistaTabla.classList.remove('hide');
          // Cambiar el icono a tabla
          botonIcono.className = 'bx bx-table';
        }
      }
      // End: MOSTAR OCULTAR tabla y grafico principal
      document.getElementById('btn-vista-graf_table').addEventListener('click', setVistaGT)




      // GRAFICO 2

      <?php if ($ejercicio_fiscal != 'No') { ?>
        var root = am5.Root.new("grafico_2");

        root.setThemes([
          am5themes_Animated.new(root)
        ]);

        var chart = root.container.children.push(am5xy.XYChart.new(root, {
          panX: true,
          panY: false,
          wheelX: "panX",
          wheelY: "zoomX",
          paddingLeft: 0,
          layout: root.verticalLayout
        }));

        chart.set("scrollbarX", am5.Scrollbar.new(root, {
          orientation: "horizontal"
        }));

        var data = [];

        var xRenderer = am5xy.AxisRendererX.new(root, {
          minGridDistance: 70,
          minorGridEnabled: true
        });

        var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
          categoryField: "country",
          renderer: xRenderer,
          tooltip: am5.Tooltip.new(root, {
            themeTags: ["axis"],
            animationDuration: 200
          })
        }));

        xRenderer.grid.template.setAll({
          location: 1
        })

        xAxis.data.setAll(data);

        var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
          min: 0,
          renderer: am5xy.AxisRendererY.new(root, {
            strokeOpacity: 0.1
          })
        }));

        var series0 = chart.series.push(am5xy.ColumnSeries.new(root, {
          name: "Income",
          xAxis: xAxis,
          yAxis: yAxis,
          valueYField: "incial",
          categoryXField: "country",
          clustered: false,
          tooltip: am5.Tooltip.new(root, {
            labelText: "Total: {valueY}"
          })
        }));

        series0.columns.template.setAll({
          width: am5.percent(50),
          tooltipY: 0,
          strokeOpacity: 0
        });


        var series1 = chart.series.push(am5xy.ColumnSeries.new(root, {
          name: "Income",
          xAxis: xAxis,
          yAxis: yAxis,
          valueYField: "restante",
          categoryXField: "country",
          clustered: false,
          tooltip: am5.Tooltip.new(root, {
            labelText: "Restante: {valueY}"
          })
        }));

        series1.columns.template.setAll({
          width: am5.percent(50),
          tooltipY: 0,
          strokeOpacity: 0
        });


        var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));

        chart.appear(1000, 100);

        let ejercicio = "<?php echo $ejercicio_fiscal ?>"

        function setBarras() {

          let tipo
          if (this.value) {
            tipo = this.value
          } else {
            tipo = 'sector'
          }

          $.ajax({
              url: '../../back/modulo_ejecucion_presupuestaria/pre_ejercicio_tipos.php',
              type: 'POST',
              dataType: 'json', // Cambiado a 'json'
              contentType: 'application/json',
              data: JSON.stringify({
                ejercicio: ejercicio,
                tipo: tipo
              }),
            })
            .done(function(resultado) {
              try {

                var data = [];
                var data_tabla = [];
                DataTable.clear()
                let contador = 1
                // Procesar el resultado
                resultado.forEach(element => {
                  let value = element.value;
                  let restante = element.total_restante;
                  let total_inicial = element.total_inicial;
                  data.push({
                    "country": value,
                    "incial": total_inicial,
                    "restante": restante
                  });
                  let porcentaje_restante = restante * 100 / total_inicial
                  let porcentaje_restante_redondeado = Math.round(porcentaje_restante * 100) / 100
                  data_tabla.push([contador++, value, total_inicial + '<small>Bs</small>', restante + '<small>Bs</small>', porcentaje_restante_redondeado + '<small>%</small>'])
                });

                DataTable.rows.add(data_tabla).draw()

                // Ordenar los datos
                data.sort((a, b) => b.value - a.value);

                // Actualizar el gráfico o visualización
                xAxis.data.setAll([]);
                xAxis.data.setAll(data);

                series0.data.setAll([]);
                series0.data.setAll(data);
                series1.data.setAll(data);
                series1.data.setAll(data);

                series0.appear();
                series1.appear();



              } catch (error) {
                console.error('Error procesando los datos:', error);
              }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
              console.error('Error en la solicitud:', textStatus, errorThrown);
              //  alert('Hubo un problema al obtener los datos. Por favor, inténtalo de nuevo.');
            })
            .always(function(res) {});
        }

        setBarras()

        document.getElementById('select_tipo').addEventListener('change', setBarras)

      <?php
      }

      ?>
      // GRAFICO 2
    </script>

</body>

</html>