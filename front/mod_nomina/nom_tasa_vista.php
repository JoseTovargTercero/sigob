<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
    <link rel="stylesheet" href="../src/styles/style.css">

    <title>TASA DEL DÍA</title>
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
                        <div class="col-md-6">
                            <div class="page-header-title">
                                <h5 class="mb-0">Tasa del dólar del (BCV)</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] start -->
            <div class="row mb3">

                <div class="col-lg-12 mb-3" id="tasa-view">
                    <div class="card">
                        <div class="card-header">
                            <div class="">
                                <h5 class="mb-0">Visualice la tasa del día</h5>
                                <small class="mt-0 text-muted">Actualice según sea requerido</small>
                            </div>
                        </div>

                        <div class="card-body" id="tasa-card-body"></div>
                        <div class="mx-auto slide-up-animation hide" id="tasa-form-container">

                            <form id="tasa-form">
                                <small class="mb-2 text-muted">*Nota: Asegúrese de que coincide con la tasa del Banco
                                    Central
                                    de Venezuela (BCV)</small>

                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text mb-auto">Nueva tasa del $</span>
                                        <div>
                                            <input type="text" class="form-control" name="tasa-input"
                                                placeholder="00.0000 Bs">
                                        </div>
                                        <button class="btn btn-outline-secondary mb-auto" id="tasa-guardar"
                                            type="button">Guardar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="">
                            <h5 class="mb-0">Registros de cambio de la tasa BCV</h5>
                            <small class="mt-0 text-muted">Visualice los registros realizados de tasas
                                anteriores</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tasa-table" class="table table-striped" style="width:100%">
                            <thead class="w-100">
                                <th>USUARIO</th>
                                <th>VALOR TASA</th>
                                <th>FECHA</th>
                                <th>DESCRIPCIÓN</th>

                            </thead>
                            <tbody>

                            </tbody>
                        </table>
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