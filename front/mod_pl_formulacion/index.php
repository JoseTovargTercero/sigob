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
  $annio = '2025';
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
        $y_d = date('Y') + 1;
        $y_d1 = $y_d - 1;
        $y_d2 = date('Y') + 2;
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
      <div class="row">
        <div class="top-col col-lg-4">

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

        </div>


        <div class="top-col col-lg-4">
          <?php if ($ejercicio_fiscal == 'No') { ?>
            <div class="card h-15">
              <div class="card-body d-flex justify-content-between ">
                <div class="mb-0 d-flex flex-column justify-content-between text-center text-sm-start me-3">
                  <div class="card-title">
                    <h4 class="mb-2">Plan de inversión </h4>
                    <p class="text-body app-academy-sm-60 app-academy-xl-100">
                      Inicie un nuevo ejercicio fiscal para poder acceder a sus proyectos.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          <?php } else { ?>
            <div class="card mb-3  h-15">
              <div class="card-body">
                <h5 class="d-flex justify-content-between align-items-center mb-3">Plan de inversión</h5>
                <p class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">Monto asignado:
                  <b>

                    <?php
                    $badge_0 = '<label class="badge me-2  bg-light-dark f-12 f-w-400">0</label>';



                    echo ($plan_inversion == 'no' ? $badge_0 : number_format($plan_inversion, 0, ',', '.') . 'Bs');

                    ?>

                  </b>
                </p>
                <p class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">Proyectos:

                  <?php echo ($proyectos == 0 ? $badge_0 : "<span>$proyectos</span>"); ?>
                </p>
                <hr>
                <div class="text-end">
                  <?php
                  if ($plan_inversion == 'no') {
                    echo '<a class="text-danger pointer" onclick="toggleDialogs()">Iniciar Plan de Inversión...</a>';
                  } else {
                    echo '<a class="text-info pointer" href="form_plan_inversion">Gestionar proyectos...</a>';
                  }
                  ?>

                </div>
              </div>
            </div>
          <?php } ?>




        </div>

        <div class="top-col col-lg-4">



          <?php

          if ($ejercicio_fiscal == 'No') {
            echo ' <div class="card bg-label-danger  h-15">
                <div class="card-body d-flex justify-content-between ">


                  <div class="mb-0 d-flex flex-column justify-content-between text-center text-sm-start me-3">
                    <div class="card-title">
                      <h4 class="text-danger mb-2">Ejercicio fiscal ' . (isset($_GET["a"]) ? $_GET["a"] : $annio) . ' </h4>
                      <p class="text-body app-academy-sm-60 app-academy-xl-100">
                        No hay ningún ejercicio registrado este año.
                      </p>
                      <div class="mb-0"><button class="btn btn-danger" onclick="toggleDialogs()">Iniciar ejercicio</button></div>
                    </div>
                  </div>



                </div>
                </div>';
          } else {
          ?>

            <div class="card mb-3 h-15">
              <div class="card-body">
                <h5 class="d-flex justify-content-between align-items-center mb-3">Ejercicio fiscal <?php echo $annio ?>

                  <div id="status">
                    <?php
                    if ($status == 1 || $status == 2) {
                      echo '<div class="badge bg-light-success">Abierto</div>';
                    } else {
                      echo '<div class="badge bg-light-dark">Cerrado</div>';
                    }
                    ?>
                  </div>

                </h5>


                <?php
                echo '<p class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">Situado constitucional: <b>' . number_format($situado, 0, ',', '.') . ' Bs</b></p><hr>';


                if ($status == 1) {
                  echo '<div class="text-center"><button class="btn btn-sm btn-danger" id="btn-cerrar">Cerrar ejercicio</button></div>';
                } else if ($status == 1) {
                }
                ?>


              </div>
            </div>



          <?php
          }
          ?>






        </div>

      </div>


      <div class="row ">

        <div class="col-lg-8">
          <div class="card" style="height: 72vh;">
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
        <div class="col-lg-4">



          <div class="card mb-3" style="height: 72vh;">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto">
                  <h5 class="mb-0">Porcentaje de distribución</h5>
                  <small>Del situado constitucional (Por partidas)</small>

                </div>

                <div id="grafico_1" style="width: 100%; height: 50vh;"></div>
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
                  WHERE id_ejercicio = ?");
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
        // Enviar los datos al back
        function sendData(data, url) {
          fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(response => response.text()) // Recupera la respuesta como texto
            .then(responseText => {
              // console.log('Respuesta del servidor (raw):', responseText); // Imprimir la respuesta original

              // Intentar convertir la respuesta a JSON
              try {
                const jsonResponse = JSON.parse(responseText);

                if (jsonResponse.success) {

                  toast_s('success', jsonResponse.success);
                  // actualizar despues de un segundo
                  toggleDialogs()
                  setTimeout(function() {
                    window.location.reload();
                  }, 1500);
                } else {
                  console.error('Error en la respuesta JSON: ', jsonResponse);
                }
              } catch (error) {
                console.error('Error al parsear JSON: ', error);
                console.error('Respuesta del servidor (no es JSON):', responseText);
              }
            })
            .catch(error => {
              console.error('Error en la solicitud: ', error);
              alert('Error: No se pudo iniciar');
            });
        }


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





        <?php if ($ejercicio_fiscal == 'No') { // En caso de que no exista el ejercicio fiscal 
        ?>
          document.getElementById('dataEjercicio').addEventListener('submit', function(event) {
            event.preventDefault();
            var situado = document.getElementById('situado').value;

            if (verificarMonto(situado)) {
              // si todos los campos son validos, envia los datos por ajax
              const data = {
                ano: '<?php echo $annio ?>',
                situado: situado,
                divisor: '12',
                accion: 'insert'
              };

              sendData(data, '../../back/modulo_pl_formulacion/form_ejercicio_fiscal.php')
            }
          })
        <?php } else {
        ?>

          document.getElementById('dataEjercicio_2').addEventListener('submit', function(event) {
            event.preventDefault();
            var monto = document.getElementById('monto').value;

            if (verificarMonto(monto)) {
              // si todos los campos son validos, envia los datos por ajax
              const data = {
                id: '<?php echo $ejercicio_fiscal ?>',
                monto: monto,
                accion: 'insert'
              };

              sendData(data, '../../back/modulo_pl_formulacion/form_plan_inversion_nuevo.php')
            }
          })

        <?php
        } // End: En caso de que no exista el ejercicio fiscal  
        ?>


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


        <?php if (@$status == 1) { // En caso de que el ejercicio exista    
        ?>

          function cerrarEjercicio() {
            Swal.fire({
              title: "¿Estás seguro?",
              text: "Se cerrará el ejercicio fiscal. La acción no se podrá revertir!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#04a9f5",
              cancelButtonColor: "#d33",
              confirmButtonText: "Sí, cerrar!",
              cancelButtonText: "Cancelar",
            }).then((result) => {
              if (result.isConfirmed) {
                $.ajax({
                  url: '../../back/modulo_pl_formulacion/form_cerrar_ejercicio.php',
                  type: "POST",
                  data: {
                    id: '<?php echo $annio ?>'
                  },
                  success: function(response) {
                    const respuesta = JSON.parse(response)

                    if (respuesta.status == 'ok') {
                      toast_s('success', 'El ejercicio fiscal fue cerrado')
                      $('#status').html('<div class="badge bg-light-dark">Cerrado</div>')
                      $('#btn-cerrar').remove()

                    } else {
                      toast_s('error', 'Error al cerrar el ejercicio fiscal')
                    }

                  },
                });
              }
            });
          }
          document.getElementById('btn-cerrar').addEventListener('click', cerrarEjercicio)
        <?php } ?>




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




        //  GRAFICO 1 - Principal
        function grafico_1() {
          am5.ready(function() {

            // Create root element
            var root = am5.Root.new("grafico_1");

            // Set themes
            root.setThemes([
              am5themes_Animated.new(root)
            ]);

            // Create chart
            var chart = root.container.children.push(am5percent.PieChart.new(root, {
              layout: root.verticalLayout,
              innerRadius: am5.percent(50)
            }));

            // Create series
            var series = chart.series.push(am5percent.PieSeries.new(root, {
              valueField: "value",
              categoryField: "category",
              alignLabels: false
            }));

            series.labels.template.setAll({
              textType: "circular",
              forceHidden: true,
              centerX: 0,
              centerY: 0
            });

            // Set data
            series.data.setAll([{
              value: <?php echo ($distribuido ? $distribuido : '0') ?>,
              category: "Distribuido",
            }, {
              value: <?php echo $situado - $distribuido ?>,
              category: "Faltante"
            }]);

            // Create legend
            // https://www.amcharts.com/docs/v5/charts/percent-charts/legend-percent-series/
            var legend = chart.children.push(am5.Legend.new(root, {
              centerX: am5.percent(50),
              x: am5.percent(50),
              marginTop: 15,
              marginBottom: 15,
            }));

            legend.data.setAll(series.dataItems);

            series.appear(1000, 100);

          }); // end am5.ready()
        }


        if ("<?php echo $ejercicio_fiscal ?>" != 'No') {
          grafico_1()
        } else {
          document.getElementById("grafico_1").innerHTML = "<div class='text-opacity' style='display: grid;place-items: center;height: inherit;'>No hay datos para mostrar</div>";

        }

        //  GRAFICO 1


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


          /*   var series1 = chart.series.push(am5xy.ColumnSeries.new(root, {
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
*/

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
                url: '../../back/modulo_pl_formulacion/form_ejercicio_tipos.php',
                type: 'POST',
                dataType: 'json', // Cambiado a 'json'
                contentType: 'application/json',
                data: JSON.stringify({
                  ejercicio: ejercicio,
                  tipo: tipo
                }),
              })
              .done(function(resultado) {
                console.log(resultado)
                try {

                  var data = [];
                  var data_tabla = [];
                  DataTable.clear()
                  let contandor = 1
                  // Procesar el resultado
                  resultado.forEach(element => {
                    let value = element.value;
                    let restante = element.total_restante;
                    let total_inicial = element.total_inicial;

                    data.push({
                      "country": value,
                      "incial": total_inicial,
                      // "restante": restante
                    });

                    let porcentaje_restante = restante * 100 / total_inicial
                    let porcentaje_restante_redondeado = Math.round(porcentaje_restante * 100) / 100

                    data_tabla.push([contandor++, value, total_inicial + '<small>Bs</small>', restante + '<small>Bs</small>', porcentaje_restante_redondeado + '<small>%</small>'])
                  });

                  DataTable.rows.add(data_tabla).draw()

                  // Ordenar los datos
                  data.sort((a, b) => b.value - a.value);

                  // Actualizar el gráfico o visualización

                  xAxis.data.setAll([]);
                  xAxis.data.setAll(data);

                  series0.data.setAll([]);
                  series0.data.setAll(data);
                  //  series1.data.setAll(data);
                  //  series1.data.setAll(data);

                  series0.appear();
                  //  series1.appear();



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