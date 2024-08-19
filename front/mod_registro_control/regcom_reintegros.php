<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Reintegros</title>
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
                <h5 class="mb-0">Reintegros</h5>
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
                  <h5 class="mb-0">Pago de reintegros</h5>
                  <small class="text-muded">Gestione los pagos de reintegros a empleados</small>
                </div>
                <button class="btn btn-primary" id="btn-nuevo-pago">Nuevo pago</button>
              </div>
            </div>
            <div class="card-body">


            <section class="hide" id="section_registro">

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
                <label for="" class="form-label">Fecha de suspensión</label>
                <input type="text" disabled id="fecha__suspension" class="form-control">
              </div>


              <div class="mb-3 col-lg-6">
                <label for="demo-month-only" class="form-label">Desde cuando pagar</label> 
                <input class="form-control" type="month" id="pagar_desde">
              </div>

            </div>
           
              <button id="btn-section-reintegro" class="btn btn-primary">Iniciar pago de reintegro</button>
              



            </section>


            <section id="section_tabla">

            <div class="table-responsive p-1">
                <table id="table" class="table table-hover">
                  <thead>
                    <tr>
                      <th class="w-10"></th>
                      <th class="w-20">Cédula</th>
                      <th class="w-30">Nombre</th>
                      <th class="w-30">Reintegros</th>
                      <th class="w-30">Total</th>
                      <th class="w-5"></th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>

                </table>
              </div>
            </section>



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
    var id_revision, reintegros_por_empleados

    function section_registro() {
      $('#section_registro').toggleClass('hide')
      $('#section_tabla').toggleClass('hide')
    }
    document.getElementById('btn-nuevo-pago').addEventListener('click', section_registro)
    
    
    
    function getDatosEmpleados() {
      let cedula = this.value
      $.ajax({
        url: '../../back/modulo_registro_control/regcon_reintegros_datos_empleados.php',
        type: 'POST',
        data: {
          cedula : cedula
        },
        cache: false,
        success: function(data) {


          if (data.error) {
            toast_s('error', 'El empleado no existe')
          }else{
           
            $('#nombre_empleado').val(data.nombres)
            $('#nomina_empleado').val(data.nombreNomina)
            $('#status_empleado').val(data.status)
            $('#fecha__suspension').val('')


          }
          return
          reintegros_por_empleados = data;
          if (data) {
            let contador = 1;
            for (let i in data) {
              const cedula = data[i].cedula;
              const nombres = data[i].nombres;
              const id_dependencia = data[i].id_dependencia;
              const dependencia = data[i].dependencia;
              const nacionalidad = data[i].nacionalidad;
              const fecha_ingreso = data[i].fecha_ingreso;

            }
          }
        }
      });
    }

    document.getElementById('cedula_empleado').addEventListener('change', getDatosEmpleados)

    /**
     * Function to load the table with employee data.
     */
    function cargarTabla() {
      $('#table tbody').html('');
      $.ajax({
        url: '../../back/modulo_registro_control/regcon_reintegros_tabla.php',
        type: 'POST',
        cache: false,
        success: function(data) {
          reintegros_por_empleados = data;

          $('#table tbody').html('');
          if (data) {
            let contador = 1;
            for (let i in data) {
              const cedula = data[i].cedula;
              const nombres = data[i].nombres;
              const id_dependencia = data[i].id_dependencia;
              const dependencia = data[i].dependencia;
              const nacionalidad = data[i].nacionalidad;
              const fecha_ingreso = data[i].fecha_ingreso;
              const otros_años = data[i].otros_años;
              const status = data[i].status;
              const observacion = data[i].observacion;
              const cod_cargo = data[i].cod_cargo;
              const banco = data[i].banco;
              const cuenta_bancaria = data[i].cuenta_bancaria;
              const hijos = data[i].hijos;
              const instruccion_academica = data[i].instruccion_academica;
              const discapacidades = data[i].discapacidades;
              let time = data[i].time;
              // quitar los ultimos 3 caracteres de time
              time = time.substring(0, time.length - 3);

              const cargo = data[i].cargo;
              const reintegros = data[i].reintegros;

              // cuanta cuantos hay en reintegros
              const reintegros_keys = Object.keys(reintegros)

              let total_pagado = 0;

              reintegros_keys.forEach(element => {
                for (let index in reintegros[element]) {
                  total_pagado += parseInt(reintegros[element][index]['total_pagar'])
                }
              });

              $('#table tbody').append(`<tr>
              <td>${contador}</td>
              <td>${cedula}</td>
              <td>${nombres}</td>
              <td>${reintegros_keys.length}</td>
              <td class="text-center">${total_pagado}</td>
              <td><a class="pointer btn-wicon badge me-2 bg-brand-color-1 text-white f-12" onclick="revisar(` + i + `)"><i class="bx bx-detail me-1"></i> Revisar</a></td>
              </tr>`);
            }
          }

        }

      });
    }
    cargarTabla()

    function revisar(id) {
      $('#tabla-detalles').html('')
      let reintegro = reintegros_por_empleados[id];
      $('#titulo_').html('Reintegros pagados a: ' + reintegro['nombres'])
      let pagos = reintegro.reintegros;
      let cedula = reintegro['cedula'];

      // cuanta cuantos hay en reintegros
      const reintegros_keys = Object.keys(pagos)

      let total_pagado = 0;

      reintegros_keys.forEach(element => {
        let time = element;
        let total_pagado = 0;

        for (let index in pagos[element]) {
          total_pagado += parseInt(pagos[element][index]['total_pagar'])
        }
        $('#tabla-detalles').append(`<tr>
            <td class="text-center">${time}</td>
            <td class="text-center">${total_pagado}</td>
            <td class="text-center"><img class="pointer" onclick="descargarReintegro('${id}', '${time}')" src="../../src/assets/images/icons-png/pdf.svg" width="20px"></td>
          </tr>`)
      });
      toggleDialogs()

    }

    function descargarReintegro(id, fecha) {
      toggleDialogs()

      Swal.fire({
        title: '¿Desea descargar el reintegro?',
        html: 'Se descargará el reintegro correspondiente a la fecha seleccionada, <b>si el reintegro acaba de ser realizado, se almacenara y no podra ser eliminado</b>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar',
        }).then((result) => {
          if (result.value) {
            window.location.href = '../../back/modulo_registro_control/regcon_reintegro_pdf.php?id_empleado='+id+'&fecha='+fecha
          }
          toggleDialogs()

        })



    }

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
    } // Solo estara disponible para antes de descargar el archivo




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