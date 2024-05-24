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
  <title>Formulación de nómina</title>
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
                <h5 class="mb-0">Formulación de nómina <br><small class="text-muted"><?php echo $codigo . ' ' . $nombre ?></small> </h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-12">
          <div class="card">
            <div class="card-body p-3">
              <ul class="nav nav-pills nav-justified">
                <span id="link_basico" class="nav-item nav-link item-wizard"><i class="ph-duotone ph-user-circle"></i> <span class="d-none d-sm-inline">Basico</span></span>
                <span id="link_empleados" class="nav-item nav-link item-wizard active"><i class="ph-duotone ph-graduation-cap"></i> <span class="d-none d-sm-inline">Empleados</span></span>
                <span id="link_conceptos" class="nav-item nav-link item-wizard"><i class="ph-duotone ph-map-pin"></i> <span class="d-none d-sm-inline">Conceptos</span></span>
                <span id="link_resmune" class="nav-item nav-link item-wizard"><i class="ph-duotone ph-check-circle"></i> <span class="d-none d-sm-inline">Resumen general</span></span>
              </ul>
            </div>
          </div>
          <div class="card">
            <div class="card-body">

              <div class="progress mb-3">
                <div class="progress-bar bg-success " id="progressbar" style="width: 25%;" aria-valuemin="0" aria-valuemax="100"></div>
              </div>


              <div class="tab-content">

                <section class="tab-pane" id="tab_basico">
                  <div id="contactForm" method="post" action="#">
                    <div class="text-center">
                      <h3 class="mb-2">Comencemos con la información básica.</h3>
                      <small class="text-muted">
                        Por favor, ingrese la información básica de la nómina.
                      </small>
                    </div>
                    <div class="row mt-4">

                      <div class="col">
                        <div class="row">
                          <div class="col-sm-6">
                            <div class="mb-3"><label class="form-label">Nombre de la nomina</label>
                              <input type="text" class="form-control" id="nombre_nomina" placeholder="Nombre de la nomina">
                            </div>
                          </div>
                          <div class="col-sm-6">
                            <div class="mb-3">
                              <label class="form-label">Frecuencia de pago</label>
                              <select class="form-control" id="frecuencia_pago">
                                <option value="">Seleccione</option>
                                <option value="1">Semanal</option>
                                <option value="2">Quincenal</option>
                                <option value="3">Mensual</option>
                                <option value="4">Una vez al mes</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-sm-12">
                            <div class="mb-3">
                              <label class="form-label">Tipo de nomina</label>
                              <select class="form-control" id="tipo_nomina">
                                <option value="">Seleccione</option>
                                <option value="1">Normal</option>
                                <option value="2">Especial</option>
                              </select>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex w-100 mt-3">
                    <div class="d-flex m-a">
                      <div class="me-2"><button class="btn btn-secondary disabled">Regresar</button></div>
                      <div class="next"><button class="btn btn-secondary mt-3 mt-md-0" onclick="nextStep('1')">Siguiente</button></div>
                    </div>
                  </div>

                </section>
                <section class="tab-pane show active" id="tab_empleados">
                  <div id="educationForm" method="post" action="#">
                    <div class="text-center">
                      <h3 class="mb-2">Continua agregando los empleados</h3>
                      <small class="text-muted">
                        Por favor, ingrese los empleados de la nómina.
                      </small>
                    </div>
                    <div class="row">
                      <div class="col-md-12">

                        <div class="mb-3">
                          <label class="form-label" for="filtro_empleados">¿Como quieres seleccionar a tus empleados?</label>
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
                              <option value="<?php echo $n->id; ?>">&nbsp;<?php echo $n->nombre; ?></option>
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

                          <tfoot>
                            <tr>
                              <td></td>
                              <td class="fw-bold" id="resumen_epleados_seleccionados"></td>
                              <td class="w-auto text-center"><button class="btn btn-sm btn-primary" id="guardar_empleados_nomina">Guardar </button></td>
                            </tr>
                          </tfoot>
                        </table>


                      </section>

                    </div>
                    <div class="d-flex w-100 mt-3">
                      <div class="d-flex m-a">
                        <div class="me-2"><button class="previous btn btn-secondary" onclick="beforeStep('1')">Regresar</button>
                        </div>
                        <div class="next"><button class="btn btn-secondary mt-3 mt-md-0" onclick="nextStep('2')">Siguiente</button></div>
                      </div>
                    </div>
                  </div>
                </section>
                <section class="tab-pane" id="tab_conceptos">
                  <div id="jobForm" method="post" action="#">
                    <div class="text-center">
                      <h3 class="mb-2">Es hora de agregar los conceptos</h3>
                      <small class="text-muted">
                        Por favor, ingrese los conceptos de la nómina.
                      </small>
                    </div>
                    <div class="row mt-4">


                      <section class="hidex" id="nuevo_concepto-sec">
                        <div class="mb-3">
                          <label class="form-label" for="concepto_aplicar">Seleccione el concepto de desea agregar</label>

                          <div class="input-group">

                            <select class="form-control" id="concepto_aplicar">
                              <option value="">Seleccione</option>
                              <option value="sueldo_base">Sueldo</option>
                            </select>

                            <span class="input-group-text" onclick="getData('conceptos')"><i class='bx bx-refresh reload-icon pointer'></i></span>
                          </div>
                        </div>


                        <div class="mb-3" id="section_fechas">
                          <label class="form-label" for="fechas_aplicar">Cuando se debe aplicar el conceto?</label>
                          <select multiple="" class="form-select" id="fechas_aplicar">
                            <option value="1">Primera semana</option>
                          </select>
                          <small>Mantén presionada la tecla shift o presiona ctrl para selección múltiple.</small>
                        </div>



                        <section id="sueldo-options" class="hide">
                          <div class="mb-3">
                            <label class="form-label" for="tabulador">Seleccione el tabulador</label>
                            <select class="form-control" id="tabulador">
                              <option value="">Seleccione</option>
                            </select>
                          </div>
                        </section>


                        <section id="n_conceptos_porcentajes" class="hide mb-3">

                          <label class="form-label" for="concepto_aplicados">Conceptos ya aplicados</label>
                          <select multiple="" class="form-select" id="concepto_aplicados">
                            <option value="VALOR">FUNERARIA</option>
                          </select>
                          <small>Mantén presionada la tecla shift o presiona ctrl para selección múltiple.</small>


                        </section>

                        <section id="aplicacion_conceptos-options" class="hide">
                          <div class="mb-3">
                            <label class="form-label" for="tipo_aplicacion_concept">¿Como desea aplicar el concepto?</label>
                            <select class="form-control" id="tipo_aplicacion_concept">
                              <option value="">Seleccione</option>
                              <option value="1">Por sus características (Formulación)</option>
                              <option value="2">Enlistar todos los empleados de la nomina</option>
                            </select>
                          </div>

                          <div id="formulacion-conceptos" class="hide">
                       
                        <!-- HERRAMIENTA PARA FILTRAR SEGUN FORMULA-->
                        <div class="row">

                          <div class="col-lg-6">
                            <div class="mb-3"><label class="form-label">Formulación</label>
                              <div class="input-group mb-3">
                                <textarea class="form-control condicion" rows="1" id="t_area-2"></textarea>
                                <button class="btn btn-primary" onclick="validarFormula('t_area-2', 'emp_pre_seleccionados-list')" type="button">Obtener</button>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">

                            <div class="mb-3">
                              <label class="form-label" for="campo_condiciona">Condicionantes</label>
                              <select name="campo_condiciona" onchange="setCondicionante(this.value, 'result-em_nomina')" id="campo_condiciona_epNomina" class="form-control">
                                <option value="">Seleccione</option>
                                <option value="cod_cargo">Código de cargo</option>
                                <option value="discapacidades">Discapacidades</option>
                                <option value="instruccion_academica">Instrucción académica</option>
                                <option value="hijos">Hijos</option>
                                <option value="antiguedad">Antigüedad (desde la fecha de ingreso)</option>
                                <option value="antiguedad_total">Antigüedad (Sumando años anteriores)</option>

                              </select>
                            </div>
                            <ol class="list-group list-group-numbered" id="result-em_nomina">
                            </ol>
                          </div>
                        </div>



                          </div>
                          <div id="tabla_empleados-conceptos" class="hide">



                            <table class="table table-striped table-hover">
                              <thead>
                                <tr>
                                  <th class="w-40">Cedula</th>
                                  <th class="w-40">Nombre</th>
                                  <th class="w-auto text-center"><input type="checkbox" id="selectAllC" onchange="checkAll(this.checked, '_C')" class="form-check-input" /></th>
                                </tr>
                              </thead>
                              <tbody id="emp_pre_seleccionados-list">
                              </tbody>
                            </table>




                          </div>
                        </section>




                        <div class="text-end mt-3">
                          <button class="btn btn-primary" id="guardar_concepto">Guardar concepto</button>
                        </div>

                      </section>

                      <div class="col-lg-12 mh-60 hide" id="conceptos_aplicados-list">
                        <table class="table table-striped table-hover">
                          <thead>
                            <tr>
                              <th class="w-40">Nombre del concepto</th>
                              <th class="w-40">Empleados</th>
                              <th class="w-auto text-center"><button class="btn btn-sm btn-primary" id="btn_agg_concepto"><i class='bx bx-folder-plus'> Agregar concepto</i> </button></th>
                            </tr>
                          </thead>
                          <tbody>

                          </tbody>
                        </table>
                      </div>




















                    </div>
                    <div class="d-flex w-100 mt-3">
                      <div class="d-flex m-a">
                        <div class=" me-2"><button class="previous btn btn-secondary" onclick="beforeStep('2')">Regresar</button></div>
                        <div class="next"><button class="previous btn btn-secondary mt-3 mt-md-0" onclick="nextStep('3')">Siguiente</button></div>
                      </div>
                    </div>
                  </div>
                </section>
                <section class="tab-pane" id="tab_resmune">
                  <div class="row d-flex justify-content-center">
                    <div class="col-lg-6">
                      <div class="text-center"><i class="ph-duotone ph-gift f-50 text-danger"></i>
                        <h3 class="mt-4 mb-3">Verifica que todo este correcto</h3>

                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsam cupiditate possimus deserunt esse necessitatibus sint aspernatur laboriosam dolore consequatur perspiciatis nulla fuga beatae assumenda voluptatum, est libero saepe autem fugit.
                      </div>
                    </div>
                  </div>
                  <div class="d-flex w-100 mt-3">
                    <div class="d-flex m-a">
                      <div class="me-2"><button class="previous btn btn-secondary" onclick="beforeStep('3')">Regresar</button=>
                      </div>
                      <div class="next"><button onclick="guardarNomina()" class="btn btn-secondary mt-3 mt-md-0">Siguiente</button></div>
                    </div>
                  </div>
                </section>

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
    const url_back = '../../back/modulo_nomina/nom_formulacion_back';
    let textarea = 't_area-1';


    /**
     * Adds an event listener to the 'filtro_empleados' element and performs different actions based on the selected value.
     * @param {Event} event - The event object.
     */

     function seleccion_empleados(value, result_list){

      const filtro = value;
      const empleadosList = document.getElementById('empleados-list');
      const herramientaFormulacion = $('#herramienta-formulacion');
      const otrasNominasList = $('#otras_nominas-list');

      empleadosList.innerHTML = '';

      switch (filtro) {
        case '1':
          aplicar_filtro(1, 'null', result_list);
          herramientaFormulacion.addClass('hide');
          otrasNominasList.addClass('hide');
          break;
        case '2':
          herramientaFormulacion.removeClass('hide');
          otrasNominasList.addClass('hide');
          break;
        case '3':
          otrasNominasList.removeClass('hide');
          herramientaFormulacion.addClass('hide');
          break;
      }
  }



    /**
     * Adds an event listener to the 'otra_nominas' element and applies a filter when the value changes.
     * 
     * @param {Event} event - The event object.
     */
    document.getElementById('otra_nominas').addEventListener('change', function(event) {
      let id = this.value;
      if (id != '') {
        aplicar_filtro(3, id);
      }
    });


    /**
     * Adds an event listener to the 'btn-obtener' button and performs a specific action when clicked.
     * 
     * @param {Event} event - The event object.
     * @returns {void}
     */
