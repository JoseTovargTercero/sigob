<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Pago de nómina</title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="src/styles/style.css">

</head>
<?php require_once '../includes/header.php' ?>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

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
                        <div class="col-md-6">
                            <div class="page-header-title">
                                <h5 class="mb-0">Petición de nomina</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] start -->
            <div class="row mb3">
                <!-- [ worldLow section ] start -->
                <div class="col-xl-12">
                    <div class="card" id="employee-pay-form">
                        <div class="card-header">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <h5 class="mb-0">Petición de nómina a pagar</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body employee-nom-group">

                            <div class="mb-2">
                                <label for="grupo" class="form-label">Grupo de nomina</label>
                                <small class="text-muted mt-0 d-block mb-2">Seleccione un grupo de nomina</small>

                                <select id="grupo" name="grupo" class="form-control" size="6">
                                    <?php
                                    $stmt = mysqli_prepare($conexion, "SELECT id, codigo, nombre FROM `nominas_grupos` ORDER BY codigo");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . $row['id'] . '">' . $row['codigo'] . ' - ' . $row['nombre'] . '</option>';
                                        }
                                    }
                                    $stmt->close();
                                    ?>
                                </select>
                            </div>
                            <box-icon type='solid' color="gray" name='right-arrow'></box-icon>

                            <div class="mb-2">
                                <label for="nomina" class="form-label">Nómina</label>
                                <small class="text-muted mt-0 d-block mb-2">Seleccione la nómina a registrar</small>
                                <select id="nomina" name="nomina" class="form-control" size="6">
                                    <option value="">Seleccionar grupo de nómina</option>
                                </select>
                            </div>

                            <!-- <div class="mb-3">
                                <button class="btn btn-info">REALIZAR PETICIÓN</button>

                            </div> -->
                        </div>


                        <div class="loader-container card-footer py-4" id="employee-pay-loader">
                            <div class="loader"></div>
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
    <script type="module" src="src/controllers/peticionesNominaForm.js"></script>
    <!-- <script type="module" src="app.js"></script> -->
    <script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
    <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
    <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
    <script src="../../src/assets/js/pcoded.js"></script>
    <script src="../../src/assets/js/plugins/feather.min.js"></script>
    <script src="../../src/assets/js/main.js"></script>
    <!-- <script>
        const url_back = '../../back/modulo_nomina/nom_empleados_pagar_back.php';


        function obt_nominas() {
            let grupo = this.value
            if (grupo == '') {
                return
            }
            console.log('grupo')
            $.ajax({
                url: url_back,
                type: 'POST',
                data: {
                    select: true,
                    grupo: grupo
                },
                success: function (response) {
                    $('#nomina').html('<option value="">Selección</option>');
                    if (response) {
                        var data = JSON.parse(response);


                        for (var i = 0; i < data.length; i++) {
                            $('#nomina').append('<option value="' + data[i] + '">' + data[i] + '</option>');
                        }
                    }
                }
            });
        }

        function obt_nomina() {
            let nomina = this.value
            console.log(nomina)
            if (nomina == '') {
                return
            }

            $.ajax({
                url: '../../../sigob/back/modulo_nomina/nom_calculonomina.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    nombre: nomina
                }),
                success: function (response) {
                    console.log('Respuesta del servidor:', response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('Error en la petición:', textStatus, errorThrown);
                }
            });

        }

        $(document).ready(function () {
            document.getElementById('grupo').addEventListener('change', obt_nominas);
            document.getElementById('nomina').addEventListener('change', obt_nomina);
        });
    </script> -->

</body>

</html>