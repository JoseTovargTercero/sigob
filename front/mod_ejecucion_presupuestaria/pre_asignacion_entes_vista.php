<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
  <link rel="stylesheet" href="../src/styles/style.css">

  <title>Asignación de presupuesto</title>
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
              <!-- <div class="page-header-title">
                <h5 class="mb-0">Asignación de presupuesto a entes</h5>
              </div> -->

              <div class=" d-flex justify-content-between">

                <h4 class="fw-bold py-3 mb-4">
                  <span class="text-muted fw-light">Ejecución presupuestaria /</span> Presupuesto de unidades y
                  dependencias
                </h4>

                <div class="row" id="ejercicios-fiscales">
                </div>

              </div>

            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">

        <div class="col-lg-12 mb-3" id="pre-asignacion-entes-view">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <div class="">
                <h5 class="mb-0">Asignación de presupuesto a unidades y dependencias</h5>
              </div>
              <!-- <button class="btn btn-sm btn-primary" id="entes-asignar">REGISTRAR</button> -->
            </div>
            <div class="card-body">
              <!-- <div class="table-responsive d-none">
                <table id="distribucion-entes-table" class="table table-striped" style="width:100%">
                  <thead class="w-100">
                    <th>DISTRIBUCION</th>
                    <th>MONTO</th>
                    <th>ACCIONES</th>
                  </thead>
                </table>
              </div> -->
              <div class="table-responsive">
                <table id="pre-asignacion-entes-table" class="table table-striped" style="width:100%">
                  <thead class="">
                    <th class="w-50">ENTE</th>
                    <th class="w-10">MONTO ASIGNADO</th>
                    <th class="w-10">spp</th>
                    <th>FECHA</th>
                    <th>ACCIONES</th>
                  </thead>
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


















</body>

<script type="module" src="../app.js"></script>

<script src="../../src/assets/js/notificaciones.js"></script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>