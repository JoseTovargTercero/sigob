<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Estatus de los empleados</title>
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
                <h5 class="mb-0">Estatus de los empleados</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-lg-12 mb-3">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Modificar el estatus de un empleado</h5>
                  <small class="text-muded">Gestione el estatus de sus empleados</small>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="mb-3 col-lg-6">
                  <label for="" class="form-label">Cédula del empleado</label>
                  <input type="number" id="cedula_empleado" class="form-control">
                </div>
                <div class="mb-3 col-lg-6">
                  <label for="" class="form-label">Nombre</label>
                  <input type="text" disabled id="nombre_empleado" class="form-control">
                </div>
                <div class="mb-3 col-lg-6">
                  <label for="" class="form-label">Nomina</label>
                  <input type="text" disabled id="nomina_empleado" class="form-control">
                </div>
                <div class="mb-3 col-lg-6">
                  <label for="" class="form-label">Estatus</label>
                  <input type="text" disabled id="status_empleado" class="form-control">
                </div>
                <div class="mb-3 col-lg-6">
                  <label for="nuevo_status" class="form-label">Nuevo estatus</label>
                  <select name="status" id="nuevo_status" class="form-select employee-select">
                    <option value="" selected>Seleccione</option>
                  </select>
                </div>
                <div class="mb-3 text-right">
                  <button id="btn-cambiar_estatus" class="btn btn-primary hide">Cambiar estatus</button>
                </div>
              </div>
            </div>
          </div>
        </div>




        <div class="col-lg-12 mb-3">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Movimientos realizados</h5>
                  <small class="text-muded">Sus movimientos. En caso de ser requerido, puede revertir un movimiento en un plazo de 5 minutos</small>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Fecha</th>
                      <th>Acción</th>
                      <th>Cambio</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody id="tabla-movimientos">

                  </tbody>

                </table>
              </div>
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
      <h5 class="mb-1">Detalles de los pago realizados</h5>
      <hr>
      <div class="card-body">
        <div class="w-100 d-flex justify-content-between mb-2">
          <h5 class="text-primary" id="titulo_">Reintegros</h5>
        </div>
        <table class="table table-hover">
          <thead>
            <tr>
              <th class="text-center">Fecha de reintegro</th>
              <th class="text-center">Total cancelado</th>
              <th class="text-center"></th>
            </tr>
          </thead>
          <tbody id="tabla-detalles">
            <!-- Aquí se mostrarán los datos -->
          </tbody>
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
    var id_revision, reintegros_por_empleados, id_empleado
    const url_back= '../../back/modulo_nomina/nom_estatus_empleados_back.php'
  
    function verificarFecha(fechaMovimiento) {
      const fechaMovimientoDate = new Date(fechaMovimiento);
      const fechaActual = new Date();
      // Calcular la diferencia en milisegundos
      const diferenciaMs = fechaActual - fechaMovimientoDate;
      // Convertir la diferencia a minutos
      const diferenciaMinutos = diferenciaMs / (1000 * 60);
      return diferenciaMinutos > 180000;
    }

    function tabla_movimientos(section) {
      $('#' + section).html('')

      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          movimientos: true
        },
        cache: false,
        success: function(data) {
          var datos = JSON.parse(data)
          
          try {
            if (datos.error) {
              console.log(datos.error)
            } else if(datos.status != 'vacio'){
              if (datos) {
                let contador = 1
                datos.forEach(element => {
                  $('#' + section).append(`<tr>
                  <td>${contador++}</td>
                  <td>${element.fecha_movimiento}</td>
                  <td>${element.accion}</td>
                  <td>`+element.valor_anterior +' -> '+ element.valor_nuevo+`</td>
                  <td>${(!verificarFecha(element.fecha_movimiento) ? '<button class="btn btn-primary btn-sm" onclick="eliminar(\''+element.id+'\')">Deshacer</button>':'')}</td>
                  </tr>`)
                });
              }
            }

          } catch (error) {
            console.error('No se puede cargar: ' + error)
          }
        }
      });
    }
    // actualiza cada 30 segundos tabla_movimientos('tabla-movimientos')

    tabla_movimientos('tabla-movimientos')
    setInterval(tabla_movimientos, 30000, 'tabla-movimientos')

    let status = {
      'A': 'ACTIVO',
      'R': 'RETIRADO',
      'S': 'SUSPENDIDO',
      'C': 'COMISIÓN DE SERVICIO',
    }



    function getDatosEmpleados() {
      let cedula = this.value
      $.ajax({
        url: '../../back/modulo_nomina/nom_datos_empleados.php',
        type: 'POST',
        data: {
          cedula: cedula
        },
        cache: false,
        success: function(data) {
          if (data.error) {
            toast_s('error', 'El empleado no existe')
            $('#btn-cambiar_estatus').addClass('hide')
          } else {
            $('#btn-cambiar_estatus').removeClass('hide')
            $('#nombre_empleado').val(data.nombres)
            $('#nomina_empleado').val(data.nombreNomina)
            $('#status_empleado').val(status[data.status])
            id_empleado = data.id
            $('#nuevo_status').html('<option value="" selected>Seleccione</option>')
            for (const key in status) {
              if (data.status != key) {
                $('#nuevo_status').append(`<option value="${key}">${status[key]}</option>`)
              }
            }
          }
        }
      });
    }



    
    document.getElementById('cedula_empleado').addEventListener('change', getDatosEmpleados)

    function cambiar_estatus() {

      let cedula_empleado = document.getElementById('cedula_empleado').value
      let nombre_empleado = document.getElementById('nombre_empleado').value
      let nomina_empleado = document.getElementById('nomina_empleado').value
      let status_empleado = document.getElementById('status_empleado').value
      let nuevo_status = document.getElementById('nuevo_status').value

      if (cedula_empleado == '' || nombre_empleado == '' || nomina_empleado == '' || status_empleado == '' || nuevo_status == '') {
        toast_s('error', 'Debe rellenar todos los campos')
        return
      }

      if (id_empleado == undefined || id_empleado == null || id_empleado == '') {
        toast_s('error', 'Ningún empleado seleccionado')
        return
      }

      const datos = [{
        id: id_empleado,
        value: nuevo_status
      }];

      let text_extra = (nuevo_status == 'R' ? 'Esta accion no se podrá revertir':'En caso de ser requerido, puede revertir este movimiento en un plazo de 3 minutos')
      
      Swal.fire({
        title: '¿Estás seguro?',
        html: "Esta acción modificara el estatus del empleado y se generara un movimiento en la nomina a la que pertenece.<br><b>* "+text_extra+"</b>",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {

          fetch('../../back/modulo_nomina/nom_cambiar_status.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(datos)
            })
            .then(response => response.json())
            .then(data => {
              if (data.status == 'success') {
                $('#btn-cambiar_estatus').addClass('hide')
                $('#cedula_empleado').val('')
                $('#nombre_empleado').val('')
                $('#nomina_empleado').val('')
                $('#status_empleado').val('')
                $('#nuevo_status').html('<option value="" selected>Seleccione</option>')
                toast_s('success', 'El estatus del empleado se actualizo correctamente')
                tabla_movimientos('tabla-movimientos')
              } else {
                toast_s('error', 'Ocurrió un error')
              }
            })
        }
      });
    }
    document.getElementById('btn-cambiar_estatus').addEventListener('click', cambiar_estatus)




    /**
     * Deletes a record with the specified ID.
     * @param {number} id - The ID of the record to be deleted.
     * @returns {boolean} - Returns true if the record is deleted successfully, false otherwise.
     */
    function eliminar(id) {
      Swal.fire({
        title: "¿Estás seguro?",
        text: "¡Se eliminará el movimiento y se revertirá la acción!",
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
        console.log(response)

              let data = JSON.parse(response)
              
              if (data.status == "success") {
                tabla_movimientos('tabla-movimientos')
                toast_s("success", "Eliminado con éxito");
              } else {
                toast_s("error", data.mensaje);
              }
            },
          });
        }
      });
    }
  </script>

</body>

</html>