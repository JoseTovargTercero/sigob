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
          <span class="text-muted fw-light">Formulación /</span> Actividades
        </h4>
      </div>


      <div class="row ">
        <div class="col-lg-12" id="vista-tabla">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0">
                    Actividades registradas
                  </h5>
                  <button class="btn btn-secondary btn-sm" onclick="nuevaActividad()">
                    <i class="bx bx-plus"></i>
                    Nueva actividad
                  </button>
                </div>


                <div class="mt-2 card-body">

                  <table class="table" id="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Actividad</th>
                        <th>Denominación</th>
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
          <h5 class="mb-1">Agregar nueva actividad</h5>

          <div class="card-body pt-3">

            <form id="data_actividad">

              <div class="mb-3 ">
                <label for="partida" class="form-label">Denominación</label>
                <input type="text" id="nombre" class="form-control" placeholder="Denominación de la actividad">
              </div>

              <div class="mb-3 ">
                <label for="partida" class="form-label">Actividad</label>
                <input type="text" id="actividad" class="form-control">
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
      <script src="../../src/assets/js/ajax_class.js"></script>

      <script src="../../src/assets/js/amcharts5/index.js"></script>
      <script src="../../src/assets/js/amcharts5/themes/Animated.js"></script>
      <script src="../../src/assets/js/amcharts5/xy.js"></script>



      <script>
        const url_back = '../../back/modulo_pl_formulacion/form_actividades_back.php'



        // DATA TABLE
        var DataTable = $("#table").DataTable({
          language: lenguaje_datat
        });

        let actividades = []
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
          console.log(actividades[id])

          $('#nombre').val(actividades[id]['2'])
          $('#actividad').val(actividades[id]['1'])
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



        // onsubmit registrar actividad
        document.getElementById('data_actividad').addEventListener('submit', function(event) {
          event.preventDefault();

          const nombre = document.getElementById('nombre').value;
          const actividad = document.getElementById('actividad').value;


          let campos = ['nombre', 'actividad'];

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


          $.ajax({
            url: url_back,
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              accion: accion,
              info: {
                nombre: nombre,
                actividad: actividad,
                id: edt
              }
            }),

            success: function(response) {
              let data_tabla = [] // Informacion de la tabla

              if (response.success) {
                get_tabla()
                toggleDialogs()
                $('#data_actividad')[0].reset()

                toast_s('success', 'Se ha agregado cone éxito.');
              } else {
                toast_s('error', 'Error al agregar la unidad. ' + response.error);
              }
            },
            error: function(xhr, status, error) {
              toast_s('error', 'Ocurrió un error al ejecutar la orden ' + xhr.responseText)
            },
          });
        })


        function get_tabla() {
          $.ajax({
            url: '../../back/sistema_global/_DBH-select.php',
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              table: 'pl_actividades'
            }),
            success: function(response) {
              let data_tabla = [] // Informacion de la tabla
              actividades = []

              if (response.success) {
                let count = 1;
                DataTable.clear()


                response.success.forEach(function(item) {


                  actividades[item.id] = [
                    item.id,
                    item.actividad,
                    item.denominacion
                  ]

                  data_tabla.push([
                    count++,
                    item.actividad,
                    item.denominacion,
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