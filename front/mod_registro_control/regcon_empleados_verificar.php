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
                <h5 class="mb-0">Empleados</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->




        <div class="col-lg-12 mb-3 hide" id="informacionPersona">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Detalles</h5>
                  <small class="text-muded">Información de la solicitud</small>
                </div>
                <button class="btn btn-light" onclick="setVista()"> Cancelar</button>
              </div>
            </div>
            <div class="card-body">

              <div class="table-responsive p-1">
               Lorem ipsum, dolor sit amet consectetur adipisicing elit. Dolores nostrum possimus quia eos eaque, enim ipsam vero? Placeat, quod cupiditate laborum illum assumenda error tempore soluta sapiente similique, optio quibusdam.
              </div>
            </div>
          </div>
        </div>



        <div class="col-lg-12" id="vistaPrincipal">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Nuevos empleados</h5>
                  <small class="text-muded">Administre las solicitudes de admisión</small>
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
                      <th class="w-30">Dependencia</th>
                      <th class="w-5"></th>
                      <th class="w-5"></th>
                    </tr>

                    <tr id="section_registro" class="hide">
                      <td></td>
                      <td class="ps-0">
                        <div>
                          <input type="text" class="form-control  check-length" name="prefijo" id="prefijo" placeholder="Prefijo" data-max="4">
                        </div>
                      </td>
                      <th class="ps-0"><input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre del banco"></th>
                      <th class="ps-0">
                        <div>
                          <input type="text" class="form-control check-length" name="cuenta_matriz" id="cuenta_matriz" placeholder="Cuenta matriz" data-max="20">
                        </div>
                      </th>
                      <th class="ps-0"><input type="text" class="form-control" name="afiliado" id="afiliado" placeholder="Numero de afiliado"></th>
                      <th><button type="submit" class="btn btn-primary rounded" id="btn-guardar">Guardar</button></th>
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



  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script>
    const url_back = '../../back/modulo_registro_control/';

    function cargarTabla() {

      $.ajax({
        url: url_back + 'regcon_empleados_datos.php',
        type: 'POST',
        data: {
          tabla: true
        },
        cache: false,
        success: function(data) {
          $('#table tbody').html('');
          if (data) {
            for (var i = 0; i < data.length; i++) {
              const cedula = data[i].cedula;
              const nombres = data[i].nombres;
              const dependencia = data[i].dependencia;
              const id = data[i].id;

              $('#table tbody').append(`<tr>
              <td><img  src="../../src/assets/images/icons-png/empleado.png" alt="activity-user"></td>
              <td>` + cedula + `</td>
              <td>` + nombres + `</td>
              <td>` + dependencia + `</td>


              <td><a class="pointer btn-wicon badge me-2 bg-brand-color-1 text-white f-12" onclick="revisar(` + id + `)"><i class="bx bx-detail me-1"></i> Revisar</a></td>

              <td><a class="pointer btn-wicon badge me-2 bg-brand-color-2 text-white f-12" onclick="eliminar(` + id + `)"><i class="bx bx-trash me-1"></i> Eliminar</a></td>
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
              id: id
            },
            success: function(text) {

              if (text == "ok") {
                cargarTabla();
                toast_s("success", "Eliminado con éxito");
              }else if(text == 'negado'){
                toast_s("error", "No se puede eliminar el banco, existen empleados asociados.");
              }  else {
                toast_s("error", text);
              }
            },
          });
        }
      });
    }


    function revisar(id){
      $('#cargando').show()


      $.ajax({
        url: url_back + 'regcon_empleado_datos.php',
        type: 'POST',
        data: {
          id: id
        },
        cache: false,
        success: function(data) {
          //$('#table tbody').html('');
          console.log(data)
          if (data) {
            for (var i = 0; i < data.length; i++) {
              setVista()
              $('#cargando').hide()

            /*  const cedula = data[i].cedula;
              const nombres = data[i].nombres;
              const dependencia = data[i].dependencia;
              const id = data[i].id;*/

            }
          }

        }

      });


    }

    function setVista(){
      $('#informacionPersona').toggleClass('hide')
      $('#vistaPrincipal').toggleClass('hide')
    }
 

    // cuando el boton btn-guardar sea pulsado, se ejecuta la funcion anterior
   /* $(document).ready(function() {
      
    });*/
  </script>

</body>

</html>