<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Grupos de nominas</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.dataTables.css" />

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
                <h5 class="mb-0">Grupo de nominas</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-xl-12 col-md-6">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <h5 class="mb-0">Grupos</h5>
                <button class="btn btn-light" id="btn-svr" onclick="setVistaRegistro()"> Nuevo grupo</button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive p-1">
                <table id="table" class="table">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Nombre del grupo</th>
                      <th class="w-15"></th>
                    </tr>

                    
                  </thead>
                  <tbody>
                   
                  </tbody>
                  <tfoot>
                  <tr id="section_registro" class="hide">
                      <td><input type="text" class="form-control" name="codigo" id="codigo" placeholder="Código"></td>
                      <th><input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre del grupo"></th>
                      <th><button type="submit" class="btn btn-sm btn-primary" id="btn-guardar">Guardar</button></th>
                    </tr>
                  </tfoot>
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
  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/fonts/custom-font.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script>
    const url_back = '../../back/modulo_nomina/nom_grupos_back.php';

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
              var codigo = data[i].codigo;
              var nombre = data[i].nombre;
              var id = data[i].id;

              $('#table tbody').append('<tr><td>' + codigo + '</td><td>' + nombre + '</td><td><a href="#!" class="badge me-2 bg-brand-color-2 text-white f-12" onclick="eliminar(' + id + ')">Eliminar</a></td></tr>');
            }
          }

        }

      });
    }
    // ready function
    cargarTabla()




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






    // enviar data al back
    function guardar() {
      let codigo = document.getElementsByName('codigo')[0].value;
      let nombre = document.getElementsByName('nombre')[0].value;

      if (nombre.trim() === '' || codigo.trim() === '') {
        toast_s('error', 'Por favor, complete todos los campos')
        return;
      } else {

        $.ajax({
          url: url_back,
          type: 'POST',
          data: {
            codigo: codigo,
            nombre: nombre,
            registro: true
          },
          success: function(text) {
            console.log(text)

            if (text == 'ok') {
              cargarTabla()
              toast_s('success', 'Creado con éxito')
              $('#codigo').val('');
              $('#nombre').val('');
              setVistaRegistro()
            } else if (text == 'ye') {
              toast_s('error', 'Ya existe un concepto con este nombre')
            } else {
              toast_s('error', 'error')
            }
          }
        });

      }
    }

    // cuando el boton btn-guardar sea pulsado, se ejecuta la funcion anterior
    $(document).ready(function() {
      document.getElementById('btn-guardar').addEventListener('click', guardar);
    });
  </script>

</body>

</html>