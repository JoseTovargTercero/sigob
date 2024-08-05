<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

$stmt = mysqli_prepare($conexion, "SELECT * FROM `backups` ORDER BY id DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ultima_Act = $row['fecha']; // formato: dd-mm-YY
  }
} else {
  $ultima_Act = 'Nunca';
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
                <h5 class="mb-0">Inicio</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row">

        <!-- [ worldLow section ] start -->
        <div class="col-xl-6 col-md-6">
          <div class="card">
            <div class="card-header">
              <h5>Estado del sistema</h5>
            </div>
            <div class="card-body d-flex">
              <div class="m-a">
                <div class="file-upload width-338p">
                  <div class="upload-area">
                    <div class="icon d-flex text-center w-100">
                      <i class='bx bx-cloud-upload'></i>
                    </div>
                    <p class="text-title">Ultima copia de seguridad</p>
                    <div class="mb-3">
                      <p class="mb-0" id="ultima_Act"><?php echo $ultima_Act ?></p>
                      <small class="text-muted" id="timeAgo"></small>
                    </div>


                    <button id="respaldar-btn" class="browse-btn">Respaldar</button>
                  </div>
                </div>
                <p class="text-sm text-muted mt-3 text-center width-338p">
                  * Después de 7 días se realizar una copia de seguridad automática
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ worldLow section ] end -->





      <!-- [ Recent Users ] start -->
      <div class="col-xl-12 col-md-6">
        <div class="card Recent-Users">
          <div class="card-header">
            <h5>Titulo</h5>
          </div>
          <div class="card-body px-0 py-3">
            <div class="table-responsive">
              <table class="table ">
                <tbody>
                  <tr class="unread">

                    <td>TEXT</td>
                    <td>
                      TEXT
                    </td>
                    <td><a href="#!" class="badge me-2 bg-brand-color-2 text-white f-12">Rechazar</a><a href="#!"
                        class="badge me-2 bg-brand-color-1 text-white f-12">Aprobar</a></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
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
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script src="../../src/assets/js/ajax_class.js"></script>


  <script>


    // cuando cargue la pagina, agrega 'pc-sidebar-hide' a .pc-sidebar
    // document.addEventListener('DOMContentLoaded', function () {
    //   if ("<?php echo $ultima_Act ?>" == 'Nunca') {
    //     // actualizar()
    //   } else {
    //     // verificar si han pasado mas de 7 dias desde la ultima actualizacion
    //     var fecha = "<?php echo $ultima_Act ?>";
    //     var fecha = fecha.split('-');
    //     var fecha = new Date(fecha[2], fecha[1] - 1, fecha[0]);
    //     var hoy = new Date();
    //     var dias = Math.floor((hoy - fecha) / (1000 * 60 * 60 * 24));
    //     if (dias >= 7) {
    //       actualizar()
    //     }
    //     $('#timeAgo').html('Hace ' + dias + ' dias')
    //   }

    // });

    function actualizar() {
      document.querySelector('.pc-sidebar').classList.add('pc-sidebar-hide');
      document.querySelector('.pc-sidebar-collapse').classList.add('hide');
      // Prepare data object for AJAX request
      let data = { accion: 'get_data' }
      $('#cargando').show()


      // Send AJAX request to add the employee
      const ajaxRequest = new AjaxRequest('application/json', data, '../../back/modulo_nomina/copia_seguridad.php');


      const onSuccess = (response) => {

        if (response.status == 'ok') {
          swal('success', 'Actualizado correctamente')
          let fecha = new Date();
          document.getElementById('ultima_Act').innerHTML = fecha.getDate() + '-' + (fecha.getMonth() + 1) + '-' + fecha.getFullYear()
          $('#timeAgo').html('Hace 0 dias')
          $('#cargando').hide()

        } else {
          swal('error', response.mensaje)
        }
        document.querySelector('.pc-sidebar').classList.remove('pc-sidebar-hide');
        document.querySelector('.pc-sidebar-collapse').classList.remove('hide');
      };
      const onError = (response) => {
        console.log('Error:', response);
        toast_s('error', 'Error: ' + response);
        actualizar()
      };

      ajaxRequest.send(onSuccess, onError);
    }

    document.getElementById('respaldar-btn').addEventListener('click', actualizar)
  </script>

</body>

</html>