function validarFormula(area, result_list) {
  let condicion = $('#' + area).val();
  if (condicion == '') {
    toast_s('error', 'Debe indicar una condición');
    return;
  } else {
    if (result_list == 'empleados-list') { 
      aplicar_filtro(2, condicion, result_list);
    } else {
      let accion = 'todos'; // Definir 'accion' aquí si es necesario
      tbl_emp_seleccionados(condicion, accion); // Pasar 'accion' como parámetro
    }
  }
}



    /**
     * Applies a filter to retrieve employees based on the specified type and filter.
     *
     * @param {string} tipo - The type of filter to apply.
     * @param {string} filtro - The filter to apply.
     */

    let empleadosFiltro = []

    function aplicar_filtro(tipo, filtro, result_list) {
      empleadosFiltro = []
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          tipo_filtro: tipo,
          filtro: filtro.trim(),
          tabla_empleados: true
        },
        success: function(response) {
          let empleados = JSON.parse(response);
          let tabla = '';

          empleados.forEach(e => {
            empleadosFiltro[e.id] = [e.id, e.nacionalidad, e.cedula, e.cod_empleado, e.nombres, e.fecha_ingreso, e.anios_actuales, e.otros_anios, e.anios_totales, e.status, e.observacion, e.cod_cargo, e.hijos, e.instruccion_academica, e.discapacidades, e.id_dependencia];
            tabla += '<tr>';
            tabla += '<td>' + e.cedula + '</td>';
            tabla += '<td>' + e.nombres + '</td>';
            tabla += '<td class="text-center"><input class="form-check-input itemCheckbox" type="checkbox" value="' + e.id + '"></td>';
            tabla += '</tr>';
          });

          document.getElementById(result_list).innerHTML = tabla;
        }
      });
    }

    /**
     * Checks or unchecks all checkboxes with the class 'itemCheckbox'.
     *
     * @param {boolean} status - The status to set for all checkboxes.
     */
    function checkAll(status, subfijo) {
      let itemCheckboxes = document.querySelectorAll('.itemCheckbox' + subfijo);
      itemCheckboxes.forEach(checkbox => {
        checkbox.checked = status;
      });
    }

    let empleadosSeleccionados = [] // Todos los emleados seleccionados para la nomina 

    /**
     * This function is responsible for saving the selected employees for the payroll.
     * It retrieves all the selected checkboxes and adds the corresponding employees to the 'empleadosSeleccionados' array.
     */
    function guardar_empleados_nomina() {
      empleadosSeleccionados = []
      let itemCheckboxes = document.querySelectorAll('.itemCheckbox');
      itemCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
          empleadosSeleccionados.push(empleadosFiltro[checkbox.value]);
        }
      });
      document.getElementById('resumen_epleados_seleccionados').innerText = 'Empleados seleccionados: ' + empleadosSeleccionados.length;
    }

    document.getElementById('guardar_empleados_nomina').addEventListener('click', guardar_empleados_nomina);


    /**
     * Function to navigate to the next step based on the provided step value.
     *
     * @param {string} step - The current step value.
     */
    function nextStep(step) {
      if (step == '1') {
        const inputs = ['nombre_nomina', 'frecuencia_pago', 'tipo_nomina'];
        if (inputs.some(id => !document.getElementById(id).value)) {
          toast_s('error', 'Debe completar todos los campos');
          return;
        }
        toggleStep('basico', 'empleados');
        document.getElementById('progressbar').style.width = '50%';

      } else if (step == '2') {
        if (empleadosSeleccionados.length === 0) {
          toast_s('error', 'Debe seleccionar al menos un empleado');
          return;
        }
        toggleStep('empleados', 'conceptos');
        document.getElementById('progressbar').style.width = '75%';
      }
    }
    /**
     * Function to toggle between steps by hiding and showing the corresponding elements.
     *
     * @param {string} hideId - The ID of the element to hide.
     * @param {string} showId - The ID of the element to show.
     */
    function toggleStep(hideId, showId) {
      document.getElementById('tab_' + hideId).classList.remove('show', 'active');
      document.getElementById('tab_' + showId).classList.add('show', 'active');
      document.getElementById('link_' + hideId).classList.remove('active');
      document.getElementById('link_' + showId).classList.add('active');
    }

    /**
     * This function is called before transitioning to a new step in a wizard.
     * It updates the active and show classes of the wizard items and tabs based on the given step.
     *
     * @param {string} step - The step to transition to.
     */
    function beforeStep(step) {
      const stepMap = {
        '1': {
          link: 'link_basico',
          tab: 'tab_basico',
          progressbar: 25
        },
        '2': {
          link: 'link_empleados',
          tab: 'tab_empleados',
          progressbar: 50
        },
        '3': {
          link: 'link_conceptos',
          tab: 'tab_conceptos',
          progressbar: 75
        },
        '4': {
          link: 'link_resmune',
          tab: 'tab_resmune',
          progressbar: 100
        }
      };

      // Eliminar las clases 'active' y 'show' de todos los elementos
      document.querySelectorAll('.item-wizard, .tab-pane').forEach(item => {
        item.classList.remove('active', 'show');
      });

      // Establecer las clases 'active' y 'show' según el paso
      if (stepMap[step]) {
        const {
          link,
          tab,
          progressbar
        } = stepMap[step];
        document.getElementById(link).classList.add('active');
        document.getElementById(tab).classList.add('active', 'show');
        document.getElementById('progressbar').style.width = progressbar + '%';
      }
    }



    /**
     * Adds an event listener to the 'btn_agg_concepto' element.
     * When the button is clicked, it hides the 'conceptos_aplicados-list' element
     * and shows the 'nuevo_concepto-sec' element.
     */
    document.getElementById('btn_agg_concepto').addEventListener('click', function() {
      $('#conceptos_aplicados-list').addClass('hide');
      $('#nuevo_concepto-sec').removeClass('hide');
    });





    function loadData(value) {
      return new Promise((resolve, reject) => {
        $.ajax({
          url: url_back,
          type: 'POST',
          data: {
            loadData: value
          },
          success: function(response) {
            resolve(JSON.parse(response));
          },
          error: function(xhr, status, error) {
            reject(error);
          }
        });
      });
    }



    var conceptos = []
    let concetos_formulacion = []


    /**
     * Fetches data based on the provided value and populates the dropdown lists accordingly.
     *
     * @param {string} value - The value indicating which dropdown list to populate.
     * @returns {Promise<void>} - A promise that resolves once the data is loaded and the dropdown lists are populated.
     */
    async function getData(value) {
      try {
        const data = await loadData(value);

        if (value == 'tabulador') {
          data.forEach(d => {
            $('#tabulador').append(`<option value="${d.id}">${d.nombre}</option>`);
          });
        } else if (value == 'conceptos') {
          let data1 = data.data1;
          let data2 = data.data2;
          conceptos = []
          conceptos_formulacion = []

          data1.forEach(d => {
            conceptos[d.id] = d;
          });
          data2.forEach(d => {
            conceptos_formulacion[d.id] = d;
          });

          $('#concepto_aplicar').html('<option value="">Seleccione</option> <option value="sueldo_base">Sueldo</option>');
          data1.forEach(d => {
            $('#concepto_aplicar').append(`<option value="${d.id}">${d.nom_concepto}</option>`);
          });
        }
      } catch (error) {
        console.error('Error loading data:', error);
      }
    }


    getData('tabulador')
    getData('conceptos')

    /**
     * Handles the logic for determining the type of concept.
     * 
     * This function is responsible for showing or hiding certain options based on the selected concept type.
     * If the selected concept type is 'sueldo_base', it shows the 'aplicacion_conceptos-options' and 'sueldo-options'.
     * If the selected concept type is not 'sueldo_base', it hides the 'aplicacion_conceptos-options' if the concept's 'tipo_calculo' is '6'.
     * 
     * @returns void
     */
    function tipoConcepto() {
      const sueldoOptions = $('#sueldo-options');
      const aplicacionConceptosOptions = $('#aplicacion_conceptos-options');
      const n_conceptos_porcentajes = $('#n_conceptos_porcentajes');
      const tipoCalculo = (this.value == 'sueldo_base' ? null : conceptos[this.value]['tipo_calculo']);

      sueldoOptions.addClass('hide');

      if (this.value == 'sueldo_base') {
        aplicacionConceptosOptions.removeClass('hide');
        sueldoOptions.removeClass('hide');
      } else if (tipoCalculo == '5') {
        n_conceptos_porcentajes.toggleClass('hide', false);
      } else {
        n_conceptos_porcentajes.toggleClass('hide', true);
        aplicacionConceptosOptions.toggleClass('hide', tipoCalculo == '6');
      }
    }
    document.getElementById('concepto_aplicar').addEventListener('change', tipoConcepto);


    /**
     * Handles the change event of the select element for tipo de aplicación.
     * Shows or hides certain elements based on the selected value.
     */
 function tipoAplicacion() {
  $('#formulacion-conceptos').addClass('hide');
  $('#tabla_empleados-conceptos').addClass('hide');

  if (this.value == '1') {
    $('#formulacion-conceptos').removeClass('hide');
  } else if (this.value == '2') {
    // Aquí pasamos el valor de 'condicion' como un parámetro adicional
    tbl_emp_seleccionados('todos', condicion);
  }
}

    document.getElementById('tipo_aplicacion_concept').addEventListener('change', tipoAplicacion);

    function tbl_emp_seleccionados(condicion, accion) {
      // TODO: aca se deben aplicar los filtros



      if (accion == 'todos') {
        $('#tabla_empleados-conceptos').removeClass('hide');
        let tabla = '';
        empleadosSeleccionados.forEach(e => {
          console.log(e)
          tabla += '<tr>';
          tabla += '<td>' + e[1] + '</td>';
          tabla += '<td>' + e[3] + '</td>';
          tabla += '<td class="text-center"><input class="form-check-input itemCheckbox_C" type="checkbox" value="' + e[0] + '"></td>';
          tabla += '</tr>';
        });
        document.getElementById('emp_pre_seleccionados-list').innerHTML = tabla;
      }else{

        // saca el index 0 del arreglo 'empleadosSeleccionados'
        let seleccionados_id = []
        empleadosSeleccionados.forEach(e => {
          seleccionados_id.push(e[0])
        });
    

          $.ajax({
          url: url_back,
          type: 'POST',
          data: {
            condicion: condicion,
            ids: seleccionados_id,
            tabla_seleccionados: true
          },
          success: function(response) {
            let empleados = JSON.parse(response);
            let tabla = '';

            empleados.forEach(e => {
              empleadosFiltro[e.id] = [e.id, e.nacionalidad, e.cedula, e.cod_empleado, e.nombres, e.fecha_ingreso, e.anios_actuales, e.otros_anios, e.anios_totales, e.status, e.observacion, e.cod_cargo, e.hijos, e.instruccion_academica, e.discapacidades, e.id_dependencia];
              tabla += '<tr>';
              tabla += '<td>' + e.cedula + '</td>';
              tabla += '<td>' + e.nombres + '</td>';
              tabla += '<td class="text-center"><input class="form-check-input itemCheckbox" type="checkbox" value="' + e.id + '"></td>';
              tabla += '</tr>';
            });

            document.getElementById(result_list).innerHTML = tabla;
          }
        });
      }
    }


    let conceptosAplicados = {}






    function guardar_concepto() {
      let concepto = $('#concepto_aplicar').val();
      let fechas_aplicar = $('#fechas_aplicar').val();
      let concepto_aplicados = $('#concepto_aplicados').val();
      const tipoCalculo = this.value == 'sueldo_base' ? null : conceptos[this.value]['tipo_calculo'];
      let tabulador = null;
      let tipo_aplicacion = null;

      if (concepto == '' || fechas_aplicar == '') {
        toast_s('error', 'Debe rellenar todos los campos');
        return;
      } // SIEMPRE SE VALIDAN

      if (concepto != 6 && tipo_aplicacion == '') {
        toast_s('error', 'Debe seleccionar un tipo de aplicación');
        return;
      } // VALIDAN EN LOS CASOS NO FORMULADOS

      if (tipoCalculo == 5 && concepto_aplicados == '') {
        toast_s('error', 'Debe seleccionar al menos un concepto al cual aplicar el porcentaje');
        return;
      } // SE VALIDA QUE SE HAYA SELECCIONADO ALGUN CONCEPTO -- CASO DE PORCENTAJE APLICADOS A N CONCEPTOS 

      if (tipo_calculo == 'sueldo_base' && tabulador == '') {
        toast_s('error', 'Debe seleccionar el tabulador');
        return;
      } // SI ES EL SUELDO BASE, SE VALIDA QUE SE HAYA SELECCIONADO EL TABULADOR



      // asignar a empleados itemCheckbox_C
    }



    document.getElementById('guardar_concepto').addEventListener('click', guardar_concepto);



    /*

          conceptosAplicados[concepto] = [concepto, fechas_aplicar, tabulador, tipo_aplicacion]

          console.log(conceptosAplicados)
    */



    /**
     * Sets the frequency of payment options based on the selected value.
     * @returns {void}
     */
    function setFrecueciaPago() {
      const opciones = {
        '1': `
          <option value="1">Primera semana</option>
          <option value="2">Segunda semana</option>
          <option value="3">Tercera semana</option>
          <option value="4">Cuarta semana</option>
        `,
        '2': `
          <option value="1">Primera quincena</option>
          <option value="2">Segunda quincena</option>
        `
      };

      const value = this.value;
      const fechasAplicar = $('#fechas_aplicar');
      const sectionFechas = $('#section_fechas');

      if (opciones[value]) {
        fechasAplicar.html(opciones[value]);
        sectionFechas.removeClass('hide');
      } else {
        sectionFechas.addClass('hide');
      }
    }
    document.getElementById('frecuencia_pago').addEventListener('change', setFrecueciaPago);


    /*




    Mostrar tabla todos los concepto con un filtro por tipo
    Al aplicar, si corresponde, mostra el tipo de aplicacion
    Antes de aplicar se contabilizaran el total de empleados a los que se aplicaran
    Seleccionar en que fecha se desea pagar el concepto


    Resumen de nomina tal cual

    */
  </script>


</body>

</html>