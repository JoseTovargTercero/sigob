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
              <ul class="nav nav-pills nav-justified" role="tablist">
                <li class="nav-item">
                  <a href="#contactDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link" aria-selected="true" role="tab"><i class="ph-duotone ph-user-circle"></i> <span class="d-none d-sm-inline">Basico</span></a>
                </li>

                <li class="nav-item">
                  <a href="#educationDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link icon-btn active" aria-selected="false" tabindex="-1" role="tab"><i class="ph-duotone ph-graduation-cap"></i> <span class="d-none d-sm-inline">Empleados</span></a>
                </li>

                <li class="nav-item">
                  <a href="#jobDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link icon-btn" aria-selected="false" tabindex="-1" role="tab"><i class="ph-duotone ph-map-pin"></i> <span class="d-none d-sm-inline">Conceptos</span></a>
                </li>


                <li class="nav-item">
                  <a href="#finish" data-bs-toggle="tab" data-toggle="tab" class="nav-link icon-btn" aria-selected="false" tabindex="-1" role="tab"><i class="ph-duotone ph-check-circle"></i> <span class="d-none d-sm-inline">Resumen general</span></a>
                </li>
              </ul>
            </div>
          </div>
          <div class="card">
            <div class="card-body">
              <div class="tab-content">

                <div class="tab-pane " id="contactDetail">
                  <form id="contactForm" method="post" action="#">
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
                            <div class="mb-3"><label class="form-label">Nombre de la nomina</label> <input type="text" class="form-control" placeholder="Nombre de la nomina"></div>
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
                  </form>
                  <div class="d-flex w-100 mt-3">
                    <div class="d-flex m-a">
                      <div class="previous me-2"><a href="javascript:void(0);" class="btn btn-secondary disabled">Regresar</a></div>
                      <div class="next" data-target-form="#contactDetailForm" role="presentation"><a href="#contactDetail" data-bs-toggle="tab" data-toggle="tab" aria-selected="true" role="tab" class="btn btn-secondary mt-3 mt-md-0">Siguiente</a></div>
                    </div>
                  </div>

                </div>

                <div class="tab-pane show active" id="educationDetail">
                  <form id="educationForm" method="post" action="#">
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
                          <select class="form-select" id="filtro_empleados">
                            <option>Seleccione</option>
                            <option value="1">Enlistar todos</option>
                            <option value="2">Por sus características (Formulación)</option>
                            <option value="3">Heredar de otra nomina</option>
                          </select>
                        </div>
                      </div>

                      <section id="herramienta-formulacion" class="hidex p-3">
                        <!-- HERRAMIENTA PARA FILTRAR SEGUN FORMULA-->
                        <div class="row">
                          
                          <div class="col-lg-6">
                              <div class="mb-3" id="forms"><label class="form-label">Formulación</label>
                                <div class="input-group mb-3" id="form-1">
                                  <textarea class="form-control condicion" aria-label="With textarea" rows="1" id="t_area-1"></textarea>
                                  <button class="btn btn-primary" id="btn-obtener" type="button">Obtener</button>
                                </div>
                              </div>
                          </div>
                          <div class="col-lg-6">

                              <div class="mb-3">
                                <label class="form-label" for="campo_condiciona">Condicionantes</label>
                                <select name="campo_condiciona" onchange="setCondicionante(this.value)" id="campo_condiciona" class="form-control">
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
                      <section id="empleados-list" class="mt-3 mh-70">

                      </section>

                    </div>











                    <div class="d-flex w-100 mt-3">
                      <div class="d-flex m-a">
                        <div class="previous me-2"><a href="javascript:void(0);" class="btn btn-secondary disabled">Regresar</a></div>
                        <div class="next"><a href="javascript:void(0);" class="btn btn-secondary mt-3 mt-md-0">Siguiente</a></div>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="tab-pane" id="jobDetail">
                  <form id="jobForm" method="post" action="#">
                    <div class="text-center">
                      <h3 class="mb-2">Es hora de agregar los conceptos</h3>
                      <small class="text-muted">
                        Por favor, ingrese los conceptos de la nómina.
                      </small>
                    </div>
                    <div class="row mt-4">
                      <div class="col-sm-6">
                        <div class="mb-3"><label class="form-label">Street Name</label> <input type="text" class="form-control" placeholder="Enter Street Name"></div>
                      </div>
                      <div class="col-sm-6">
                        <div class="mb-3"><label class="form-label">Street No</label> <input type="text" class="form-control" placeholder="Enter Street No"></div>
                      </div>
                      <div class="col-sm-6">
                        <div class="mb-3"><label class="form-label">City</label> <input type="text" class="form-control" placeholder="Enter City"></div>
                      </div>
                      <div class="col-sm-6">
                        <div class="mb-3"><label class="form-label">Country</label> <select class="form-select">
                            <option>Select Contry</option>
                            <option>India</option>
                            <option>Rusia</option>
                            <option>Dubai</option>
                          </select></div>
                      </div>
                    </div>
                    <div class="d-flex w-100 mt-3">
                      <div class="d-flex m-a">
                        <div class="previous me-2"><a href="javascript:void(0);" class="btn btn-secondary disabled">Regresar</a></div>
                        <div class="next"><a href="javascript:void(0);" class="btn btn-secondary mt-3 mt-md-0">Siguiente</a></div>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="tab-pane" id="finish">
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
                      <div class="previous me-2"><a href="javascript:void(0);" class="btn btn-secondary disabled">Regresar</a></div>
                      <div class="next"><a href="javascript:void(0);" class="btn btn-secondary mt-3 mt-md-0">Siguiente</a></div>
                    </div>
                  </div>
                </div>

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
  <script src="../../src/assets/js/fonts/custom-font.js"></script>
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
    document.getElementById('filtro_empleados').addEventListener('change', function(event) {
      let filtro = this.value;
      if (filtro == 1) {
        aplicar_filtro(1, null);
      } else if (filtro == 2) {
        $('#herramienta-formulacion').removeClass('hide');
        $('#otras_nominas-list').addClass('hide');
      } else if (filtro == 3) {
        $('#otras_nominas-list').removeClass('hide');
        $('#herramienta-formulacion').addClass('hide');
      }
    });



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
    document.getElementById('btn-obtener').addEventListener('click', function(event) {
      let condicion = $('#t_area-1').val();
      if (condicion == '') {
        toast_s('error', 'Debe indicar una condición');
        return;
      } else {
        aplicar_filtro(2, condicion);
      }
    });

    

    /**
     * Applies a filter to retrieve employees based on the specified type and filter.
     *
     * @param {string} tipo - The type of filter to apply.
     * @param {string} filtro - The filter to apply.
     */
    function aplicar_filtro(tipo, filtro) {
      let data = {
        tabla_empleados: true,
        filtro: filtro
      };
      $.ajax({
        url: url_back,
        type: 'POST',
        data: {
          tipo_filtro: tipo,
          filtro: filtro,
          tabla_empleados: true
        },
        success: function(response) {
          let empleados = JSON.parse(response);
          let tabla = '<table class="table table-striped table-hover">';
          tabla += '<thead><tr><th>Cedula</th><th>Nombre</th><th>Apellido</th><th>Correo</th><th>Telefono</th><th>Acciones</th></tr></thead>';
          tabla += '<tbody>';
          empleados.forEach(e => {
            tabla += '<tr>';
            tabla += '<td>' + e.cedula + '</td>';
            tabla += '<td>' + e.nombre + '</td>';
            tabla += '<td>' + e.apellido + '</td>';
            tabla += '<td>' + e.correo + '</td>';
            tabla += '<td>' + e.telefono + '</td>';
            tabla += '<td><button class="btn btn-primary">Agregar</button></td>';
            tabla += '</tr>';
          });
          tabla += '</tbody>';
          tabla += '</table>';
          document.getElementById('empleados-list').innerHTML = tabla;
          document.getElementById('empleados-list').style.display = 'block';
          document.getElementById('herramienta-formulacion').style.display = 'none';
          document.getElementById('otras_nominas-list').style.display = 'none';
        }
      });
    }
  </script>


</body>

</html>