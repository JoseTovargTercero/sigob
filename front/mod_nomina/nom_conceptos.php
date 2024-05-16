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
                <h5 class="mb-0">Conceptos</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-xl-12 col-md-6 mb-3 hidex" id="section-registro">
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
                      <option value="6">Matriz condición/valores</option>
                    </select>
                  </div>
                  <section class="mb-3 hide" id="section-valor">
                    <label class="form-label" for="valor"></label>
                    <input type="number" class="form-control" id="valor">
                  </section>
                </div>
                <div class="col-lg-6">
                <section class="mb-3 hide" id="section-formulado">


                <div class="mb-3">
                    <label class="form-label" for="tipo_valor">Tipo de Calculo aplicado</label>
                    <select class="form-control" name="tipo_valor" id="tipo_valor">
                      <option value="">Seleccione</option>
                      <option value="1">Monto neto en BS</option>
                      <option value="2">Porcentaje al sueldo base</option>
                      <option value="4">Porcentaje al integral</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="campo_condiciona">Condicionante</label>
                    <select name="campo_condiciona" onchange="setCondicionante(this.value)" id="campo_condiciona" class="form-control">
                      <option value="">Seleccione</option>
                      <option value="cod_cargo">Código de cargo</option>
                      <option value="discapacidades">Discapacidades</option>
                      <option value="instruccion_academica">Instrucción académica</option>
                      <option value="hijos">Hijos</option>
                      <option value="antiguedad">Antigüedad</option>
                    </select>
                  </div>
    <div class="result" id="result"></div>

                </section>
                </div>
              </div>
            </div>
          </div>
        </div>

        <script>
          function setCondicionante(value) {

            fetch('../../back/modulo_nomina/nom_columnas_return.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ columna: value })
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                if (data.error) {
                    resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
                } else {
                    resultDiv.innerHTML = `<p>Valores distintos:</p><ul>${data.map(value => `<li>${value}</li>`).join('')}</ul>`;
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


          function tipoCalculo(type) {
            if (type == '6') {
              $('#section-formulado').removeClass('hide')
              $('#section-valor').addClass('hide')
            }else{
              $('#section-formulado').addClass('hide')
              $('#section-valor').removeClass('hide')

              $('#section-valor label').html(titulos_placeholders[type])
              $('#section-valor input').attr('placeholder', titulos_placeholders[type])

            }

          }
        </script>

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
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
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
        $("#section-registro").show(300);
        $("#section-tabla").hide();
      }
    }

    function finalizarRegistroConcepto() {

      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          nombre: nombre,
          tipo: tipo,
          partida: partida,
          registro: true
        },
        success: function(text) {
          if (text == 'ok') {
            cargarTabla()
            toast_s('success', 'Creado con éxito')
            $('#nombre').val('');
            $('#partida').val('');
            $("#tipo" + " option[value='']").attr("selected", true);
            setVistaRegistro()
          } else if (text == 'ye') {
            toast_s('error', 'Ya existe un concepto con este nombre')
          } else {
            toast_s('error', 'error')
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
  </script>

</body>

</html>