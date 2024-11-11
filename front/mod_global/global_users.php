<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Usuarios</title>
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
                <h5 class="mb-0">Usuarios</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-lg-12" id="vistaPrincipal">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Lista de usuarios</h5>
                  <small class="text-muded">Cree y gestione sus usuarios</small>
                </div>
                <button class="btn btn-light" id="btn-svr" onclick="setVista('s')"> Nuevo usuario</button>
              </div>
            </div>
            <div class="card-body">

              <div class="table-responsive p-1">
                <table id="table" class="table table-hover">
                  <thead>
                    <tr>
                      <th class="w-5"></th>
                      <th class="w-10">Cédula</th>
                      <th class="w-10">Usuario</th>
                      <th class="w-50">Fecha de creación</th>
                      <th class="w-10"></th>
                      <th class="w-10"></th>
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>

                </table>
              </div>
            </div>
          </div>
        </div>
        <script>
          function setVista(param) {
            if (param == 's') {
              $('#vistaPrincipal').removeClass('col-lg-12')
              $('#vistaPrincipal').addClass('col-lg-8')
              $('#vistaRegistro').removeClass('hide')
            } else {
              $('#vistaPrincipal').addClass('col-lg-12')
              $('#vistaPrincipal').removeClass('col-lg-8')
              $('#vistaRegistro').addClass('hide')
            }

          }
        </script>
        <div class="col-lg-4 hide" id="vistaRegistro">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Nuevo usuario</h5>
                  <small class="text-muded">Ingrese los datos del usuario</small>

                </div>
                <button class="btn btn-light" id="btn-svr" onclick="setVista('h')"> Cancelar</button>
              </div>
            </div>
            <div class="card-body">
              <form id="form-data">

                <div class="mb-3">
                  <label for="nombre" class="form-label">Nombre</label>
                  <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre completo">
                </div>
                <div class="mb-3">
                  <label for="cedula" class="form-label">Cédula</label>
                  <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cédula">
                </div>


                <div class="mb-3">
                  <label for="mail" class="form-label">Correo</label>
                  <input type="email" class="form-control" id="mail" name="mail" placeholder="Correo electrónico">
                </div>

                <div class="mb-3">
                  <label for="pass1" class="form-label">Contraseña</label>
                  <input type="password" autocomplete="off" class="form-control" id="pass1" name="pass1" placeholder="Contraseña">
                </div>

                <div class="mb-3">
                  <label for="pass2" class="form-label">Repetir Contraseña</label>
                  <input type="password" autocomplete="off" class="form-control" id="pass2" name="pass2" placeholder="Repetir Contraseña">
                </div>

                <div class="mb-3 text-end">
                  <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </form>

            </div>
          </div>
        </div>
        <!-- [ worldLow section ] end -->
        <!-- [ Recent Users ] end -->
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>

  <script src="../../src/assets/js/notificaciones.js"></script>


  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script>
    const url_back = '../../back/mod_global/glob_users_back.php';

    function cargarTabla() {

      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          tabla: true
        },
        cache: false,
        success: function(response) {

          $('#table tbody').html('');
          if (response) {

            var data = JSON.parse(response);

            for (var i = 0; i < data.length; i++) {
              let u_id = data[i].u_id;
              let u_nombre = data[i].u_nombre;
              let creado = data[i].creado;
              let u_status = data[i].u_status;
              let cedula = data[i].u_cedula ?? '';


              $('#table tbody').append(`<tr>
              <td><i class="bx bx-user"></i></td>
              <td>` + cedula + `</td>
              <td>` + u_nombre + `</td>
              <td>` + creado + `</td>


              ` + (u_status == '1' ? `<td><a class="pointer btn-wicon badge me-2 bg-warning f-12 text-black" onclick="bloquear(` + u_id + `)"> <i class="bx bx-block"></i> Bloquear</a></td>` : `<td><a class="pointer btn-wicon badge me-2 bg-info f-12 text-white" onclick="bloquear(` + u_id + `)"> <i class="bx bx-unblock"></i> Desbloquear</a></td>`) + `
              <td><a class="pointer btn-wicon badge me-2 bg-brand-color-2 text-white f-12" onclick="eliminar(` + u_id + `)"><i class="bx bx-trash"></i> Eliminar</a></td>
              </tr>`);
            }
          }

        }

      });
    }
    // ready function
    cargarTabla()


    function verGrupo(gurpo) {
      toggleDialogs()

    }




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
            type: "POST",
            data: {
              eliminar: true,
              id: id,
            },
            success: function(response) {
              if (response.trim() == "ok") {
                cargarTabla();

                toast_s("success", "Eliminado con éxito");
              } else {
                toast_s("error", response);
              }
            },
          });
        }
      });
    }

    /**
     * Deletes a record with the specified ID.
     * @param {number} id - The ID of the record to be deleted.
     * @returns {boolean} - Returns true if the record is deleted successfully, false otherwise.
     */
    function bloquear(id) {
      Swal.fire({
        title: "¿Estás seguro?",
        text: "¡Se cambiara el estatus del usuario!",
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
            type: "POST",
            data: {
              bloquear: true,
              id: id
            },
            success: function(response) {
              console.log(response)
              if (response.trim() == "ok") {
                cargarTabla();

                toast_s("success", "Actualizado con éxito");
              } else {
                toast_s("error", response);
              }
            },
          });
        }
      });
    }




















    /*  Guardar informacion */
    $(document).ready(function(e) {
      $("#form-data").on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        if ($('#nombre').val() == '' || $('#cedula').val() == '' || $('#mail').val() == '' || $('#pass1').val() == '' || $('#pass2').val() == '') {
          toast_s('error', 'Rellene todos los campos')
          return
        }

        if ($('#pass1').val() !== $('#pass2').val()) {
          toast_s('error', 'Las contraseñas no coinciden')
          return
        }


        formData.append('registro', true);

        $.ajax({
          type: 'POST',
          url: url_back,
          data: formData,
          contentType: false,
          cache: false,
          processData: false,
          success: function(msg) {
            let msgTrim = msg.trim()

            if (msgTrim == 'ok') {
              toast_s('success', 'Guardado con éxito')
              cargarTabla()
              setVista('a')
              $('#form-data')[0].reset()

            } else if (msgTrim == 'pass') {
              toast_s('error', 'Las contraseñas no coinciden')
            } else if (msgTrim == 'existe') {
              toast_s('error', 'Ya existe un usuario con el correo indicado')
            }

          }
        }).fail(function(jqXHR, textStatus, errorThrown) {

          alert('Uncaught Error: ' + jqXHR.responseText);
        });

      });




    });
  </script>

</body>

</html>