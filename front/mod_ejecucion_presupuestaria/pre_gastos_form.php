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
                                    <span class="text-muted fw-light">Ejecución presupuestaria /</span> Gastos de
                                    funcionamiento
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

                <div class="col-lg-12 mb-3" id="gastos-view">
                    <div class="card slide-up-animation" id="gastos-registrar-container">
                        <div class="card-header">
                            <div class="">
                                <h5 class="mb-0">Lista de solicitudes de dozavos por entes</h5>
                                <small class="mt-0 text-muted">Administre los gastos dado el presupuesto total</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <h5 class="text-center m-0">Presupuesto total: <span id="presupuesto">10.000 Bs</span>
                                </h5>
                                <button class="btn btn-success btn-sm" id="gastos-registrar">REGISTRAR GASTO</button>
                            </div>

                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="">
                                <h5 class="mb-0">Histórico de gastos realizados</h5>
                                <small class="mt-0 text-muted">Visualice el historial de gastos de
                                    funcionamiento</small>
                            </div>
                            <nav class="nav nav-pills nav-justified" id="request-table-options">
                                <button class="nav-link active" data-tableid="gastos-table">Gastos realizados</button>
                                <button class="nav-link" data-tableid="tipos-gastos-table">Tipos de gastos</button>

                            </nav>
                        </div>
                        <div class="card-body">
                            <div class="d-block mb-2 mx-auto slide-up-animation" id="gastos-table-container">
                                <table id="gastos-table" class="table table-striped" style="width:100%">
                                    <thead>
                                        <th>N° COMPROMISO</th>
                                        <th>DESCRIPCION</th>
                                        <th>TIPO</th>
                                        <th>MONTO</th>
                                        <th>FECHA</th>
                                        <th>ESTADO</th>
                                        <th>ACCIONES</th>
                                    </thead>
                                </table>
                            </div>
                            <div class="d-none mb-2 mx-auto slide-up-animation" id="tipos-gastos-table-container">
                                <table id="tipos-gastos-table" class="table table-striped" style="width:90%">
                                    <thead>
                                        <th>NOMBRE</th>

                                        <!-- <th>PARTIDA DESCRIPCION</th> -->
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