<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Conceptos</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.dataTables.css" />
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
        <div class="col-xl-12 col-md-6 mb-3 hide" id="section-registro">
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
                    <label class="form-label" for="tipo_calculo">Tipo de Calculo</label>
                    <select class="form-control" onchange="tipoCalculo(this.value)" name="tipo_calculo" id="tipo_calculo">
                      <option value="">Seleccione</option>
                      <option value="1">Monto neto en BS</option>
                      <option value="2">Monto neto indexado</option>
                      <option value="3">Porcentaje al sueldo base</option>
                      <option value="4">Porcentaje al integral</option>
                      <option value="5">Porcentaje a N conceptos</option>
                      <option value="6">Formulado</option>
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
                        <option value="4">Porcentaje al integral</option>
                        <option value="5">Porcentaje a N conceptos</option>
                      </select>
                    </div>


                    <div class="mb-3" id="forms"><label class="form-label">Formulación</label>
                      <div class="input-group mb-3" id="form-1">
                        <textarea class="form-control condicion" aria-label="With textarea" rows="1" id="t_area-1"></textarea>
                        <span class="input-group-text p-0"><input id="val-1" type="text" placeholder="Valor"></span>
                        <span class="input-group-text d-flex">
                          <a onclick="removeForm('form-1')" class="m-a">
                            <box-icon style="width: 20px;" class="fill-light" name='log-out-circle'></box-icon>
                          </a>
                        </span>
                      </div>
                    </div>
                    <div class="text-end">

                      <button type="button" onclick="addForm()" class="btn btn-secondary d-inline-flex btn-sm rounded"><box-icon class="icon" name='add-to-queue'></box-icon> &nbsp; Agregar opción </button>
                    </div>

                  </section>





                </div>
                <div class="col-lg-6">
                  <section class="section-formulado mb-3 hide">

                    <div class="mb-3">
                      <label class="form-label" for="campo_condiciona">Condicionantes</label>
                      <select name="campo_condiciona" onchange="setCondicionante(this.value)" id="campo_condiciona" class="form-control">
                        <option value="">Seleccione</option>
                        <option value="cod_cargo">Código de cargo</option>
                        <option value="discapacidades">Discapacidades</option>
                        <option value="instruccion_academica">Instrucción académica</option>
                        <option value="hijos">Hijos</option>
                        <option value="antiguedad">Antigüedad</option>
                      </select>
                    </div>
                    <ol class="list-group list-group-numbered" id="result">
                    </ol>
                  </section>
                </div>

              </div>

              <div class="d-flex justify-content-end">
                <button type="button" id="btn-registrar" class="btn btn-primary d-inline-flex btn-sm rounded"><box-icon class="icon" name='save'></box-icon> &nbsp; Guardar concepto</button>

              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-12 col-md-6 mb-3" id="section-tabla">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-start justify-content-between">
                <h5 class="mb-0">Lista de conceptos</h5>
                <button class="btn btn-light" id="btn-svr" onclick="setVistaRegistro()"> Nuevo Concepto</button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive p-1">
                <table id="table" class="table">
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Tipo</th>
                      <th>Partida</th>
                      <th class="w-15"></th>
                    </tr>
                    <tr id="section_registro" class="hide">
                      <th><input type="text" class="form-control" name="nombre" id="nombre"></th>
                      <th> <select class="form-control" name="tipo" id="tipo">
                          <option value="">Seleccione</option>
                          <option value="A">Asignacion</option>
                          <option value="D">Deducción</option>
                          <option value="P">Aporte</option>
                        </select></th>
                      <th><input type="text" list="partidas" class="form-control" name="partida" id="partida" placeholder="Partida"></th>
                      <th><button type="submit" class="btn btn-sm btn-primary" id="btn-continuar">Continuar</button></th>
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
  <script src="../../src/assets/js/fonts/custom-font.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/clasificador-presupuestario.js"></script>
  <script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script>




    let tipo_concepto = {
      'A': 'Asignacion',
      'D': 'Deducción',
      'P': 'Aporte'
    }
    const url_back = '../../back/modulo_nomina/nom_conceptos_back.php';

    /**
     * Function to load the table data using AJAX.
     */
    function cargarTabla() {
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          tabla: true
        },
        success: function(response) {
          $('#table tbody').html('');

          if (response) {
            var data = JSON.parse(response);

            for (var i = 0; i < data.length; i++) {
              var nombre = data[i].nom_concepto;
              var tipo = data[i].tipo_concepto;
              var cod_partida = data[i].cod_partida;
              var id = data[i].id;

              $('#table tbody').append('<tr><td>' + nombre + '</td><td>' + tipo_concepto[tipo] + '</td><td>' + cod_partida + '</td><td><a href="#!" class="badge me-2 bg-brand-color-2 text-white f-12" onclick="eliminar(' + id + ')">Eliminar</a></td></tr>');
            }
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
     * This code iterates over the 'clasificador' object and appends options to the 'partidas' element.
     * Each option is created with the key and value from the 'clasificador' object.
     */
    for (var key in clasificador) {
      $('#partidas').append('<option value="' + key + '">' + key + ' - ' + clasificador[key] + '</option>');
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

      if (nombre.trim() === '' || tipo.trim() === '' || partida.trim() === '') {
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
            consulta_nombre: true
          },

          success: function(response) {
            $('#cargando').hide();
            if (response.trim() == 'ok') {
              $("#section-registro").show(300);
              $("#section-tabla").hide();
            } else {
              toast_s('error', 'Ya existe un concepto con este nombre')
            }
          }
        })
      }
    }

    function finalizarRegistroConcepto() {

      let nombre = document.getElementsByName('nombre')[0].value;
      let tipo = document.getElementsByName('tipo')[0].value;
      let partida = document.getElementsByName('partida')[0].value;
      let tipo_calculo = document.getElementsByName('tipo_calculo')[0].value;
      let valor = document.getElementsByName('valor')[0].value;
      let tipo_calculo_aplicado;

      if (nombre == '' || tipo == '' || partida == '' || tipo_calculo == '') {
        toast_s('error', 'Por favor, complete todos los campos')
        return
      }

      let condiciones = [];
      let valores = [];

      if (tipo_calculo == '6') {
        // recorre los elementos textarea e input de la clase section-formulado[0] alguno de ellos se encuentra vacio le agregas la clase 'invalidate' sino lo agregas al array
        tipo_calculo_aplicado = document.getElementsByName('tipo_calculo_aplicado')[0].value;



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



      }else{

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
          tipo_calculo_aplicado: tipo_calculo_aplicado,
          condiciones: condiciones,
          valor: valor,
          valores: valores,
          registro: true
        },
        success: function(text) {
          $('#cargando').hide();

          if (text == 'ok') {

            setVistaRegistro()
            $("#section-registro").hide();
            $("#section-tabla").show(300);

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

    var textarea = 't_area-1';
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

    function validarCondicion(condicion, textArea) {
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          validarConceptoFormulado: true,
          condicion: condicion
        },
        success: function(response) {
          if (response.trim() == 'prohibido') {
            toast_s('error', 'Se detectaron palabras reservadas')
            $('#textArea').addClass('invalidate');
          } else if (response.trim() == 'error') {
            toast_s('error', 'Error en la condición')
            $('#textArea').addClass('invalidate');
          } else {
            toast_s('success', 'Se encontraron ' + response + ' coincidencias')
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

    /**
     * Adds a condition to a textarea based on the provided field, operator, and value.
     *
     * @param {string} campo - The field to be used in the condition.
     * @param {string} operador - The operator to be used in the condition.
     * @param {string} valor - The value to be used in the condition.
     */
    function addCondicion(campo, operador, valor) {
      let t_area = document.getElementById(textarea);

      // verifies if t_area has text, if true, using a swal, asks if the user wants to use OR or AND before the condition, depending on the selection, adds them before
      // if it doesn't have text, adds the condition directly
      if (t_area.value != '') {
        Swal.fire({
          title: "Operador lógico",
          text: "¿Qué operador lógico quieres usar?",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: "#04a9f5",
          cancelButtonColor: "#d33",
          confirmButtonText: "AND",
          cancelButtonText: "OR",
        }).then((result) => {
          if (result.isConfirmed) {
            let cursor = t_area.selectionStart;
            let text = t_area.value;
            let textBefore = text.substring(0, cursor);
            let textAfter = text.substring(cursor, text.length);
            t_area.value = textBefore + ` AND ` + campo + operador + `'` + valor + `'` + textAfter + ` `;
            t_area.focus();
            t_area.selectionStart = cursor + 5 + campo.length + operador.length + valor.length + 3;
            t_area.selectionEnd = cursor + 5 + campo.length + operador.length + valor.length + 3;
          } else {
            let cursor = t_area.selectionStart;
            let text = t_area.value;
            let textBefore = text.substring(0, cursor);
            let textAfter = text.substring(cursor, text.length);
            t_area.value = textBefore + ` OR ` + campo + operador + `'` + valor + `'` + textAfter + ` `;
            t_area.focus();
            t_area.selectionStart = cursor + 4 + campo.length + operador.length + valor.length + 3;
            t_area.selectionEnd = cursor + 4 + campo.length + operador.length + valor.length + 3;
          }
        });

      } else {
        let cursor = t_area.selectionStart;
        let text = t_area.value;
        let textBefore = text.substring(0, cursor);
        let textAfter = text.substring(cursor, text.length);
        t_area.value = textBefore + campo + operador + `'` + valor + `'` + textAfter + ` `;
        t_area.focus();
        t_area.selectionStart = cursor + campo.length + operador.length + valor.length + 3;
        t_area.selectionEnd = cursor + campo.length + operador.length + valor.length + 3;
      }
    }

    const booleans = {
      1: 'Si',
      0: 'No'
    }

    /**
     * Sets the condition for the given value.
     *
     * @param {string} value - The value to set the condition for.
     */
    function setCondicionante(value) {
      if (value == '') {
        return
      }
      fetch('../../back/modulo_nomina/nom_columnas_return.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            columna: value
          })
        })
        .then(response => response.json())
        .then(data => {
          const resultDiv = document.getElementById('result');
          if (data.error) {
            resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
          } else {

            // toma el html del option seleccionado en el campo tipo_calculo
            let html = document.getElementById('campo_condiciona').options[document.getElementById('campo_condiciona').selectedIndex].innerHTML;
            let option = document.getElementById('campo_condiciona').value;

            resultDiv.innerHTML = `<p>` + html + `:</p>`

            // recorre 'data' y verifica si es igual a 1 o 0 remplazas con si y no, sino imprimes el resultado normal
            data = data.map(value => {
              let val
              if (value == '1' || value == '0') {
                val = booleans[value];
              } else {
                val = value;
              }
              // agrega al resutdiv
              resultDiv.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                          <div class="fw-bold">${val}</div>
                        </div>
                        <button onclick="addCondicion('` + option + `', '=', '${val}')" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
                        <button onclick="addCondicion('` + option + `', '!=', '${val}')" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
                      </li>`;

            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }

    const titulos_placeholders = {
      '1': 'Monto neto en BS',
      '2': 'Monto neto indexado',
      '3': 'Porcentaje al sueldo base',
      '4': 'Porcentaje al integral',
      '5': 'Porcentaje a N conceptos'
    }


    /**
     * Updates the visibility of sections based on the selected type.
     *
     * @param {string} type - The selected type.
     */
    function tipoCalculo(type) {
      if (type == '6') {
        $('.section-formulado').removeClass('hide');
        $('#section-valor').addClass('hide');
      } else {
        $('.section-formulado').addClass('hide');
        $('#section-valor').removeClass('hide');

        $('#section-valor label').html(titulos_placeholders[type]);
        $('#section-valor input').attr('placeholder', titulos_placeholders[type]);
      }
    }
  </script>

</body>

</html>