<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Nuevos empleados</title>
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
                <h5 class="mb-0">Actualización de datos</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Actualizaciones por aprobar</h5>
                  <small class="text-muded">Lista de empleados con actualizaciones pendientes</small>
                </div>
              </div>
            </div>
            <div class="card-body">

              <div class="table-responsive p-1">
                <table id="table" class="table table-hover">
                  <thead>
                    <tr>
                      <th class="w-10"></th>
                      <th class="w-20">Cédula</th>
                      <th class="w-30">Nombre</th>
                      <th class="w-30">Fecha de solicitud</th>
                      <th class="w-5"></th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>

                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- [ worldLow section ] end -->
        <!-- [ Recent Users ] end -->
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>


  <div class="dialogs">
    <div class="dialogs-content " style="width: 75%;">
      <span class="close-button">×</span>




      <div class="p-3">
        <h5 class="mb-3">Datos a modificar</h5>
        <table class="table" id="table_mod">
          <thead>
            <tr>
              <td></td>
              <td>Dato</td>
              <td>Valor anterior</td>
              <td>Valor nuevo</td>
              <td>Acción</td>
            </tr>
          </thead>
          <tbody></tbody>

        </table>

      </div>





    </div>
  </div>

  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script>
    const url_back = '../../back/modulo_registro_control/regcon_modificacion_empleados.php';
    var id_revision

    /**
     * Function to load the table with employee data.
     */
    function cargarTabla() {
      $('#table tbody').html('');
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          accion: 'tabla'
        },
        cache: false,
        success: function(data) {
          $('#table tbody').html('');
          if (data) {
            for (var i = 0; i < data.length; i++) {
              const cedula = data[i].cedula;
              const nombres = data[i].nombres;
              const timestamp = data[i].timestamp;
              const id = data[i].id;

              $('#table tbody').append(`<tr>
              <td><img  src="../../src/assets/images/icons-png/empleado.png" alt="activity-user"></td>
              <td>` + cedula + `</td>
              <td>` + nombres + `</td>
              <td>` + timestamp + `</td>
              <td><a class="pointer btn-wicon badge me-2 bg-brand-color-1 text-white f-12" onclick="revisar(` + id + `)"><i class="bx bx-detail me-1"></i> Revisar</a></td>
              </tr>`);
            }
          } else {
            checkTablesForData();
          }

        }

      });
    }
    cargarTabla()

    /**
     * Deletes a record with the specified ID.
     * @param {number} id - The ID of the record to be deleted.
     * @returns {boolean} - Returns true if the record is deleted successfully, false otherwise.
     */
    function eliminar() {
      toggleDialogs();
      Swal.fire({
        title: "¿Estás seguro?",
        html: "Se eliminara la solicitud de registro <strong>¡No podrás revertir esto!</strong>",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#04a9f5",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminarlo!",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: url_back + 'regcon_empleados_delete.php',
            type: "POST",
            data: {
              id: id_revision
            },
            success: function(text) {

              if (text.trim() == "ok") {
                cargarTabla();
                toast_s("success", "Eliminado con éxito");
              } else {
                toast_s("error", text);
              }
            },
          });
        } else {
          toggleDialogs()

        }
      });
    }


    const nombre_campos = {
      'nacionalidad': 'Nacionalidad',
      'cedula': 'Cédula',
      'nombres': 'Nombres',
      'otros_años': 'Otros años',
      'status': 'Estatus',
      'observacion': 'Observación',
      'cod_cargo': 'Cargo',
      'banco': 'Banco',
      'cuenta_bancaria': 'Cuenta bancaria',
      'hijos': 'Hijos',
      'instruccion_academica': 'Instrucción académica',
      'discapacidades': 'Discapacidades',
      'tipo_nomina': 'Tipo de nómina',
      'id_dependencia': 'Dependencia',
      'verificado': 'Verificado',
      'correcion': 'Corrección',
      'beca': 'Beca',
      'fecha_ingreso': 'Fecha de ingreso'
    }


    /**
     * Function to review employee modifications.
     *
     * @param {number} id - The ID of the employee to be reviewed.
     */
    function revisar(id) {
      $('#cargando').show();

      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          accion: 'revisar',
          id: id
        },
        cache: false,
        success: function(data) {
          $('#cargando').hide();
          toggleDialogs();

          $('#table_mod tbody').html('');

          for (var i = 0; i < data.length; i++) {
            const campo = data[i].campo;
            const valor = data[i].valor;
            const valor_antiguo = data[i].valor_antiguo;
            const id = data[i].id;

            $('#table_mod tbody').append(`
                    <tr>
                        <td><img src="../../src/assets/images/icons-png/empleado.png" alt="activity-user"></td>
                        <td>${nombre_campos[campo]}</td>
                        <td style="background-color: #fcfcfc;">${valor_antiguo}</td>
                        <td style="background-color: #fbfffb;">${valor}</td>
                        <td class='d-flex'>
                        
                        <a class="pointer btn-wicon badge me-2 bg-brand-color-1 text-white f-11" onclick="accion('a', ${id})">Aceptar</a>
                        <a class="pointer btn-wicon badge me-2 bg-brand-color-2 text-white f-11" onclick="accion('r', ${id})">Rechazar</a>
                        
                        </td>
                    </tr>
                `);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#cargando').hide();
          console.error('Error en la solicitud:', textStatus, errorThrown);
          alert('Ocurrió un error al intentar revisar el empleado. Por favor, intente de nuevo.');
        }
      });
    }


    const acciones = {
      'a': ['Al aceptar, se actualizaran los datos del empleado, esta acción pueden alterar el pago de su nomina'],
      'r': ['Al rechazar, se eliminaran los cambios solicitados por la oficina de nomina']
    }


    function accion(accion, id) {
      toggleDialogs();
      Swal.fire({
        title: '¿Estás seguro?',
        html: acciones[accion][0],
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#04a9f5",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, aceptar!",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: url_back,
            type: "POST",
            data: {
              id: id,
              accion: accion
            },
            success: function(text) {
              if (text.text == "ok") {
                cargarTabla()
                toast_s("success", "La acción se realizo con exito");
              } else {
                toast_s("error", text.trim());
              }
            },
              error: function(jqXHR, textStatus, errorThrown) {
              $('#cargando').hide();
              console.error('Error en la solicitud:', textStatus, errorThrown);
              alert('Ocurrió un error al intentar revisar el empleado. Por favor, intente de nuevo.');
            }
          });
        } else {
          toggleDialogs()
        }
      });

    }

  </script>

</body>

</html>