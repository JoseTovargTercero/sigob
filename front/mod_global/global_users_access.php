<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="es">

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
                <h5 class="mb-0">Permisos de usuarios</h5>
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
              <h5 class="mb-0">Gestión de permisos</h5>
              <small class="text-muded">Gestione el nivel de acceso de sus usuarios</small>
            </div>
            <div class="card-body">

              <div class="table-responsive p-1">
                <table id="table" class="table table-hover">
                  <thead>
                    <tr>
                      <th class="w-5"></th>
                      <th class="w-20">Usuario</th>
                      <th class="w-10">Fecha de creación</th>
                      <th class="w-50">Acceso</th>
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

        <!-- [ Recent Users ] end -->
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>


  <div class="dialogs">
    <div class="dialogs-content " style="width: 60%;">
      <span class="close-button">×</span>
      <h5 class="mb-1">Permisos des usuario</h5>
      <hr>
      <div class="card-body" style="max-height: 60vh;overflow-y: auto;">
        <table class="table table-hover datatable-table" id="list_permisos">
          <thead>
            <tr>
              <th>Categoría</th>
              <th>Permiso</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>

      </div>
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
    const url_back = '../../back/mod_global/glob_users_access_back.php';

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
              let permisos = data[i].permisos;

              let html_permisos = ''

              if (permisos) {
                permisos.forEach(element => {
                  html_permisos += `
                  <span title="${element[2]}" class="badge bg-info">${element[1]}</span>
                  `
                });
              }

              $('#table tbody').append(`<tr>
              <td><i class="bx bx-user"></i></td>
              <td>` + u_nombre + `</td>
              <td>` + creado + `</td>
              <td>` + html_permisos + `</td>
              <td><a class="pointer btn-wicon badge me-2 bg-brand-color-1 text-white f-12" onclick="modificar(` + u_id + `)"><i class="bx bx-edit-alt me-2"></i> Modificar</a></td>
              </tr>`);
            }
          }

        }

      });
    }
    cargarTabla()

    let mdf



    function permisosDisponibles(user) { // cargar los permisos que le pueden asignar/quitar al usuario
      mdf = user
      $('#cargando').show()


      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          permisos: true,
          user: user
        },
        cache: false,
        success: function(response) {


          $('#list_permisos tbody').html('');
          if (response) {

            var data = JSON.parse(response);

            for (var i = 0; i < data.length; i++) {
              let id = data[i].id;
              let categoria = data[i].categoria;
              let nombre = data[i].nombre;
              let icono = data[i].icono;
              let permisos = data[i].permisos;

              $('#list_permisos tbody').append(`
               <tr data-index="0">
                <td>
                  <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                    <i class="bx ${icono} img-radius"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">${(categoria != null ? categoria : '')}</h6>
                    </div>
                    </div>
                    </td>
                    <td>${nombre}</td>
                    <td>
                      ${(permisos ? '<a onclick="setPermiso(\''+user+'\', \''+id+'\', '+permisos+')" class="avtar avtar-xs btn-light-success"><i class="bx bx-check f-20"></i>' : '</a><a onclick="setPermiso(\''+user+'\', \''+id+'\', '+permisos+')" class="avtar avtar-xs btn-light-danger"><i class="bx bx-x f-20"></i>')}
                    </td>
                    </tr>
               `)
            }

          }
          $('#cargando').hide()

        }

      });
    }

    function setPermiso(user, permiso, status) {
      $('#cargando').show()


      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          set_permisos: true,
          user: user,
          permiso: permiso,
          status: status
        },
        cache: false,
        success: function(response) {
          let respuesta = JSON.parse(response)

          if (respuesta.success) {
            toast_s('success', respuesta.success)
            permisosDisponibles(user)
            cargarTabla()
          } else {
            toast_s('error', respuesta.success)
          }
          $('#cargando').hide()
        }
      });

    }


    function modificar(user) {
      permisosDisponibles(user)
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
  </script>

</body>

</html>