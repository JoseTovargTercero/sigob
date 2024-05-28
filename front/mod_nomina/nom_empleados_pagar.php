<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
    <link rel="stylesheet" href="src/styles/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.dataTables.css" />
    <title>TABULADORES</title>
</head>

<body>
    <?php require_once '../includes/menu.php' ?>
    <!-- [ MENU ] -->

    <?php require_once '../includes/top-bar.php' ?>
    <!-- [ top bar ] -->

    <div class="pc-container flex-container" id="employee-pay-view">
        <div class="form-header w-75 mx-auto">
            <a class="btn btn-outline-info btn-sm" href="nom_empleados_tabla"><box-icon
                    name='arrow-back'></box-icon></a>
            <h2 class="text-uppercase text-center">REALIZAR PAGO</h2>
            <box-icon name=''></box-icon>
        </div>
        <form class="row w-75 mx-auto form-container employee-form" id="employee-pay-form" autocomplete="off">
            <div class="form-group">
                <div class="row">
                    <label class="form-label" for="tipo_nomina">TIPO NÓMINA</label>
                    <select name="tipo_nomina" class="form-select employee-select" id="tipo_nomina">
                        <option selected value="">ELEGIR...</option>
                        <option value="1">NOMINA 1</option>
                        <option value="2">NOMINA 2</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label class="form-label" for="tipo_nomina">TIPO DE PAGO</label>
                    <select name="tipo_pago" class="form-select employee-select" id="tipo_pago">
                        <option selected value="">ELEGIR...</option>
                        <option value="1">1RA SEMANA</option>
                        <option value="2">1RA QUINCENA</option>
                        <option value="3">3RA QUINCENA</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="card-body w-75 mx-auto">
            <table id="employee-table" class="table table-striped" style="width:100%">
                <thead class="w-100">
                    <th>NOMBRES</th>
                    <th>CEDULA</th>
                    <th>DEPENDENCIA</th>
                    <th>NOMINA</th>
                    <th>ACCIONES</th>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>


</body>

<script type="module" src="app.js"></script>
<!-- DATATABLES -->
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/fonts/custom-font.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>