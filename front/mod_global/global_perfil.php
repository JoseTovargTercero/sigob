<?php
require_once '../../back/sistema_global/session.php';
require_once '../../back/sistema_global/conexion.php';

$u_cedula = $_SESSION["u_cedula"];
$id_user = $_SESSION["u_id"];
$u_nombre = $_SESSION['u_nombre'];
$u_nivel = $_SESSION['u_nivel'];




$stmt = mysqli_prepare($conexion, "SELECT * FROM `system_users` WHERE u_id = ? LIMIT 1");
$stmt->bind_param('s', $id_user);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $u_email = $row['u_email'];
  }
}
$stmt->close();



$stmt = mysqli_prepare($conexion, "SELECT * FROM `empleados` WHERE cedula = ? LIMIT 1");
$stmt->bind_param('s', $u_cedula);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $datosEmpleado = $row;
  }
}
$stmt->close();



if (isset($datosEmpleado['cedula'])) {
  $u_nombre = $datosEmpleado['nombres'];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Usuarios</title>
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
    <!-- [ Main Content ] start -->
    <div class="pc-content">
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="mb-0">Perfil del usuario</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4">
          <div class="card user-card user-card-1">
            <div class="card-body pb-0">
              <div class="d-flex user-about-block align-items-center mt-0 mb-3">
                <div class="flex-shrink-0">
                  <div class="position-relative d-inline-block"><img class="img-radius img-fluid wid-80" src="../../src/assets/images/user/avatar-2.jpg" alt="User image">
                    <div class="certificated-badge"><i class="fas fa-certificate text-primary bg-icon"></i> <i class="fas fa-check front-icon text-white"></i></div>
                  </div>
                </div>




                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-1"><?php echo $u_nombre ?></h6>
                  <p class="mb-0 text-muted"><?php echo ($u_cedula ? 'V' . $u_cedula : '') ?></p>
                </div>
              </div>
            </div>
            <ul class="list-group list-group-flush">
              <li class="list-group-item"><span class="f-w-500"><i class="feather icon-mail m-r-10"></i>Correo</span> <a class="float-end text-body"><?php echo $u_email ?></a></li>
              <li class="list-group-item"><span class="f-w-500"><i class="feather icon-mail m-r-10"></i>Oficina</span> <a class="float-end text-body"><?php echo $oficinas[$u_oficina_id] ?></a></li>
              <li class="list-group-item"><span class="f-w-500"><i class="feather icon-mail m-r-10"></i>Tipo de usuario</span> <a class="float-end text-body"><?php echo ($u_nivel == 1 ? 'Administrador' : 'Estandar') ?></a></li>


            </ul>

            <div class="nav flex-column nav-pills list-group list-group-flush list-pills" id="user-set-tab" role="tablist" aria-orientation="vertical">
              <a class="nav-link list-group-item list-group-item-action " id="user-set-profile-tab" data-bs-toggle="pill" href="#user-set-profile" role="tab" aria-controls="user-set-profile" aria-selected="false" tabindex="-1"><span class="f-w-500 d-inline-flex align-items-center"><i class="mb-0 feather icon-user m-r-10 h5"></i>Acciones del usuario</span> <span class="float-end"><i class="feather icon-chevron-right"></i></span> </a>
              <a class="nav-link list-group-item list-group-item-action active" id="user-set-passwort-tab" data-bs-toggle="pill" href="#user-set-passwort" role="tab" aria-controls="user-set-passwort" aria-selected="true"><span class="f-w-500 d-inline-flex align-items-center"><i class="mb-0 feather icon-shield m-r-10 h5"></i>Cambiar contraseña</span> <span class="float-end"><i class="feather icon-chevron-right"></i></span> </a>
            </div>
          </div>

        </div>
        <div class="col-lg-8">
          <div class="tab-content" id="user-set-tabContent">
            <div class="tab-pane fade " id="user-set-profile" role="tabpanel" aria-labelledby="user-set-profile-tab">

              <div class="card">
                <div class="card-header">
                  <h5><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user icon-svg-primary wid-20">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                    </svg><span class="p-l-5">Acciones del usuario</span></h5>
                </div>
                <div class="card-body">

                  <table class="table" id="table-2">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>FECHA</th>
                        <th>TIPO</th>
                        <th>DESCRIPCIÓN</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $stmt = mysqli_prepare($conexion, "SELECT * FROM `movimientos` WHERE usuario_id = ?");
                      $stmt->bind_param('s', $id_user);
                      $stmt->execute();
                      $result = $stmt->get_result();
                      if ($result->num_rows > 0) {
                        $count = 1;
                        while ($row = $result->fetch_assoc()) {
                          echo '  <tr>
                        <td>' . $count++ . '</td>
                        <td>' . $row['fecha_movimiento'] . '</td>
                        <td>' . $row['accion'] . '</td>
                        <td>' . $row['descripcion'] . '</td>
                      </tr>';
                        }
                      }
                      $stmt->close();

                      ?>
                    </tbody>
                  </table>


                </div>
              </div>
            </div>

            <div class="tab-pane fade active show" id="user-set-passwort" role="tabpanel" aria-labelledby="user-set-passwort-tab">
              <div class="alert alert-warning" role="alert">
                <h5 class="alert-heading"><i class="feather icon-alert-circle me-2"></i>Alerta</h5>
                <p>Su contraseña caducará cada 3 meses. Cámbiala periódicamente.</p>
                <hr>
                <p class="mb-0">No compartas tu contraseña</p>
              </div>
              <div class="card">
                <div class="card-header">
                  <h5><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock icon-svg-primary wid-20">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                      <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg><span class="p-l-5">Cambiar contraseña</span></h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="mb-3"><label class="form-label">Contraseña actual <span class="text-danger">*</span></label> <input id="current-pass" autocomplete="off" type="password" class="form-control" placeholder="Ingresa tu contraseña actual"> </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="mb-3"><label class="form-label">Nueva contraseña <span class="text-danger">*</span></label> <input id="new-pass" type="password" class="form-control" placeholder="Ingresa tu nueva contraseña"></div>
                    </div>
                    <div class="col-sm-6">
                      <div class="mb-3"><label class="form-label">Confirme su contraseña <span class="text-danger">*</span></label> <input id="new-pass-again" type="password" class="form-control" placeholder="Ingresa tu nueva contraseña de nuevo"></div>
                    </div>
                  </div>

                </div>
                <div class="card-footer text-end"><button class="btn btn-danger " id="btn-cambiar-pass">Cambiar Contraseña</button> <button class="btn btn-outline-dark ms-2" onclick="$('.form-control').val('')">Cancelar</button></div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../../src/assets/js/notificaciones.js"></script>


  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>

</body>

</html>


<script>
  var DataTable_2 = $("#table-2").DataTable({
    language: lenguaje_datat
  });





  function setPass() {

    let current_pass = $('#current-pass').val()
    let new_pass = $('#new-pass').val()
    let new_pass_again = $('#new-pass-again').val()

    if (!validarCampo('current-pass')) {
      return;
    }
    if (!validarCampo('new-pass')) {
      return;
    }
    if (!validarCampo('new-pass-again')) {
      return;
    }

    if (new_pass != new_pass_again) {
      toast_s('error', 'Las contraseñas no coinciden')
      return
    }

    $.ajax({
      url: "../../back/mod_global/glob_perfil_back.php",
      type: "json",
      contentType: 'application/json',
      data: JSON.stringify({
        current_pass: current_pass,
        new_pass: new_pass,
        new_pass_again: new_pass_again
      }),

      success: function(response) {

        if (response.success) {
          toast_s('success', response.success)
          $('.form-control').val('')
        } else {
          toast_s('error', response.error)

        }
      },
      error: function(xhr, status, error) {
        console.log(xhr.responseText);
      },
    });


  }

  document.getElementById('btn-cambiar-pass').addEventListener('click', setPass)
</script>