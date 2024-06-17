<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
  <link rel="stylesheet" href="../src/styles/style.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.dataTables.css" />
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
                <h5 class="mb-0">Pago de nómina</h5>
                <small class="text-muted mt-0 d-block mb-2">Consulte la nómina a revisar</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-xl-12 mb-3" id="pay-nom-form">
          <div class="card mx-auto">
            <div class="card-header">
              <div>
                <h5 class="mb-0">Pago de nómina</h5>
                <small class="mt-0 text-muted">Gestione la generación de txt de nóminas</small>
              </div>
            </div>
            <div class="card-body">
              <div class="forum-group">
                <div class="row mx-auto align-items-end">
                  <div class="col-sm-9">
                    <label for="select-correlativo" class="form-label">Selecciona el correlativo</label>
                    <select id="select-correlativo" name="select-correlativo" class="form-control">
                      <option value="">Seleccionar correlativo</option>
                    </select>
                  </div>
                  <div class="col-sm align-items-center">
                    <button class="btn btn-primary" id="consultar-correlativo">Consultar</button>
                  </div>
                </div>
              </div>
              <div class="loader-container card-footer py-4" id="pay-nom-loader">
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

</script>
<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/fonts/custom-font.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>