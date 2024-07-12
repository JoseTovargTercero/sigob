<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';


?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Inicio</title>
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
                <h5 class="mb-0">Estatus</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row">


        <div class="">
          <?php
          $errores_profesion = [];
          $empleadosXcargos = [];
          $result = $conexion->query("SELECT DISTINCT cod_cargo, nombres FROM `empleados`");

          while ($row = $result->fetch_assoc()) {
            $empleadosXcargos[$row['cod_cargo']] = $row['nombres'];
          }

          $cargos_result = $conexion->query("SELECT cod_cargo FROM `cargos_grados`");
          $cargos = array_column($cargos_result->fetch_all(MYSQLI_ASSOC), 'cod_cargo');

          $cargos_empleados_result = $conexion->query("SELECT DISTINCT cod_cargo FROM `empleados`");
          $cargos_empleados = array_column($cargos_empleados_result->fetch_all(MYSQLI_ASSOC), 'cod_cargo');

          // Verifica si todos los $cargos_empleados están en $cargos
          $errores_cargos = array_diff($cargos_empleados, $cargos);

          if (count($errores_cargos) > 0) {
            echo '<div class="card text-center m-a" style="width: 50%;">
                    <div class="card-body">
                        <i class="bx bx-error fz-40p text-danger"></i>
                        <h5 class="card-title text-dark">Errores en cargos</h5>
                        <p class="card-text mb-1">Se han encontrado ' . count($errores_cargos) . ' errores en la información</p>
                        <p class="card-text"><small class="text-muted">Ultima actualización: menos de 1 minuto</small></p>
                    </div>
                </div>';
          ?>

          <div class="card mt-3">
            <div class="card-header">
              <h5 class="mb-0">Errores de cargos</h5>
              <small class="text-muted">Empleados con cargos no encontrados</small>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-bordered" id="example">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Empleados con el cargo</th>
                      <th class="w-10 text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($errores_cargos as $value) { ?>
                      <tr>
                        <td><?php echo $value ?></td>
                        <td><?php echo $empleadosXcargos[$value] ?></td>
                        <td>
                          <button onclick="corregir('<?php echo $value ?>')" class="btn btn-danger">Corregir</button>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>




          
          <?php } if (count($errores_cargos) == 0 && count($errores_profesion) == 0) { ?>


              <!-- Estado optimo -->
              <div class="card text-center m-a" style="width: 50%;">
                <div class="card-body">
                  <i class="bx bx-check fz-40p text-success"></i>
                  <h5 class="card-title text-dark">Optimo</h5>
                  <p class="card-text mb-1">No se han encontrado errores en la información</p>
                  <p class="card-text"><small class="text-muted">Ultima actualización: menos de 1 minuto</small></p>
                </div>
              </div>

            <?php } ?>



          </div>
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
    <script src="../../src/assets/js/notificaciones.js"></script>
    <script src="../../src/assets/js/ajax_class.js"></script>
    <script>
       async function corregir(codigo) {
                const {
                  value: formValues
                } = await Swal.fire({
                  title: "Indique el nombre y el grado del concepto",
                  html: '<label for="swal-input1">Nombre</label>' +
                    '<input id="swal-input1" class="swal2-input">' +
                    '<label for="swal-input2">Grado</label>' +
                    '<input id="swal-input2" class="swal2-input">',
                  focusConfirm: false,
                  showCancelButton: true,
                  confirmButtonColor: "#69a5ff",
                  cancelButtonText: `Cancelar`,
                  preConfirm: () => {
                    const nombre = document.getElementById('swal-input1').value;
                    const grado = document.getElementById('swal-input2').value;
                    if (!nombre) {
                      Swal.showValidationMessage("¡Es necesario completar ambos campos!");
                      return null; // Evita cerrar el diálogo
                    }
                    if (!grado) {
                      Swal.showValidationMessage("¡Es necesario completar ambos campos!");
                      return null; // Evita cerrar el diálogo
                    }
                    return {
                      nombre: nombre,
                      grado: grado
                    };
                  }
                });

                if (formValues) {
                  const {
                    nombre,
                    grado
                  } = formValues;
                  corregirCargo(codigo, nombre, grado);
                }
              }



              function corregirCargo(codigo, nombre, grado) {
                $.ajax({
                    type: 'POST',
                    url: '../../back/modulo_nomina/nom_errores',
                    dataType: 'html',
                    data: {
                      corregir_cargo: true,
                      codigo: codigo,
                      nombre: nombre,
                      grado: grado
                    },
                    cache: false,
                    success: function(msg) {
                      console.log(msg)
                        const response = JSON.parse(msg);
                        if (response.status) {
                            toast_s('s', response.mensaje)
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            toast_s('r', response.mensaje)
                        }
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                  toast_s('r', 'Ocurrió un error, inténtelo nuevamente ' + errorThrown)
                });


              }
    </script>
</body>


</html>