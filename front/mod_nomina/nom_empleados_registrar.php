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
            <h2 class="text-uppercase text-center">REGISTRAR EMPLEADO</h2>
        </div>
        <form class="row w-100 mx-auto form-container employee-form" id="employee-form" autocomplete="off">
            <div class="form-group">
                <label class="form-label" class="form-label" for="nombre">NOMBRE COMPLETO</label>
                <input class=" form-control" type="text" name="nombre" id="nombre" placeholder="NOMBRE COMPLETO" />
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-sm">
                        <label class="form-label" for="nacionalidad">NACIONALIDAD</label>
                        <select name="nacionalidad" class="form-select employee-select" id="nacionalidad">
                            <option selected>NACIONALIDAD</option>
                            <option value="V">V</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                    <div class="col-sm">
                        <label class="form-label" for="identificacion">IDENTIFICACION</label>
                        <input class="employee-input form-control" type="number" name="identificacion"
                            id="identificacion" placeholder="IDENTIFICACION" />
                    </div>
                    <div class="col-sm">
                        <label class="form-label" for="grados">ESTADO DEL TRABAJADOR</label>
                        <select name="status" id="status" class="form-select employee-select">
                            <option>ELEGIR...</option>
                            <option value="activo" selected>ACTIVO</option>
                            <option value="inactivo">INACTIVO</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-sm">
                        <label class="form-label" for="instruccion_academica">INSTRUCCIÓN ACADÉMICA</label>
                        <select name="instruccion_academica" id="instruccion_academica"
                            class="form-select employee-select">
                            <option selected>ELEGIR...</option>
                            <option value="activo">INGENIERO</option>
                            <option value="inactivo">TSU</option>
                            <option value="inactivo">ETC</option>
                        </select>
                    </div>

                    <div class="col-sm">
                        <label class="form-label" for="cargo">CARGO AL QUE OPTA</label>
                        <select name="cod_cargo" class="form-select employee-select" id="cargo">
                            <option value="activo" selected>ELEGIR...</option>
                            <option value="inactivo">OPERADOR</option>
                            <option value="inactivo">ADMINISTRATIVO</option>
                            <option value="inactivo">MANTENIMIENTO</option>
                        </select>
                    </div>
                    <div class="col-sm">
                        <label class="form-label" for="fecha_ingreso">FECHA DE INGRESO</label>
                        <input class="employee-input form-control" type="date" name="fecha_ingreso"
                            placeholder="Fecha de ingreso" id="fecha_ingreso" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-sm">
                        <label class="form-label" for="otros_anios">AÑOS LABORALES</label>
                        <input class="employee-input form-control" type="number" name="otros_anios"
                            placeholder="Cantidad de años" id="otros_anios" />
                    </div>
                    <div class="col-sm">
                        <label class="form-label" for="">HIJOS</label>
                        <input class="employee-input form-control " type="number" name="hijos"
                            placeholder="CANTIDAD DE HIJOS">
                    </div>
                    <div class="col-sm">
                        <label class="form-label" for="discapacidades">DISCAPACIDAD</label>
                        <select name="discapacidades" class="form-select employee-select" id="discapacidades">
                            <option value="activo" selected>ELEGIR...</option>
                            <option value="">SÍ POSEE</option>
                            <option value="inactivo">NO POSEE</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-sm-3">
                        <label class="form-label" for="banco">BANCO</label>
                        <select name="banco" class="form-select employee-input" id="banco">
                            <option value="activo" selected>ELEGIR...</option>
                            <option value="1">VENEZUELA</option>
                            <option value="2">BICENTENARIO</option>
                            <option value="2">TESORO</option>
                        </select>
                    </div>

                    <div class="col-sm">
                        <label class="form-label" for="cuenta">N° DE CUENTA</label>
                        <input class="employee-input form-control" type="number" name="cuenta"
                            placeholder="0000 0000 00 0000" id="cuenta">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="dependencias">DEPENDENCIAS LABORALES</label>
                <select name="dependencias" class="form-select employee-input" id="dependencias">
                    <option value="dependenciaID" selected>AÑADIR DEPENDENCIAS</option>

                </select>
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
        </form>

    </div>


</body>

<script type="module" src="app.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/fonts/custom-font.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>