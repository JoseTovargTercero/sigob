<?php 
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Dashboard</title>
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
                <h5 class="mb-0">Dashboard</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row">

        <!-- [ worldLow section ] start -->
        <div class="col-xl-12 col-md-6">
          <div class="card">
            <div class="card-header">
              <h5>Titulo</h5>
            </div>
            <div class="card-body">
              <div id="world-low" style="height:450px;"></div>
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
                      <td><a href="#!" class="badge me-2 bg-brand-color-2 text-white f-12">Rechazar</a><a href="#!" class="badge me-2 bg-brand-color-1 text-white f-12">Aprobar</a></td>
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

</body>

</html>

e5 2650 v4