<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
  <link rel="stylesheet" href="../src/styles/style.css">
  <title>Tabuladores</title>
</head>

<body>





  <!-- MATRIZ MODAL DE INPUTS  -->
  <div id="modal-secondary-form-tabulator" class="modal-window hide">
    <div id="tabulator-secundary-form" class="modal-box">

      <header class="modal-box-header">
        <h5>Matriz del tabulador</h5>
        <button id="btn-close" type="button" class="btn btn-danger" aria-label="Close">
          &times;
        </button>
      </header>

      <div class="tabulator-matrix" id="tabulator-matrix"></div>

      <button class="btn-form btn btn-primary" id="tabulator-save-btn">ENVIAR TABULADOR</button>

    </div>
  </div>






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
                <h5 class="mb-0">Nuevo tabulador</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-xl-6 col-md-6 mb-3 m-auto">
          <div class="card">
            <div class="card-header">
              <div>
                <h5 class="mb-0">Nuevo tabulador</h5>
                <small class="mt-0 text-muted">Configure su nuevo tabulador</small>
              </div>
            </div>
            <div class="card-body">

              <form id="tabulator-primary-form" autocomplete="off">

                <div class="mb-3">
                  <label class="form-label" class="form-label" for="nombre">NOMBRE</label>
                  <input class="tabulator-input form-control form-input" type="text" name="nombre" id="nombre"
                    placeholder="NOMBRE DE TABULADOR" />
                </div>

                <div class="mb-3">
                  <div class="form-group">
                    <label class="form-label" for="grados">GRADOS</label>
                    <input class="tabulator-input form-control form-input" type="number" name="grados" id="grados"
                      placeholder="GRADOS" />
                  </div>
                </div>

                <div class="mb-3">
                  <div class="form-group">
                    <label class="form-label" for="pasos">PASOS</label>
                    <input class="tabulator-input form-control form-input" type="number" name="pasos" id="pasos"
                      placeholder="PASOS" />
                  </div>
                </div>
                <div class="mb-3">
                  <div class="form-group">
                    <label class="form-label" for="aniosPasos">AÑOS POR PASO</label>
                    <input class="tabulator-input form-control form-input" type="number" name="aniosPasos"
                      id="aniosPasos" placeholder="AÑOS POR PASO" />
                  </div>
                </div>
                <div class="text-end">
                  <button class="btn btn-primary" id="tabulator-btn">SIGUIENTE</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



</body>









<script type="module" src="../app.js"></script>
<script src="../../src/assets/js/notificaciones.js"></script>

<script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>