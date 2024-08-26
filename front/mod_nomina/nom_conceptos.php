<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

$nomina = @$_GET["n"];
if (!$nomina || $nomina == '') {
  $nomina = false;
}



/**
 * Retrieves data from the `nominas_grupos` table based on the provided ID.
 *
 * @param mysqli $conexion The mysqli connection object.
 * @param int $i The ID of the record to retrieve.
 * @return array|null Returns an array containing the retrieved data or null if no records found.
 */

if ($nomina) {
  $stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas_grupos` WHERE id = ?");
  $stmt->bind_param('i', $nomina);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $nombre = $row['nombre'];
      $codigo = $row['codigo'];
    }
  } else {
    header("Location: nom_grupos");
  }
  $stmt->close();
}




?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Conceptos</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

</head>
<?php require_once '../includes/header.php' ?>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">



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
                <h5 class="mb-0">Conceptos</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->


        <div class="col-lg-12 mb-3 hide" id="section-editar">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <h5 class="mb-0">Editar concepto</h5>
                <button class="btn btn-light" onclick="setVistaRegistro('hide-s')"> Cancelar</button>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-lg-6">
                  <p id="informacion_concepto" class="mb-3"></p>

                  <section class="mb-3 hide" id="section-valor_editar">
                    <label class="form-label" for="valor-editar"></label>
                    <input type="number" class="form-control" id="valor-editar" name="valor-editar">
                  </section>





                </div>
                <div class="col-lg-6">

                  <section class="mb-3 hide" id="section-formulado_editar">
                    <ol class="list-group" id="listaformulada"></ol>
                  </section>

                </div>
                <div class="d-flex mt-3">
                  <button type="button" id="btn-editar"
                    class="m-a text-center btn btn-primary d-inline-flex btn-sm rounded"> <i class="bx bx-dave"></i>
                    &nbsp; Guardar cambios</button>
                </div>
              </div>
            </div>
          </div>

        </div>


        <div class="col-lg-12 mb-3 hide" id="section-registro">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <h5 class="mb-0">Formulación del concepto</h5>
                <button class="btn btn-light" onclick="setVistaRegistro('hide-s')"> Cancelar</button>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label class="form-label" for="tipo_calculo">Como se calcula?</label>
                    <select class="form-control" onchange="tipoCalculo(this.value)" name="tipo_calculo"
                      id="tipo_calculo">
                      <option value="">Seleccione</option>

                      <optgroup label="Normales">

                        <option value="1">Monto neto en BS</option>
                        <option value="2">Monto neto indexado</option>
                        <option value="3">Porcentaje al sueldo base</option>
                        <option value="4">Porcentaje al integral</option>
                        <option value="5">Porcentaje a N conceptos</option>
                        <option value="7">Valor multiplicado</option>
                        <option value="6">Formulado</option>
                      </optgroup>
                      <optgroup label="Especiales">
                        <option value="8">Porcentaje al integral de otra nómina</option>
                        <option value="9">Fracción en base al integral de otra nómina</option>
                      </optgroup>
                    </select>
                  </div>
                  <section class="mb-3 hide" id="section-valor">
                    <label class="form-label" for="valor"></label>
                    <input type="number" class="form-control" id="valor" name="valor">
                  </section>

                  <section class="section-formulado hide">

                    <div class="mb-3">
                      <label class="form-label" for="tipo_calculo_aplicado">Tipo de Calculo aplicado</label>
                      <select class="form-control" name="tipo_calculo_aplicado" id="tipo_calculo_aplicado">
                        <option value="">Seleccione</option>
                        <option value="1">Monto neto en BS</option>
                        <option value="2">Monto neto indexado</option>
                        <option value="3">Porcentaje al sueldo base</option>
                        <option value="4">Porcentaje al integral de la misma nómina</option>
                        <option value="5">Porcentaje al integral de otra nómina (del mismo grupo)</option>
                      </select>
                    </div>

                    <div class="mb-3" id="forms"><label class="form-label">Formulación</label>
                      <div class="input-group mb-3" id="form-1">
                        <textarea class="form-control condicion" aria-label="With textarea" rows="1" id="t_area-1"
                          onchange="validarContenido()"></textarea>
                        <span class="input-group-text p-0"><input id="val-1" onchange="validarContenido()" type="text"
                            placeholder="Valor"></span>
                        <span class="input-group-text d-flex">

                        </span>
                      </div>
                    </div>
                    <div class="text-end hide" id="btn-addFormulacion">

                      <button type="button" onclick="addForm()"
                        class="btn btn-secondary d-inline-flex btn-sm rounded"><box-icon class="icon"
                          name='add-to-queue'></box-icon> &nbsp; Agregar opción </button>
                    </div>

                  </section>
                </div>
                <div class="col-lg-6">
                  <section class="section-formulado mb-3 hide">

                    <div class="mb-3">
                      <label class="form-label" for="campo_condiciona">Condicionantes</label>
                      <select name="campo_condiciona" onchange="setCondicionanteConceptos(this.value)"
                        id="campo_condiciona" class="form-control">
                        <option value="">Seleccione</option>
                      </select>
                    </div>
                    <ol class="list-group list-group-numbered" id="result">
                    </ol>
                  </section>
                </div>

              </div>

              <div class="d-flex justify-content-end">
                <button type="button" id="btn-registrar" class="btn btn-primary d-inline-flex btn-sm rounded"> <i
                    class="bx bx-dave"></i> &nbsp; Guardar concepto</button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-12 mb-3" id="section-tabla">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Lista de conceptos</h5>
                  <?php
                  if ($nomina) {
                    echo '<span>Grupo de nómina <b>' . $codigo . ' - ' . $nombre . '</b></span>';
                  }
                  ?>

                </div>
                <button class="btn btn-light" id="btn-svr" onclick="setVistaRegistro()"> Nuevo Concepto</button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive p-1">
                <table id="table" class="table">
                  <thead>
                    <tr>
                      <th>Nómina</th>
                      <th>Código</th>
                      <th>Nombre</th>
                      <th>Tipo</th>
                      <th>Partida</th>
                      <th class="w-15"></th>
                      <th class="w-15"></th>
                    </tr>
                    <tr id="section_registro" class="hide">
                      <th></th>
                      <th>
                        <input type="text" class="form-control" name="codigo_concepto" id="codigo_concepto"
                          placeholder="Código">

                      </th>
                      <th>

                        <input type="text" class="form-control" placeholder="Nombre del concepto" name="nombre"
                          id="nombre">
                      </th>



                      <th> <select class="form-control" name="tipo" id="tipo">
                          <option value="">Seleccione</option>
                          <option value="A">Asignacion</option>
                          <option value="D">Deducción</option>
                          <option value="P">Aporte</option>
                        </select></th>
                      <th><input type="text" list="partidas" class="form-control" name="partida" id="partida"
                          placeholder="Partida"></th>
                      <th colspan=2><button type="submit" class="btn btn-sm btn-primary"
                          id="btn-continuar">Continuar</button>
                      </th>
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
  <datalist id="partidas"></datalist>
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/clasificador-presupuestario.js"></script>

  <script src="../../src/assets/js/main.js"></script>
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/ajax_class.js"></script>
  <script>
    let tipo_concepto = {
      'A': 'Asignacion',
      'D': 'Deducción',
      'P': 'Aporte'
    }
    const url_back = '../../back/modulo_nomina/nom_conceptos_back.php';
    const nomina_g = "<?php echo ($nomina ? $nomina : '0') ?>";


    /**
     * Function to load the table data using AJAX.
     */
    function cargarTabla() {
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          tabla: true,
          g_nomina: nomina_g
        },
        success: function(response) {
          $('#table tbody').html('');

          if (response) {
            var data = JSON.parse(response);

            for (var i = 0; i < data.length; i++) {
              var nombre = data[i].nom_concepto;
              var codigo_concepto = data[i].codigo_concepto;
              var tipo = data[i].tipo_concepto;
              var cod_partida = data[i].cod_partida;
              var nombre_grupo = data[i].nombre_grupo;
              var codigo_grupo = data[i].codigo_grupo;
              var id = data[i].id;
              let nombre_nomina = (codigo_grupo == null ? '' : codigo_grupo + ' - ' + nombre_grupo);

              $('#table tbody').append('<tr><td>' + nombre_nomina + '</td><td>' + codigo_concepto + ' </td><td>' + nombre + '</td><td>' + tipo_concepto[tipo] + '</td><td>' + cod_partida + '</td><td><a href="#!" class="badge me-2 bg-brand-color-1 text-white f-12" onclick="editar(' + id + ')">Editar</a></td><td><a href="#!" class="badge me-2 bg-brand-color-2 text-white f-12" onclick="eliminar(' + id + ')">Eliminar</a></td></tr>');
            }
          }
        }
      });
    }

    cargarTabla()

    const tiposCalculo = {
      '1': 'Monto neto en BS',
      '2': 'Monto neto indexado',
      '3': 'Porcentaje al sueldo base',
      '4': 'Porcentaje al integral',
      '5': 'Porcentaje a N conceptos',
      '6': 'Formulado',
      '7': 'Valor multiplicado'
    }

    let conceptoEditar

    function editar(id) {
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          editar_getData: true,
          id: id
        },
        success: function(response) {
          if (response) {
            var data = JSON.parse(response);
            conceptoEditar = data;


            $('#informacion_concepto').html(`Editando el concepto: <b>` + data.nombre + `</b><br>
                Tipo: <b>` + tipo_concepto[data.tipo] + `</b><br>
                Partida: <b>` + data.partida + `</b><br>
                Tipo de calculo: <b>` + (data.tipo_calculo_origen == '7' ? 'Valor multiplicado' : tiposCalculo[data.tipo_calculo]) + `</b><br>
                Valor actual: <b>` + data.valor + `</b><br>
                <small class="text-danger"><br>* Solo se pueden modificar los montos. Las formulas y metodos de aplicación no pueden ser modificados.</small>`)
            if (data.tipo_calculo != 6 || data.tipo_calculo_origen == '7') {
              $('#section-valor_editar > label').html((data.tipo_calculo_origen == '7' ? 'Valor por unidad (Multiplicado):' : tiposCalculo[data.tipo_calculo]));
              $('#section-valor_editar').removeClass('hide');
              $('#section-formulado_editar').addClass('hide');
            } else {
              $('#section-formulado_editar').removeClass('hide');
              $('#section-valor_editar').addClass('hide');
              $('#listaformulada').html('');

              data.formulacion.forEach(element => {
                $('#listaformulada').append(`
                    <li class="p-3 border d-flex justify-content-between">
                      <div class="fw-bold">
                      <code class="ms-0">` + element['condicion'] + `</code>
                      <br><small class="text-v-actual">Valor actual: ` + element['valor'] + `</small>
                      </div>
                      <input type="text" class="form-control form-control-sm w-20"  id="inp_` + element['id'] + `">
                    </li>`);
              });
            }

            $('#section-editar').removeClass('hide')
            $('#section-registro').addClass('hide')
            $('#section-tabla').addClass('hide')
          }
        }
      });
    }



    function guardaCambiosEditar() {
      let error = false;
      let valores

      if (conceptoEditar.tipo_calculo == 6) {
        valores = []

        if (conceptoEditar.tipo_calculo_origen != '7') {
          conceptoEditar.formulacion.forEach(element => {
            let valor = document.getElementById('inp_' + element.id).value;
            if (valor.trim() == '') {
              error = true;
              toast_s('error', 'Por favor, complete todos los campos')
              return;
            } else {
              valores.push({
                id: element.id,
                valor: valor
              })
            }
          });
        } else {

          let valor = document.getElementById('valor-editar').value;
          let pasos = 1;
          conceptoEditar.formulacion.forEach(element => {
            valores.push({
              id: element.id,
              valor: valor * pasos
            })
            pasos++
          });
        }



      } else {
        let valor = document.getElementById('valor-editar').value;
        if (valor.trim() == '') {
          toast_s('error', 'Por favor, complete todos los campos')
          return;
        }
        valores = document.getElementById('valor-editar').value;
      }
      if (!error) {
        $.ajax({
          url: url_back,
          type: 'POST',
          data: {
            editar_setData: true,
            id: conceptoEditar.id,
            valor: valores
          },
          success: function(response) {

            let result = JSON.parse(response)
            if (result.status == 'ok') {

              $('#section-formulado_editar').addClass('hide');
              $('#section-valor_editar').addClass('hide');
              $('#listaformulada').html('');

              $('#section-editar').addClass('hide')
              $('#section-registro').addClass('hide')
              $('#section-tabla').removeClass('hide')




              toast_s('success', 'Cambios guardados con éxito')
            } else {
              toast_s('error', response)
            }
          }
        });
      }
    }

    document.getElementById('btn-editar').addEventListener('click', guardaCambiosEditar)



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
     * Creates a new concepto.
     * 
     * Show formulation section
     * @return 
     */
    function nuevoConcepto() {
      let nombre = document.getElementsByName('nombre')[0].value;
      let tipo = document.getElementsByName('tipo')[0].value;
      let partida = document.getElementsByName('partida')[0].value;
      let codigo_concepto = document.getElementsByName('codigo_concepto')[0].value;

      if (nombre.trim() === '' || tipo.trim() === '' || partida.trim() === '' || codigo_concepto.trim() == '') {
        toast_s('error', 'Por favor, complete todos los campos')
        return;
      } else {
        // verificar si la partida existe como key en el objeto 'clasificador'
        if (!clasificador.hasOwnProperty(partida)) {
          toast_s('error', 'Partida no encontrada')
          return;
        }

        // muestra el loader
        $('#cargando').show();

        // envia un ajax a la url_back con el nombre
        $.ajax({
          url: url_back,
          type: 'POST',
          data: {
            nombre: nombre,
            codigo_concepto: codigo_concepto,
            consulta_nombre: true
          },
          success: function(response) {
            $('#cargando').hide();
            if (response.trim() == 'ok') {
              $("#section-registro").removeClass('hide');
              $("#section-tabla").addClass('hide');
            } else {
              toast_s('error', 'Tanto el codigo como el nombre del concepto debe ser único.')
            }
          }
        })
      }
    }

    function finalizarRegistroConcepto() {

      let maxValue = 0
      let nombre = document.getElementsByName('nombre')[0].value;
      let tipo = document.getElementsByName('tipo')[0].value;
      let partida = document.getElementsByName('partida')[0].value;
      let codigo_concepto = document.getElementsByName('codigo_concepto')[0].value;
      let tipo_calculo = document.getElementsByName('tipo_calculo')[0].value;
      let valor = document.getElementsByName('valor')[0].value;
      let tipo_calculo_aplicado;

      if (nombre == '' || tipo == '' || partida == '' || tipo_calculo == '' || codigo_concepto == '') {
        toast_s('error', 'Por favor, complete todos los campos')
        return
      } // en caso de que este vacio

      let condiciones = [];
      let valores = [];

      if (tipo_calculo == '6') {
        // recorre los elementos textarea e input de la clase section-formulado[0] alguno de ellos se encuentra vacio le agregas la clase 'invalidate' sino lo agregas al array
        tipo_calculo_aplicado = document.getElementsByName('tipo_calculo_aplicado')[0].value;

        if (tipo_calculo_aplicado == '') {
          toast_s('error', 'Por favor, complete todos los campos')
          return
        }


        // while mientras form-N exista en el dom, donde N=1,2,3,4,5...
        let i = 1;
        while (document.getElementById("form-" + i) !== null) {
          let condicion = $('#t_area-' + i).val();
          let valor = $('#val-' + i).val()

          console.log('-')
          // verifica si alguno de los dos campos esta vacio, si es asi, le agregas la clase invalidate solo al campo que esta vacio
          if (condicion.trim() == '' || valor.trim() == '') {
            if (condicion.trim() === '') {
              $('#t_area-' + i).addClass('invalidate');
            }
            if (valor.trim() == '') {
              $('#val-' + i).addClass('invalidate');
            }
            toast_s('error', 'Rellene todos los campos')
            return;
          } else {
            // si no esta vacio, lo agregas al array
            condiciones.push(condicion);
            valores.push(valor);
          }
          i++;
        }

      } else if (tipo_calculo == '7') {
        tipo_calculo_aplicado = document.getElementsByName('tipo_calculo_aplicado')[0].value;

        if (tipo_calculo_aplicado == '') {
          toast_s('error', 'Por favor, complete todos los campos')
          return
        }
        if (formulacionesMultiplicadas.length < 1) {
          toast_s('error', 'No se ha agregado ninguna condicion')
          return
        }
        maxValue = formulacionesMultiplicadas.length
        formulacionesMultiplicadas.forEach(element => {
          condiciones.push(element[0]);
          valores.push(element[1]);
        });


      } else {

        if (valor == '') {
          $('#valor').addClass('invalidate')
          toast_s('error', 'Por favor, complete todos los campos')
          return
        }
      }
      //verifica la clase invalidate
      if ($('.invalidate').length > 0) {
        toast_s('error', 'Por favor, complete todos los campos')
        return;
      }
      // mostrar loader
      $('#cargando').show();

      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          nombre: nombre,
          tipo: tipo,
          partida: partida,
          tipo_calculo: tipo_calculo,
          codigo_concepto: codigo_concepto,
          tipo_calculo_aplicado: tipo_calculo_aplicado,
          condiciones: condiciones,
          valor: valor,
          valores: valores,
          maxValue: maxValue,
          nomina_g: nomina_g,
          registro: true
        },
        success: function(text) {

          console.log(nombre + ' - ' + tipo + ' - ' + partida + ' - ' + tipo_calculo + ' - ' + valor + ' - ' + tipo_calculo_aplicado)
          $('#cargando').hide();

          if (text == 'ok') {

            setVistaRegistro()
            $("#section-registro").addClass('hide');
            $("#section-tabla").removeClass('hide');

            Swal.fire({
              title: "Concepto creado",
              text: "El concepto fue creado con éxito",
              icon: "success",
              showCancelButton: false,
              confirmButtonColor: "#04a9f5",
              confirmButtonText: "Ok",
            }).then((result) => {
              location.reload();
            });

          } else {
            toast_s('error', 'error' + text)
          }

          if (nomina_g != '0') {
            window.close()
          }

        }
      });
    }

    $(document).ready(function() {
      document.getElementById('btn-continuar').addEventListener('click', nuevoConcepto);
      document.getElementById('btn-registrar').addEventListener('click', finalizarRegistroConcepto);
    });

    /**
     * Initializes the DataTable.
     */
    $(document).ready(function() {
      var DataTable = $("#table").DataTable({
        language: {
          decimal: "",
          emptyTable: "No hay información",
          info: "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
          infoEmpty: "Mostrando 0 to 0 of 0 Entradas",
          infoFiltered: "(Filtrado de _MAX_ total entradas)",
          infoPostFix: "",
          thousands: ",",
          lengthMenu: "Mostrar _MENU_ Entradas",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "Sin resultados encontrados",
          paginate: {
            first: "Primero",
            last: "Ultimo",
            next: "Siguiente",
            previous: "Anterior",
          },
        },
        ordering: false,
        //desactiva data-dt-column
        info: false,
        columnDefs: [{
          targets: [0, 1],
          className: "text-start",
        }, ],
      });
    });

    // Validación y creación de conceptos
    // Validación y creación de conceptos
    // Validación y creación de conceptos

    /**
     * This code assigns the value 't_area-1' to the variable 'textarea' and sets up a click event listener for all textareas in the document.
     * When a textarea is clicked, the ID of the clicked textarea is assigned to the 'textarea' variable.
     */

    let textarea = 't_area-1';
    $(document).on('click', 'textarea', function() {
      textarea = $(this).attr('id');
    });


    $(document).on('click', '.invalidate', function() {
      $(this).removeClass('invalidate')
    });


    const palabrasProhibidas = ['UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'INSERT', 'ALTER', 'GRANT', 'REVOKE'];

    $(document).on('change', 'textarea', function() {
      if ($(this).val() != '') {

        var condicion = $(this).val();
        var condicion = condicion.replace(/[\n\r]/g, ' ');
        var condicion = condicion.replace(/[\t]/g, ' ');
        var condicion = condicion.replace(/[\s]{2,}/g, ' ');

        for (var i = 0; i < palabrasProhibidas.length; i++) {
          var palabra = palabrasProhibidas[i];
          var palabra = palabra.toUpperCase();
          var palabra = palabra.toLowerCase();
          var condicion_validar = condicion.toUpperCase();
          var condicion_validar = condicion_validar.toLowerCase();
          if (condicion_validar.includes(palabra)) {
            $(this).val('');
            $(this).addClass('invalidate');
            toast_s('error', 'Se detectaron palabras reservadas')
            return;
          }
        }

        validarCondicion(condicion, $(this).attr('id'))
      }
    })

    /**
     * Validates a condition by making an AJAX request to the server.
     *
     * @param {string} condicion - The condition to be validated.
     * @param {string} textArea - The ID of the textarea element to be updated.
     */
    function validarCondicion(condicion, textArea) {
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          validarConceptoFormulado: true,
          condicion: condicion
        },
        success: function(response) {
          const trimmedResponse = response.trim();
          const textAreaElement = $('#' + textArea);

          if (trimmedResponse === 'prohibido' || trimmedResponse === 'error') {
            const errorMessage = trimmedResponse === 'prohibido' ? 'Se detectaron palabras reservadas' : 'Error en la condición';
            toast_s('error', errorMessage);
            textAreaElement.addClass('invalidate');
          } else {
            toast_s('success', 'Se encontraron ' + trimmedResponse + ' coincidencias');
          }
        }
      });
    }


    /**
     * Adds a new form to the page.
     */
    var form = 1

    function addForm() {
      form++;
      let html = `<div class="input-group mb-3" id="form-${form}">
              <textarea class="form-control condicion" aria-label="With textarea" id="t_area-${form}"></textarea>
              <span class="input-group-text p-0"><input type="text" id="val-${form}" placeholder="Valor"></span>
              <span class="input-group-text d-flex">
                <a onclick="removeForm('${form}')" class="m-a">
                  <box-icon style="width: 20px;" class="fill-danger" name='log-out-circle'></box-icon>
                </a>
              </span>
            </div>`;
      $('#forms').append(html);
    }

    /**
     * Removes a form element from the DOM based on the provided ID.
     *
     * @param {string} id - The ID of the form element to be removed.
     * @returns {void}
     */
    function removeForm(id) {
      // el form-1 no se puede eliminar
      if (id == 'form-1') {
        return;
      }
      // si el text area tiene texto, se debe preguntar primero al usuario usando un swal
      if ($('#t_area-' + id).val() != '') {
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
            $('#form-' + id).remove();
          }
        });
      } else {
        $('#form-' + id).remove();
      }
    }



    const valoresColumnas = {
      cod_cargo: ['Código de cargo', '1'],
      discapacidades: ['Discapacidades', '1'],
      instruccion_academica: ['Instrucción académica', '1'],
      hijos: ['Hijos', '2'],
      antiguedad: ['Antigüedad (desde la fecha de ingreso)', '1'],
      antiguedad_total: ['Antigüedad (Sumando años anteriores)', '1'],
      beca: ['becas', '2'],
      status: ['Estatus del empleado', '1'],
    }


    function agregarCondicionantes() {
      let tipoCondicion = $('#tipo_calculo').val()
      const select = document.getElementById('campo_condiciona');
      select.innerHTML = '<option value="">Seleccione</option>';
      for (const key in valoresColumnas) {
        if (valoresColumnas.hasOwnProperty(key)) {
          if (tipoCondicion == '6') {
            let option = document.createElement('option');
            option.value = key;
            option.textContent = valoresColumnas[key][0];
            select.appendChild(option);
          } else if (tipoCondicion == '7' && valoresColumnas[key][1] == '2') {
            let option = document.createElement('option');
            option.value = key;
            option.textContent = valoresColumnas[key][0];
            select.appendChild(option);
          }

        }
      }
      addOptionsCamposCondicionantes()
    }
    let tipoCalculoSimbolo = {
      '1': 'BS',
      '2': '$',
      '3': '%',
      '4': '%',
      '9': '%'
    }

    function validarContenido() {
      if ($('#tipo_calculo').val() == '7') {
        const campo = $('#t_area-1').val()
        const valor = $('#val-1').val()

        if (!valoresColumnas[campo] && campo != '') {
          toast_s('error', 'Campo  no encontrado')
          $('#t_area-1').addClass('border-dangers')
          return false
        } else {
          if (campo != '' && valor != '') {
            //verificar opciones
            contenidoMultiplicacion(campo, valor)
          }
        }
      }
    }
    var tipoCalculoAplicado = document.getElementById('tipo_calculo_aplicado');
    tipoCalculoAplicado.addEventListener('change', validarContenido);

    let formulacionesMultiplicadas = []


    function contenidoMultiplicacion(campo, valor) {
      formulacionesMultiplicadas = []
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          valorMultiplicado: true,
          campo: campo
        },
        success: function(response) {
          let tabla = document.getElementById('result')
          tabla.innerHTML = `<p>Ejemplo de posibles aplicaciones: </p>`

          for (let index = 1; index <= response; index++) {
            let texto = index + ' ' + campo;
            let resultado = index * valor;
            formulacionesMultiplicadas.push([campo + "='" + index + "'", resultado])
            let tipoCalculo = $('#tipo_calculo_aplicado').val();
            if (tipoCalculo != '') {
              resultado += ' ' + tipoCalculoSimbolo[tipoCalculo]
            }



            if (index < 4) {


              tabla.innerHTML += `
              <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                      <div class="fw-bold">` + texto + `</div>
                    </div>
                  
                   <span class="badge bg-light-secondary">${resultado}</span>
                  </li>
              `

            } else if (index == 4) {
              tabla.innerHTML += `
              <li class="list-group-item  align-items-start  no-number">
                    . . .
                  </li>
              `
            }
          }
          //  formulacionesMultiplicadas.length
          //  console.log(formulacionesMultiplicadas)
        }
      });
    }

    /**
     * Sets the condition for the given value.
     *
     * @param {string} value - The value to set the condition for.
     */
    function setCondicionanteConceptos(condicionante, div = null) {
      if ($('#tipo_calculo').val() == '7') {
        $('#t_area-1').val(condicionante)
        // mover el tabulador a valor-1
        $('#val-1').focus()
        return
      }
      if (condicionante == '') {
        return
      }
      const resultDiv = div == null ? document.getElementById('result') : document.getElementById(div);


      if (condicionante == 'antiguedad' || condicionante == 'antiguedad_total') {
        resultDiv.innerHTML = `<p>` + (condicionante == 'antiguedad' ? 'Antiguedad (desde la fecha de ingreso)' : 'Antiguedad (Sumando años anteriores)') + `:</p>`

        resultDiv.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
      <div class="ms-2 me-auto">
        <div class="fw-bold">` + condicionante + `</div>
      </div>
      <button onclick="addCondicion('` + condicionante + `', '<', 'N', ` + div + `)" type="button" class="btn btn-sm btn-info  me-2" title="Menor"><</button>
      <button onclick="addCondicion('` + condicionante + `', '>', 'N', ` + div + `)" type="button" class="btn btn-sm btn-success  me-2" title="Mayor">></button>
      <button onclick="addCondicion('` + condicionante + `', '=', 'N', ` + div + `)" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
      <button onclick="addCondicion('` + condicionante + `', '!=', 'N', ` + div + `)" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
    </li>`;

        return
      }


      fetch('../../back/modulo_nomina/nom_columnas_return.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            columna: condicionante
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
          } else {

            // toma el html del option seleccionado en el campo tipo_calculo
            let html = document.getElementById('campo_condiciona').options[document.getElementById('campo_condiciona').selectedIndex].innerHTML;


            resultDiv.innerHTML = `<p>` + html + `:</p>`

            // recorre 'data' y verifica si es igual a 1 o 0 remplazas con si y no, sino imprimes el resultado normal
            data = data.map(value => {
              let val = value;
              // agrega al resutdiv
              resultDiv.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                      <div class="fw-bold">${val}</div>
                    </div>
                    <button onclick="addCondicion('` + condicionante + `', '<', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Menor"><</button>
                    <button onclick="addCondicion('` + condicionante + `', '>', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Mayor">></button>
                    <button onclick="addCondicion('` + condicionante + `', '=', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
                    <button onclick="addCondicion('` + condicionante + `', '!=', '${val}', '${div}')" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
                  </li>`;

            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }






    const labes = {
      '1': 'Monto neto en BS',
      '2': 'Monto neto indexado',
      '3': 'Porcentaje al sueldo base',
      '4': 'Porcentaje al integral',
      '5': 'Porcentaje a N conceptos',
      '8': 'Porcentaje al integral de otra nomina',
      '9': 'Fracción en base al integral de otra nomina',
    }

    const titulos_placeholders = {
      '1': 'Indique el monto expresado en BS',
      '2': 'Indique el monto expresado en $',
      '3': 'Indique el porcentaje',
      '4': 'Indique el porcentaje',
      '5': 'Indique el porcentaje',
      '8': 'Indique el porcentaje',
      '9': 'Indique el divisor',
    }



    /**
     * Updates the visibility of sections based on the selected type.
     *
     * @param {string} type - The selected type.
     */
    function tipoCalculo(type) {
      $('#result').html('')
      $("#campo_condiciona" + " option[value='']").attr("selected", true);
      $('#t_area-1').val('')


      let i = 2;
      form = 1

      while ($('#form-' + i).length > 0) {
        $('#form-' + i).remove()
        i++
      }



      if (type == '6' || type == '7') {
        agregarCondicionantes()
        $('.section-formulado').removeClass('hide');
        $('#section-valor').addClass('hide');
        if (type == '6') {
          $('#btn-addFormulacion').removeClass('hide');
        } else {
          $('#btn-addFormulacion').addClass('hide');
        }
      } else {
        $('.section-formulado').addClass('hide');
        $('#section-valor').removeClass('hide');
        $('#section-valor label').html(labes[type]);
        $('#section-valor input').attr('placeholder', titulos_placeholders[type]);
      }
    }
  </script>

</body>

</html>