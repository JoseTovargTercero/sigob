<?php
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Campos</title>
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
                <h5 class="mb-0">Campos</h5>
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
                  <h5 class="mb-0">Información almacenada de los empleados</h5>
                  <small class="text-muded">Gestione la información que se guarda de sus empleados</small>
                </div>
                <div class="d-flex">
                  
                  <button class="btn btn-secondary me-2" id="btn-show-sistema" onclick="mostrar_sistema()"> Campos del sistema</button>
                  <button class="btn btn-light" id="btn-svr" onclick="setVistaRegistro()">  Nuevo campo</button>
                </div>
              </div>
            </div>
            <div class="card-body">

              <div class="table-responsive p-1">
                <table id="table" class="table table-hover">
                  <thead>
                    <tr>
                      <th class="w-5">#</th>
                      <th class="w-20">Usuario</th>
                      <th class="w-40">Nombre</th>
                      <th class="w-10">Tipo</th>
                      <th class="w-10"></th>
                    </tr>

                    <tr id="section_registro" class="hide">
                      <td></td>
                      <td class="ps-0">
                      </td>
                      <th class="ps-0"><input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre del campo"></th>
                      <th class="ps-0">
                        <select class="form-control" name="tipo" id="tipo" placeholder="Tipo de campo">
                          <option value="">Seleccione</option>
                          <option value="varchar">Texto</option>
                          <option value="int">Número</option>
                          <option value="date">Fecha</option>
                        </select>
                        </th>
                      
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
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script>
    const url_back = '../../back/modulo_nomina/nom_columnas_back.php';



    function cargarTabla() {

      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          tabla: true
        },
        cache: false,
        success: function (response) {

          $('#table tbody').html('');
          if (response) {

            var data = JSON.parse(response);
            cont = 1;

            for (var i = 0; i < data.length; i++) {
              const columna = data[i].COLUMN_NAME;
              const tipo = data[i].DATA_TYPE;
              const maxlenght = data[i].CHARACTER_MAXIMUM_LENGTH;
              let columnas_s = es_columnas_sistema(columna.trim());

              $('#table tbody').append(`<tr class="` + (columnas_s? '_especial hide':'') + `">
                <td>${cont++}</td>
                <td>${(columnas_s? 'Sistema':'Usuario')}</td>
                <td>${columna}</td>
                <td>${tipo} (${maxlenght}) </td>
                <td  class="text-center"> <button onclick="copy('${columna}')" class="btn btn-primary btn-sm"><i class="bx bx-copy"></i></button> </td>
              </tr>`);
            }
          }
  //              <td class="text-center">${(columnas_s? '':'<a class="pointer btn-wicon badge me-2 bg-brand-color-2 text-white f-12" onclick="eliminar()"><i class="bx bx-trash"></i> Eliminar</a>')}</td>
        }

      });
    }
    // ready function
    cargarTabla()



    function copy(contenido){
      navigator.clipboard.writeText(contenido)
      toast_s('success', 'Copiado al portapapeles')
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
            success: function (response) {
              console.log(response)
              text = JSON.parse(response)

              if (text == "ok") {
                cargarTabla();
                toast_s("success", "Eliminado con éxito");
              } else if (text == 'negado') {
                toast_s("error", "No se puede eliminar el banco, existen empleados asociados.");
              } else {
                toast_s("error", response);
              }
            },
          });
        }
      });
    }

    function mostrar_sistema(){
      // verifica si ._especial tiene display none, de ser positivo se lo quitas, de ser negativo se lo agregas
      if ($("._especial").hasClass("hide")) {
        $('._especial').removeClass('hide')
        $('#btn-show-sistema').removeClass('btn-secondary')
        $('#btn-show-sistema').addClass('btn-primary')
      }else{
        $('#btn-show-sistema').addClass('btn-secondary')
        $('#btn-show-sistema').removeClass('btn-primary')
        $('._especial').addClass('hide')
      }
    }

    const palabras_ban = ['DROP','INSERT','DELETE','UPDATE','SELECT','CREATE','ALTER','TRUNCATE','RENAME','REVOKE','GRANT','COMMIT','ROLLBACK','SAVEPOINT','MERGE','REPLACE','SET','SHOW','USE','DESCRIBE','DESC','EXPLAIN','LOCK','UNLOCK','KILL','FLUSH','ANALYZE','OPTIMIZE','REPAIR','CHECK','ANALYSE','BACKUP','RESTORE','RELOAD','PURGE','RESET','SHUTDOWN','START','STOP','RESTART','STATUS','STATS','VERSION','VARIABLES','WARNINGS','ERRORS','LOGS','BINARY','MASTER','SLAVE', "'", '!','"','#','$','%','&','/','(',')','=','?','¡','¿','´','+','*','¨','^','`','}','{',']','[',';',':',',','.','-','|','@','~','°','¬','·','ç','€','£','§','Ñ','ñ', ' ']

    function validarSql(nombre){
      // verificar nombre no contenga nada de palabras_ban
      for (let i = 0; i < palabras_ban.length; i++) {
        if (nombre.includes(palabras_ban[i])) {
          return false
        }
      }
      return true
    }
    // enviar data al back
    function guardar() {
      const nombre = $('#nombre').val()
      const tipo = $('#tipo').val()

      if (nombre == '') {
        toast_s('error', 'Por favor, indique el nombre del campo')
        return;
      } 

      if (tipo == '') {
        toast_s('error', 'Por favor, indique el tipo del campo')
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
          success: function (text) {
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
              toast_s('error', text.mensaje)
            }
          }
        });

      }else{
        toast_s('error', 'El nombre del campo no cumple con los requisitos mínimos')
      }
    }

    // cuando el boton btn-guardar sea pulsado, se ejecuta la funcion anterior
    $(document).ready(function () {
      document.getElementById('btn-guardar').addEventListener('click', guardar);
    });
  </script>

</body>

</html>