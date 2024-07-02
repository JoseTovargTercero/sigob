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
                <span id="link_basico" class="nav-item nav-link item-wizard active"><i class="ph-duotone ph-user-circle"></i> <span class="d-none d-sm-inline">Basico</span></span>
                <span id="link_empleados" class="nav-item nav-link item-wizard"><i class="ph-duotone ph-graduation-cap"></i> <span class="d-none d-sm-inline">Empleados</span></span>
                <span id="link_conceptos" class="nav-item nav-link item-wizard"><i class="ph-duotone ph-map-pin"></i>
                  <span class="d-none d-sm-inline">Conceptos</span></span>
                <span id="link_resumen" class="nav-item nav-link item-wizard"><i class="ph-duotone ph-check-circle"></i>
                  <span class="d-none d-sm-inline">Resumen general</span></span>
              </ul>
            </div>
          </div>
          <div class="card">
            <div class="card-body">

              <div class="progress mb-3">
                <div class="progress-bar bg-success " id="progressbar" style="width: 25%;" aria-valuemin="0" aria-valuemax="100"></div>
              </div>


              <div class="tab-content">

                <section class="tab-pane show active" id="tab_basico">
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
                <section class="tab-pane" id="tab_empleados">
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


                      <section class="hide" id="nuevo_concepto-sec">
                        <div class="mb-3">
                          <label class="form-label" for="concepto_aplicar">Seleccione el concepto de desea
                            agregar</label>
                          <div class="input-group">
                            <select class="form-control" id="concepto_aplicar">
                              <option value="">Seleccione</option>
                              <option value="sueldo_base">-- SUELDO BASE --</option>
                              <option value="sueldo_diferencia">-- DIFERENCIA DE SUELDO --</option>
                            </select>

                            <span class="input-group-text" onclick="getData('conceptos')"><i class='bx bx-refresh reload-icon pointer'></i></span>
                          </div>
                        </div>


                        <div class="mb-3" id="section_fechas">
                          <label class="form-label" for="fechas_aplicar">¿Cuando se debe aplicar el conceto?</label>
                          <select multiple="" class="form-select" id="fechas_aplicar">
                          </select>
                          <small>Mantén presionada la tecla shift o presiona ctrl para selección múltiple.</small>
                        </div>


                        <section id="diferenciaNomina-options" class="hide">



                          <div class="mb-3" id="section_fechas">
                            <label class="form-label" for="nominas_restar">Nominas a restar</label>
                            <select multiple="" class="form-select" id="nominas_restar">

                            <?php 
                            $stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas` WHERE tipo='1'");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                              while ($row = $result->fetch_assoc()) {
                                $id = $row['id'];
                                $nomina = $row['nombre'];
                                echo "<option value='$nomina'>$nomina</option>";
                              }
                            } 
                            $stmt->close();
                            
                            ?>
   
                            </select>
                            <small>Mantén presionada la tecla shift o presiona ctrl para seleccionar las nominas que desea restar.</small>
                          </div>

                        </section>


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
                            <label class="form-label" for="tipo_aplicacion_concept">¿Como desea aplicar el
                              concepto?</label>
                            <select class="form-control" id="tipo_aplicacion_concept">
                              <option value="">Seleccione</option>
                              <option value="2">Enlistar todos los empleados de la nomina</option>
                              <option value="1">Por sus características (Formulación)</option>
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
                          <div id="tabla_empleados-conceptos" class="hide mh-60">



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





                        <div class="d-flex justify-content-between mt-3">
                          <button class="btn btn-secondary" onclick="setViewRegistro()">Cancelar</button>
                          <button class="btn btn-primary" id="guardar_concepto">Guardar concepto</button>
                        </div>

                      </section>

                      <div class="col-lg-12 mh-60" id="conceptos_aplicados-list">
                        <table class="table table-striped table-hover">
                          <thead>
                            <tr>
                              <th class="w-40">Nombre del concepto</th>
                              <th class="w-40">Empleados</th>
                              <th class="w-auto text-center"><button class="btn btn-sm btn-primary" onclick="setViewRegistro()"><i class='bx bx-folder-plus'> Agregar concepto</i>
                                </button></th>
                            </tr>
                          </thead>
                          <tbody id="table-conceptos">

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
                <section class="tab-pane" id="tab_resumen">
                  <div class="row d-flex justify-content-center">
                    <div class="col-lg-6">
                      <div class="text-center"><i class="ph-duotone ph-gift f-50 text-danger"></i>
                        <h3 class="mt-4 mb-3">Verifica que todo este correcto</h3>
                      </div>
                    </div>
                  </div>
                  <div class="p-3">
                    <div class="row">
                      <div class="col-lg-6" id="nomina_resumen">
                      </div>
                    </div>
                  </div>
                  <div class="d-flex w-100 mt-3">
                    <div class="d-flex m-a">
                      <div class="me-2"><button class="previous btn btn-secondary" onclick="beforeStep('3')">Regresar
                          </button=>
                      </div>
                      <div class="next"><button onclick="guardarNomina()" class="btn btn-primary mt-3 mt-md-0"> <i class="bx bx-save"></i> Guardar</button></div>
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
  <script src="../../src/assets/js/notificaciones.js"></script>
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

    function seleccion_empleados(value, result_list) {

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
      let nombre = this.value;
      if (nombre != '') {
        aplicar_filtro(3, nombre, 'empleados-list');
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
        return toast_s('error', 'Debe indicar una condición');
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
            empleadosFiltro[e.id] = [e.id, e.nacionalidad, e.cedula, e.nombres, e.fecha_ingreso, e.anios_actuales, e.otros_anios, e.anios_totales, e.status, e.observacion, e.cod_cargo, e.hijos, e.instruccion_academica, e.discapacidades, e.id_dependencia];
            tabla += '<tr>';
            tabla += '<td>' + e.cedula + '</td>';
            tabla += '<td>' + e.nombres + '</td>';
            tabla += '<td class="text-center"><input class="form-check-input itemCheckbox" onchange="guardar_empleados_nomina()" type="checkbox" value="' + e.id + '"></td>';
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

      guardar_empleados_nomina()
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

    // document.getElementsByClassName('guardar_empleados_nomina').addEventListener('click', guardar_empleados_nomina);


    /**
     * Loads data from the server using AJAX.
     *
     * @param {string} value - The value to be sent to the server.
     * @returns {Promise} - A promise that resolves with the parsed JSON response from the server.
     */
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
    let conceptos_formulacion = []


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

          $('#concepto_aplicar').html('   <option value="sueldo_base">-- SUELDO BASE --</option><option value="sueldo_diferencia">-- DIFERENCIA DE SUELDO --</option>');
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
      const difereciaOptions = $('#diferenciaNomina-options');
      const sueldoOptions = $('#sueldo-options');
      const aplicacionConceptosOptions = $('#aplicacion_conceptos-options');
      const n_conceptos_porcentajes = $('#n_conceptos_porcentajes');
      let tipoCalculo;

      if (this.value == 'sueldo_base') {
        tipoCalculo = null
      } else if (this.value == 'sueldo_diferencia') {
        tipoCalculo = 7;
      } else {
        tipoCalculo = conceptos[this.value]['tipo_calculo'];
      }

      resetValsConceptos()
      sueldoOptions.addClass('hide');

      if (this.value == 'sueldo_base') {
        aplicacionConceptosOptions.removeClass('hide');
        sueldoOptions.removeClass('hide');
      } else if (this.value == 'sueldo_diferencia') {
        aplicacionConceptosOptions.removeClass('hide');
        difereciaOptions.removeClass('hide');
      } else if (tipoCalculo == '5') {
        n_conceptos_porcentajes.toggleClass('hide', false);
        aplicacionConceptosOptions.removeClass('hide');
      } else if (tipoCalculo != '6') {
        aplicacionConceptosOptions.removeClass('hide');
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
        tbl_emp_seleccionados(null, 'todos');
      }
    }

    document.getElementById('tipo_aplicacion_concept').addEventListener('change', tipoAplicacion);

    function tbl_emp_seleccionados(condicion, accion) {
      if (accion == 'todos') {
        $('#tabla_empleados-conceptos').removeClass('hide');
        let tabla = '';
        empleadosSeleccionados.forEach(e => {
          tabla += '<tr>';
          tabla += '<td>' + e[1] + '</td>';
          tabla += '<td>' + e[3] + '</td>';
          tabla += '<td class="text-center"><input class="form-check-input itemCheckbox_C" type="checkbox" value="' + e[0] + '"></td>';
          tabla += '</tr>';
        });
        document.getElementById('emp_pre_seleccionados-list').innerHTML = tabla;
      } else {

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
            tabla_empleados: tabla_empleados,
            tabla_seleccionados: true
          },
          success: function(response) {
            let empleados = JSON.parse(response);
            let tabla = '';

            empleados.forEach(e => {
              //console.log(e)
              tabla += '<tr>';
              tabla += '<td>' + e[2] + '</td>';
              tabla += '<td>' + e[4] + '</td>';
              tabla += '<td class="text-center"><input class="form-check-input itemCheckbox"  type="checkbox" value="' + e.id + '"></td>';
              tabla += '</tr>';
            });

            document.getElementById(result_list).innerHTML = tabla;



          }
        });
      }
    }


    let conceptosAplicados = {}

    function verificarElementosResta() {
      let nominas_restar = $('#nominas_restar').val()
      if (nominas_restar.length > 2) {
        $('#nominas_restar').val([])
        toast_s('error', 'Solo se pueden seleccionar 2 nominas')
        return false
      }
    }
    document.getElementById('nominas_restar').addEventListener('change', verificarElementosResta)

    /**
     * Function to save a concept.
     * 
     * This function retrieves the values of the concept to be saved from the form inputs,
     * performs validation checks, and saves the concept in the 'conceptosAplicados' object.
     * It also updates the UI by adding the concept to the select options and the table.
     */
    async function guardar_concepto() {
      const concepto_aplicar = $('#concepto_aplicar').val();
      const fechas_aplicar = $('#fechas_aplicar').val();
      const concepto_aplicados = $('#concepto_aplicados').val();
      const nominas_restar = $('#nominas_restar').val()

      if (conceptosAplicados[concepto_aplicar]) {
        return toast_s('error', 'No puede agregar el mismo concepto más de una vez');
      }

      if (!concepto_aplicar) {
        return toast_s('error', 'Debe seleccionar algún concepto');
      }

      if ($('#frecuencia_pago').val() == '1' || $('#frecuencia_pago').val() == '2') {
        if (fechas_aplicar == '') {
          return toast_s('error', 'Debe indicar una fecha a aplicar');
        }
      }

      let tipoCalculo, nombreConcepto, tabulador = null;

      if (concepto_aplicar === 'sueldo_base') {
        tipoCalculo = null;
        nombreConcepto = 'Sueldo Base';
      } else if(concepto_aplicar === 'sueldo_diferencia'){
        tipoCalculo = 7;
        nombreConcepto = 'Diferencia de sueldo';

      } else {
        tipoCalculo = conceptos[concepto_aplicar].tipo_calculo;
        nombreConcepto = conceptos[concepto_aplicar].nom_concepto;
      }

      if (tipoCalculo != 6 && !$('#tipo_aplicacion_concept').val()) {
        return toast_s('error', 'Debe seleccionar un tipo de aplicación');
      }

      if (tipoCalculo === 5 && !concepto_aplicados) {
        return toast_s('error', 'Debe seleccionar al menos un concepto al cual aplicar el porcentaje');
      }

      if (concepto_aplicar === 'sueldo_base' && !$('#tabulador').val()) {
        return toast_s('error', 'Debe seleccionar el tabulador');
      } else if (concepto_aplicar === 'sueldo_base') {
        tabulador = $('#tabulador').val();
      }

      if (concepto_aplicar === 'sueldo_diferencia' && nominas_restar.length != 2) {
        return toast_s('error', 'Debe seleccionar las nominas a restar');
      }

      let empleadosDelConcepto = [];

      if (tipoCalculo !== 6) {
        document.querySelectorAll('.itemCheckbox_C').forEach(checkbox => {
          if (checkbox.checked) {
            empleadosDelConcepto.push(checkbox.value);
          }
        });

        if (empleadosDelConcepto.length < 1) {
          return toast_s('error', 'Debe seleccionar al menos un empleado');
        }
      }

      let cantidad_t;
      let valor = 0;
      let subCalculo = '0';
      let nombre_nomina = document.getElementById('nombre_nomina').value;

      if (tipoCalculo !== 6) {
        cantidad_t = empleadosDelConcepto.length;
      } else {
        infoResolve = await cantidadFormulada(concepto_aplicar, Object.keys(empleadosFiltro))
        empleadosDelConcepto = infoResolve
        cantidad_t = infoResolve.length
        console.log(cantidad_t)
      }

      let concepto = {
        'concepto_id': concepto_aplicar,
        'nom_concepto': nombreConcepto,
        'fecha_aplicar': fechas_aplicar,
        'formulacionConcepto': {
          'TipoCalculo': tipoCalculo,
          'n_conceptos': concepto_aplicados, // Solo en caso de que tipoCalculo == 5
          'emp_cantidad': cantidad_t
        },
        'tabulador': tabulador, // solo en caso de que sea sueldo_base
        'empleados': empleadosDelConcepto,
        'nombre_nomina': nombre_nomina,
        'nominas_restar': nominas_restar
      };

      console.log(concepto)

      conceptosAplicados[concepto_aplicar] = concepto;

      // Enviar los datos al archivo PHP mediante AJAX
      $.ajax({
        url: '../../back/modulo_nomina/guardar_concepto.php',
        type: 'POST',
        data: JSON.stringify(concepto),
        contentType: 'application/json',
        success: function(response) {
          // Analizar la respuesta JSON
       //   console.log(response.status);
       //   console.log(response.message);
        },
        error: function(xhr, textStatus, errorThrown) {
          console.error("Error al procesar la solicitud AJAX:", errorThrown);
        }

      });

      $('#concepto_aplicados').append(`<option value="${concepto_aplicar}">${nombreConcepto}</option>`);
      toast_s('success', 'Agregado con éxito');

      $('#table-conceptos').append(`
        <tr id="row_${concepto_aplicar}">
          <td>${nombreConcepto}</td>
          <td>${cantidad_t}</td>
          <td><a class="pointer" onclick="borrarConcepto('${concepto_aplicar}')"><i class='bx bx-trash-alt'></i></a></td>
        </tr>
    `);

      setViewRegistro();
    }

    document.getElementById('guardar_concepto').addEventListener('click', guardar_concepto);

    let valorPrevio_frecuencia_pago;

    /**
     * Sets the frequency of payment options based on the selected value.
     * Updates the options in the 'fechas_aplicar' select element and shows/hides the 'section_fechas' section accordingly.
     * If there are already applied concepts, it prompts the user for confirmation before clearing the concepts and updating the options.
     */
    function setFrecueciaPago() {
      const opciones = {
        '1': `
      <option value="s1">Primera semana</option>
      <option value="s2">Segunda semana</option>
      <option value="s3">Tercera semana</option>
      <option value="s4">Cuarta semana</option>`,
        '2': `
      <option value="q1">Primera quincena</option>
      <option value="q2">Segunda quincena</option>`,
        '3': `
      <option value="m">Pago Mensual</option>`,
        '4': `
      <option value="um">Pago Unico Mensual</option>`,
      };

      const value = this.value;
      const fechasAplicar = $('#fechas_aplicar');
      const sectionFechas = $('#section_fechas');

      /**
       * Updates the options in the 'fechas_aplicar' select element and shows/hides the 'section_fechas' section based on the selected value.
       */
      function actualizarOpciones() {
        if (opciones[value]) {
          fechasAplicar.html(opciones[value]);
          sectionFechas.removeClass('hide');
        } else {
          sectionFechas.addClass('hide');
        }
      }

      if (Object.keys(conceptosAplicados).length === 0) {
        actualizarOpciones();
        valorPrevio_frecuencia_pago = this.value;
      } else {
        Swal.fire({
          title: "¿Estás seguro?",
          text: "Esta acción borrará los conceptos registrados y deberá agregarlos nuevamente",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#04a9f5",
          cancelButtonColor: "#d33",
          confirmButtonText: "Sí, eliminarlo!",
          cancelButtonText: "Cancelar",
        }).then((result) => {
          if (result.isConfirmed) {
            conceptosAplicados = {};
            $('#table-conceptos').html('');
            $('#concepto_aplicados').html('');

            actualizarOpciones();
            valorPrevio_frecuencia_pago = value;
          } else {
            document.querySelector(`#tipo_aplicacion_concept option[value='${valorPrevio_frecuencia_pago}']`).selected = true;
          }
        });
      }
    }
    document.getElementById('frecuencia_pago').addEventListener('change', setFrecueciaPago);


    /**
     * Calculates the formulated quantity for a given concept and employees.
     *
     * @param {string} concepto - The concept for which the quantity is being calculated.
     * @param {array} empleados - The list of employees for whom the quantity is being calculated.
     * @returns {Promise<number>} - A promise that resolves to the formulated quantity.
     */
    async function cantidadFormulada(concepto, empleados) {
      try {
        return await cargarCantidad(concepto, empleados);
      } catch (error) {
        console.error('Error loading data:', error);
      }
    }

    /**
     * Loads the quantity of a concept for a given set of employees.
     *
     * @param {string} c - The concept to load the quantity for.
     * @param {array} e - The array of employees to load the quantity for.
     * @returns {Promise} - A promise that resolves with the parsed JSON response or rejects with an error message.
     */
    function cargarCantidad(c, e) {
      return new Promise((resolve, reject) => {
        $.ajax({
          url: '../../back/modulo_nomina/nom_formulacion_emp_tc_6.php',
          type: 'POST',
          data: {
            concepto: c,
            empleados: e
          },
          success: function(response) {
            console.log(response)
            try {
              resolve(JSON.parse(response));
            } catch (parseError) {
              reject('Error parsing JSON response: ' + parseError);
            }
          },
          error: function(xhr, status, error) {
            reject(`AJAX error - Status: ${status}, Error: ${error}`);
          }
        });
      });
    }

    /**
     * Deletes a concept and performs related operations.
     *
     * @param int id The ID of the concept to be deleted.
     * @return void
     */
    function borrarConcepto(id) {
      // swal preguntando si esta seguro

      Swal.fire({
        title: "¿Estás seguro?",
        text: "¡No podrás revertir esto!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#04a9f5",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminarlo!",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          delete conceptosAplicados[id]
          $('#concepto_aplicados option[value="' + id + '"]').remove();
          toast_s('success', 'Concepto eliminado con éxito')
          $('#row_' + id).remove()

          // recorrer todas la propiedades del objeto 'conceptosAplicados.formulacionConcepto.n_conceptos' y eliminar el concepto si existe
          for (const key in conceptosAplicados) {
            if (conceptosAplicados[key].formulacionConcepto.n_conceptos) {
              let index = conceptosAplicados[key].formulacionConcepto.n_conceptos.indexOf(id);
              if (index > -1) {
                conceptosAplicados[key].formulacionConcepto.n_conceptos.splice(index, 1);
              }
            }
          }

          console.log(conceptosAplicados)
        }
      });
    }


    function resumenDeNomina() {
      const nombre = $('#nombre_nomina').val()
      const frecuencia = $('#frecuencia_pago').val()
      const tipo = $('#tipo_nomina').val()


      $('#nomina_resumen').html(`<h5>Datos de la nómina:</h5>
       <p>Nombre: <b>` + nombre + `</b><br>
       Frecuencia de pago: <b>` + frecuencia + `</b><br>
       Tipo de nómina: <b>` + tipo + `</b>
       </p>
     `)


      let totalPrimas = 0;
      let totalDeducciones = 0;
      let totalAportes = 0;



      $('#nomina_resumen').append(`<h5> Conceptos aplicados:</h5>
      <ul class="list-group">`)

      for (let concepto_aplicar in conceptosAplicados) {
        if (conceptosAplicados.hasOwnProperty(concepto_aplicar)) {
          let concepto = conceptosAplicados[concepto_aplicar];
          let nombreConcepto = concepto.nom_concepto;
          let cantidadEmpleados = concepto.formulacionConcepto.emp_cantidad;
          $('#nomina_resumen').append(`
            <li class="list-group-item d-flex justify-content-between align-items-center">
            <a class="pointer" onclick="verTrabajadoresConcepto('` + concepto + `')">${nombreConcepto}</a>
             <span class="badge bg-primary rounded-pill">${cantidadEmpleados} Empleados</span>
          </li>`)
        }
      }
      $('#nomina_resumen').append('</ul>')
      /*
      return

      let tabla = '';

      for (const key in conceptosAplicados) {
        if (conceptosAplicados.hasOwnProperty(key)) {
          const element = conceptosAplicados[key];
          let cantidad = element.formulacionConcepto.emp_cantidad;
          let concepto = conceptos[key];
          let total = cantidad * concepto.valor;
          tabla += `
         
            <p>${concepto.nom_concepto}</p>
            <p>${cantidad}</p>
            <p>${concepto.valor}</p>
            <p>${total}</p>

          `;

          if (concepto.tipo_concepto == 1) {
            totalPrimas += total;
          } else if (concepto.tipo_concepto == 2) {
            totalDeducciones += total;
          } else if (concepto.tipo_concepto == 3) {
            totalAportes += total;
          }
        }
      }*/
    }


    /**
     * Function to navigate to the next step based on the provided step value.
     *
     * @param {string} step - The current step value.
     */

    function checkNombreNominaExists(nombre_nomina) {
      return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../../back/modulo_nomina/check_nombre_nomina.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
              const response = JSON.parse(xhr.responseText);
              if (response.exists) {
                resolve(true);
              } else {
                resolve(false);
              }
            } else {
              reject('Error al verificar el nombre de la nómina');
            }
          }
        };
        xhr.send(`nombre_nomina=${encodeURIComponent(nombre_nomina)}`);
      });
    }

    async function nextStep(step) {
      if (step == '1') {
        const inputs = ['nombre_nomina', 'frecuencia_pago', 'tipo_nomina'];
        if (inputs.some(id => !document.getElementById(id).value)) {
          return toast_s('error', 'Debe completar todos los campos');
        }

        const nombreNomina = document.getElementById('nombre_nomina').value;
        try {
          const exists = await checkNombreNominaExists(nombreNomina);
          if (exists) {
            return toast_s('error', 'El nombre de la nómina ya existe');
          } else {
            toggleStep('basico', 'empleados');
            document.getElementById('progressbar').style.width = '50%';
          }
        } catch (error) {
          return toast_s('error', error);
        }
      } else if (step == '2') {
        if (empleadosSeleccionados.length === 0) {
          return toast_s('error', 'Debe seleccionar al menos un empleado');
        }
        toggleStep('empleados', 'conceptos');
        document.getElementById('progressbar').style.width = '75%';
      } else if (step == '3') {
        if (Object.keys(conceptosAplicados).length === 0) {
          return toast_s('error', 'Debe seleccionar al menos un concepto');
        } else {
          toggleStep('conceptos', 'resumen');
          document.getElementById('progressbar').style.width = '100%';
          resumenDeNomina()
        }
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
          link: 'link_resumen',
          tab: 'tab_resumen',
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
    function setViewRegistro() {
      $('#concepto_aplicar').val('');
      resetValsConceptos();
      toggleVisibility('#conceptos_aplicados-list', '#nuevo_concepto-sec');
    }


    /**
     * Resets the values of various elements in the form.
     * This function is typically called when a reset action is triggered.
     */
    function resetValsConceptos() {
      document.querySelector("#tabulador option[value='']").selected = true;
      document.querySelector("#tipo_aplicacion_concept option[value='']").selected = true;
      document.getElementById('selectAllC').checked = false;

      $('#diferenciaNomina-options').addClass('hide')
      $('#sueldo-options').addClass('hide')
      $('#n_conceptos_porcentajes').addClass('hide')
      $('#aplicacion_conceptos-options').addClass('hide')
      $('#formulacion-conceptos').addClass('hide')
      $('#tabla_empleados-conceptos').addClass('hide')
      $('#emp_pre_seleccionados-list').html('')
      $('nuevo_concepto-sec').addClass('hide')
      $('conceptos_aplicados-list').removeClass('hide')
      $('#fechas_aplicar').val([])
      $('#concepto_aplicados').val([])
    }


    function guardarNomina() {
      const nombre = $('#nombre_nomina').val()
      const frecuencia = $('#frecuencia_pago').val()
      const tipo = $('#tipo_nomina').val()
      console.log('hola')


    /*  $.ajax({
        url: '../../back/modulo_nomina/guardar_nominas.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            grupo_nomina: '<?php echo $i ?>',
            nombre: nombre,
            frecuencia: frecuencia,
            tipo: tipo,
            conceptosAplicados: conceptosAplicados
        }),
        success: function(response) {


          console.log(response)
          return 
          if (response.status == 'ok') {
            toast_s('success', 'Creado con exito')
            window.location.href = 'nom_grupos.php'
          }else{
            console.log(response.message)
          }

        }
      });*/


      $.ajax({
        url: '../../back/modulo_nomina/guardar_nominas.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            grupo_nomina: '<?php echo $i ?>',
            nombre: nombre,
            frecuencia: frecuencia,
            tipo: tipo,
            conceptosAplicados: conceptosAplicados
        }),
        success: function(response) {
            console.log(response);
            // Asegúrate de que response es un objeto JSON
            try {
                response = JSON.parse(response);
            } catch (e) {
                console.error('Error al parsear la respuesta JSON:', e);
                toast_s('error', 'Respuesta del servidor no válida');
                return;
            }

            if (response.status === 'ok') {
                toast_s('success', 'Creado con éxito');
                window.location.href = 'nom_grupos.php';
            } else {
                console.log(response.message);
                toast_s('error', 'Error: ' + response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
            toast_s('error', 'Error en la solicitud: ' + textStatus);
        },
        complete: function() {
            console.log('Solicitud AJAX completada');
        }
    });



    }
  </script>
</body>

</html>