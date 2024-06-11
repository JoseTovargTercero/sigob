<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Bancos</title>
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
                <h5 class="mb-0">Bancos</h5>
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
                <div>
                  <h5 class="mb-0">Bancos registrados</h5>
                  <small class="text-muded">Gestione los bancos usados por sus empleados</small>
                </div>
                <button class="btn btn-light" id="btn-svr" onclick="setVistaRegistro()"> Nuevo banco</button>
              </div>
            </div>
            <div class="card-body">

              <div class="table-responsive p-1">
                <table id="table" class="table table-hover">
                  <thead>
                    <tr>
                      <th class="w-5"></th>
                      <th class="w-30">Prefijo</th>
                      <th class="w-30">Banco</th>
                      <th class="w-30">Cuenta matriz</th>
                      <th class="w-5">N° Afiliado</th>
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
    const url_back = '../../back/modulo_nomina/nom_bancos_back.php';

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
              const prefijo = data[i].prefijo;
              const nombre = data[i].nombre;
              const matriz = data[i].matriz;
              const afiliado = data[i].afiliado;
              const id = data[i].id;

              $('#table tbody').append(`<tr>
              <td><img  src="../../src/assets/images/icons-png/banco.png" alt="activity-user"></td>
              <td>` + prefijo + `</td>
              <td>` + nombre + `</td>
              <td>` + matriz + `</td>
              <td>` + afiliado + `</td>


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
              console.log(response)
            text = JSON.parse(response)

              if (text == "ok") {
                cargarTabla();
                toast_s("success", "Eliminado con éxito");
              }else if(text == 'negado'){
                toast_s("error", "No se puede eliminar el banco, existen empleados asociados.");
              }  else {
                toast_s("error", response);
              }
            },
          });
        }
      });
    }


    function verificarPrefijo() {
      // Obtener los elementos de los campos
      var cuentaMatriz = document.getElementById('cuenta_matriz');
      var prefijo = document.getElementById('prefijo').value;
     
      if (cuentaMatriz.value != '' && prefijo != '') {

        var cuentaMatrizValor = cuentaMatriz.value;

        // Verificar si los primeros 4 dígitos de cuenta_matriz coinciden con prefijo
        if (cuentaMatrizValor.substring(0, 4) !== prefijo) {
          // Agregar clase 'border-danger' si no coinciden
          cuentaMatriz.classList.add('border-danger');
          toast_s('error', 'La cuenta matriz debe ser del mismo banco')
          return true
        } else {
          // Remover clase 'border-danger' si coinciden (por si ya se había agregado antes)
          cuentaMatriz.classList.remove('border-danger');
          return false
        }
      }

    }



    // enviar data al back
    function guardar() {
      const prefijo = $('#prefijo').val()
      const nombre = $('#nombre').val()
      const cuenta_matriz = $('#cuenta_matriz').val()
      const afiliado = $('#afiliado').val()
      if (verificarPrefijo()) {
        return
      }

      if (cuenta_matriz.length != 20) {
        $('#cuenta_matriz').addClass('border-danger')
        toast_s('danger', 'El numero de cuenta debe tener 20 caracteres')
        return
      }

      if (prefijo == '' || nombre == '' || cuenta_matriz == '') {
        toast_s('error', 'Por favor, complete todos los campos')
        return;
      } else {

        $.ajax({
          url: url_back,
          type: 'POST',
          data: {
            prefijo: prefijo,
            nombre: nombre,
            cuenta_matriz: cuenta_matriz,
            afiliado: afiliado,
            registro: true
          },
          success: function(text) {
            text = JSON.parse(text)

            if (text == 'ok') {
              cargarTabla()
              toast_s('success', 'Creado con éxito')
              $('#prefijo').val('');
              $('#nombre').val('');
              $('#cuenta_matriz').val('');
              $('#afiliado').val('');
              setVistaRegistro()
            } else if (text == 'ye') {
              toast_s('error', 'Ya existe un banco con el mismo prefijo')
            } else {
              toast_s('error', 'error ' + text)
            }
          }
        });

      }
    }

    // cuando el boton btn-guardar sea pulsado, se ejecuta la funcion anterior
    $(document).ready(function() {
      document.getElementById('btn-guardar').addEventListener('click', guardar);
      document.getElementById('cuenta_matriz').addEventListener('change', verificarPrefijo);
      document.getElementById('prefijo').addEventListener('change', verificarPrefijo);
    });
  </script>

</body>

</html>