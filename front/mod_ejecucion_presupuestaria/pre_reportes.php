<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
    <link rel="stylesheet" href="../src/styles/style.css">
    <link rel="stylesheet" href="../../src/assets/css/chosen.min.css">

    <script src="../../src/assets/js/chosen.jquery.min.js"></script>

    <title>Gastos de funcionamiento</title>
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

                            <div class=" d-flex justify-content-between">
                                <h4 class="fw-bold py-3 mb-4">
                                    <span class="text-muted fw-light">Ejecución presupuestaria /</span> Reportes
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

                <div class="col-lg-12 mb-3" id="reportes-view">

                    <div class="row">
                        <div class="col-5" id="reportes-lista">

                        </div>
                        <div class="col">
                            <div class="card">

                                <div class="card-header d-flex justify-content-between">
                                    <div class="">
                                        <h5 class="mb-0">Histórico de gastos realizados</h5>
                                        <small class="mt-0 text-muted">Visualice el historial de gastos de
                                            funcionamiento</small>
                                    </div>
                                </div>
                                <div class="card-body d-flex justify-content-center align-items-center"
                                    id="reportes-container">
                                    <div class="alert alert-info"'>
                                        <p class="text-center m-0">Elija alguno de los reportes</p>
                                    </div>
                                </div>
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
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>