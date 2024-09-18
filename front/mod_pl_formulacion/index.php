<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';



if (isset($_GET["ejercicio"])) {
  $annio = $_GET["ejercicio"];
} else {
  $annio = date('Y');
}


$stmt = mysqli_prepare($conexion, "SELECT * FROM `ejercicio_fiscal`  WHERE ano = ?");
$stmt->bind_param('s', $annio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ejercicio_fiscal = $row['id']; // formato: dd-mm-YY
    $situado = $row['situado']; // formato: dd-mm-YY
  }
} else {
  $ejercicio_fiscal = 'No';
}
$stmt->close();









?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Inicio</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">



  <style>
    td {
      padding: 7px !important;
    }
  </style>

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




      <div class=" d-flex justify-content-between">

        <?php
        $y_d1 = date('Y') - 1;
        $y_d = date('Y');
        $y_d2 = date('Y') + 1;
        ?>
        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> Ejercicio fiscal <?php echo $y_d; ?>
        </h4>

        <div class="d-flex gap-1">
          <p> <a href="">Años anteriores</a>... </p>

          <p><a class="pointer <?php echo ($annio == $y_d1 ? 'text-decoration-underline text-primary' : 'text-dark') ?>" href="?ejercicio=<?php echo $y_d1 ?>"><?php echo $y_d1 ?></a></p>
          <p><a class="pointer <?php echo ($annio == $y_d ? 'text-decoration-underline text-primary' : 'text-dark') ?> " href="?ejercicio=<?php echo $y_d ?>"><?php echo $y_d ?></a></p>
          <p><a href="?ejercicio=<?php echo $y_d2 ?>" class="pointer <?php echo ($annio == $y_d2 ? 'text-decoration-underline text-primary' : 'text-dark') ?>"><?php echo $y_d2 ?></a></p>

        </div>
      </div>


      <!-- CONTENIDO -->


      <div class="row ">




        <div class="col-lg-4">
          <div class="card" style="min-height: 165px;">
            <div class="card-body">
              <div class="d-flex justify-content-between" style="position: relative;">
                <div class="d-flex flex-column">
                  <div class="card-title mb-auto">
                    <h5 class="mb-0">Entes jurídicos</h5>
                    <small>Con asignación presupuestaria</small>
                  </div>
                  <div class="chart2-statistics">
                    <h3 class="card-title ">
                      <span id="cant_planes_cont">12</span>
                      <small class="text-muted">Entes</small>
                    </h3>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card" style="min-height: 165px;">
            <div class="card-body">
              <div class="d-flex justify-content-between" style="position: relative;">
                <div class="d-flex flex-column">
                  <div class="card-title mb-auto">
                    <h5 class="mb-0">Descentralizados</h5>
                    <small>Con asignación presupuestaria</small>
                  </div>
                  <div class="chart2-statistics">
                    <h3 class="card-title ">
                      <span id="cant_planes_cont">12</span>
                      <small class="text-muted">Entes</small>
                    </h3>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>


        <div class="col-lg-4">



          <?php

          if ($ejercicio_fiscal == 'No') {
            echo ' <div class="card bg-label-danger" style="min-height: 165px;">
                <div class="card-body d-flex justify-content-between ">


                  <div class="mb-0 d-flex flex-column justify-content-between text-center text-sm-start me-3">
                    <div class="card-title">
                      <h4 class="text-danger mb-2">Ejercicio fiscal ' . (isset($_GET["a"]) ? $_GET["a"] : date('Y')) . ' </h4>
                      <p class="text-body app-academy-sm-60 app-academy-xl-100">
                        No hay ningún plan registrado este año.
                      </p>
                      <div class="mb-0"><button class="btn btn-danger" onclick="n_plan()">Iniciar Plan</button></div>
                    </div>
                  </div>



                </div>
                </div>';
          } else {
          ?>

            <div class="card mb-3" style="min-height: 165px;">
              <div class="card-body">
                <h5 class="d-flex justify-content-between align-items-center mb-3">Ejercicio fiscal <?php echo $annio ?>
                </h5>
                <?php
                echo '<p class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">Situado constitucional: <b>' . number_format($situado, 0, ',', '.') . ' Bs</b></p>';
                echo '<p class="mb-1 d-flex flex-column flex-sm-row justify-content-between text-center gap-3">Planes operativos: <b>' . number_format($situado, 0, ',', '.') . ' Bs</b></p>';

                ?>
              </div>
            </div>



          <?php
          }
          ?>





          <script>
            function n_plan() {
              toggleDialogs();
            }
          </script>


        </div>


      </div>




      <div class="dialogs">
        <div class="dialogs-content " style="width: 35%;">
          <span class="close-button">×</span>
          <h5 class="mb-1">Nuevo ejercicio fiscal</h5>
          <hr>

          <div class="card-body">

            <form id="dataEjercicio">

              <div class="mb-3">
                <label for="situado" class="form-label">Situado constitucional</label>
                <input type="number" id="situado" name="situado" class="form-control" placeholder="Presupuesto asignado para el ejercicio fiscal <?php echo $annio ?>">
              </div>

              <div class="mb-2 text-end">
                <button type="submit" class="btn btn-primary">Registrar</button>
              </div>


            </form>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] end -->
      <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
      <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
      <script src="../../src/assets/js/pcoded.js"></script>
      <script src="../../src/assets/js/plugins/feather.min.js"></script>
      <script src="../../src/assets/js/notificaciones.js"></script>
      <script src="../../src/assets/js/main.js"></script>
      <script src="../../src/assets/js/ajax_class.js"></script>

      <script>
        // detecta el onsubmit de dataEjercicio y valida los datos para enviarlos por ajax
        document.getElementById('dataEjercicio').addEventListener('submit', function(event) {
          event.preventDefault();
          var situado = document.getElementById('situado').value;


          // verifica que situado sea un numero
          if (isNaN(situado)) {
            toast_s('error', 'El campo situado debe ser un número.');
            return false;
          }
          // verifica que situado sea mayor a 0
          if (situado <= 0) {
            toast_s('error', 'El campo situado debe ser mayor a 0.');
            return false;
          }
          // verifica que situado sea un número entero
          if (situado % 1 !== 0) {
            toast_s('error', 'El campo situado debe ser un número entero.');
            return false;
          }
          // si todos los campos son validos, envia los datos por ajax



          // Datos a enviar
          const data = {
            ano: '<?php echo $annio ?>',
            situado: situado,
            divisor: '12',
            accion: 'insert'
          };

          // Crear la solicitud AJAX usando fetch
          fetch('../../back/modulo_pl_formulacion/form_ejercicio_fiscal.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(response => {
              // Manejar la respuesta exitosa

              if (response.success) {
                toast_s('success', response.success)

                // recargar la pagina
                window.location.reload();
              } else {
                console.error('Error en la respuesta: ', response);
              }
            })
            .catch(error => {
              // Manejar el error
              console.error('Error en la solicitud: ', error);
              alert('Error: No se iniciar');
            });




          /*
                    const data = {
                      ano: '<?php //echo $annio 
                            ?>',
                      situado: situado,
                      divisor: '12',
                      accion: 'insert'
                    };
                    const ajaxRequest = new AjaxRequest('application/json', data, '../.../back/modulo_pl_formulacion/form_ejercicio_fiscal.php');

                    /**
                     * Callback function to handle the successful response.
                     * @param {Object} response - The response object.
                     */
          /*  const onSuccess = (response) => {
            if (response.success) {
              document.getElementById('tabla_empleados').classList.add('hide');
              document.getElementById('seleccion_empleados').classList.remove('hide');
            } else {
              console.log(response)
            }
          };
*/
          /**
           * Callback function to handle the error response.
           * @param {Object} response - The response object.
           */
          /*   const onError = (response) => {
               console.log(response)
               toast_s('error', 'Error: No se puede modificar el listado');
               document.getElementById('btn-add-list').classList.add('hide');

             };

             ajaxRequest.send(onSuccess, onError);*/
        })
      </script>

</body>

</html>