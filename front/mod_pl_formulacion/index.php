<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';



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
    $status_ejercicio = $row['status_ejercicio']; // formato: dd-mm-YY
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
        $y_d1 = date('Y') - 1;
        $y_d = date('Y');
        $y_d2 = date('Y') + 1;
        ?>
        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> Ejercicio fiscal <?php echo $y_d; ?>
        </h4>

        <div class="d-flex gap-1">
          <p> <a href="">Años anteriores</a>... </p>

          <p><a class="pointer <?php echo ($annio == $y_d1 ? 'text-decoration-underline text-primary' : 'text-dark') ?>" href="?ejercicio=<?php echo $y_d1 ?>"><?php echo $y_d1 ?></a></p>
          <p><a class="pointer <?php echo ($annio == $y_d ? 'text-decoration-underline text-primary' : 'text-dark') ?> " href="?ejercicio=<?php echo $y_d ?>"><?php echo $y_d ?></a></p>
          <p><a href="?ejercicio=<?php echo $y_d2 ?>" class="pointer <?php echo ($annio == $y_d2 ? 'text-decoration-underline text-primary' : 'text-dark') ?>"><?php echo $y_d2 ?></a></p>

        </div>
      </div>


      <!-- CONTENIDO -->


      <div class="row ">




        <div class="col-lg-4">
          <div class="card" style="min-height: 165px;">
            <div class="card-body">
              <div class="d-flex justify-content-between" style="position: relative;">
                <div class="d-flex flex-column">
                  <div class="card-title mb-auto">
                    <h5 class="mb-0">Entes jurídicos</h5>
                    <small>Con asignación presupuestaria</small>
                  </div>
                  <div class="chart2-statistics">
                    <h3 class="card-title ">
                      <span id="cant_planes_cont">12</span>
                      <small class="text-muted">Entes</small>
                    </h3>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card" style="min-height: 165px;">
            <div class="card-body">
              <div class="d-flex justify-content-between" style="position: relative;">
                <div class="d-flex flex-column">
                  <div class="card-title mb-auto">
                    <h5 class="mb-0">Descentralizados</h5>
                    <small>Con asignación presupuestaria</small>
                  </div>
                  <div class="chart2-statistics">
                    <h3 class="card-title ">
                      <span id="cant_planes_cont">12</span>
                      <small class="text-muted">Entes</small>
                    </h3>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">



          <?php

          if ($ejercicio_fiscal == 'No') {
            echo ' <div class="card bg-label-danger" style="min-height: 165px;">
                <div class="card-body d-flex justify-content-between ">


                  <div class="mb-0 d-flex flex-column justify-content-between text-center text-sm-start me-3">
                    <div class="card-title">
                      <h4 class="text-danger mb-2">Ejercicio fiscal ' . (isset($_GET["a"]) ? $_GET["a"] : date('Y')) . ' </h4>
                      <p class="text-body app-academy-sm-60 app-academy-xl-100">
                        No hay ningún plan registrado este año.
                      </p>
                      <div class="mb-0"><button class="btn btn-danger" onclick="n_plan()">Iniciar Plan</button></div>
                    </div>
                  </div>



                </div>
                </div>';
          } else {
          ?>

            <div class="card mb-3" style="min-height: 165px;">
              <div class="card-body">
                <h5 class="d-flex justify-content-between align-items-center mb-3">Ejercicio fiscal <?php echo $annio ?>

                  <div id="status_ejercicio">
                    <?php
                    if ($status_ejercicio == 1) {
                      echo '<div class="badge bg-light-success">Abierto</div>';
                    } else {
                      echo '<div class="badge bg-light-dark">Cerrado</div>';
                    }
                    ?>
                  </div>

                </h5>


                <?php
                echo '<p class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">Situado constitucional: <b>' . number_format($situado, 0, ',', '.') . ' Bs</b></p>';
                ?>
                <hr>


                <?php
                if ($status_ejercicio == 1) {
                  echo '<div class="text-center"><button class="btn btn-sm btn-danger" id="btn-cerrar">Cerrar ejercicio</button></div>';
                }
                ?>


              </div>
            </div>



          <?php
          }
          ?>





          <script>
            function n_plan() {
              toggleDialogs();
            }
          </script>


        </div>




        <div class="col-lg-8">
          <div class="card" style="min-height: 165px;">
            <div class="card-body" style="height: 60vh;">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0">
                    <button id="btn-vista-graf_table" class="btn btn-icon btn-primary avtar-s mb-0 me-1" style="border-radius: 5px;">
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
                    </select>
                  </div>
                </div>
                <section id="vista-grafico">
                  <div id="grafico_2" style="width: 100%; height: 50vh;"></div>
                </section>
                <section id="vista-tabla" class="hide mt-2">

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
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card" style="min-height: 165px;">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto">
                  <h5 class="mb-0">Porcentaje de distribución</h5>
                  <small>del situado (por partidas)</small> -
                  <b><?php echo number_format($distribuido, 0, '.', '.') ?>Bs / <?php echo number_format($situado, 0, '.', '.') ?>Bs</b>
                </div>

                <div id="grafico_1" style="width: 100%; height: 20vh;"></div>
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
                    <th>#</th>
                    <th>Partida</th>
                    <th>Asignación inicial</th>
                    <th>Disponibilidad</th>
                    <th></th>
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
                      <td>' . $row['id'] . '</td>
                      <td>' . $row['partida'] . '<br>
                      <small class="text-muted">' . substr($row['descripcion'], 0, 35) . '</small>...
                      </td>
                      <td class="text-center">' . number_format($row['monto_inicial'], 0, '.', '.') . ' Bs</td>
                      <td class="text-center">' . number_format($row['monto_actual'], 0, '.', '.') . ' Bs</td>
                      <td><button type="button" class="btn btn-sm btn-primary" data-toggle="
                      tooltip" title="Ver detalles">
                      <i class="bx bx-detail"></i>
                      </button></td>
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
      <div class="dialogs">
        <div class="dialogs-content " style="width: 35%;">
          <span class="close-button">×</span>
          <h5 class="mb-1">Nuevo ejercicio fiscal</h5>
          <hr>
          <div class="card-body">
            <form id="dataEjercicio">
              <div class="mb-3">
                <label for="situado" class="form-label">Situado constitucional</label>
                <input type="number" id="situado" name="situado" class="form-control" placeholder="Presupuesto asignado para el ejercicio fiscal <?php echo $annio ?>">
              </div>
              <div class="mb-2 text-end">
                <button type="submit" class="btn btn-primary">Registrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
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
        <?php
        if ($status_ejercicio == 1) {
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
                      $('#status_ejercicio').html('<div class="badge bg-light-dark">Cerrado</div>')
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
        <?php
        }
        ?>

        const lenguaje_datat = {
          decimal: "",
          emptyTable: "No hay información",
          info: "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
          infoEmpty: "Mostrando 0 to 0 of 0 Entradas",
          infoFiltered: "(Filtrado de _MAX_ total entradas)",
          infoPostFix: "",
          thousands: ",",
          lengthMenu: "Mostrar _MENU_ Entradas",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "Sin resultados encontrados",
          paginate: {
            first: "Primero",
            last: "Ultimo",
            next: "Siguiente",
            previous: "Anterior",
          },
        }

        var DataTable = $("#table").DataTable({
          language: lenguaje_datat
        });

        var DataTable_2 = $("#table-2").DataTable({
          language: lenguaje_datat
        });

        // detecta el onsubmit de dataEjercicio y valida los datos para enviarlos por ajax
        document.getElementById('dataEjercicio').addEventListener('submit', function(event) {
          event.preventDefault();
          var situado = document.getElementById('situado').value;


          // verifica que situado sea un numero
          if (isNaN(situado)) {
            toast_s('error', 'El campo situado debe ser un número.');
            return false;
          }
          // verifica que situado sea mayor a 0
          if (situado <= 0) {
            toast_s('error', 'El campo situado debe ser mayor a 0.');
            return false;
          }
          // verifica que situado sea un número entero
          if (situado % 1 !== 0) {
            toast_s('error', 'El campo situado debe ser un número entero.');
            return false;
          }
          // si todos los campos son validos, envia los datos por ajax



          // Datos a enviar
          const data = {
            ano: '<?php echo $annio ?>',
            situado: situado,
            divisor: '12',
            accion: 'insert'
          };

          // Crear la solicitud AJAX usando fetch
          fetch('../../back/modulo_pl_formulacion/form_ejercicio_fiscal.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(response => {
              // Manejar la respuesta exitosa

              if (response.success) {
                toast_s('success', response.success)

                // recargar la pagina
                window.location.reload();
              } else {
                console.error('Error en la respuesta: ', response);
              }
            })
            .catch(error => {
              // Manejar el error
              console.error('Error en la solicitud: ', error);
              alert('Error: No se pudo iniciar');
            });


        })






        document.getElementById('btn-vista-graf_table').addEventListener('click', setVistaGT)

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

        // * GRAFICO 1
        // * GRAFICO 1


        function grafico_1() {
          am5.ready(function() {

            // Create root element
            // https://www.amcharts.com/docs/v5/getting-started/#Root_element
            var root = am5.Root.new("grafico_1");


            // Set themes
            // https://www.amcharts.com/docs/v5/concepts/themes/
            root.setThemes([
              am5themes_Animated.new(root)
            ]);


            // Create chart
            // https://www.amcharts.com/docs/v5/chargts/percent-charts/pie-chart/
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
              forceHidden: true,
              centerX: 0,
              centerY: 0
            });


            // Set data
            // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Setting_data
            series.data.setAll([{
                value: <?php echo $situado - $distribuido + 500 ?>,
                category: "Distribuido",
              },
              {
                value: <?php echo $situado ?>,
                category: "Faltante"
              }
            ]);

            series.appear(1000, 100);

          }); // end am5.ready()
        }
        if (<?php echo $situado ?> != 0) {
          grafico_1()
        } else {
          document.getElementById("grafico_1").innerHTML = "<div class='text-opacity' style='display: grid;place-items: center;height: inherit;'>No hay datos para mostrar</div>";
        }

        // * GRAFICO 1
        // * GRAFICO 1



        // * GRAFICO 2
        // * GRAFICO 2

        // Create root element
        // https://www.amcharts.com/docs/v5/getting-started/#Root_element
        var root = am5.Root.new("grafico_2");

        // Set themes
        // https://www.amcharts.com/docs/v5/concepts/themes/
        root.setThemes([
          am5themes_Animated.new(root)
        ]);

        // Create chart
        // https://www.amcharts.com/docs/v5/charts/xy-chart/
        var chart = root.container.children.push(am5xy.XYChart.new(root, {
          panX: true,
          panY: false,
          wheelX: "panX",
          wheelY: "zoomX",
          paddingLeft: 0,
          layout: root.verticalLayout
        }));

        // Add scrollbar
        // https://www.amcharts.com/docs/v5/charts/xy-chart/scrollbars/
        chart.set("scrollbarX", am5.Scrollbar.new(root, {
          orientation: "horizontal"
        }));

        var data = [];

        // Create axes
        // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
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

        // Add series
        // https://www.amcharts.com/docs/v5/charts/xy-chart/series/

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


        // Make stuff animate on load
        // https://www.amcharts.com/docs/v5/concepts/animations/
        chart.appear(1000, 100);


        let ejercicio = "<?php echo $ejercicio_fiscal ?>"

        function setBarras() {

          let tipo
          if (this.value) {
            tipo = this.value
          } else {
            tipo = 'sector'
          }

          console.log(tipo)


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
                console.log('Respuesta recibida:', resultado);

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
                    "restante": restante
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
            .always(function(res) {
              console.log(res)
              console.log('Solicitud AJAX finalizada.');
            });
        }

        setBarras()

        document.getElementById('select_tipo').addEventListener('change', setBarras)




        // * GRAFICO 2
        // * GRAFICO 2
      </script>

</body>

</html>