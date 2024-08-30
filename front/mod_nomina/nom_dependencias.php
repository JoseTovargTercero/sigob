<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Unidades</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <style>
    td {
      white-space: normal !important;
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
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="mb-0">Unidades</h5>
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
            <div class="card-header" id="dependencia-card-header">
              <div class="d-flex align-items-start justify-content-between">
                <h5 class="mb-0">Gestión de dependencias</h5>

                <button class="btn btn-primary" onclick="mostrarInicio()">Volver al inicio</button>
              </div>
            </div>

            <div class="card-body">
              <section class="hide" id="modificar_valores">


                <section class="hide" id="seleccion_empleados">
                  <div class="row mt-3">
                    <div class="col-md-12">

                      <div class="mb-3">
                        <label class="form-label" for="filtro_empleados">¿Como quieres seleccionar a tus
                          empleados?</label>
                        <select class="form-select" id="filtro_empleados"
                          onchange="seleccion_empleados(this.value, 'empleados-list')">
                          <option>Seleccione</option>
                          <option value="1">Enlistar todos</option>
                          <option value="2">Por sus características (Formulación)</option>
                        </select>
                      </div>
                    </div>

                    <section id="herramienta-formulacion" class="hide p-3">
                      <!-- HERRAMIENTA PARA FILTRAR SEGUN FORMULA-->
                      <div class="row">

                        <div class="col-lg-6">
                          <div class="mb-3"><label class="form-label">Formulación</label>
                            <div class="input-group mb-3">
                              <textarea class="form-control condicion" rows="1" id="t_area-1"></textarea>
                              <button class="btn btn-primary" onclick="validarFormula('t_area-1', 'empleados-list')"
                                type="button">Obtener</button>
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-6">

                          <div class="mb-3">
                            <label class="form-label" for="campo_condiciona">Condicionantes</label>
                            <select name="campo_condiciona" onchange="setCondicionante(this.value, 'result')"
                              id="campo_condiciona" class="form-control">
                              <option value="">Seleccione</option>
                              <option value="cod_cargo">Código de cargo</option>
                              <option value="discapacidades">Discapacidades</option>
                              <option value="instruccion_academica">Instrucción académica</option>
                              <option value="hijos">Hijos</option>
                              <option value="antiguedad">Antigüedad (desde la fecha de ingreso)</option>
                              <option value="antiguedad_total">Antigüedad (Sumando años anteriores)</option>
                              <option value="tipo_nomina">Tipo de nomina</option>

                            </select>
                          </div>
                          <ol class="list-group list-group-numbered" id="result">
                          </ol>
                        </div>
                      </div>
                    </section>


                    <div class="col-md-12 hide" id="otras_nominas-list">
                      <div class="mb-3">
                        <label class="form-label" for="otra_nominas">Nominas registradas</label>
                        <select class="form-select" id="otra_nominas">
                          <option>Seleccione</option>
                          <?php foreach ($nominas as $n): ?>
                            <option value="<?php echo $n->nombre; ?>">&nbsp;<?php echo $n->nombre; ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>

                    <!-- SIEMPRE VISIBLE, CON LA LISTA DE TRABAJADORES-->
                    <section class="mt-3 mh-60">
                      <table class="table table-striped table-hover">
                        <thead>
                          <tr>
                            <th class="w-40">Cedula</th>
                            <th class="w-40">Nombre</th>
                            <th class="w-auto text-center"><input type="checkbox" id="selectAll"
                                onchange="checkAll(this.checked, '')" class="form-check-input" /></th>
                          </tr>
                        </thead>
                        <tbody id="empleados-list">
                        </tbody>

                      </table>


                    </section>

                    <p class="text-end mt-2" id="resumen_epleados_seleccionados">

                    </p>
                  </div>
                  <div class="d-flex w-100 mt-3">
                    <div class="d-flex m-a">
                      <button class="previous btn btn-info me-2" onclick="guardarEnArray()">Reservar cambios</button>
                    </div>
                  </div>


                  <div style="display: grid;place-content: end;">
                    <div class=" d-flex">

                      <p class="me-2 pt-2" id="cantidadEmpleados"></p>
                      <button class="previous btn btn-danger" onclick="guardarListaEmpleados()">Finalizar</button>
                    </div>
                  </div>

                </section>
                <section class="hide" id="resumen_antes_guardar">
                  <div class="text-center mb-3">
                    <h5>Los siguientes empleados se agregaran a la dependencia <b id="nombreDependencia"></b> </h5>
                    <p id="resumen"></p>
                  </div>


                  <div class="table-responsive mh-50">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th class="w-5">#</th>
                          <th class="w-40">Nombre</th>
                          <th class="w-10">Cedula</th>
                          <th class="w-5 text-center"></th>
                        </tr>
                      </thead>
                      <tbody id="table-resumen">
                      </tbody>
                    </table>
                  </div>

                  <div class="mt-3 text-end">
                    <button class="btn btn-primary" id="btn-finalizar">finalizar</button>
                  </div>
                </section>
              </section>
              <div class="table-responsive p-1" id="tabla">
                <table id="table" class="table table-hover">
                  <thead>
                    <tr>
                      <th class="w-5">#</th>
                      <th class="w-60">Nombre</th>
                      <th class="w-20">Codigo</th>
                      <th class="w-10">Empleados</th>
                      <th class="w-5 text-center"></th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-12 mb-3 hide" id="section_tabla-filtrada">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Empleados de la unidad seleccionada</h5>
                </div>
              </div>
            </div>
            <div class="card-body">

              <div class="table-responsive p-1 h-50">
                <table id="tabla_filtrada" class="table table-hover">
                  <thead>
                    <tr>
                      <th class="w-5">#</th>
                      <th class="w-40">Nombre</th>
                      <th class="w-10">Cédula</th>
                      <th class="w-5 text-center"></th>
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
  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script src="../../src/assets/js/ajax_class.js"></script>
  <script src="../../src/assets/js/lista-empleados.js"></script>
  <script type="module" src="../src/components/nom_dependencia_form.js">
  </script>


  <script>
    const ToastRT = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
      }
    });

    function toast_rt(type, text) {
      ToastRT.fire({
        icon: type,
        title: text,
      })
    }



    const url_back = '../../back/modulo_nomina/nom_unidades_back.php';
    let textarea = 't_area-1';


    function mostrarInicio() {
      $('#tabla').removeClass('hide')
      $('#modificar_valores').addClass('hide')
    }



    let empleados = []

    function guardarEnArray() {
      // define una variable con la cantidad valores de empleados
      let cantidad = empleados.length

      $(`.itemCheckbox`).each(function() {
        if (this.checked) {
          empleados.push(this.value)
        }
      })

      empleados = [...new Set(empleados)]

      let nuevaCantidad = empleados.length - cantidad
      if (nuevaCantidad > 0) {
        toast_rt('success', 'Se han agregado ' + nuevaCantidad + ' empleados nuevos')
      } else {
        toast_rt('info', 'No se han agregado empleados nuevos')
      }

      // deselecciona los checkboxs
      $(`.itemCheckbox`).prop('checked', false)
      $('#resumen_epleados_seleccionados').html('')
      $('#cantidadEmpleados').html('Empleados seleccionados: <b>' + empleados.length + '</b>')

    }

    function guardarListaEmpleados() {
      let seleccionados = 0

      $(`.itemCheckbox`).each(function() {
        if (this.checked) {
          seleccionados++
        }
      })


      if (seleccionados != 0) {
        Swal.fire({
          title: "Atención",
          text: "Tiene cambios sin reservar, reserve los cambios o deseleccione los empleados para poder continuar",
          icon: "warning",
          confirmButtonColor: "#04a9f5",
          confirmButtonText: "Ok",
        })
        return
      }

      if (empleados.length == 0) {
        toast_rt('error', 'Debe seleccionar al menos un empleado')
        return
      }
      dataResumen()

      $('#resumen_antes_guardar').removeClass('hide')
      $('#seleccion_empleados').addClass('hide')
    }

    function dataResumen() {
      $('#cargando').show()

      // recorre empleados e imprime en la tabla 'table-resumen' los datos 'cedula' y 'nombre' del empleado almacenados en  'empleadosFiltro'
      $('#table-resumen').html('')
      empleados.forEach((item, index) => {
        $('#table-resumen').append(`<tr>
          <td>${index + 1}</td>
          <td>${empleadosDatos[item][1]}</td>
          <td>${empleadosDatos[item][0]}</td>
          <td class="text-center"><button class="btn btn-danger btn-sm" onclick="eliminarEmpleado(${index})"><i class="bx bx-trash"></i></button></td>
        </tr>`)
      })

      $('#cargando').hide()
    }

    function eliminarEmpleado(id) {
      empleados.splice(id, 1)
      dataResumen()
    }



    function actualizarRegistros() {
      $('#cargando').show()

      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          id_dependencia: id_dependencia,
          empleados: empleados,
          updates: true
        },
        cache: false,
        success: function(response) {

          text = JSON.parse(response)

          if (text.status == "success") {
            empleados = []
            cargarTabla();
            $('#section_tabla-filtrada').addClass('hide')
            $('#resumen_antes_guardar').addClass('hide')
            $('#tabla').removeClass('hide')

            toast_rt("success", "Datos actualizados con exito");
            $('#cargando').hide()

            $('#cantidadEmpleados').html('')


          } else {
            toast_rt("error", text.mensaje);
          }

        }

      });
    }

    document.getElementById('btn-finalizar').addEventListener('click', actualizarRegistros)


    function cargarTabla() {
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          tabla: true
        },
        cache: false,
        success: function(response) {
          cont = 1;
          $('#table tbody').html('');
          if (response) {
            var data = JSON.parse(response);
            for (columna in data) {

              const dependencia = data[columna]['dependencia'];
              const id_dependencia = data[columna]['id_dependencia'];
              const cod_dependencia = data[columna]['cod_dependencia'];
              const id_categoria = data[columna]['id_categoria'];
              const categoria = data[columna]['categoria_nombre'];
              const cant_empleados = data[columna]['total_empleados'];


              $('#table tbody').append(`<tr>
                  <td>${cont++}</td>
                  <td><p class="mb-0"><b>${dependencia}</b></p><small class="text-muted">Categoría: ${(categoria == null ? `<a data-dependencia="${id_dependencia}" href="#">Primero debe asignar una categoría</a>` : categoria)}</small></td>
                  <td>${cod_dependencia} </td>
                  <td>${cant_empleados} </td>
                  <td  class="text-center">
                  ${(categoria == null ? '' : `<button onclick="editar('${id_dependencia}', '${dependencia}')" class="btn btn-primary btn-sm"><i class="bx bx-user-plus"></i></button>`)}


                   </td>
                </tr>`);
            }
          }

        }

      });
    }
    // ready function
    cargarTabla()

    function mostrarEmpleados(id_dependencia) {
      $('#section_tabla-filtrada').removeClass('hide')

      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          tabla_filtrada: true,
          id_dependencia: id_dependencia
        },
        cache: false,
        success: function(response) {
          let data = JSON.parse(response)

          for (empleado in data) {

            const nombre_empleado = data[empleado]['nombres'];
            const cedula = data[empleado]['cedula'];


            $('#tabla_filtrada').append(`<tr >
                  <td>${cont++}</td>
                  <td>${nombre_empleado}</td>
                  <td>${cedula} </td>
                </tr>`);
          }


        }

      });
    }



    var id_dependencia = null;

    function editar(id, nombre) {
      $('#tabla').addClass('hide')
      $('#section_tabla-filtrada').addClass('hide')
      $('#modificar_valores').removeClass('hide')
      id_dependencia = id;
      mostrarEmpleados(id_dependencia)
      $('#seleccion_empleados').removeClass('hide')
      $('#nombreDependencia').html(nombre)

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
              text = JSON.parse(response)
              if (text == "ok") {
                cargarTabla();
                toast_rt("success", "Eliminado con éxito");
              } else if (text == 'negado') {
                toast_rt("error", "No se puede eliminar el banco, existen empleados asociados.");
              } else {
                toast_rt("error", response);
              }
            },
          });
        }
      });
    }

    function guardar() {
      const nombre = $('#nombre').val()
      const tipo = $('#tipo').val()

      if (nombre == '') {
        toast_rt('error', 'Por favor, indique el nombre del campo')
        return;
      }

      if (tipo == '') {
        toast_rt('error', 'Por favor, indique el tipo del campo')
        return;
      }
      if (validarSql(nombre)) {
        $('#cargando').show()

        $.ajax({
          url: url_back,
          type: 'POST',
          data: {
            tipo: tipo,
            nombre: nombre,
            registro: true
          },
          success: function(text) {
            text = JSON.parse(text)
            $('#cargando').hide()
            if (text.status == 'success') {
              cargarTabla()
              setVistaRegistro()
              copy(nombre)
              $('#nombre').val('');
              $("#tipo" + " option[value='']").attr("selected", true);

              $('#btn-show-sistema').addClass('btn-secondary')
              $('#btn-show-sistema').removeClass('btn-primary')
            } else if (text.status == 'error') {
              toast_rt('error', text.mensaje)
            }
          }
        });

      } else {
        toast_rt('error', 'El nombre del campo no cumple con los requisitos mínimos')
      }
    }
  </script>

</body>

</html>