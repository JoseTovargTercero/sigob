<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

/*

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
*/


?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Unidades</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <style>
    table tr td:nth-child(1),
    table tr td:nth-child(2),
    table tr th:nth-child(1),
    table tr th:nth-child(2),
    #table-2 tr th:nth-child(5),
    #table-2 tr td:nth-child(5) {
      text-align: center !important;
      /* Alineación al centro, puedes cambiarla a 'left' o 'right' */
    }

    select {
      background-image: none !important;
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
        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> Unidades
        </h4>


        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
          <li class="nav-item" role="presentation"><a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">Unidades</a></li>
          <li class="nav-item" role="presentation"><a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false" tabindex="-1">Dependencias internas</a></li>
        </ul>

      </div>









      <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">

          <div class="row ">
            <div class="col-lg-12 hide" id="vista_datalles">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex flex-column">
                    <div class="card-title mb-auto d-flex justify-content-between">
                      <h5 class="mb-0">Nueva unidad</h5>

                      <button class="btn avtar avtar-xs btn-light-dark" onclick="$('#vista_datalles').addClass('hide')"><i class="bx bx-x f-20"></i></button>

                    </div>


                    <div class="mt-2 card-body">

                      <div class="row">
                        <div class="col-lg-4 br-g">
                          <div class="mb-3">
                            <small class="text-muted">Denominación:</small>
                            <h5 class="fw-bold" id="info_nombre_p"></h5>
                          </div>



                          <div class="mb-3">
                            <small class="text-muted">Sector:</small>
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

                          <table class="table">
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
                      <h5 class="mb-0">Nueva unidad</h5>

                    </div>


                    <div class="mt-2 card-body">

                      <form id="data_ente">

                        <div class="row">
                          <div class="mb-3 col-lg-9">
                            <label for="partida" class="form-label">Denominación</label>
                            <input type="text" id="nombre" class="form-control" placeholder="Denominación de la unidad">
                          </div>

                          <div class="mb-3 col-lg-3">
                            <label for="partida" class="form-label">Actividad</label>
                            <input type="text" id="actividad" class="form-control" value="51">
                          </div>

                        </div>
                        <div class="row mb-3">
                          <div class="col-lg-4">
                            <label for="sector" class="form-label">Sector</label>
                            <select type="text" class="form-control" id="sector">
                              <option value="">Seleccione</option>

                              <?php
                              $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_sectores");
                              $stmt->execute();
                              $result = $stmt->get_result();
                              if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                  $sector = $row['sector'];
                                  $denominacion = $row['denominacion'];
                                  echo ' <option value="' . $sector . '">' . $sector . ' - ' . $denominacion . '</option>;';
                                }
                              }
                              $stmt->close();
                              ?>
                            </select>
                          </div>

                          <div class="col-lg-4">
                            <label for="programa" class="form-label">Programa</label>
                            <select type="text" class="form-control" id="programa">
                              <option value="">Seleccione</option>
                            </select>
                          </div>
                          <div class="col-lg-4">
                            <label for="proyecto" class="form-label">Proyecto</label>
                            <select class="form-control" id="proyecto">
                              <option value="">Seleccione</option>
                              <option value="00">00</option>

                              <?php
                              $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_proyectos");
                              $stmt->execute();
                              $result = $stmt->get_result();
                              if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                  $proyecto_id = $row['proyecto_id'];
                                  $denominacion = $row['denominacion'];
                                  echo ' <option value="' . $proyecto_id . '">' . $proyecto_id . ' - ' . $denominacion . '</option>;';
                                }
                              }
                              $stmt->close();
                              ?>
                            </select>


                          </div>
                        </div>
                        <div class="mb-4">
                          <label for="tipo_ente" class="form-label">Tipo</label>
                          <select type="text" class="form-control" id="tipo_ente">
                            <option value="">Seleccione</option>
                            <option value="J">Jurídico</option>
                            <option value="D">Descentralizado</option>
                          </select>
                        </div>
                        <div class="mt-4 d-flex justify-content-between">
                          <button type="button" class="btn btn-secondary" id="btn-cancelar-registro">Cancelar</button>
                          <button type="button" class="btn btn-primary" id="btn-registro">Guardar</button>
                        </div>

                      </form>
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
                        Unidades registradas
                      </h5>
                      <button class="btn btn-secondary btn-sm" onclick="nuevoProyecto()">
                        <i class="bx bx-plus"></i>
                        Nueva unidad
                      </button>
                    </div>


                    <div class="mt-2 card-body">

                      <table class="table" id="table">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Denominación</th>
                            <th>Sector</th>
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

        </div>
        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">




          <div class="row ">

            <div class="col-lg-12" id="vista-tabla-2">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex flex-column">
                    <div class="card-title mb-auto d-flex justify-content-between">
                      <h5 class="mb-0">
                        Unidades registradas
                      </h5>
                      <button class="btn btn-secondary btn-sm" onclick="nuevoProyecto()">
                        <i class="bx bx-plus"></i>
                        Nueva unidad
                      </button>
                    </div>


                    <div class="mt-2 card-body table-responsive">

                      <table class="table" id="table-2">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Denominación</th>
                            <th>Sector</th>
                            <th>Actividad</th>
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


        </div>

      </div>












      <div class="dialogs">
        <div class="dialogs-content " style="width: 45%;">
          <span class="close-button">×</span>
          <h5 class="mb-1">Agregar nueva dependencia interna</h5>
          <hr>
          <p class="text-danger mb-3">
            Unidad principal: <b id="nombre_unidad"></b><br>
            Sector: <b id="sector_informacion"></b><br>
          </p>
          <div class="card-body">
            <form id="nuevo_ente">

              <input type="text" hidden id="id_ente">

              <div class="row mb-3">
                <div class="col-lg-4">
                  <label for="sector-2" class="form-label">Sector</label>
                  <select class="form-control" id="sector-2">
                    <option value="">Seleccione</option>

                    <?php
                    $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_sectores");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                        $sector = $row['sector'];
                        $denominacion = $row['denominacion'];
                        echo ' <option value="' . $sector . '">' . $sector . ' - ' . $denominacion . '</option>;';
                      }
                    }
                    $stmt->close();
                    ?>
                  </select>
                </div>

                <div class="col-lg-4">
                  <label for="programa-2" class="form-label">Programa</label>
                  <select class="form-control" id="programa-2">
                    <option value="">Seleccione</option>
                  </select>
                </div>
                <div class="col-lg-4">
                  <label for="proyecto-" class="form-label">Proyecto</label>
                  <select class="form-control" id="proyecto-2">
                    <option value="">Seleccione</option>
                    <option value="00">00</option>

                    <?php
                    $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_proyectos");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                        $proyecto_id = $row['proyecto_id'];
                        $denominacion = $row['denominacion'];
                        echo ' <option value="' . $proyecto_id . '">' . $proyecto_id . ' - ' . $denominacion . '</option>;';
                      }
                    }
                    $stmt->close();
                    ?>
                  </select>


                </div>
              </div>
              <div class="row">
                <div class="mb-3 col-lg-3">
                  <label for="actividad_suu" class="form-label">Actividad</label>
                  <input id="actividad_suu" type="number" min="0" minlength="2" maxlength="2" class="form-control">
                </div>
                <div class="mb-3 col-lg-9">
                  <label for="denominacion_suu" class="form-label">Denominación</label>
                  <input id="denominacion_suu" type="text" class="form-control">
                </div>
              </div>




              <div class="mb-2 d-flex justify-content-between">
                <hr class="hr-w-btn">
                <button type="submit" class="btn btn-primary">Guardar</button>
              </div>
            </form>



            <div class="mt-4" id="section_lista_actividades_usadas" style="background-color: #f9f9f9;padding: 15px;">
              <p class="mb-3 text-info">Lista de actividades en uso</p>
              <ul class="list-group" id="actividades_usadas"></ul>
            </div>
          </div>
        </div>
      </div>


      <div class="btn-float hide" id="btn-back">
        <button type="button" class="btn btn-primary"> Regresar a la vista anterior</button>
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
        let programas = []
        <?php
        $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_programas");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $programa = $row['programa'];
            $sector = $row['sector'];
            $denominacion = $row['denominacion'];
            echo 'programas.push(["' . $sector . '", "' . $programa . '", "' . $denominacion . '"]);' . PHP_EOL;
          }
        }
        $stmt->close();
        ?>


        const url_back = '../../back/modulo_pl_formulacion/form_unidades_back.php'
        let entes = []
        let sub_entes = []



        // DATA TABLE
        var DataTable = $("#table").DataTable({
          language: lenguaje_datat
        });
        var DataTable_2 = $("#table-2").DataTable({
          language: lenguaje_datat
        });





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
          console.log(infoProyecto)
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


        let accion


        // verificar si hay dinero antes de mostrar el formulario
        function nuevoProyecto() {
          accion = 'registrar_ente'
          $('#vista_registro').removeClass('hide')
          $('#vista-tabla').addClass('hide')
          $('#vista_datalles').addClass('hide')
        }

        var edt

        // Mostrar interfaz para editar proyecto existente
        function editar(id) {
          edt = id

          $('#vista_datalles').addClass('hide')
          $('#vista_registro').removeClass('hide')
          $('#vista-tabla').addClass('hide')
          $('#cargando').show()
          accion = 'update_ente'

          $('#nombre').val(entes[id]['6'])
          $('#actividad').val(entes[id]['4'])

          let sector = entes[id]['1'];

          $("#sector" + " option[value='" + sector + "']").attr("selected", true);

          if (get_programa(sector)) {
            $("#programa" + " option[value='" + entes[id]['2'] + "']").attr("selected", true);
          }

          $("#proyecto" + " option[value='" + entes[id]['3'] + "']").attr("selected", true);
          $("#tipo_ente" + " option[value='" + entes[id]['5'] + "']").attr("selected", true);

          $('#actividad').attr('disabled', true)
          $('#tipo_ente').attr('disabled', true)
          $('#cargando').hide()

        }

        function cancelarRegistro() {
          $('#vista_registro').addClass('hide')
          $('#vista-tabla').removeClass('hide')
          $('.form-control').val('')

          $('#actividad').attr('disabled', false)
          $('#tipo_ente').attr('disabled', false)
        }
        document.getElementById('btn-cancelar-registro').addEventListener('click', cancelarRegistro)

        // Registrrar nuevo proyecto
        function guardarProyecto() {

          let errors = false
          // Obtener todos los campos de partida y monto
          const nombre = document.getElementById('nombre').value;
          const actividad = document.getElementById('actividad').value;
          const sector = document.getElementById('sector').value;
          const programa = document.getElementById('programa').value;
          let proyecto = document.getElementById('proyecto').value;
          const tipo_ente = document.getElementById('tipo_ente').value;
          // verificar si proyecto tiene un largo de 2 caracteres, sino le pones un cero a la izquierda

          if (!validarCampo('nombre') || !validarCampo('actividad') || !validarCampo('sector') || !validarCampo('programa') || !validarCampo('proyecto') || !validarCampo('tipo_ente')) {
            errors = true;
          } // verificar que ningun campo este vacio

          // Si hay errores, detener la ejecución
          if (errors) {
            return;
          }

          const unidad = {
            nombre: nombre,
            actividad: actividad,
            sector: sector,
            programa: programa,
            proyecto: proyecto,
            tipo_ente: tipo_ente,
            id_ente: null
          }

          if (accion == 'update_ente') {
            unidad.id_ente = edt
          }

          $.ajax({
            url: url_back,
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              unidad: unidad,
              accion: accion
            }),
            success: function(response) {
              if (response.success) {
                toast_s('success', 'Unidad ' + (accion == 'update_ente' ? 'actualizada' : 'registrada') + ' con éxito')
                get_tabla()
                cancelarRegistro()

                document.getElementById('data_ente').reset();

              } else {
                console.log(response)
                toast_s('error', 'Error al ' + (accion == 'update_ente' ? 'actualizar' : 'registrar') + ' la unidad')
              }
            },
            error: function(xhr, status, error) {
              console.log(xhr.responseText);

            },
          });
        }

        // addevenlister btn-registro click

        document.getElementById('btn-registro').addEventListener('click', guardarProyecto);



        // eliminar ente
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
                  accion: 'eliminar_ente',
                  id: id
                }),
                success: function(response) {

                  if (response.success) {
                    get_tabla()
                    get_sub_unidades()
                    toast_s("success", "Eliminado con éxito");
                  } else {
                    toast_s("error", response.error);
                  }
                },
                error: function(xhr, status, error) {
                  console.error(xhr.responseText);
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

          if (event.target.closest('.btn-plus')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.btn-plus').getAttribute('data-add-id');
            agregaSubUnidad(id);
          }
          if (event.target.closest('.li-search')) { // ACCION DE ELIMINAR
            const texto = event.target.closest('.li-search').getAttribute('data-search');
            buscarSuu(texto);
          }



        });

        function agregaSubUnidad(id) {
          const info_id = entes[id][0]
          const info_sector = entes[id][1]
          const info_programa = entes[id][2]
          const info_proyecto = entes[id][3]
          const info_actividad = entes[id][4]
          const info_tipo_ente = entes[id][5]
          const info_ente_nombre = entes[id][6]

          document.getElementById('nombre_unidad').innerHTML = info_ente_nombre
          document.getElementById('sector_informacion').innerHTML = `${info_sector}.${info_programa}.${info_proyecto}`
          document.getElementById('id_ente').value = info_id


          listarActividades()


          toggleDialogs()
        }

        function listarActividades() {
          // info de la actividades en uso
          let id = document.getElementById('id_ente').value;
          const actividades_usadas = sub_entes[id] || []; // Asigna un array vacío si sub_entes[id] es undefined

          if (actividades_usadas.length == 0) {
            document.getElementById('section_lista_actividades_usadas').classList.add('hide')
          } else {
            document.getElementById('section_lista_actividades_usadas').classList.remove('hide')
          }

          const actividades_usadas_html = actividades_usadas.map(function(item) {
            return `<li class="list-group-item d-flex justify-content-between align-items-center pointer li-search" data-search="${item[7]}"><small>${item[7]}</small> <span class="badge bg-primary rounded-pill">${item[5]}</span></li>`;
          }).join('');

          document.getElementById('actividades_usadas').innerHTML = actividades_usadas_html;

        }


        function buscarSuu(suu) {
          DataTable_2.search(suu).draw()
          toggleDialogs()
          document.getElementById('pills-profile-tab').click();
          document.getElementById('btn-back').classList.remove('hide');
        }





        document.getElementById('btn-back').addEventListener('click', function(event) {
          listarActividades()
          document.getElementById('btn-back').classList.add('hide');
          document.getElementById('pills-home-tab').click();
          toggleDialogs()
        })


        // onsubmit nuevo_ente
        document.getElementById('nuevo_ente').addEventListener('submit', function(event) {
          event.preventDefault();

          const sector_2 = document.getElementById('sector-2').value;
          const programa_2 = document.getElementById('programa-2').value;
          const proyecto_2 = document.getElementById('proyecto-2').value;
          const actividad_suu = document.getElementById('actividad_suu').value;
          const denominacion_suu = document.getElementById('denominacion_suu').value;

          if (denominacion_suu == '51') {
            toast_s('error', 'No se puede registrar por la actividad 51')
            return
          }

          let id = document.getElementById('id_ente').value;

          const actividades_usadas = sub_entes[id] || [];

          const actividades_usadas_ente = actividades_usadas.map(function(item) {
            return item[5];
          });

          let campos = ['id_ente', 'sector-2', 'programa-2', 'proyecto-2', 'actividad_suu', 'denominacion_suu'];

          let errors = false

          campos.forEach(campo => {
            if (!validarCampo(campo)) {
              errors = true;
            }
          });

          if (errors) {
            toast_s('error', 'Todos los campos son obligatorios.');
            return;
          }


          if (actividades_usadas_ente.indexOf(actividad_suu) != -1) {
            toast_s('error', 'La actividad ya esta en uso.')
            return;
          }


          $.ajax({
            url: url_back,
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              accion: 'guardar_suu',
              info: {
                id_ente: id,
                sector: sector_2,
                programa: programa_2,
                proyecto: proyecto_2,
                actividad_suu: actividad_suu,
                denominacion_suu: denominacion_suu
              }
            }),


            success: function(response) {
              let data_tabla = [] // Informacion de la tabla

              if (response.success) {
                get_sub_unidades()
                toggleDialogs()
                toast_s('success', 'Se ha agregado cone éxito.');
              } else {
                toast_s('error', 'Error al agregar la unidad.');
              }
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
            },
          });




        })





        function get_tabla() {
          $.ajax({
            url: url_back,
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              accion: 'get_unidades'
            }),
            success: function(response) {
              let data_tabla = [] // Informacion de la tabla

              if (response.success) {
                let count = 1;
                DataTable.clear()


                response.success.forEach(function(item) {


                  entes[item.id] = [
                    item.id,
                    item.sector,
                    item.programa,
                    item.proyecto,
                    item.actividad,
                    item.tipo_ente,
                    item.ente_nombre
                  ]

                  data_tabla.push([
                    count++,
                    item.tipo_ente,
                    item.ente_nombre,
                    item.sector + "." + item.programa + "." + item.proyecto,
                    item.tipo_ente == 'J' ?
                    `<button title="Agregar dependencia interna" class="btn btn-plus btn-sm bg-brand-color-1 text-white " data-add-id="${item.id}"><i class="bx bx-plus"></i></button>` : '-',
                    `<button class="btn btn-edit btn-sm bg-brand-color-2 text-white " data-edit-id="${item.id}"><i class="bx bx-edit-alt"></i></button>`,
                    `<button class="btn btn-danger btn-sm btn-delete" data-delete-id="${item.id}"><i class="bx bx-trash"></i></button>`
                  ]);



                });

                DataTable.rows.add(data_tabla).draw()
              }
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
              console.error(error);
            },
          });
        }
        get_tabla()



        function get_sub_unidades() {
          $.ajax({
            url: url_back,
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              accion: 'get_sub_unidades'
            }),
            success: function(response) {
              let data_tabla = [] // Informacion de la tabla
              sub_entes = []

              if (response.success) {
                let count = 1;
                DataTable_2.clear()


                response.success.forEach(function(item) {



                  if (!sub_entes[item.ue]) {
                    sub_entes[item.ue] = [];
                  }
                  // Agrega los elementos al array existente
                  sub_entes[item.ue].push([
                    item.id,
                    item.ue,
                    item.sector,
                    item.programa,
                    item.proyecto,
                    item.actividad,
                    item.tipo_ente,
                    item.ente_nombre,
                    item.nombre_ente_p
                  ]);

                  data_tabla.push([
                    count++,
                    item.tipo_ente,
                    item.ente_nombre + "<br> <small class='mt-0 text-muted'>" + item.nombre_ente_p + "</small>",
                    item.sector + "." + item.programa + "." + item.proyecto,
                    item.actividad,
                    `<button class="btn btn-danger btn-sm btn-delete" data-delete-id="${item.id}"><i class="bx bx-trash"></i></button>`
                  ]);



                });

                DataTable_2.rows.add(data_tabla).draw()
              }
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
              console.error(error);
            },
          });
        }
        get_sub_unidades()

        // ejecutar una funcion cuando la pagina se cargue
        $(document).ready(function() {
          $('#vista_registro').addClass('hide')
        })

        // cargar el programa cuando el sector cambie

        document.getElementById('sector').addEventListener('change', function(event) {
          let sector_s = this.value;
          get_programa(sector_s)
        })


        function get_programa(sector_s) {
          document.getElementById('programa').innerHTML = '<option value="">Seleccione</option>'
          programas.forEach(element => {
            if (element[0] == sector_s) {
              document.getElementById('programa').innerHTML += `<option value="${element[1]}">${element[1]} - ${element[2]}</option>`
            }
          });

          return true
        }




        // cargar el programa cuando el sector cambie
        document.getElementById('sector-2').addEventListener('change', function(event) {
          let sector_s = this.value;
          document.getElementById('programa-2').innerHTML = '<option value="">Seleccione</option>'
          programas.forEach(element => {
            if (element[0] == sector_s) {
              document.getElementById('programa-2').innerHTML += `<option value="${element[1]}">${element[1]} - ${element[2]}</option>`
            }
          });
        })
      </script>

</body>

</html>