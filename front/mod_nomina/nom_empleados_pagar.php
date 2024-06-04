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
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="mb-0">Pagar</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] start -->
            <div class="row mb3">
                <!-- [ worldLow section ] start -->
                <div class="col-xl-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <h5 class="mb-0">Pago de nómina</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label for="grupo" class="form-label">Grupo de nomina</label>
                                <select id="grupo" class="form-control">
                                    <option value="">Selección</option>
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

                            <div class="mb-3">
                                <label for="nomina" class="form-label">Nómina</label>
                                <select id="nomina" class="form-control">
                                    <option value="">Selección</option>
                                </select>
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
    <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
    <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
    <script src="../../src/assets/js/pcoded.js"></script>
    <script src="../../src/assets/js/plugins/feather.min.js"></script>
    <script src="../../src/assets/js/main.js"></script>
    <script>
        const url_back = '../../back/modulo_nomina/nom_empleados_pagar_back.php';


        function obt_nominas() {
            let grupo = this.value
            if (grupo == '') {
                return
            }
            $.ajax({
                url: url_back,
                type: 'POST',
                data: {
                    select: true,
                    grupo: grupo
                },
                success: function(response) {
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
                url: '../../back/modulo_nomina/nom_calculonomina.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    nombre: nomina
                }),
                success: function(response) {
                    console.log('Respuesta del servidor:', response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Error en la petición:', textStatus, errorThrown);
                }
            });

        }

        $(document).ready(function() {
            document.getElementById('grupo').addEventListener('change', obt_nominas);
            document.getElementById('nomina').addEventListener('change', obt_nomina);
        });
    </script>

</body>

</html>