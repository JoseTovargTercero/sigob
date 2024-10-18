<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

function contar($table, $condicion)
{
  global $conexion;

  $stmt = $conexion->prepare("SELECT count(*) FROM " . $table . " WHERE " . $condicion);
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

$stmt = mysqli_prepare($conexion, "SELECT * FROM `ejercicio_fiscal` WHERE ano = ?");
$stmt->bind_param('s', $annio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ejercicio_fiscal = $row['id']; // formato: dd-mm-YY
    $situado = $row['situado']; // formato: dd-mm-YY
    $status_ejercicio = $row['status']; // formato: dd-mm-YY
  }
} else {
  $ejercicio_fiscal = 'No';
  $situado = 0; // formato: dd-mm-YY
}
$stmt->close();


$stmt = mysqli_prepare($conexion, "SELECT * FROM plan_inversion WHERE id_ejercicio=?");
$stmt->bind_param('i', $ejercicio_fiscal);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $plan_inversion_monto = $row['monto_total'];
    $plan_inversion = number_format($plan_inversion_monto, 0, '.', '.');
    $id_plan_inversion = number_format($row['id'], 0, '.', ',');

    $proyectos = contar("proyecto_inversion", 'id_plan=' . $id_plan_inversion);
    $proyectos_ejecutados = contar("proyecto_inversion", 'id_plan=' . $id_plan_inversion . ' AND status=1');
  }
} else {
  $id_plan_inversion = 0;
  $plan_inversion = 'Sin asignación';
  $proyectos = 0;
  $proyectos_ejecutados = 0;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Plan de inversión</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <style>
    #table td,
    #table th,
    #table-2 td,
    #table-2 th {
      text-align: center;
    }

    td {
      padding: 7px !important;
    }

    table tr td:nth-child(2),
    table tr th:nth-child(2) {
      text-align: left !important;
      /* Alineación al centro, puedes cambiarla a 'left' o 'right' */
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

    .overMark {
      background-color: #ffffff;
      width: 100%;
      height: 44px;
      position: absolute;
      margin-top: -19px;
    }
  </style>

</head>
<?php require_once '../includes/header.php' ?>
<script src="../../src/assets/js/chosen.jquery.min.js"></script>
<link rel="stylesheet" href="../../src/assets/css/chosen.min.css">

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

        <?php
        $y_d1 = date('Y') - 1;
        $y_d = date('Y');
        $y_d2 = date('Y') + 1;
        ?>
        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> Plan de inversión <?php echo $y_d; ?>
        </h4>

        <div class="d-flex gap-1">
          <p> <a href="">Años anteriores</a>... </p>

          <p><a class="pointer <?php echo ($annio == $y_d1 ? 'text-decoration-underline text-primary' : 'text-dark') ?>" href="?ejercicio=<?php echo $y_d1 ?>"><?php echo $y_d1 ?></a></p>
          <p><a class="pointer <?php echo ($annio == $y_d ? 'text-decoration-underline text-primary' : 'text-dark') ?> " href="?ejercicio=<?php echo $y_d ?>"><?php echo $y_d ?></a></p>
          <p><a href="?ejercicio=<?php echo $y_d2 ?>" class="pointer <?php echo ($annio == $y_d2 ? 'text-decoration-underline text-primary' : 'text-dark') ?>"><?php echo $y_d2 ?></a></p>

        </div>
      </div>



      <!-- CONTENIDO -->
      <div class="row">
        <div class="top-col col-lg-4">

          <div class="card bg-brand-color-2 bitcoin-wallet h-15">
            <div class="card-body ">
              <h5 class="text-white mb-2">Monto</h5>
              <h3 class="text-white mb-2 f-w-300" id="situado_h2">
                <?php echo $plan_inversion ?> </h3>
              <span class="text-white">
                <b id="monto_asigando_ap">0</b> <small>Bs</small> Asignado a proyectos. <br>
                <b id="monto_ejecutado">0</b> <small>Bs</small> Ejecutados.
              </span>

            </div>
          </div>

        </div>


        <div class="top-col col-lg-4">

          <div class="card mb-3  h-15">
            <div class="card-body">
              <h5 class="d-flex justify-content-between align-items-center mb-3">Proyectos</h5>

              <ul class="list-group ">

                <li class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">
                  <span>Total de proyectos: </span>
                  <b id="total_proyectos"></b>
                </li>
                <li class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">
                  <span>Proyectos ejecutados: </span>
                  <b id="total_proyectos_ejecutados"></b>
                </li>
                <li class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">
                  <span>Proyectos pendientes: </span>
                  <b id="total_proyectos_pendientes"></b>
                </li>
              </ul>

              <hr>
            </div>
          </div>




        </div>
        <div class="top-col col-lg-4">
          <div class="card mb-3 h-15" style="overflow: hidden;">
            <div class="card-body">
              <h5 class="d-flex justify-content-between align-items-center mb-0">Proyectos</h5>
              <span>Ejecución de proyectos</span>


              <div id="chartdiv" style="width: 100%; height: 12vh;"></div>
              <div class="overMark"></div>

            </div>
          </div>
        </div>
      </div>


      <div class="row ">
        <div class="col-lg-12 hide" id="vista_datalles">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0">Detalles del proyecto</h5>

                  <button class="btn avtar avtar-xs btn-light-dark" onclick="$('#vista_datalles').addClass('hide')"><i class="bx bx-x f-20"></i></button>

                </div>


                <div class="mt-2 card-body">

                  <div class="row">
                    <div class="col-lg-4 br-g">
                      <div class="mb-3">
                        <small class="text-muted">Nombre del proyecto:</small>
                        <h5 class="fw-bold" id="info_nombre_p"></h5>
                      </div>

                      <div class="mb-3">
                        <small class="text-muted">Descripción del proyecto:</small>
                        <p class="text-dark" id="info_descripcion_p"></p>
                      </div>

                      <div class="mb-4 d-flex justify-content-between">
                        <small class="text-muted">Asignación presupuestaria:</small>
                        <h5 class="fw-bold" id="info_monto_p"></h5>
                      </div>



                      <div class="text-center" id="info_estatus_p">
                      </div>


                    </div>
                    <div class="col-lg-8">

                      <table class="table" id="table-2">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Sector</th>
                            <th>Partida</th>
                            <th>Nombre</th>
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
        </div>




        <div class="col-lg-12" id="vista_registro">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0">Nuevo proyecto</h5>

                </div>


                <div class="mt-2 card-body">

                  <div class="mb-3">
                    <label for="partida" class="form-label">Nombre del proyecto</label>
                    <input type="text" id="nombre" class="form-control" placeholder="Nombre del proyecto">
                  </div>


                  <div class="mb-3">
                    <label for="partida" class="form-label">Descripción del proyecto</label>
                    <textarea type="text" id="descripcion" class="form-control">
                    </textarea>
                  </div>


                  <div class="mb-4">
                    <label for="partida" class="form-label">Asignación presupuestaria total (Monto)</label>
                    <input type="text" class="form-control" id="monto" placeholder="Indique el monto asignado para la ejecución del proyecto">
                  </div>


                  <div class="mb-2 mt-3">
                    <h6 class=" text-uppercase ">Distribución presupuestaria</h6>
                    <!-- Gradient divider -->
                    <hr data-content="AND" class="hr-text">
                  </div>

                  <div class="mb-3" id="section-partidas">
                    <div class="row mb-2 d-asignacion">
                      <div class="col-lg-4">

                        <label class="form-label">Sector</label>
                        <select type="text" class="form-control c_sector chosen-select">
                          <option value="">Seleccione</option>
                        </select>
                      </div>
                      <div class="col-lg-4">
                        <label class="form-label">Partida presupuestaria</label>
                        <select type="text" class="form-control c_partida chosen-select">
                          <option value="">Seleccione</option>
                        </select>
                      </div>
                      <div class="col-lg-4 row">
                        <div class="col-lg-9">
                          <label class="form-label">Monto</label>
                          <input type="text" class="form-control c_monto" placeholder="Indique el monto">
                        </div>
                        <div class="col-lg-3 g-self-e">
                        </div>
                      </div>
                    </div>
                  </div>


                  <div class="text-center mb-4">
                    <button class="btn btn-sm bg-brand-color-1 text-white" id="btn-add-row-inputs"><i class="bx bx-plus"></i>Agregar otra partida</button>
                  </div>

                  <div class="mb-3 d-flex justify-content-between">
                    <button class="btn btn-secondary" id="btn-cancelar-registro">Cancelar</button>
                    <button class="btn btn-primary" id="btn-registro">Guardar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <div class="col-lg-12" id="vista-tabla">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0">
                    Proyectos del plan de inversión
                  </h5>
                  <button class="btn btn-info btn-sm" onclick="nuevoProyecto()">
                    <i class="bx bx-plus"></i>
                    Nuevo proyecto
                  </button>
                </div>


                <div class="mt-2 card-body">

                  <table class="table" id="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Proyecto</th>
                        <th>Monto</th>
                        <th>Estatus</th>
                        <th></th>
                        <th></th>
                        <th></th>
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

      <div class="dialogs">
        <div class="dialogs-content " style="width: 35%;">
          <span class="close-button">×</span>
          <h5 class="mb-1">Ejecución de proyecto</h5>
          <hr>
          <p class="text-danger">
            * Una vez marcado el proyecto como "Ejecutado" este no podrá ser modificado ni eliminado.
          </p>
          <div class="card-body">
            <div class="mb-3">
              <label for="comentario" class="form-label">Comentario</label>
              <textarea id="comentario" class="form-control"></textarea>
            </div>
            <div class="mb-2 d-flex justify-content-between">
              <button type="submit" class="btn btn-primary" id="btn-ejecutar">Marcar como ejecutado</button>
              <button type="button" class="btn btn-secondary" onclick="toggleDialogs()">Cancelar</button>
            </div>
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
      <script src="../../src/assets/js/amcharts5/themes/Animated.js"></script>
      <script src="../../src/assets/js/amcharts5/xy.js"></script>


      <script>
        let sectores_options = []
        let partidas_options = []
        <?php
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_sectores_presupuestarios` ORDER BY sector");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo 'sectores_options.push(\'<option value="' . $row['id'] . '">' . $row['sector'] . '.' . $row['programa'] . '.' . $row['proyecto'] . ' - ' . $row['nombre'] . '</option>\');';
          }
        }
        $stmt->close();


        ?>

        sectores_options.forEach(element => {
          $('.c_sector').append(element)
        });



        const url_back = '../../back/modulo_pl_formulacion/form_plan_inversion.php'
        const planData = <?php echo json_encode([
                            'id' => $id_plan_inversion,
                            'monto' => $plan_inversion_monto
                          ]); ?>;

        let monto_total_proyectos = 0;
        let proyectos = []


        // Obtener la lista de partidas
        let clasificador = {};


        // DATA TABLE
        var DataTable = $("#table").DataTable({
          language: lenguaje_datat
        });
        var DataTable_2 = $("#table-2").DataTable({
          language: lenguaje_datat
        });



        // agregar los campos para mas partidas
        function addInputsPartidas() {



          var section = document.getElementById('section-partidas');

          var row = document.createElement('div');

          let inputSector = `<div class="col-lg-4">
                          <label class="form-label">Sector</label>
                          <select type="text" class="form-control c_sector chosen-select">
                            <option value="">Seleccione</option>`

          sectores_options.forEach(element => {
            inputSector += element
          });

          inputSector += `</select>
                        </div>`


          let inputPartida = `<div class="col-lg-4">
                        <label class="form-label">Partida presupuestaria</label>
                         <select type="text" class="form-control c_partida chosen-select">
                          <option value="">Seleccione</option>`

          for (const key in clasificador) {
            let element = clasificador[key];
            inputPartida += '<option value="' +
              key +
              '">' + key + '-' + element +
              "</option>"
          }

          inputPartida += `
          </select></div>`




          row.innerHTML = `<div class="row mb-2 fila d-asignacion">
                        ${inputSector}
                        ${inputPartida}
                      
                      <div class="col-lg-4 row">
                        <div class="col-lg-9">
                          <label  class="form-label">Monto</label>
                          <input type="text" class="form-control c_monto" placeholder="Indique el monto">
                        </div>
                        <div class="col-lg-3 g-self-e">
                          <button class="btn btn-light-danger btn-delete-row"><i class="bx bx-x f-20"></i></button>
                        </div>
                      </div>
                    </div>
                    `
          section.appendChild(row);



          $('.chosen-select').chosen({}).change(function(obj, result) {
            console.debug("changed: %o", arguments);
          });

        }

        document.getElementById('btn-add-row-inputs').addEventListener('click', addInputsPartidas)

        // Eliminar los campos creados para mas partidas
        document.addEventListener('click', function(event) {
          if (event.target.closest('.btn-delete-row')) {
            const row = event.target.closest('.fila');
            row.remove();
          }
        });



        function getPartidas() {
          $.ajax({
            url: "../../back/modulo_pl_formulacion/form_partidas.php",
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              accion: 'consultar'
            }),
            success: function(response) {
              if (response.success) {
                let data = response.success;
                data.forEach(function(item) {
                  sectores_options.push()

                  $(".c_partida").append(
                    '<option value="' +
                    item.partida +
                    '">' + item.partida + '-' + item.descripcion +
                    "</option>"
                  );
                  clasificador[item.partida] = item.descripcion;
                });

              }
            },
            error: function(xhr, status, error) {
              console.log(xhr.responseText);
            },
          });
        }

        getPartidas();



        /*
                status
                partidas
                */
        function getDetallesProyecto(proyecto_id) {
          let infoProyecto = proyectos[proyecto_id]

          document.getElementById('info_nombre_p').innerHTML = infoProyecto[1]
          document.getElementById('info_descripcion_p').innerHTML = infoProyecto[2]
          document.getElementById('info_monto_p').innerHTML = infoProyecto[3] + ' Bs'
          document.getElementById('info_estatus_p').innerHTML = infoProyecto[4] === 1 ?
            `<span class="badge bg-light-primary text-lg">Ejecutado</span>` : `<span class="badge bg-light-secondary text-lg">Pendiente</span>`

          DataTable_2.clear();
          let data = []
          let cont = 1;
          for (const key in infoProyecto[5]) {
            data.push(
              [cont++,
                infoProyecto[5][key]['sector'] + '.' + infoProyecto[5][key]['programa'] + '.' + infoProyecto[5][key]['proyecto'],
                infoProyecto[5][key]['partida'],
                infoProyecto[5][key]['nombre'],
                infoProyecto[5][key]['monto'],
              ])
          }
          DataTable_2.rows.add(data).draw();

          document.getElementById('vista_datalles').classList.remove('hide')

        }





        function ejecutarProyecto() {
          let comentario = $('#comentario').val()
          toggleDialogs()

          Swal.fire({
            title: "¿Estás seguro?",
            text: "¡No podrás revertir esto! Se cambiara el estatus del proyecto",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#04a9f5",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, continuar!",
            cancelButtonText: "Cancelar",
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: url_back,
                type: "json",
                contentType: 'application/json',
                data: JSON.stringify({
                  accion: 'ejecutar_proyecto',
                  id_proyecto: edt,
                  comentario: comentario
                }),
                success: function(response) {
                  if (response.success) {
                    get_tabla()
                    toast_s("success", "Actualizado correctamente");

                  } else {
                    toast_s("error", response.error);
                  }
                },
              });
            } else {
              toggleDialogs()
            }
          });



        }

        document.getElementById('btn-ejecutar').addEventListener('click', ejecutarProyecto)


        let accion


        // verificar si hay dinero antes de mostrar el formulario
        function nuevoProyecto() {
          if (planData.monto == monto_total_proyectos) {
            toast_s("error", "No se puede crear un proyecto sin disponibilidad presupuestaria");
          } else {
            accion = 'registrar_proyecto'
            $('#vista_registro').removeClass('hide')
            $('#vista-tabla').addClass('hide')
            $('#vista_datalles').addClass('hide')
          }
        }



        // Mostrar interfaz para editar proyecto existente
        function editarProyecto(id) {
          $('#vista_datalles').addClass('hide')
          $('#vista_registro').removeClass('hide')
          $('#vista-tabla').addClass('hide')
          $('#cargando').show()
          accion = 'update_proyecto'

          $('#nombre').val(proyectos[id]['1'])
          $('#descripcion').val(proyectos[id]['2'])
          $('#monto').val(proyectos[id]['3'])
          $('#partida').val(proyectos[id]['4'])

          // ejecutar addInputsPartidas() tantas partidas haya en proyectos[id]['5']

          let cantidadPartidas = proyectos[id]['5'].length
          $('.fila').remove()

          let i = 1
          while (i < cantidadPartidas) {
            addInputsPartidas()
            i++
          }

          // Iterar sobre cada d-asignacion dentro de #section-partidas
          $('#section-partidas .d-asignacion').each(function(index) {
            let partida = proyectos[id]['5'][index]['partida'];
            let monto = proyectos[id]['5'][index]['monto'];
            let c_sector = proyectos[id]['5'][index]['sector_id'];

            // Asignar el valor de 'partida' al campo c_partida dentro de la fila actual
            $(this).find('.c_sector').val(c_sector).trigger("chosen:updated"); // Actualiza Chosen después de seleccionar el valor
            $(this).find('.c_partida').val(partida).trigger("chosen:updated");


            // Asignar el valor de 'monto' al campo c_monto dentro de la fila actual
            $(this).find('.c_monto').val(monto);
          });
          $('#cargando').hide()


        }

        function cancelarRegistro() {
          $('#vista_registro').addClass('hide')
          $('#vista-tabla').removeClass('hide')
          $('.form-control').val('')
          $('.fila').remove()
        }
        document.getElementById('btn-cancelar-registro').addEventListener('click', cancelarRegistro)

        // Registrrar nuevo proyecto
        function guardarProyecto() {

          // Obtener todos los campos de partida y monto
          const c_sectores = document.querySelectorAll('.c_sector');
          const partidas = document.querySelectorAll('.c_partida');
          const montos = document.querySelectorAll('.c_monto');

          // Crear un array para almacenar los valores
          const datos_presupuestarios = [];
          let consolidado = 0;

          let errors = false;

          // Recorrer cada campo de partida y monto
          partidas.forEach((partida, index) => {
            const monto = montos[index].value; // Obtener el valor del monto correspondiente
            const sector = c_sectores[index].value; // Obtener el valor del monto correspondiente


            // Verificar si el campo de partida está vacío
            if (partida.value === '') {
              errors = true;
              partida.classList.add('border-danger'); // Agregar la clase 'errors' al campo de partida vacío
              toast_s('error', 'Faltan datos');
            }

            // Verificar si el campo de monto está vacío
            if (monto === '') {
              errors = true;
              montos[index].classList.add('border-danger'); // Agregar la clase 'errors' al campo de monto vacío
              toast_s('error', 'Faltan datos');
            }
            if (sector === '') {
              errors = true;
              c_sectores[index].classList.add('border-danger'); // Agregar la clase 'errors' al campo de monto vacío
              toast_s('error', 'Faltan datos');
            }

            consolidado += parseInt(monto)
            // Agregar los valores válidos al array de datos
            datos_presupuestarios.push({
              partida: partida.value,
              monto: monto,
              sector: sector
            });
          });

          const nombre = $("#nombre").val();
          const descripcion = $("#descripcion").val();
          const monto_total = $("#monto").val().replace(/\./g, "");

          // quitar puntos de monto
          if (consolidado != monto_total) {
            toast_s('error', 'El monto total no coincide con las asignaciones por partida.');
            errors = true;
          }

          if (!validarCampo('nombre')) {
            errors = true;
          }
          if (!validarCampo('descripcion')) {
            errors = true;
          }
          if (!validarCampo('monto')) {
            errors = true;
          }
          // Si hay errores, detener la ejecución
          if (errors) {
            return;
          }

          const proyecto = {
            nombre: nombre.trim(),
            descripcion: descripcion.trim(),
            monto: monto_total.trim(),
            partida: datos_presupuestarios,
            id_plan: planData.id,
            id: ''
          }

          // enviar los datos al back con el nuevo formato
          if (accion == 'update_proyecto') {
            nueva_dist = (parseInt(monto_total_proyectos) - parseInt(proyectos[edt][3])) + parseInt(monto_total);
            proyecto.id = edt
          } else {
            nueva_dist = parseInt(monto_total_proyectos) + parseInt(monto_total);
          }


          if (nueva_dist > parseInt(planData.monto)) {
            toast_s("error", "El monto es mayor al presupuesto disponible");
            return
          }

          if (verificarMonto(monto_total)) {
            $.ajax({
              url: url_back,
              type: "json",
              contentType: 'application/json',
              data: JSON.stringify({
                proyecto: proyecto,
                accion: accion
              }),
              success: function(response) {
                if (response.success) {
                  toast_s('success', 'Proyecto ' + (accion == 'update_proyecto' ? 'actualizado' : 'registrado') + ' con éxito')
                  get_tabla()
                  cancelarRegistro()

                  $('.fila').remove()
                  $(".c_partida").eq(0).val('').trigger("chosen:updated");
                  $(".c_sector").eq(0).val('').trigger("chosen:updated");
                } else {
                  console.log(response)
                  toast_s('error', 'Error al ' + (accion == 'update_proyecto' ? 'actualizar' : 'registrar') + ' proyecto')
                }
              },
              error: function(xhr, status, error) {
                console.log(xhr.responseText);

              },
            });
          }
        }

        // addevenlister btn-registro click

        document.getElementById('btn-registro').addEventListener('click', guardarProyecto);



        /**
         * Deletes a record with the specified ID.
         * @param {number} id - The ID of the record to be deleted.
         * @returns {boolean} - Returns true if the record is deleted successfully, false otherwise.
         */
        function eliminar(id) {
          Swal.fire({
            title: "¿Estás seguro?",
            text: "¡No podrás revertir esto!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#04a9f5",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, eliminarlo!",
            cancelButtonText: "Cancelar",
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: url_back,
                type: "json",
                contentType: 'application/json',
                data: JSON.stringify({
                  accion: 'eliminar_proyecto',
                  id_proyecto: id
                }),
                success: function(response) {
                  if (response.success) {
                    get_tabla()
                    toast_s("success", "Eliminado con éxito");
                  } else {
                    toast_s("error", response.error);
                  }
                },
              });
            }
          });
        }

        document.addEventListener('click', function(event) {

          if (event.target.closest('.btn-delete')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.btn-delete').getAttribute('data-delete-id');
            eliminar(id);
          }
          if (event.target.closest('.btn-edit')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.btn-edit').getAttribute('data-edit-id');
            editar(id);
          }

          if (event.target.closest('.btn-detalles-p')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.btn-detalles-p').getAttribute('data-id-proyecto');
            getDetallesProyecto(id);
          }


        });

        var edt

        function editar(id) {
          edt = id

          Swal.fire({
            title: "¿Que desea hacer?",
            text: "¡Elija una de las opciones para modificar!",
            icon: "info",
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: "#04a9f5",
            denyButtonColor: '#a389d4',
            denyButtonText: "Información",
            confirmButtonText: "Estatus",
            cancelButtonText: "Cancelar",
          }).then((result) => {
            if (result.isConfirmed) {
              toggleDialogs()
            } else if (result.isDenied) {
              editarProyecto(id)
            }
          });

        }


        // GRAFICO 1 - BARRAS HORIZONTALES
        // Create root element
        var root = am5.Root.new("chartdiv");

        root.setThemes([
          am5themes_Animated.new(root)
        ]);

        var chart = root.container.children.push(am5xy.XYChart.new(root, {
          panX: false,
          panY: false,
          paddingLeft: 0,
          layout: root.verticalLayout
        }));

        var legend = chart.children.push(am5.Legend.new(root, {
          centerX: am5.p50,
          x: am5.p50
        }))

        var data = [{
          year: "",
          income: <?php echo $proyectos ?>,
          expenses: <?php echo $proyectos_ejecutados ?>
        }];

        var yAxis = chart.yAxes.push(am5xy.CategoryAxis.new(root, {
          categoryField: "year",
          renderer: am5xy.AxisRendererY.new(root, {
            inversed: true,
            cellStartLocation: 0.1,
            cellEndLocation: 0.9,
            minorGridEnabled: true
          })
        }));

        yAxis.data.setAll(data);

        var xAxis = chart.xAxes.push(am5xy.ValueAxis.new(root, {
          renderer: am5xy.AxisRendererX.new(root, {
            strokeOpacity: 0.1,
            minGridDistance: 50
          }),
          min: 0
        }));

        // Add series
        function createSeries(field, name) {
          var series = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: name,
            xAxis: xAxis,
            yAxis: yAxis,
            valueXField: field,
            categoryYField: "year",
            sequencedInterpolation: true,
            tooltip: am5.Tooltip.new(root, {
              pointerOrientation: "horizontal",
              labelText: "[bold]{name}[/] {valueX}"
            })
          }));

          series.columns.template.setAll({
            height: am5.p100,
            strokeOpacity: 0
          });

          series.bullets.push(function() {
            return am5.Bullet.new(root, {
              locationX: 1,
              locationY: 0.5,
              sprite: am5.Label.new(root, {
                centerY: am5.p50,
                text: "{valueX}",
                populateText: true
              })
            });
          });

          series.bullets.push(function() {
            return am5.Bullet.new(root, {
              locationX: 1,
              locationY: 0.5,
              sprite: am5.Label.new(root, {
                centerX: am5.p100,
                centerY: am5.p50,
                text: "{name}",
                fill: am5.color(0xffffff),
                populateText: true
              })
            });
          });

          series.data.setAll(data);
          series.appear();

          return series;
        }

        createSeries("income", "Total");
        createSeries("expenses", "EJecutados");

        // Add cursor
        var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
          behavior: "zoomY"
        }));
        cursor.lineY.set("forceHidden", true);
        cursor.lineX.set("forceHidden", true);

        // Make stuff animate on load
        chart.appear(1000, 100);

        function actualizarGrafico(i, e) {
          // Actualizar los datos con los nuevos valores
          var nuevosDatos = [{
            year: "",
            income: i, // Nuevo valor de ingresos
            expenses: e // Nuevo valor de gastos
          }];

          // Actualizar la data del eje y
          yAxis.data.setAll(nuevosDatos);

          // Actualizar la data de las series
          chart.series.each(function(series) {
            series.data.setAll(nuevosDatos);
          });
        }
        // GRAFICO 1 - BARRAS HORIZONTALES


        // verificar montos
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
        window.onresize = adjustFontSize;

        // End: Ajustar el tamaño del texto con la cantidad del situado para el card con el bg-info




        function get_tabla() {
          $.ajax({
            url: url_back,
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              accion: 'get_proyectos',
              id_plan: planData.id
            }),
            success: function(response) {
              let data_tabla = [] // Informacion de la tabla

              if (response.success) {
                let count = 1;
                DataTable.clear()

                monto_total_proyectos = 0;
                monto_total_ejecutado = 0;
                let proyectos_ejecutados = 0
                let proyectos_pendientes = 0

                response.success.forEach(function(item) {


                  proyectos[item.id] = [
                    item.id,
                    item.proyecto,
                    item.descripcion,
                    item.monto_proyecto,
                    item.status,
                    item.partidas
                  ]

                  data_tabla.push([
                    count++,
                    item.proyecto,
                    item.monto_proyecto,
                    item.status === 1 ?
                    `<span class="badge bg-light-primary">Ejecutado</span>` : `<span class="badge bg-light-secondary">Pendiente</span>`,
                    `<button data-id-proyecto="${item.id}" type="button" class="btn avtar avtar-xs btn-success btn-detalles-p" data-toggle="
                      tooltip" title="Ver detalles">
                      <i class="bx bx-detail"></i>
                      </button>`,
                    item.status === 0 ?
                    `<button class="btn btn-edit btn-sm bg-brand-color-2 text-white " data-edit-id="${item.id}"><i class="bx bx-edit-alt"></i></button>` :
                    '-',
                    item.status === 0 ?
                    `<button class="btn btn-danger btn-sm btn-delete" data-delete-id="${item.id}"><i class="bx bx-trash"></i></button>` :
                    '-'
                  ]);


                  monto_total_proyectos += parseInt(item.monto_proyecto) // Para calcular el total asignado en caso de que se requiera registrar uno nuevo

                  if (item.status == '1') {
                    monto_total_ejecutado += parseInt(item.monto_proyecto)
                    proyectos_ejecutados += 1

                  } else {
                    proyectos_pendientes += 1

                  }
                });

                $('#total_proyectos').html(data_tabla.length)
                $('#total_proyectos_ejecutados').html(proyectos_ejecutados)
                $('#total_proyectos_pendientes').html(proyectos_pendientes)


                actualizarGrafico(data_tabla.length, proyectos_ejecutados)

                DataTable.rows.add(data_tabla).draw()
                $('#monto_asigando_ap').html(monto_total_proyectos)
                $('#monto_ejecutado').html(monto_total_ejecutado)
              }
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
              console.error(error);
            },
          });
        }
        get_tabla()




        // ejecutar una funcion cuando la pagina se cargue
        $(document).ready(function() {
          $('.chosen-select').chosen({}).change(function(obj, result) {
            console.debug("changed: %o", arguments);
          });

          $('#vista_registro').addClass('hide')
        })
      </script>

</body>

</html>