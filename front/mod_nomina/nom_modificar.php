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






/**
 * This script handles the POST requests and returns JSON data based on the request parameters.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  if (isset($data['nomina'])) {
    /**
     * If the 'nomina' parameter is set in the request data, it calls the 'getConceptosXnomina' function
     * to retrieve the conceptos (concepts) associated with the given nomina (payroll).
     * The retrieved data is then encoded as JSON and echoed as the response.
     */
    echo json_encode(getConceptosXnomina($data['nomina']));
    exit;
  } elseif (isset($data['grupo_nomina'])) {
    /**
     * If the 'grupo_nomina' parameter is set in the request data, it retrieves the nominas (payrolls)
     * associated with the given grupo_nomina (payroll group) from the database.
     * The retrieved data is then encoded as JSON and echoed as the response.
     */
    $grupo_nomina = $data['grupo_nomina'];
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas` WHERE grupo_nomina = ?");
    $stmt->bind_param('i', $grupo_nomina);
    $stmt->execute();
    $result = $stmt->get_result();
    $nominas = [];
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $nominas[] = $row;
      }
    }
    $stmt->close();
    echo json_encode($nominas);
    exit;
  }
}

/**
 * Retrieves the conceptos (concepts) associated with the given nomina (payroll) from the database.
 *
 * @param string $nomina The name of the nomina (payroll).
 * @return array An array of conceptos (concepts) associated with the given nomina (payroll).
 */
function getConceptosXnomina($nomina)
{
  global $conexion;
  $conceptos = [];
  $stmt = mysqli_prepare($conexion, "SELECT c.id, c.tipo_calculo, ca.nom_concepto, ca.empleados FROM `conceptos_aplicados` AS ca LEFT JOIN conceptos AS c ON ca.concepto_id = c.id WHERE nombre_nomina = ?");
  $stmt->bind_param('s', $nomina);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      if ($row['nom_concepto'] == 'Sueldo Base') {
        // lo unico que cambia es que el id_concepto es 'sueldo_base'
        $conceptos[] = [
          'id' => 'sueldo_base',
          'tipo_calculo' => $row['tipo_calculo'],
          'nom_concepto' => $row['nom_concepto'],
          'empleados' => $row['empleados']
        ];
      } else {
        $conceptos[] = $row;
      }
    }
  }
  $stmt->close();
  return $conceptos;
}


/**
 * Selects records from the 'nominas' table based on the provided 'grupo_nomina' value.
 *
 * @param mysqli $conexion The mysqli connection object.
 * @param int $i The value of 'grupo_nomina' to filter the records.
 * @return string The status of the 'grupo_nomina' based on the number of records found.
 */
$stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas` WHERE grupo_nomina = ?");
$stmt->bind_param('i', $i);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  $statusGrupo = 'conRegistros';
} else {
  $statusGrupo = 'nuevo';
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
  <?php if ($statusGrupo == 'conRegistros') { ?>

    <script>
      let nominasDelGrupo

      async function getConceptosXnomina(nomina) {
        const response = await fetch('', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nomina: nomina
          }),
        });

        if (!response.ok) {
          throw new Error('Error al obtener los conceptos');
        }

        const conceptos = await response.json();
        return conceptos;
      }

      async function getNominas(grupoNomina) {
        const response = await fetch('', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            grupo_nomina: grupoNomina
          }),
        });

        if (!response.ok) {
          throw new Error('Error al obtener las nóminas');
        }

        const nominas = await response.json();

        const nominasConConceptos = [];
        for (const nomina of nominas) {
          const conceptos = await getConceptosXnomina(nomina.nombre);
          console.log(nomina.nombre)
          if (conceptos.length > 0) {
            nominasConConceptos.push([nomina.id, nomina.nombre, conceptos]);
          }
        }

        console.log(nominasConConceptos.length)
        return nominasConConceptos.length > 0 ? {
          status: 'conRegistros',
          data: nominasConConceptos
        } : {
          status: 'nuevo'
        };
      }

      // Ejemplo de uso
      getNominas(<?php echo $i ?>).then((result) => {
        console.log(result)
        nominasDelGrupo = result;
        //  console.log(nominasDelGrupo)
      }).catch((error) => {
        console.error(error);
      });
    </script>

  <?php } ?>

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
                  <div class="mb-3">
                    <h5 class="mb-0">Agregar empleado al grupo</h5>
                    <small class="text-muted">Indique la cédula del empleado que desea agregar al grupo de nominas</small>
                  </div>
                  <form class="mb-3" id="formularioBusqueda">
                    <label class="form-label" for="cedula">Cedula</label>
                    <div class="input-group"><input type="text" class="form-control" placeholder="Numero de cedula" id="cedula" name="cedula"> <button type="submit" class="btn btn-primary"><i class="feather icon-download-cloud"></i> BUSCAR</button></div>
                    <small class="text-danger">* Se excluirán empleados que ya pertenezcan al mismo grupo de nominas.</small>
                  </form>

                  <section id="resultado_busqueda">

                  </section>
                  <section id="resultado_nominas_disponibles">

                  </section>
                  <section id="resultado_reintegro" class="hide">
                    <h5 class="mb-0">Reintegro</h5><span class="text-primary">Indique si es necesario pagar un reintegro al empleado.</span>
                    <div class="mt-3">
                      <div class="mb-3">
                        <label class="form-label" for="reintegro_aplica">¿Aplica reintegro?</label>
                        <select class="form-select" id="reintegro_aplica">
                          <option>Seleccione</option>
                          <option value="1">Si</option>
                          <option value="0">No</option>
                        </select>
                      </div>
                      <section id="section-reintegro_aplica" class="hide">
                        <div class="mb-3">
                          <label class="form-label" for="desde_cuando_pagar">¿Desde cuando se debe pagar?</label>
                          <select class="form-control" id="desde_cuando_pagar">
                            <option>Seleccione</option>
                            <option value="1">Desde la fecha de ingreso</option>
                            <option value="2">Desde fecha especifica</option>
                          </select>
                        </div>
                      </section>
                      <section id="section-fecha_especifica" class="hide">
                        <div class="mb-3">
                          <label class="form-label" for="fecha_especifica">Fecha especifica</label>
                          <input type="date" class="form-control" id="fecha_especifica">
                        </div>
                      </section>

                      <div class="text-end">
                        <button class="btn btn-primary" onclick="finalizarRegistroNuevoEmpleado()">Finalizar</button>
                      </div>
                    </div>

                  </section>
                </div>

                <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                  <p class="mb-0">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-5">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-3">
                <div class="card-header">
                  <h5>Opciones disponibles</h5>
                </div>
                <div class="card-body">
                  <ul class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <li><a class="nav-link active" id="v-listaEmpleados-tab" data-bs-toggle="pill" href="#v-listaEmpleados" role="tab" aria-controls="v-listaEmpleados" aria-selected="false" tabindex="-1">Lista de empleados</a></li>
                    <?php if ($statusGrupo == 'conRegistros') { ?>
                      <li><a class="nav-link" id="v-pills-addEmpleado-tab" data-bs-toggle="pill" href="#v-pills-addEmpleado" role="tab" aria-controls="v-pills-addEmpleado" aria-selected="false" tabindex="-1">Agregar empleado al grupo</a></li>
                    <?php } ?>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-lg-12">

              <div class="card mb-3">
                <div class="card-header">
                  <h5>Frecuencia de pago normal</h5>
                </div>
                <div class="card-body">

                  <?php


                  $stmt = mysqli_prepare($conexion, "SELECT * FROM `frecuencias_por_grupo` WHERE id_grupo = ?");
                  $stmt->bind_param('s', $i);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      echo '<input type="text" class="form-control text-center" disabled value="' . ($row['tipo'] == 'Q' ? 'Quincenal' : 'Semanal') . '">';
                    }
                  } else {
                  ?>

                    <form id="myForm" action="../../back/modulo_nomina/nom_frecuencia_pago.php" method="POST">
                      <input type="hidden" name="id_grupo" value="<?php echo $i; ?>">

                      <div class="mb-3">
                        <label class="form-label" for="frecuencia_pago">Frecuencia de pago normal</label>
                        <select class="form-select" name="frecuencia_pago" id="frecuencia_pago">
                          <option value="">Seleccione</option>
                          <option value="Q">Quincenal</option>
                          <option value="S">Semanal</option>
                        </select>
                      </div>

                      <div class="text-end">
                        <button class="btn btn-primary" type="submit">Establecer</button>
                      </div>
                    </form>

                    <script>
                      document.getElementById('myForm').addEventListener('submit', function(event) {
                        event.preventDefault(); // Evita que el formulario se envíe inmediatamente

                        Swal.fire({
                          title: '¿Estás seguro?',
                          text: "Esta acción establecerá la frecuencia de pago y no podrá ser cambiado.",
                          icon: 'warning',
                          showCancelButton: true,
                          confirmButtonColor: '#3085d6',
                          cancelButtonColor: '#d33',
                          confirmButtonText: 'Sí, continuar',
                          cancelButtonText: 'Cancelar'
                        }).then((result) => {
                          if (result.isConfirmed) {
                            this.submit(); // Envía el formulario si se confirma
                          }
                        });
                      });
                    </script>


                  <?php
                  }
                  $stmt->close();

                  ?>







                </div>
              </div>
            </div>
          </div>




        </div>
      </div>
    </div>
  </div>
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script src="../../src/assets/js/ajax_class.js"></script>
  <?php if ($statusGrupo == 'nuevo') {
    echo '<script src="../../src/assets/js/lista-empleados.js"></script> ';
  } ?>

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
        //loader
        $('#cargando').show();

        const ajaxRequest = new AjaxRequest('application/json', data, url_back);
        const onSuccess = (response) => {
          toast_s('success', 'Registrados con éxito');
          cargarListaEmpleados();
          document.getElementById('tabla_empleados').classList.remove('hide');
          document.getElementById('seleccion_empleados').classList.add('hide');
          $('#cargando').hide();
        };
        const onError = (response) => {
          console.log('Error:', response);
          toast_s('error', 'Error: ' + response);
          $('#cargando').hide();
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




    let empleado_seleccion

    function agregarEmpleado(id_empleado) {

      document.getElementById('formularioBusqueda').classList.add('hide')
      document.getElementById('btn-agregar-empleado').classList.add('hide')
      empleado_seleccion = id_empleado

      console.log(nominasDelGrupo)
      $('#resultado_nominas_disponibles').html('<h5 class="mb-3">Nominas Disponibles</h5><div class="list-group">')
      nominasDelGrupo.data.forEach(nomina => {
        $('#resultado_nominas_disponibles').append(`<label class="list-group-item">
        <input class="form-check-input me-1" type="checkbox" value="${nomina[0]}" id="nomina-${nomina[0]}"> ${nomina[1]}</label>`)
      })
      $('#resultado_nominas_disponibles').append('</div><div class="mt-3 text-end"><button onclick="confirmarNominas()" class="btn  btn-primary">siguiente</button></div>')


    }


    let nominasConfirmadas

    function confirmarNominas() {
      nominasConfirmadas = []
      $('input[type="checkbox"]').each(function() {
        if (this.checked) {
          nominasConfirmadas.push(this.value)
        }
      })
      if (nominasConfirmadas.length === 0) {
        return swal('error', 'Debe seleccionar al menos una nomina')
      } else {

        seleccionarConceptos()
      }
    }

    function seleccionarConceptos() {
      $('#resultado_nominas_disponibles').html('<h5 class="mb-0">Conceptos Disponibles</h5><span class="text-primary">Los conceptos tipo "Formulados" no se muestran en la lista y se aplican automáticamente si se cumplen las condiciones.</span><div class="list-group mt-3">')
      nominasDelGrupo.data.forEach(nomina => {
        if (nominasConfirmadas.includes(String(nomina[0]))) {
          $('#resultado_nominas_disponibles').append(`
          <label class="list-group-item list-group-item-dark">
          <input class="form-check-input  me-1" onchange="checkAll(this.checked, '-c${nomina[0]}')" type="checkbox" value="${nomina[0]}" id="nomina-${nomina[0]}">
          ${nomina[1]}
          <span class="identificadorLineaDerecha">Nomina</span></label>`)



          nomina[2].forEach(concepto => {
            if (concepto.tipo_calculo != 6) {

              $('#resultado_nominas_disponibles').append(`<label class="list-group-item">
              <input class="form-check-input me-1 itemCheckbox-c${nomina[0]}" type="checkbox" value="${concepto.id}" id="concepto-${concepto.id}"> ${concepto.nom_concepto}</label>`)
            }
          })
        }
      })
      $('#resultado_nominas_disponibles').append('</div><div class="mt-3 text-end"><button onclick="guardarEmpleado()" class="btn  btn-primary">Finalizar</button></div>')
    }


    /**
     * Function to clear the search results and available payrolls.
     * Clears the inner HTML of 'resultado_busqueda' and 'resultado_nominas_disponibles' elements.
     */
    function agregarEmpleadoCancelar() {
      document.getElementById('formularioBusqueda').classList.remove('hide')
      document.getElementById('resultado_reintegro').classList.add('hide')
      document.getElementById('resultado_busqueda').innerHTML = ''
      document.getElementById('resultado_nominas_disponibles').innerHTML = ''

      if (document.getElementById('btn-agregar-empleado')) {

        document.getElementById('btn-agregar-empleado').classList.remove('hide')
      }
    }

    let nominasXconceptos = []

    function guardarEmpleado() {
      nominasXconceptos = []
      // recorre las nominas seleccionadas
      nominasConfirmadas.forEach(nomina => {
        let conceptos = []
        // recorre los conceptos seleccionados
        $(`.itemCheckbox-c${nomina}`).each(function() {
          if (this.checked) {
            conceptos.push(this.value)
          }
        })
        nominasXconceptos.push({
          nomina: nomina,
          conceptos: conceptos
        })
      })
      let status = true


      nominasXconceptos.forEach(element => {
        if (element['conceptos'].length === 0) {
          status = false
          return swal('error', 'Debe seleccionar al menos un concepto por nomina')
        }
      });
      if (status) {
        document.getElementById('resultado_nominas_disponibles').classList.add('hide')
        document.getElementById('resultado_reintegro').classList.remove('hide')

      }
    }




    /**
     * Checks or unchecks all checkboxes with the class 'itemCheckbox'.
     *
     * @param {boolean} status - The status to set for all checkboxes.
     */
    /*
        function checkAll(status, subfijo) {
          let itemCheckboxes = document.querySelectorAll('.itemCheckbox' + subfijo);
          itemCheckboxes.forEach(checkbox => {
            checkbox.checked = status;
          });
        }*/



    /**
     * 
     * This script handles the form submission event when the DOM content is loaded.
     * It sends a form using Ajax to a specified URL and displays the response in the HTML.
     * 
     * @event DOMContentLoaded
     * @param {object} e - The event object
     */
    var fecha_ingreso
    var id_empleado
    document.addEventListener("DOMContentLoaded", () => {
      const form1 = document.getElementById("formularioBusqueda");
      const sender = new AjaxFormSender();

      form1.addEventListener("submit", (e) => {
        e.preventDefault();
        const additionalData1 = {
          grupoActual: '<?php echo $i ?>'
        };
        sender.sendForm(form1, "../../back/modulo_nomina/nom_modificar_agregar_empleado_buscar.php", additionalData1, (err, response) => {
          if (err) {
            console.error(err);
          } else {
            if (response.status == 'error') {
              swal('error', response.mensaje)
            } else {
              fecha_ingreso = response.datos[2]
              id_empleado = response.datos[1]
              document.getElementById('resultado_busqueda').innerHTML = `
              <div class="alert alert-info" role="alert">
                <h4 class="alert-heading">${response.datos[0]}</h4>
                <p>Fecha de ingreso: ${response.datos[2]}</p>
                <hr>
                <p class="mb-3">¿Desea agregar a ${response.datos[0]} al grupo de nominas?</p>
                <div class="d-flex">
                <button class="btn btn-primary me-1" id="btn-agregar-empleado" onclick="agregarEmpleado(${response.datos[1]})">Agregar</button>
                <button class="btn btn-secondary" onclick="agregarEmpleadoCancelar()">Cancelar</button>
                </div>
              </div>
              `;

            }
          }
        });
      });
    });


    /**
     * Event listener for the 'reintegro_aplica' element.
     * Shows or hides the 'section-reintegro_aplica' based on the selected value.
     */
    document.getElementById('reintegro_aplica').addEventListener('change', function() {
      if (this.value == '1') {
        document.getElementById('section-reintegro_aplica').classList.remove('hide');
      } else {
        document.getElementById('section-reintegro_aplica').classList.add('hide');
      }
    });

    /**
     * Event listener for the 'desde_cuando_pagar' element.
     * Shows or hides the 'section-fecha_especifica' based on the selected value.
     */
    document.getElementById('desde_cuando_pagar').addEventListener('change', function() {
      if (this.value == '2') {
        document.getElementById('section-fecha_especifica').classList.remove('hide');
      } else {
        document.getElementById('section-fecha_especifica').classList.add('hide');
      }
    });

    /**
     * This function is used to finalize the registration of a new employee.
     * It retrieves values from various input fields and performs validation checks.
     * If all checks pass, it sends an AJAX request to add the employee to the database.
     *
     * @return void
     */
    function finalizarRegistroNuevoEmpleado() {
      // Retrieve values from input fields
      let fecha_especifica = document.getElementById('fecha_especifica').value
      let desde_cuando_pagar = document.getElementById('desde_cuando_pagar').value
      let reintegro_aplica = document.getElementById('reintegro_aplica').value
      let info_reintegro = {}

      // Perform validation checks
      if (reintegro_aplica == '') {
        return swal('error', 'Debe seleccionar si aplica reintegro')
      }
      if (reintegro_aplica == '1' && desde_cuando_pagar == '2' && fecha_especifica == '') {
        return swal('error', 'Debe seleccionar una fecha específica')
      }
      if (reintegro_aplica == '1' && desde_cuando_pagar == '2' && new Date(fecha_especifica) < new Date(fecha_ingreso)) {
        return swal('error', 'La fecha específica debe ser mayor o igual a la fecha de ingreso')
      }

      // Prepare info_reintegro object based on reintegro_aplica value
      if (reintegro_aplica == '1') {
        info_reintegro['reintegro'] = {
          'reintegro': '1',
          'datos': {
            'pagarDesde': desde_cuando_pagar,
            'fechaIngreso': fecha_ingreso,
            'fechaEspecifica': fecha_especifica
          }
        }
      } else {
        info_reintegro['reintegro'] = {
          'reintegro': '0',
          'datos': {
            'pagarDesde': '',
            'fechaEspecifica': ''
          }
        }
      }

      // Prepare data object for AJAX request
      let data = {
        accion: 'agregar_empleado',
        empleado: id_empleado,
        grupo_nomina: '<?php echo $i ?>',
        nominas: nominasXconceptos,
        info_reintegro: info_reintegro
      }

      console.log(data);

      // Send AJAX request to add the employee
      // const ajaxRequest = new AjaxRequest('application/json', data, '../../back/modulo_nomina/nom_modificar_agregar_empleado.php');
      // console.log(ajaxRequest);
      // const onSuccess = (response) => {
      //   console.log(response)
      //   if (response.status == 'success') {
      //     toast_s('success', 'Empleado agregado con éxito')
      //     agregarEmpleadoCancelar()
      //   } else {
      //     toast_s('error', response.mensaje)
      //   }
      // };
      // const onError = (response) => {
      //   console.log('Error:', response.mensaje);
      //   toast_s('error', 'Error: ' + response.mensaje);
      // };
      // ajaxRequest.send(onSuccess, onError);

      // const fetchRequest = fetch('../../back/modulo_nomina/nom_modificar_agregar_empleado.php', {
      //   method: 'POST',
      //   body: JSON.stringify(data)
      // })

      // fetchRequest.then(res=>res.json())
      // .then(json=>{
      //   console.log(json);
      //   if (json.status == 'success') {
      //     toast_s('success', 'Empleado agregado con éxito')
      //     console.log(data.info_reintegro.reintegro.reintegro);
      //     if(data.info_reintegro.reintegro.reintegro === 1 || data.info_reintegro.reintegro.reintegro === '1'){
      //       let reintegroRequest = fetch(`../../back/modulo_nomina/nom_reintegro_pdf.php?id_empleado=${data.empleado}`).then(res=> res.blob()).then(blob=> {
      //         const url = window.URL.createObjectURL(blob);
      //           const a = document.createElement('a');
      //           a.href = url;
      //           a.download = 'archivo.bin';
      //           a.click();
      //           window.URL.revokeObjectURL(url);
      //       toast_s('success', 'Reintegro generado')

      //       }).catch(error => {
      //             console.error('Error al descargar el archivo:', error);
      //         });
      //     }
      //     agregarEmpleadoCancelar()

      //   } else {
      //     toast_s('error', json.mensaje)
      //   }
      // }).catch(error=>{
      //   console.log('Error:', error);
      //   toast_s('error', 'Error: ' + error);
      // })


      const descargarArchivo = async (url) => {
        const response = await fetch(url);
        if (!response.ok) {
          throw new Error('No se pudo descargar el archivo');
        }

        const blob = await response.blob();
        const urlBlob = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = urlBlob;
        a.download = `Reintegro_${data.empleado}.rar`;
        a.click();
        window.URL.revokeObjectURL(urlBlob);
      };

      const procesarEmpleado = async (data) => {
        try {
          const res = await fetch('../../back/modulo_nomina/nom_modificar_agregar_empleado.php', {
            method: 'POST',
            body: JSON.stringify(data)
          });



          let json = await res.json();

          if (json.status === 'success') {
            toast_s('success', 'Empleado agregado con éxito');

            if (data.info_reintegro.reintegro.reintegro === 1 || data.info_reintegro.reintegro.reintegro === '1') {
              await descargarArchivo(`../../back/modulo_nomina/nom_reintegro_pdf.php?id_empleado=${data.empleado}`);
              toast_s('success', 'Reintegro generado');
            }

            agregarEmpleadoCancelar();
          } else {
            toast_s('error', json.mensaje);
          }
        } catch (error) {
          console.error('Error en el proceso:', error);
          toast_s('error', 'Error: ' + error.message);
        }
      };

      // Llamar a la función procesarEmpleado con los datos necesarios
      procesarEmpleado(data);

    }
  </script>
</body>

</html>