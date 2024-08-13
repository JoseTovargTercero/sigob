<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
  <link rel="stylesheet" href="../src/styles/style.css">
  <title>PERSONAL</title>
</head>

<body>
  <?php require_once '../includes/menu.php' ?>
  <!-- [ MENU ] -->

  <?php require_once '../includes/top-bar.php' ?>
  <!-- [ top bar ] -->




  <div class="pc-container">
    <div class="pc-content">
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="mb-0">Control de peticiones de nómina</h5>
                <small class="text-muted mt-0 d-block mb-2">Consulte la nómina a revisar</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-xl-12 mb-3">
          <div class="card mx-auto" id="request-nom-form">
            <div class="card-header">
              <div>
                <h5 class="mb-0">Peticiones de pago</h5>
                <small class="mt-0 text-muted">Revisar las peticiones de nómina</small>
              </div>
            </div>
            <div class="card-body col-lg-8 mx-auto">
              <div class="forum-group">
                <div class="row mx-auto align-items-end">
                  <div class="col-lg-8">
                    <label for="nomina" class="form-label">Seleccionar nómina</label>
                    <!-- <small class="text-muted mt-0 d-block mb-2">Seleccione la nómina a registrar</small> -->
                    <select id="select-nomina" name="select-nomina" class="form-control">
                      <option value="">Seleccionar petición de nómina</option>
                    </select>
                  </div>
                  <div class="col-lg-2 align-items-center">
                    <button class="btn btn-primary" id="consultar-nomina">Consultar</button>
                  </div>
                </div>
              </div>
              <div class="loader-container card-footer py-4" id="request-comparation-loader">
                <div class="loader"></div>
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






</body>

<script type="module" src="../app.js"></script>
<script src="../../src/assets/js/notificaciones.js"></script>
</script>
<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>
<script src="../../src/assets/js/notificaciones.js"></script>

</html>