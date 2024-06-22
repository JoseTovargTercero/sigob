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
                <h5 class="mb-0">Gestión de empleados</h5>
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
                    </tr>

                    <tr id="section_registro" class="hide">
                      <td></td>
                      <td class="ps-0">
                        <div>
                          <input type="text" class="form-control  check-length" name="prefijo" id="prefijo"
                            placeholder="Prefijo" data-max="4">
                        </div>
                      </td>
                      <th class="ps-0"><input type="text" class="form-control" name="nombre" id="nombre"
                          placeholder="Nombre del banco"></th>
                      <th class="ps-0">
                        <div>
                          <input type="text" class="form-control check-length" name="cuenta_matriz" id="cuenta_matriz"
                            placeholder="Cuenta matriz" data-max="20">
                        </div>
                      </th>
                      <th class="ps-0"><input type="text" class="form-control" name="afiliado" id="afiliado"
                          placeholder="Numero de afiliado"></th>
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

  <div class="dialogs">
    <div class="dialogs-content " style="width: 75%;">
      <span class="close-button">×</span>

      <div class="row">
        <div class="col-lg-4 pt-3 pb-3">
          <div class="d-flex user-about-block align-items-center mt-0 mb-3">
            <div class="flex-shrink-0">
              <div class="position-relative d-inline-block">
                <i class='bx bx-user text-primary fs-3'></i>

                <div class="certificated-badge"><i class="fas fa-certificate text-primary bg-icon"></i> <i
                    class="fas fa-check front-icon text-white"></i></div>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="mb-1" id="info_nombre"></h6>
              <p class="mb-0 text-muted" id="info_cargo"></p>
            </div>
          </div>

          <ul class="list-group list-group-flush">

            <li class="list-group-item border-bottom-0 ps-0"><span class="f-w-500">
                <i class="bx bx-calendar m-r-10"></i>Ingreso</span>
              <span id="info_fIngreso" class="float-end"></span>
            </li>
            <li class="list-group-item border-bottom-0 ps-0"><span class="f-w-500">
                <i class="bx bx-calendar-plus m-r-10"></i>Otros años</span>
              <span id="info_otrosAnos" class="float-end"></span>
            </li>
            <li class="list-group-item border-bottom-0 ps-0" title="Instrucción académica"><span class="f-w-500">
                <i class="bx bxs-graduation m-r-10"></i>IA: </span>
              <span id="info_instruccion_academica" class="float-end"></span>
            </li>
            </li>
            <li class="list-group-item border-bottom-0 ps-0"><span class="f-w-500">
                <i class="bx bxs-graduation m-r-10"></i>Hijos: </span>
              <span id="info_hijos" class="float-end"></span>
            </li>
            <li class="list-group-item border-bottom-0 ps-0"><span class="f-w-500">
                <i class="bx bxs-graduation m-r-10"></i>Discapacidad: </span>
              <span id="info_discapacidad" class="float-end"></span>
            </li>


          </ul>


        </div>
        <div class="col-lg-8 border-start p-3 pb-0">

          <div class="card-body">
            <p> <b>Observaciones: </b> <span id="info_observacion"></span></p>
            <h5 class="mt-5 mb-3">Personal Details</h5>
            <div class="table-responsive">
              <table class="table table-borderless">
                <tbody>
                  <tr>
                    <td>Nombre completo</td>
                    <td>:</td>
                    <td id="info_full_name"></td>
                  </tr>
                  <tr>
                    <td>Cédula</td>
                    <td>:</td>
                    <td id="info_cedula"></td>
                  </tr>
                  <tr>
                    <td>Dependencia</td>
                    <td>:</td>
                    <td id="info_dependencia"></td>
                  </tr>
                  <tr>
                    <td>Código del cargo</td>
                    <td>:</td>
                    <td id="info_cod_cargo"></td>
                  </tr>

                  <tr>
                    <td>Banco</td>
                    <td>:</td>
                    <td id="info_banco"></td>
                  </tr>
                  <tr>
                    <td>Cuenta</td>
                    <td>:</td>
                    <td id="info_cuenta_bancaria"></td>
                  </tr>

                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-3 mt-3 w-50 m-a hide" id="sectionComentario">
        <hr>
        <div class="d-flex justify-content-between mb-3">
          <label for="comentario" class="form-label">Comentario</label>
          <button class="btn btn-light-dark btn-sm" id="btn-cancelarEnvio"
            onclick="cancelarRegistro()">Cancelar</button>
        </div>
        <div>
          <textarea type="text" id="comentario" class="form-control check-length" data-max="250"></textarea>
        </div>
      </div>


      <div class="w-100 text-center pt-3">
        <button class="btn btn-danger" id="eliminar-btn">Eliminar solicitud</button>
        <button class="btn btn-secondary" id="enviar_correcion-btn" onclick="mostrarSectionComentario()">Enviar a
          corrección</button>
        <button class="btn btn-primary" id="aceptar-btn">Aceptar empleado</button>
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
    const url_back = '../../back/modulo_registro_control/';
    var id_revision


    function mostrarSectionComentario() {
      $('#sectionComentario').removeClass('hide')
      $('#eliminar-btn').addClass('hide')
      $('#aceptar-btn').addClass('hide')
      $('#enviar_correcion-btn').attr('onclick', 'enviarCorreccion()')
    }

    function cancelarRegistro() {
      $('#sectionComentario').addClass('hide')
      $('#eliminar-btn').removeClass('hide')
      $('#aceptar-btn').removeClass('hide')
      $('#comentario').val('')
      $('#enviar_correcion-btn').attr('onclick', 'mostrarSectionComentario()')
    }


    function enviarCorreccion() {
      const comentario = $('#comentario').val()
      if (comentario == '' || comentario.length < 10) {
        toast_s('error', 'Debe indicar un comentario valido');
        return
      }
      $.ajax({
        url: url_back + 'regcon_correccion_empleado.php',
        type: 'POST',
        data: {
          id: id_revision,
          comentario: comentario
        },
        cache: false,
        success: function (data) {
          const result = JSON.parse(data)
          if (result == 'ok') {
            toast_s('success', 'Se envió a corrección')
            cargarTabla()
            toggleDialogs()
            cancelarRegistro()
          } else {
            toast_s('error', result);
            return
          }
        }
      });
    }

    function aceptarSolicitud() {

    }

    function cargarTabla() {
      $.ajax({
        url: url_back + 'regcon_empleados_datos.php',
        type: 'POST',
        data: {
          tabla: true
        },
        cache: false,
        success: function (data) {
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
              </tr>`);
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
            success: function (text) {

              if (text == "ok") {
                cargarTabla();
                toast_s("success", "Eliminado con éxito");
              } else if (text == 'negado') {
                toast_s("error", "No se puede eliminar el banco, existen empleados asociados.");
              } else {
                toast_s("error", text);
              }
            },
          });
        }
      });
    }

    document.getElementById('eliminar-btn').addEventListener("click", eliminar);




    function agregarGuiones(cadena) {
      // Verifica que la cadena tenga exactamente 20 caracteres
      if (cadena.length !== 20) {
        throw new Error("La cuenta bancaria no es correcta.");
      }

      // Utiliza una expresión regular para insertar los guiones cada 4 caracteres
      return cadena.replace(/(.{4})/g, "$1-").slice(0, -1);
    }




    function revisar(id) {
      $('#cargando').show()

      $.ajax({
        url: url_back + 'regcon_empleado_datos.php',
        type: 'POST',
        data: {
          id: id
        },
        cache: false,
        success: function (data) {
          const datosEmpleado = data[0]
          if (data) {
            id_revision = datosEmpleado['id_empleado']

            $('#info_nombre').html(datosEmpleado['nombres'])
            $('#info_full_name').html(datosEmpleado['nombres'])
            $('#info_cedula').html((datosEmpleado['nacionalidad'] == 1 ? 'V' : 'E') + '-' + datosEmpleado['cedula'])
            $('#info_fIngreso').html(datosEmpleado['fecha_ingreso'])
            $('#info_otrosAnos').html(datosEmpleado['otros_años'])
            $('#info_discapacidad').html((datosEmpleado['discapacidades'] == '1' ? 'Si' : 'No'))
            $('#info_hijos').html(datosEmpleado['hijos'])
            $('#info_instruccion_academica').html(datosEmpleado['instruccion_academica'])
            $('#info_observacion').html(datosEmpleado['observacion'])
            $('#info_cargo').html(datosEmpleado['cargo'])
            $('#info_cod_cargo').html(datosEmpleado['cod_cargo'])

            $('#info_dependencia').html(datosEmpleado['dependencia'])
            $('#info_banco').html(datosEmpleado['banco'])
            $('#info_cuenta_bancaria').html(agregarGuiones(datosEmpleado['cuenta_bancaria']))

            $('#cargando').hide()




            toggleDialogs()
          }
        }
      });
    }

  </script>

</body>

</html>