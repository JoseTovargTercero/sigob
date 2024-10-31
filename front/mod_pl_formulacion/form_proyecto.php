<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

$titulo = 'Proyectos';

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

        <div class="col-lg-12" id="vista-tabla">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0"><?php echo $titulo ?></h5>
                  <button class="btn btn-info btn-sm" onclick="nuevoProyecto()">
                    <i class="bx bx-plus"></i>
                    Nuevo proyecto
                  </button>
                </div>


                <div class="mt-2 card-body">

                  <table class="table" id="table">
                    <thead>
                      <tr>
                        <th style="width: 5%;">#</th>
                        <th>Proyecto</th>
                        <th>Nombre</th>
                        <th style="width: 5%;"></th>
                        <th style="width: 5%;"></th>
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
          <h5 class="mb-1" id="titulo_form"></h5>
          <hr>

          <div class="mt-2 card-body">

            <div class="mb-3">
              <label for="partida" class="form-label">Denominación</label>
              <input type="text" id="nombre" class="form-control" placeholder="Nombre del programa">
            </div>

            <div class="mb-3">
              <label for="proyecto" class="form-label">Proyecto</label>
              <input type="number" min="0" class="form-control" id="proyecto" name="proyecto" placeholder="Proyecto">
            </div>



            <div class="mb-3 text-end">
              <button class="btn btn-primary" id="btn-registro">Guardar</button>
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
      <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0"></script>


      <script>
        const url_back = '../../back/modulo_pl_formulacion/form_proyecto_back.php'
        let datos = []


        // DATA TABLE
        var DataTable = $("#table").DataTable({
          language: lenguaje_datat
        });


        let accion

        // iniciar nuevo registro de proyecto
        function nuevoProyecto() {
          accion = 'registrar'
          $('#titulo_form').html('Nuevo proyecto')
          toggleDialogs()
        }


        var edt
        // Mostrar interfaz para editar proyecto existente
        function editarProyecto(id) {
          edt = id
          accion = 'actualizar'
          $('#titulo_form').html('Editar proyecto')
          $('#nombre').val(datos[id]['0'])
          $("#proyecto").val(datos[id]['1'])
          toggleDialogs()

        }


        // GUARDAR REGISTRO
        document.getElementById('btn-registro').addEventListener('click', function() {
          $(".border-danger").removeClass("border-danger");

          let errors = false

          const nombre = $("#nombre").val();
          const proyecto = $("#proyecto").val();

          (!validarCampo('nombre') ? errors = true : '');
          (!validarCampo('proyecto') ? errors = true : '');



          if (proyecto < 0 || proyecto.length != 2) {
            $('#proyecto').addClass('border-danger')
            toast_s('error', 'El valor del campo es incorrecto')
            errors = true;
          }

          // Si hay errores, detener la ejecución
          if (errors) {
            return;
          }

          const data = {
            nombre: nombre.trim(),
            proyecto: proyecto,
            id: null
          }

          // enviar los datos a black con el nuevo formato
          if (accion == 'actualizar') {
            data.id = edt
          }

          $.ajax({
            url: url_back,
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              data: data,
              accion: accion
            }),
            success: function(response) {
              if (response.success) {
                toast_s('success', 'Proyecto ' + (accion == 'update_proyecto' ? 'actualizado' : 'registrado') + ' con éxito')
                get_tabla()
                toggleDialogs()
                $("#nombre").val('');
                $("#proyecto").val('');
              } else {
                console.log(response)
                toast_s('error', response.error)
              }
            },
            error: function(xhr, status, error) {
              console.log(xhr.responseText);
            },
          });
        });


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
                  console.error(error);
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
            editarProyecto(id);
          }
          if (event.target.closest('.btn-detalles-p')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.btn-detalles-p').getAttribute('data-id-proyecto');
            getDetallesProyecto(id);
          }
        });



        function get_tabla() {
          $.ajax({
            url: sistema.tablas,
            type: "json",
            contentTyrepe: 'application/json',
            data: JSON.stringify({
              table: 'pl_proyectos'
            }),
            success: function(response) {
              let data_tabla = [] // Informacion de la tabla
              if (response.success) {
                let count = 1;
                DataTable.clear()

                response.success.forEach(function(item) {

                  datos[item.id] = [
                    item.denominacion,
                    item.proyecto_id,
                    item.id
                  ]

                  data_tabla.push([
                    count++,
                    item.proyecto_id,
                    item.denominacion,
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
      </script>

</body>

</html>