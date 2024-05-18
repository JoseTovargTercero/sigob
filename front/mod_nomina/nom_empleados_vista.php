<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
    <link rel="stylesheet" href="src/styles/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.dataTables.css" />
    <title>PERSONAL</title>
</head>

<body>
    <?php require_once '../includes/menu.php' ?>
    <!-- [ MENU ] -->

    <?php require_once '../includes/top-bar.php' ?>
    <!-- [ top bar ] -->

    <div class="pc-container flex-container">
        <div class="card w-90 mx-auto">
            <div class="card-header">
                <div>
                    <h3 class="text-uppercase text-center">PERSONAL</h3>
                </div>
            </div>
            <div class="card-body">
                <table id="employee-table" class="table table-striped" style="width:100%">
                    <thead>
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
    </div>


</body>

<script type="module" src="app.js"></script>
<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/fonts/custom-font.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>