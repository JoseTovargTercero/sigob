<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';


if (isset($_GET["ejercicio"])) {
  $annio = $_GET["ejercicio"];
} else {
  $annio = date('Y');
}


$stmt = mysqli_prepare($conexion, "SELECT * FROM `ejercicio_fiscal`  WHERE ano = ?");
$stmt->bind_param('s', $annio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ejercicio_fiscal = $row['id']; // formato: dd-mm-YY
    $situado = $row['situado']; // formato: dd-mm-YY
    $status = $row['status']; // formato: dd-mm-YY
  }
} else {
  $ejercicio_fiscal = 'No';
  $situado = 0; // formato: dd-mm-YY
}
$stmt->close();


?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Inicio</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <script src="../../src/assets/js/checkRemoteDB.js"></script>



  <style>
    td {
      padding: 7px !important;
    }

    .h-15 {
      min-height: 15vh !important;
    }

    .img-ng {
      height: 54vh;
      width: min-content;
      margin: auto;
      opacity: 0.2;
    }

    .top-col>.card {
      height: 192px;
    }

    #situado_h2 {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-size: 3em;
      /* Tamaño inicial */
    }

    h5 {
      font-size: 1rem !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
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

  <style>
    #table td,
    #table th {
      text-align: center;
    }
  </style>


  <!-- [ Main Content ] start -->
  <div class="pc-container">
    <div class="pc-content">
      <div class=" d-flex justify-content-between">
        <?php
        $y_d = date('Y');
        $y_d1 = $y_d - 1;
        $y_d2 = date('Y') + 1;
        ?>
        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> Inclusión de nuevas partidas en el presupuesto
        </h4>

        <div class="d-flex gap-1">

          <p><a class="pointer <?php echo ($annio == $y_d1 ? 'text-decoration-underline text-primary' : 'text-dark') ?>"
              href="?ejercicio=<?php echo $y_d1 ?>"><?php echo $y_d1 ?></a></p>
          <p><a class="pointer <?php echo ($annio == $y_d ? 'text-decoration-underline text-primary' : 'text-dark') ?> "
              href="?ejercicio=<?php echo $y_d ?>"><?php echo $y_d ?></a></p>
          <p><a href="?ejercicio=<?php echo $y_d2 ?>"
              class="pointer <?php echo ($annio == $y_d2 ? 'text-decoration-underline text-primary' : 'text-dark') ?>"><?php echo $y_d2 ?></a>
          </p>
        </div>
      </div>
      <!-- CONTENIDO -->
      <div class="row ">
        <div class="col-lg-12">
          <div class="card mb-3">
            <div class="card-header mb-3">
              <h5 class="mb-2">Partidas no incluidas inicialmente en el presupuesto</h5>
            </div>
            <div class="card-body">
              <table class="table" id="table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Partida</th>
                    <th>Descripción</th>
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
    </div>



    <div class="dialogs">
      <div class="dialogs-content " style="width: 45%;">
        <span class="close-button">×</span>
        <h5 class="mb-1"><b>Agregar nueva actividad</b></h5>
        <div class="card-body pt-3">
          <form id="data_partida">
            <div class="row">
              <div class="mb-3 col-lg-9">
                <label for="partida" class="form-label">Partida</label>
                <input required type="text" id="partida" name="partida" disabled class="form-control">
              </div>

              <div class="mb-3 col-lg-3">
                <label for="partida" class="form-label">Actividad</label>
                <input required type="text" id="actividad" name="actividad" class="form-control" value="51">
              </div>

            </div>
            <div class="row mb-3">
              <div class="col-lg-4 not_suu">
                <label for="sector" class="form-label">Sector</label>
                <select required type="text" class="form-control" id="sector" name="sector">
                  <option value="">Seleccione</option>

                  <?php
                  $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_sectores");
                  $stmt->execute();
                  $result = $stmt->get_result();
                  if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      $id = $row['id'];
                      $sector = $row['sector'];
                      $denominacion = $row['denominacion'];
                      echo ' <option value="' . $id . '">' . $sector . ' - ' . $denominacion . '</option>;';
                    }
                  }
                  $stmt->close();
                  ?>
                </select>
              </div>

              <div class="col-lg-4 not_suu">
                <label for="programa" class="form-label">Programa</label>
                <select type="text" class="form-control" id="programa" name="programa">
                  <option value="">Seleccione</option>
                </select>
              </div>
              <div class="col-lg-4">
                <label for="proyecto" class="form-label">Proyecto</label>
                <select required class="form-control" id="proyecto" name="proyecto">
                  <option value="">Seleccione</option>
                  <option value="0">00</option>

                  <?php
                  $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_proyectos");
                  $stmt->execute();
                  $result = $stmt->get_result();
                  if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      $id = $row['id'];
                      $proyecto_id = $row['proyecto_id'];
                      $denominacion = $row['denominacion'];
                      echo ' <option value="' . $id . '">' . $proyecto_id . ' - ' . $denominacion . '</option>;';
                    }
                  }
                  $stmt->close();
                  ?>
                </select>


              </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
              <button type="button" class="btn btn-secondary" onclick="toggleDialogs()">Cancelar</button>
              <button type="submit" class="btn btn-primary" id="btn-registro">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>



    <!-- [ Main Content ] end -->
    <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
    <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
    <script src="../../src/assets/js/pcoded.js"></script>
    <script src="../../src/assets/js/plugins/feather.min.js"></script>
    <script src="../../src/assets/js/notificaciones.js"></script>
    <script src="../../src/assets/js/main.js"></script>
    <script src="../../src/assets/js/ajax_class.js"></script>





    <script>
      let id_ejercicio_fiscal = "<?php echo $ejercicio_fiscal ?>"
      let partida_incluir = 0;
      const url_back = '../../back/modulo_ejecucion_presupuestaria/pre_partidas_faltantes.php';


      let programas = []
      <?php
      $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_programas");
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $programa = $row['programa'];
          $sector = $row['sector'];
          $denominacion = $row['denominacion'];
          $p_id = $row['id'];
          echo 'programas.push(["' . $sector . '", "' . $programa . '", "' . $denominacion . '", "' . $p_id . '"]);' . PHP_EOL;
        }
      }
      $stmt->close();
      ?>







      var DataTable = $("#table").DataTable({
        language: lenguaje_datat
      }); // Iniciar data-table




      // Intercambiar entre las tablas
      document.addEventListener('click', function(event) {

        if (event.target.closest('.btn-add')) {
          const id = event.target.closest('.btn-add').getAttribute('data-add-id');
          const partida = event.target.closest('.btn-add').getAttribute('data-partida');
          document.getElementById('partida').value = partida
          partida_incluir = partida;
          toggleDialogs()
        }
      });

      //agregar funcion al boton de agregar partida






      document.getElementById('sector').addEventListener('change', function(event) {
        let sector_s = this.value;
        get_programa(sector_s)
      })


      function get_programa(sector_s) {
        document.getElementById('programa').innerHTML = '<option value="">Seleccione</option>'
        programas.forEach(element => {
          if (element[0] == sector_s) {
            document.getElementById('programa').innerHTML += `<option value="${element[3]}">${element[1]} - ${element[2]}</option>`
          }
        });

        return true
      }

      $('#data_partida').on('submit', function(e) {
        e.preventDefault();

        const formData = Object.fromEntries(new FormData(this));

        // Agregar campos adicionales
        formData.accion = 'registrar';
        formData.partida_incluir = partida_incluir;
        formData.id_ejercicio = id_ejercicio_fiscal;

        // Enviar los datos al backend mediante AJAX
        $.ajax({
          url: url_back,
          type: 'POST',
          dataType: 'json',
          contentType: 'application/json',
          data: JSON.stringify(formData),
          success: function(response) {
              console.log('Respuesta del servidor:', response);

            }.fail(function(jqXHR, textStatus, errorThrown) {
              console.error('Error en la solicitud:', textStatus, errorThrown);
            })
            .always(function(res) {
              console.log('Solicitud finalizada:', res);
            })
        });
      });






      // OBTENER LISTA DE PARTIDAS SIN USAR
      function obtenerPartidas() {
        $.ajax({
            url: url_back,
            type: 'POST',
            dataType: 'json', // Cambiado a 'json'
            contentType: 'application/json',
            data: JSON.stringify({
              accion: 'consulta',
              id_ejercicio: id_ejercicio_fiscal
            }),
          })
          .done(function(resultado) {
            let contador = 1;

            for (const key in resultado) {
              if (resultado.hasOwnProperty(key)) {
                const item = resultado[key];
                DataTable.row.add([
                  contador++,
                  item.partida,
                  item.descripcion,
                  '<button class="btn btn-add btn-info" data-partida="' + item.partida + '" data-add-id="' + key + '"></button>'
                ]).draw();
              }
            }
          })
          .fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error en la solicitud:', textStatus, errorThrown);
            //  alert('Hubo un problema al obtener los datos. Por favor, inténtalo de nuevo.');
          })
          .always(function(res) {});
      }
      obtenerPartidas()
    </script>

</body>

</html>