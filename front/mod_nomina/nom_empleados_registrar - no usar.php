<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
    <link rel="stylesheet" href="../src/styles/style.css">
    <title>Registrar Personal</title>
</head>

<body>
    <?php require_once '../includes/menu.php' ?>
    <!-- [ MENU ] -->

    <?php require_once '../includes/top-bar.php' ?>
    <!-- [ top bar ] -->

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="mb-0">Formulario de empleados</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] start -->
            <div class="row mb3">
                <!-- [ worldLow section ] start -->
                <div class="col-lg-12 mb-3" id="section-tabla">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h5 class="mb-0">Nuevo empleado</h5>
                                <small class="text-muted mt-0">Registre la información del empleado</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-1">

                                <form class="row employee-form" id="employee-form" autocomplete="off">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm">
                                                <label class="form-label" class="form-label" for="nombres">NOMBRE
                                                    COMPLETO</label>
                                                <input class="form-control employee-input form-input" type="text"
                                                    name="nombres" id="nombres" placeholder="NOMBRE COMPLETO" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="form-label" for="tipo_nomina">TIPO NÓMINA</label>
                                                <select name="tipo_nomina" class="form-select employee-select"
                                                    id="tipo_nomina">
                                                    <option selected value="">ELEGIR...</option>
                                                    <option value="1">OPCIÓN 1</option>
                                                    <option value="2">OPCIÓN 2</option>
                                                </select>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm">
                                                <label class="form-label" for="nacionalidad">NACIONALIDAD</label>
                                                <select name="nacionalidad" class="form-select employee-select"
                                                    id="nacionalidad">
                                                    <option selected value="">NACIONALIDAD</option>
                                                    <option value="V">V</option>
                                                    <option value="E">E</option>
                                                </select>
                                            </div>
                                            <div class="col-sm">
                                                <label class="form-label" for="cedula">CÉDULA</label>
                                                <input class="employee-input form-input form-control" type="text"
                                                    name="cedula" id="cedula" placeholder="CEDULA..." maxlength="9" />
                                            </div>
                                            <div class="col-sm">
                                                <label class="form-label" for="status">ESTADO DEL TRABAJADOR</label>
                                                <select name="status" id="status" class="form-select employee-select">
                                                    <option value="">ELEGIR...</option>
                                                    <option value="1" selected>ACTIVO</option>
                                                    <option value="0">INACTIVO</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm">
                                                <label class="form-label" for="instruccion_academica">INSTRUCCIÓN
                                                    ACADÉMICA</label>
                                                <select class="form-select employee-select" name="instruccion_academica"
                                                    id="search-select-instruccion_academica">
                                                    <option value="" selected>ELEGIR...</option>
                                                </select>

                                            </div>

                                            <div class="col-sm" tabindex="0">
                                                <label class="form-label" for="cargo">CARGO AL QUE OPTA</label>
                                                <!-- <input class="employee-input form-input form-control select-search-input" type="text" name="cargo"
                            placeholder="Buscar cargo..." id="cargo" /> -->

                                                <select class="form-select employee-select" name="cod_cargo"
                                                    id="search-select-cargo">
                                                    <option value="" selected>ELEGIR...</option>
                                                </select>
                                            </div>
                                            <div class="col-sm">
                                                <label class="form-label" for="fecha_ingreso">FECHA DE INGRESO</label>
                                                <input class="employee-input form-input form-control" type="date"
                                                    name="fecha_ingreso" placeholder="Fecha de ingreso"
                                                    id="fecha_ingreso" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm">
                                                <label class="form-label" for="otros_años">AÑOS LABORALES</label>
                                                <input class="employee-input form-input form-control" type="number"
                                                    name="otros_años" placeholder="Cantidad de años" id="otros_años" />
                                            </div>
                                            <div class="col-sm">
                                                <label class="form-label" for="">HIJOS</label>
                                                <input class="employee-input form-input form-control " type="number"
                                                    name="hijos" placeholder="CANTIDAD DE HIJOS...">
                                            </div>
                                            <div class="col-sm">
                                                <label class="form-label" for="discapacidades">DISCAPACIDAD</label>
                                                <select name="discapacidades" class="form-select employee-select"
                                                    id="discapacidades">
                                                    <option value="" selected>ELEGIR...</option>
                                                    <option value="1">SÍ POSEE</option>
                                                    <option value="0">NO POSEE</option>
                                                </select>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm">
                                                <label class="form-label" for="banco">BANCO</label>
                                                <select name="banco" class="form-select employee-select"
                                                    id="search-select-bancos">
                                                    <option value="" selected>ELEGIR...</option>

                                                </select>
                                            </div>
                                            <div class="col-sm">
                                                <label class="form-label" for="cuenta_bancaria">N° DE CUENTA</label>
                                                <input class="employee-input form-input form-control" type="text"
                                                    name="cuenta_bancaria" placeholder="0000 0000 00 0000"
                                                    id="cuenta_bancaria" maxlength="20">
                                            </div>
                                            <!-- <div class="col-sm">
                                                <label class="form-label" for="tipo_cuenta">TIPO DE CUENTA</label>
                                                <select name="tipo_cuenta" class="form-select employee-select"
                                                    id="tipo_cuenta">
                                                    <option value="activo" selected>ELEGIR...</option>
                                                    <option value="0">CORRIENTE</option>
                                                    <option value="1">AHORRO</option>

                                                </select>
                                            </div> -->
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row mx-auto">
                                            <label class="form-label" for="id_dependencia">DEPENDENCIAS
                                                LABORALES</label>
                                            <div class="col-sm-4">
                                                <select class="form-select employee-select" name="id_dependencia"
                                                    id="search-select-dependencias">

                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="text" name="cod_dependencia" id="cod_dependencia"
                                                    placeholder="Codigo de dependencia">

                                            </div>
                                            <div class="col-sm-1">
                                                <button type="button" id="add-dependency"
                                                    class="btn btn-primary">+</button>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="observacion">OBSERVACIONES</label>
                                        <textarea class="form-control employee-input form-input" name="observacion"
                                            placeholder="Observación sobre el empleado..." id="observacion"
                                            style="height: 50px"></textarea>
                                    </div>


                                    <!-- <div class="form-group">
                <div class="row">
                    <div class="col-sm">
                        <label class="form-label" for=""></label>
                    </div>
                </div>
            </div>


            <div class="form-group">
                <div class="row">
                    <div class="col-sm">
                        <label class="form-label" for=""></label>
                    </div>
                </div>
            </div> -->

                                    <div clas="form-group">
                                        <button class="btn btn-primary w-100" id="tabulator-btn">GUARDAR</button>
                                    </div>
                                    <div id="modal-dependency" class="modal-window hide">
                                        <div class="modal-box short slide-up-animation">

                                            <div class="row">
                                                <header class="modal-box-header">
                                                    <h4>AÑADIR NUEVA DEPENDENCIA</h4>
                                                    <button id="btn-close-dependency" type="button"
                                                        class="btn btn-danger" aria-label="Close">
                                                        &times;
                                                    </button>
                                                </header>
                                            </div>

                                            <input class="employee-input form-input form-control" type="text"
                                                name="dependencia" placeholder="NUEVA DEPENDENCIA..." id="dependencia">
                                            <button class="btn-form btn btn-primary" id="dependency-save-btn">GUARDAR
                                                DEPENDENCIA</button>
                                        </div>
                                    </div>
                                </form>
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
    <!-- [ Main Content ] end -->

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