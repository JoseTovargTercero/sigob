<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
    <link rel="stylesheet" href="../src/styles/style.css">

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
                            <div class="page-header-title">
                                <h5 class="mb-0">Gastos de Funcionamiento</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] start -->
            <div class="row mb3">

                <div class="col-lg-12 mb-3" id="gastos-view">
                    <div class="card">
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
                                <button class="btn btn-success btn-sm">REGISTRAR GASTO</button>
                            </div>
                            <div class=mt-4">
                                <form>
                                    <div class="row">


                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">TIPO DE GASTO</label>
                                                <select class="form-select" name="tipo_gasto">
                                                    <option value="viaticos">Viáticos</option>
                                                    <option value="suministros">Suministros</option>
                                                    <option value="alquiler">Alquiler</option>
                                                    <option value="servicios_publicos">Servicios Públicos</option>
                                                    <option value="capacitacion">Capacitación</option>
                                                    <option value="publicidad">Publicidad</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">Monto</label>
                                                <input class="form-control" type="number" name="" id=""
                                                    placeholder="00.00Bs">
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">DESCRIPCIóN</label>
                                                <textarea class="form-control" name="" id=""></textarea>
                                            </div>
                                        </div>
                                    </div>

                                </form>
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
                        </div>
                        <div class="card-body">
                            <table id="gastos-table" class="table table-striped" style="width:100%">
                                <thead class="w-100">
                                    <th>N° COMPROMISO</th>
                                    <th>DESCRIPCION</th>
                                    <th>TIPO</th>
                                    <th>MONTO</th>
                                    <th>FECHA</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </thead>
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