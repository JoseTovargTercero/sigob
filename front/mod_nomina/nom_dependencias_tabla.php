<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
    <link rel="stylesheet" href="../src/styles/style.css">

    <title>Dependencias</title>
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
                                <h5 class="mb-0">Dependencias</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] start -->
            <div class="row mb3">

                <div class="col-lg-12 mb-3" id="dependencia-table">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-0">Dependencias</h5>
                                    <small class="text-muted mt-0">Administre las dependencias</small>
                                </div>
                                <button class="btn btn-primary" id="dependencia-nueva">Nueva dependencia</button>

                            </div>
                        </div>
                        <div class="card-body hide slide-up-animation" id="dependencia-form-container">
                            <form id="dependencia-form">
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label for="dependencia" class="form-label">NOMBRE</label>
                                            <input type="text" name="dependencia" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <label class="form-label" for="id_dependencia">CODIGO</label>
                                            <div class="col-sm-8 mb-2">
                                                <input type="text" name="cod_dependencia" class="form-control">
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="button" id="dependencia-guardar"
                                                    class="btn btn-primary">Guardar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-1">
                                <table id="dependencias-table" class="table table-xs table-striped" style="width:100%">
                                    <thead class="w-100">
                                        <th>NOMBRE</th>
                                        <th>CÓDIGO</th>
                                        <th>ACCIONES</th>

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


















</body>

<script type="module" src="../app.js"></script>
<script src="../../src/assets/js/notificaciones.js"></script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/fonts/custom-font.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>