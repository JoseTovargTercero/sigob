<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
  <link rel="stylesheet" href="src/styles/style.css">
  <title>Tabuladores</title>
</head>

<body>
  <?php require_once '../includes/menu.php' ?>
  <!-- [ MENU ] -->

  <?php require_once '../includes/top-bar.php' ?>
  <!-- [ top bar ] -->

  <div class="pc-container flex-container">

    <div>
      <h2 class="text-uppercase text-center">TABULADOR</h2>
    </div>
    <form class="row w-75 mx-auto form-container" id="tabulator-primary-form" autocomplete="off">

      <div class="form-group">
        <label class="form-label" class="form-label" for="nombre">NOMBRE</label>
        <input class="tabulator-input form-control" type="text" name="nombre" id="nombre"
          placeholder="NOMBRE DE TABULADOR" />
      </div>

      <div class="row">
        <div class="col">
          <div class="form-group">
            <label class="form-label" for="grados">GRADOS</label>
            <input class="tabulator-input form-control" type="number" name="grados" id="grados" placeholder="GRADOS" />
          </div>
        </div>

        <div class="col">
          <div class="form-group">
            <label class="form-label" for="pasos">PASOS</label>
            <input class="tabulator-input form-control" type="number" name="pasos" id="pasos" placeholder="PASOS" />
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="aniosPasos">AÑOS POR PASO</label>
        <input class="tabulator-input form-control" type="number" name="aniosPasos" id="aniosPasos"
          placeholder="AÑOS POR PASO" />
      </div>

      <div clas="form-group">
        <button class="btn btn-primary w-100" id="tabulator-btn">SIGUIENTE</button>
      </div>
    </form>

    <!-- MATRIZ MODAL DE INPUTS  -->
    <div id="modal-secondary-form-tabulator" class="modal-window hide">
      <div id="tabulator-secundary-form" class="modal-box">

        <header class="modal-box-header">
          <h4>MATRIZ DE TABULADOR</h4>
          <button id="btn-close" type="button" class="btn btn-danger" aria-label="Close">
            &times;
          </button>
        </header>

        <div class="tabulator-matrix" id="tabulator-matrix"></div>

        <button class="btn-form btn btn-primary" id="tabulator-save-btn">ENVIAR TABULADOR</button>

      </div>
    </div>
  </div>

</body>

<script type="module" src="app.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/fonts/custom-font.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>