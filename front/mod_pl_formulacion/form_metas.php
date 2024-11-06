<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';




if (isset($_GET["ejercicio"])) {
  $annio = $_GET["ejercicio"];
} else {
  $annio = '2025';
}

$stmt = mysqli_prepare($conexion, "SELECT * FROM `ejercicio_fiscal` WHERE ano = ?");
$stmt->bind_param('s', $annio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $id_ejercicio = $row['id']; // formato: dd-mm-YY
    $situado = $row['situado']; // formato: dd-mm-YY
    $status_ejercicio = $row['status']; // formato: dd-mm-YY
  }
} else {
  $id_ejercicio = 'No';
  $situado = 0; // formato: dd-mm-YY
}
$stmt->close();


?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title class="descripcion_pagina"></title>
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
        $y_d = date('Y') + 1;
        $y_d1 = $y_d - 1;
        $y_d2 = date('Y') + 2;
        ?>

        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> <span class="descripcion_pagina"></span>
        </h4>

        <div class="d-flex gap-1">
          <p><a class="pointer <?php echo ($annio == $y_d1 ? 'text-decoration-underline text-primary' : 'text-dark') ?>" href="?ejercicio=<?php echo $y_d1 ?>"><?php echo $y_d1 ?></a></p>
          <p><a class="pointer <?php echo ($annio == $y_d ? 'text-decoration-underline text-primary' : 'text-dark') ?> " href="?ejercicio=<?php echo $y_d ?>"><?php echo $y_d ?></a></p>
          <p><a href="?ejercicio=<?php echo $y_d2 ?>" class="pointer <?php echo ($annio == $y_d2 ? 'text-decoration-underline text-primary' : 'text-dark') ?>"><?php echo $y_d2 ?></a></p>

        </div>
      </div>


      <div class="row ">
        <div class="col-lg-12" id="vista-tabla">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0 descripcion_pagina"></h5>
                  <?php
                  if ($id_ejercicio != 'No') {
                    echo '<button class="btn btn-secondary btn-sm" onclick="nuevaActividad()"><i class="bx bx-plus"></i>Nueva meta</button>';
                  }
                  ?>
                </div>
                <div class="mt-2 card-body">
                  <table class="table" id="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Programa</th>
                        <th>Meta</th>
                        <th>Cantidades</th>
                        <th>Costo</th>
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
        <div class="dialogs-content " style="width: 45%;">
          <span class="close-button">×</span>
          <h5 class="mb-1">Agregar nueva meta</h5>

          <div class="card-body pt-3">

            <form id="data_form_standar">
              <div class="row d-asignacion mb-3">
                <div class="col-lg-6">

                  <label class="form-label">Sector</label>
                  <select class="form-control c_sector chosen-select" id="sector" name="sector">
                    <option value="">Seleccione</option>
                  </select>
                </div>
                <div class="col-lg-6">
                  <label class="form-label">Programa</label>
                  <select class="form-control c_program  chosen-select" id="programa" name="programa">
                    <option value="">Seleccione</option>
                  </select>
                </div>
              </div>


              <div class="mb-3">
                <label for="partida" class="form-label">Denominación</label>
                <input type="text" id="denominacion" name="denominacion" class="form-control" placeholder="Denominación de la meta">
              </div>

              <div class="row mb-3">
                <div class="col-lg-6">
                  <label for="partida" class="form-label">Unidad de medida</label>
                  <input type="text" id="unidad_medida" name="unidad_medida" class="form-control" placeholder="Denominación de la actividad">
                </div>

                <div class="col-lg-6">
                  <label for="partida" class="form-label">Cantidades programadas</label>
                  <input type="text" id="cantidades" name="cantidades" class="form-control" placeholder="Cantidades programadas">
                </div>
              </div>

              <div class="mb-3">
                <label for="partida" class="form-label">Costo financiero</label>
                <input type="text" id="costo" name="costo" class="form-control" placeholder="Costo financiero">
              </div>


              <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">Guardar</button>
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
      <script src="../../src/assets/js/forms.js"></script>
      <script src="../../src/assets/js/ajax_class.js"></script>


      <script>
        const url_back = '../../back/modulo_pl_formulacion/form_metas_back.php'
        let sectores_options = []
        let programas_options = []
        const id_ejercicio = "<?php echo $id_ejercicio ?>"


        dbh_select('pl_sectores').then(response => {
          handleResponse(
            response,
            sectores_options,
            '.c_sector',
            item => `<option value="${item.id}">${item.sector}.${item.denominacion}</option>`
          );
        }).catch(error => console.error("Error al obtener pl_sectores:", error));

        dbh_select('pl_programas').then(response => {
          handleResponse(
            response,
            programas_options,
            null,
            item => [item.sector, item.programa, item.denominacion, item.id]
          );
        }).catch(error => console.error("Error al obtener pl_programas:", error));


        // DATA TABLE
        var DataTable = $("#table").DataTable({
          language: lenguaje_datat
        });

        let metas = []
        let accion = null
        let edt = null


        // Iniciar registro
        function nuevaActividad() {
          accion = 'registrar'
          $('#actividad').attr('disabled', false)
          toggleDialogs()
        }


        // Mostrar interfaz para editar proyecto existente
        function editar(id) {
          edt = id
          accion = 'actualizar'

          $('#denominacion').val(metas[id]['5'])
          $('#unidad_medida').val(metas[id]['8'])
          $('#cantidades').val(metas[id]['0'])
          $('#costo').val(metas[id]['1'])
          $('#sector').val(metas[id]['10']).trigger("chosen:updated");

          let selectProgram = document.getElementById('programa');
          actualizarSelectPrograma(metas[id]['10'], selectProgram); // actualizar los opt del select programa antes de cambiar su valor
          $('#programa').val(metas[id]['6']).trigger("chosen:updated");

          toggleDialogs()
        }


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
                  accion: 'borrar',
                  id: id
                }),
                success: function(response) {
                  console.log(response)

                  if (response.success) {
                    get_tabla()
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

          if (event.target.closest('.btn-destroy')) {
            const id = event.target.closest('.btn-destroy').getAttribute('data-delete-id');
            eliminar(id);
          }
          if (event.target.closest('.btn-update')) {
            const id = event.target.closest('.btn-update').getAttribute('data-edit-id');
            editar(id);
          }
        });

        function initializeChosenEventListeners() {
          // Selecciona todos los elementos con la clase .c_sector y .chosen-select
          document.querySelectorAll('.c_sector.chosen-select').forEach(element => {
            // Detecta cambios usando el evento específico de Chosen
            $(element).on('change', function(event) {

              const sector_s = event.target.value;
              const contenedorAsignacion = event.target.closest('.d-asignacion');

              if (contenedorAsignacion) {
                const selectPartida = contenedorAsignacion.querySelector('.c_program');
                if (selectPartida) {
                  actualizarSelectPrograma(sector_s, selectPartida);
                }
              }
            });
          });
        }

        // Llama a la función para establecer los listeners al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
          initializeChosenEventListeners();
          $('.chosen-select').chosen().trigger("chosen:updated");
        });




        function get_tabla() {
          $.ajax({
            url: '../../back/sistema_global/_DBH-select.php',
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              table: 'pl_metas',
              config: '_join_programas',
              id_ejercicio: id_ejercicio
            }),
            success: function(response) {
              let data_tabla = [] // Informacion de la tabla
              metas = []

              if (response.success) {
                let count = 1;
                DataTable.clear()


                response.success.forEach(function(item) {

                  metas[item.id] = [
                    item.cantidad,
                    item.costo,
                    item.denominacion,
                    item.id,
                    item.id_ejercicio,
                    item.meta,
                    item.programa,
                    item.programa_n,
                    item.unidad_medida,
                    item.sector_n,
                    item.sector_id
                  ]

                  data_tabla.push([
                    count++,
                    item.sector_n + '.' + item.programa_n,
                    item.meta,
                    item.cantidad,
                    item.costo,
                    `<button class="btn btn-update btn-sm bg-brand-color-2 text-white " data-edit-id="${item.id}"></button>`,
                    `<button class="btn btn-danger btn-sm btn-destroy" data-delete-id="${item.id}"></button>`
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
      </script>

</body>

</html>