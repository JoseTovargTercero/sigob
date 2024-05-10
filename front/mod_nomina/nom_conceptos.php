<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

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
      <div class="row hide animate__animated animate__fadeInRight" id="section_registro">

        <!-- [ worldLow section ] start -->
        <div class="col-xl-12 col-md-6">
          <div class="card">
            <div class="card-header">



              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <h5 class="mb-0">Nuevo concepto</h5>
                  <span>Registro de conceptos</span>
                </div>
                <button class="btn btn-light" onclick="$('#section_registro').addClass('hide')"> <span class="icon-close"></span> &nbsp; Cancelar </button>
              </div>


            </div>
            <div class="card-body">

              <div class="row">
                <form class="col-lg-6" id="form-data" method="post">

                  <div class="mb-3">
                    <label class="form-label" for="nombre">Nombre del concepto</label>
                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Concepto">
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="tipo">Tipo</label>
                    <select class="form-control" name="tipo" id="tipo">
                      <option value="">Seleccione</option>
                      <option value="A">Asignacion</option>
                      <option value="D">Deducción</option>
                      <option value="P">Aporte</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="partida">Partida presupuestaria</label>
                    <input type="text" list="partidas" class="form-control" name="partida" id="partida" placeholder="Concepto">

                    <datalist id="partidas">
                    </datalist>
                  </div>


                  <div class="mb-3 text-end">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                  </div>

                </form>

              </div>

            </div>
          </div>
        </div>
        <!-- [ worldLow section ] end -->


        <!-- [ Recent Users ] end -->
      </div>
      <!-- [ Main Content ] end -->


      <!-- [ Main Content ] start -->
      <div class="row mb3">

        <!-- [ worldLow section ] start -->
        <div class="col-xl-12 col-md-6">
          <div class="card">
            <div class="card-header">



              <div class="d-flex align-items-start justify-content-between">
                <h5 class="mb-0">Lista de conceptos</h5>
                <button class="btn btn-light" onclick="$('#section_registro').removeClass('hide')"> <span class="icon-list"></span> &nbsp; Nuevo </button>
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
                      <th></th>
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


  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/fonts/custom-font.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/clasificador-presupuestario.js"></script>
  <script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>

  <script>
    let tipo_concepto = {
      'A': 'Asignacion',
      'D': 'Deducción',
      'P': 'Aporte'
    }

    function cargarTabla() {
      $.ajax({
        url: '../../back/modulo_nomina/nom_conceptos_back.php',
        type: 'POST',
        data: {
          tabla: true
        },
        success: function(response) {
          var data = JSON.parse(response);
          $('#table tbody').html('<tr>  <td></td>  <td></td>  <td></td>  <td></td></tr>');

          for (var i = 0; i < data.length; i++) {
            var nombre = data[i].nom_concepto;
            var tipo = data[i].tipo_concepto;
            var cod_partida = data[i].cod_partida;
            var id = data[i].id;

            $('#table tbody').append('<tr><td>' + nombre + '</td><td>' + tipo_concepto[tipo] + '</td><td>' + cod_partida + '</td><td><a href="#!" class="badge me-2 bg-brand-color-2 text-white f-12" onclick="eliminar('+id+')">Eliminar</a></td></tr>');
          }
        }
      });
    }
    // ready function
    cargarTabla()


    $(document).ready(function() {
      var table = $('#table').DataTable({
        language: {
          "decimal": "",
          "emptyTable": "No hay información",
          "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
          "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
          "infoFiltered": "(Filtrado de _MAX_ total entradas)",
          "infoPostFix": "",
          "thousands": ",",
          "lengthMenu": "Mostrar _MENU_ Entradas",
          "loadingRecords": "Cargando...",
          "processing": "Procesando...",
          "search": "Buscar:",
          "zeroRecords": "Sin resultados encontrados",
          "paginate": {
            "first": "Primero",
            "last": "Ultimo",
            "next": "Siguiente",
            "previous": "Anterior"
          }
        }
      });


    });



    // saca todas las keys del obj "clasificador" y se agregan como option a partidas
    for (var key in clasificador) {
      $('#partidas').append('<option value="' + key + '">' + key + ' - ' + clasificador[key] + '</option>');
    }


    // eliminar un registro verificando antes con swettalert
    function eliminar(id) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#04a9f5',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarlo!',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '../../back/modulo_nomina/nom_conceptos_back.php',
            type: 'POST',
            data: {
              eliminar: true,
              id: id
            },
            success: function(response) {
              if (response == 'ok') {
                cargarTabla()
                toast_s('success', 'Eliminado con éxito')
              } else {
                toast_s('error', response)
              }
            }
          });
        }
      })
    }
    

    // enviar data al back

    document.getElementById('form-data').addEventListener('submit', function(e) {
      e.preventDefault();
      let nombre = document.getElementsByName('nombre')[0].value;
      let tipo = document.getElementsByName('tipo')[0].value;
      let partida = document.getElementsByName('partida')[0].value;
      if (nombre.trim() === '' || tipo.trim() === '' || partida.trim() === '') {
        toast_s('error', 'Por favor, complete todos los campos')

        return;
      } else {

        $.ajax({
            url: '../../back/modulo_nomina/nom_conceptos_back.php',
            type: 'POST',
            data: {
              nombre: nombre,
              tipo: tipo,
              partida: partida,
              registro: true
            },
            success: function(text) {
              if (text == 'ok') {
              toast_s('success', 'Creado con éxito')
              cargarTabla()
              $('#section_registro').addClass('hide')
                $('#nombre').val('');
                $("#tipo" + " option[value='']").attr("selected", true);
                $("#partida" + " option[value='']").attr("selected", true);
            } else if (text == 'ye') {
              toast_s('error', 'Ya existe un concepto con este nombre')
            } else {
              toast_s('error', 'error')
            }
            }
          });

      }
    });
  </script>

</body>

</html>