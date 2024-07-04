<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

if (isset($_GET["i"])) {
  $i = $_GET["i"];
} else {
  header("Location: nom_grupos");
}

/**
 * Retrieves data from the `nominas_grupos` table based on the provided ID.
 *
 * @param mysqli $conexion The mysqli connection object.
 * @param int $i The ID of the record to retrieve.
 * @return array|null Returns an array containing the retrieved data or null if no records found.
 */
$stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas_grupos` WHERE id = ?");
$stmt->bind_param('i', $i);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $nombre = $row['nombre'];
    $codigo = $row['codigo'];
  }
} else {
  header("Location: nom_grupos");
}
$stmt->close();



$stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas` WHERE grupo_nomina = ?");
$stmt->bind_param('i', $i);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
  $statusGrupo = 'nuevo';
} else {
  $statusGrupo = 'conRegistros';
}
$stmt->close();


/**
 * Retrieves all records from the 'nominas' table.
 *
 * @param object $conexion The database connection object.
 * @return array An array of objects representing the retrieved records.
 */
$query = $conexion->query("SELECT * FROM nominas");
$nominas = array();
while ($r = $query->fetch_object()) {
  $nominas[] = $r;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Configuración de nómina</title>
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
                <h5 class="mb-0">Configuración de nómina <br><small class="text-muted"><?php echo $codigo . ' ' . $nombre ?></small> </h5>
              </div>
            </div>
          </div>
        </div>
      </div>




      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-7">

          <div class="card">
            <div class="card-body">
              <div class="tab-content" id="v-pills-tabContent">

                <div class="tab-pane active show" id="v-listaEmpleados" role="tabpanel" aria-labelledby="v-listaEmpleados-tab">

                  <section id="tabla_empleados">


                    <h5 class="mb-3 d-flex justify-content-between">
                      <span> Lista de empleados</span>
                      <?php if ($statusGrupo == 'nuevo') { ?>
                        <button class="btn btn-primary btn-sm" id="btn-add-list">Nuevo listado</button>
                      <?php } ?>

                    </h5>

                    <table class="table table-striped table-hover ">
                      <thead>
                        <tr>
                          <th class="w-40">Cedula</th>
                          <th class="w-40">Nombre</th>
                          <th class="w-auto text-center">Estatus</th>
                        </tr>
                      </thead>
                      <tbody id="tabla_empleados-list">
                      </tbody>

                    </table>


                  </section>
                      <?php if ($statusGrupo == 'nuevo') { ?>
                  <section class="hide" id="seleccion_empleados">
                    <h5>Agrega los empleados de la nomina</h5>
                    <div class="row mt-3">
                      <div class="col-md-12">

                        <hr>
                        <div class="mb-3">
                          <label class="form-label" for="filtro_empleados">¿Como quieres seleccionar a tus
                            empleados?</label>
                          <select class="form-select" id="filtro_empleados" onchange="seleccion_empleados(this.value, 'empleados-list')">
                            <option>Seleccione</option>
                            <option value="1">Enlistar todos</option>
                            <option value="2">Por sus características (Formulación)</option>
                            <option value="3">Heredar de otra nomina</option>
                          </select>
                        </div>
                      </div>

                      <section id="herramienta-formulacion" class="hide p-3">
                        <!-- HERRAMIENTA PARA FILTRAR SEGUN FORMULA-->
                        <div class="row">

                          <div class="col-lg-6">
                            <div class="mb-3"><label class="form-label">Formulación</label>
                              <div class="input-group mb-3">
                                <textarea class="form-control condicion" rows="1" id="t_area-1"></textarea>
                                <button class="btn btn-primary" onclick="validarFormula('t_area-1', 'empleados-list')" type="button">Obtener</button>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">

                            <div class="mb-3">
                              <label class="form-label" for="campo_condiciona">Condicionantes</label>
                              <select name="campo_condiciona" onchange="setCondicionante(this.value, 'result')" id="campo_condiciona" class="form-control">
                                <option value="">Seleccione</option>
                                <option value="cod_cargo">Código de cargo</option>
                                <option value="discapacidades">Discapacidades</option>
                                <option value="instruccion_academica">Instrucción académica</option>
                                <option value="hijos">Hijos</option>
                                <option value="antiguedad">Antigüedad (desde la fecha de ingreso)</option>
                                <option value="antiguedad_total">Antigüedad (Sumando años anteriores)</option>
                                <option value="tipo_nomina">Tipo de nomina</option>

                              </select>
                            </div>
                            <ol class="list-group list-group-numbered" id="result">
                            </ol>
                          </div>
                        </div>
                      </section>


                      <div class="col-md-12 hide" id="otras_nominas-list">
                        <div class="mb-3">
                          <label class="form-label" for="otra_nominas">Nominas registradas</label>
                          <select class="form-select" id="otra_nominas">
                            <option>Seleccione</option>
                            <?php foreach ($nominas as $n) : ?>
                              <option value="<?php echo $n->nombre; ?>">&nbsp;<?php echo $n->nombre; ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <!-- SIEMPRE VISIBLE, CON LA LISTA DE TRABAJADORES-->
                      <section class="mt-3 mh-60">
                        <table class="table table-striped table-hover">
                          <thead>
                            <tr>
                              <th class="w-40">Cedula</th>
                              <th class="w-40">Nombre</th>
                              <th class="w-auto text-center"><input type="checkbox" id="selectAll" onchange="checkAll(this.checked, '')" class="form-check-input" /></th>
                            </tr>
                          </thead>
                          <tbody id="empleados-list">
                          </tbody>

                        </table>


                      </section>

                      <p class="text-end mt-2" id="resumen_epleados_seleccionados">

                      </p>
                    </div>
                    <div class="d-flex w-100 mt-3">
                      <div class="d-flex m-a">
                        <div class="me-2"><button class="previous btn btn-info" onclick="guardarListaEmpleados()">Guardar</button>
                          </div>
                        </div>
                      </div>
                  </section>                     
                   <?php } ?>
                </div>





                <div class="tab-pane fade" id="v-pills-addEmpleado" role="tabpanel" aria-labelledby="v-pills-addEmpleado-tab">
                  <p class="mb-0">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy.</p>
                </div>
            
                <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                  <p class="mb-0">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text.</p>
                </div>
              </div>
            </div>
          </div>
        </div>


        <div class="col-5">

          <div class="card">
            <div class="card-body">
              <div class="card-head mb-3">
                <h5>Opciones disponibles</h5>
              </div>
              <ul class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                  <li><a class="nav-link active" id="v-listaEmpleados-tab" data-bs-toggle="pill" href="#v-listaEmpleados" role="tab" aria-controls="v-listaEmpleados" aria-selected="false" tabindex="-1">Lista de empleados</a></li>
                <li><a class="nav-link" id="v-pills-addEmpleado-tab" data-bs-toggle="pill" href="#v-pills-addEmpleado" role="tab" aria-controls="v-pills-addEmpleado" aria-selected="false" tabindex="-1">Agregar empleado</a></li>
                <li><a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="true">Cambiar estatus</a></li>
              </ul>
            </div>
          </div>
        </div>

        <script>
          // detecta cuando 'v-listaEmpleados' se muestre y muestra un mensaje en la consola
          $('#v-listaEmpleados-tab').on('shown.bs.tab', function(e) {
            console.log('v-listaEmpleados-tab activado');
          })
        </script>


        <!-- [ worldLow section ] end -->
        <!-- [ Recent Users ] end -->
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>
  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script src="../../src/assets/js/ajax_class.js"></script>
  <?php if ($statusGrupo == 'nuevo') { echo '<script src="../../src/assets/js/lista-empleados.js"></script> '; } ?>

  <script>
    const url_back = '../../back/modulo_nomina/nom_modificar.php';
    let textarea = 't_area-1';


    <?php if ($statusGrupo == 'nuevo') {  ?>
      // Solo en caso de que no exista ninguna nomina creada en el grupo
      /**
       * Adds an event listener to the 'btn-add-list' element.
       * When the button is clicked, it hides the 'tabla_empleados' element
       * and removes the 'hide' class from the 'seleccion_empleados' element.
       */
      document.getElementById('btn-add-list').addEventListener('click', function() {

        const data = {
          grupo_nomina: '<?php echo $i ?>',
          accion: 'verificar_grupo'
        };
        const ajaxRequest = new AjaxRequest('application/json', data, url_back);

        /**
         * Callback function to handle the successful response.
         * @param {Object} response - The response object.
         */
        const onSuccess = (response) => {
          if (response.status == 'ok') {
            document.getElementById('tabla_empleados').classList.add('hide');
            document.getElementById('seleccion_empleados').classList.remove('hide');
          }
        };

        /**
         * Callback function to handle the error response.
         * @param {Object} response - The response object.
         */
        const onError = (response) => {
          toast_s('error', 'Error: No se puede modificar el listado');
          document.getElementById('btn-add-list').classList.add('hide');

        };

        ajaxRequest.send(onSuccess, onError);
      })


      /**
       * Saves the list of selected employees.
       *
       * @return void
       */
      function guardarListaEmpleados() {
        if (empleadosSeleccionados.length === 0) {
          return toast_s('error', 'Debe seleccionar al menos un empleado');
        }
        const data = {
          grupo_nomina: '<?php echo $i ?>',
          accion: 'registro_masivo',
          empleados: empleadosSeleccionados
        };
        const ajaxRequest = new AjaxRequest('application/json', data, url_back);
        const onSuccess = (response) => {
          //console.log('Success:', response);
          toast_s('success', 'Registrados con éxito');
          cargarListaEmpleados();
          document.getElementById('tabla_empleados').classList.remove('hide');
          document.getElementById('seleccion_empleados').classList.add('hide');
        };
        const onError = (response) => {
          console.log('Error:', response);
          toast_s('error', 'Error: ' + response);
        };
        ajaxRequest.send(onSuccess, onError);
      }


    
      <?php } ?>





      const badges = {
        'A': ['Activo', 'badge bg-success'],
        'I': ['Inactivo', 'badge bg-danger'],
        'R': ['Retirado', 'badge bg-warning'],
        'S': ['Suspendido', 'badge bg-info'],
        'V': ['Vacaciones', 'badge bg-primary'],
        'L': ['Licencia', 'badge bg-secondary'],
        'E': ['Excedencia', 'badge bg-dark'],
        'B': ['Baja', 'badge bg-light']
      }


      /**
       * Function to load the list of employees.
       */
      function cargarListaEmpleados() {
        const data = {
          grupo_nomina: '<?php echo $i ?>',
          accion: 'cargar_lista'
        };
        const ajaxRequest = new AjaxRequest('application/json', data, url_back);

        /**
         * Callback function to handle the successful response.
         * @param {Object} response - The response object.
         */
        const onSuccess = (response) => {
          if (response.datos) {
            document.getElementById('tabla_empleados-list').innerHTML = '';

            response.datos.forEach((empleado) => {
              let tr = document.createElement('tr');
              tr.innerHTML = `
          <td>${empleado.cedula}</td>
          <td>${empleado.nombres}</td>
          <td class="text-center">
            <span class="${badges[empleado.status][1]}">${badges[empleado.status][0]}</span>
          </td>
        `;
              document.getElementById('tabla_empleados-list').appendChild(tr);
            });
          }
        };

        /**
         * Callback function to handle the error response.
         * @param {Object} response - The response object.
         */
        const onError = (response) => {
          console.log('Error:', response);
          toast_s('error', 'Error: ' + response);
        };

        ajaxRequest.send(onSuccess, onError);
      }

      cargarListaEmpleados();
    </script>
</body>

</html>