<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

$stmt = mysqli_prepare($conexion, "SELECT * FROM `backups` ORDER BY id DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ultima_Act = $row['fecha']; // formato: dd-mm-YY
  }
} else {
  $ultima_Act = 'Nunca';
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
                <h5 class="mb-0">Inicio</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row">
        <!-- [ Recent Users ] start -->
        <div class="col-xl-12 col-md-6">
          <div class="card Recent-Users">
            <div class="card-header">
              <h5>Reportes</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-lg-12">
                  <div class="card-body p-3">
                    <ul class="nav nav-pills nav-justified">
                      <span id="link_informacion" class="nav-item nav-link item-wizard active"><i class="ph-duotone ph-user-circle"></i> <span class="d-none d-sm-inline">Informacion</span></span>
                      <span id="link_columnas" class="nav-item nav-link item-wizard"><i class="ph-duotone ph-map-pin"></i>
                        <span class="d-none d-sm-inline">Columnas</span></span>
                      <span id="link_configuracion" class="nav-item nav-link item-wizard"><i class="ph-duotone ph-check-circle"></i>
                        <span class="d-none d-sm-inline">Configuración final</span></span>
                    </ul>
                  </div>
                  <div class="card-body">

                    <div class="progress mb-3">
                      <div class="progress-bar bg-success " id="progressbar" style="width: 25%;" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>


                    <div class="tab-content">

                      <section class="tab-pane show active" id="tab_informacion">
                        <div id="contactForm" method="post" action="#">
                          <div class="text-center">
                            <h3 class="mb-2">Comencemos con la información requerida.</h3>
                            <small class="text-muted">
                              Formulación del reporte.
                            </small>
                          </div>
                          <div class="mt-4">

                            <div id="formulacion-conceptos" class="
                            ">

                              <!-- HERRAMIENTA PARA FILTRAR SEGUN FORMULA-->
                              <div class="row">

                                <div class="col-lg-6">
                                  <div class="mb-3"><label class="form-label">Formulación</label>
                                    <div class="input-group mb-3">
                                      <textarea class="form-control condicion" rows="1" id="t_area-2"></textarea>
                                  
                                    </div>
                                  </div>


                                  <hr class="my-4">
                                  <h5 class="mb-0">Filtrar por nomina</h5>
                                  <small class="text-muted">Si no selecciona ninguna nomina no se aplicara ningún filtro</small>

                                  <div class="mb-3">
                                    <label class="form-label">Formulación</label>
                                    <select id="filtrarXnomina" class="form-control">
                                      <option value="Ninguno">Ninguno</option>
                                      <option value="nominas">Nominas</option>
                                      <option value="nominas_g">Nominas Grupos</option>
                                    </select>
                                  </div>


                                  <ul class="list-group" id="nominasFiltroSection">

                                  </ul>

                                </div>
                                <div class="col-lg-6">

                                  <div class="mb-3">
                                    <label class="form-label" for="campo_condiciona">Condicionantes</label>
                                    <select name="campo_condiciona" onchange="setCondicionante(this.value, 'result-em_nomina')" id="campo_condiciona" class="form-control">
                                      <option value="">Seleccione</option>
                                      <option value="cod_cargo">Código de cargo</option>
                                      <option value="discapacidades">Discapacidades</option>
                                      <option value="instruccion_academica">Instrucción académica</option>
                                      <option value="hijos">Hijos</option>
                                      <option value="antiguedad">Antigüedad (desde la fecha de ingreso)</option>
                                      <option value="antiguedad_total">Antigüedad (Sumando años anteriores)</option>
                                    </select>
                                  </div>
                                  <ul class="list-group" id="result-em_nomina">
                                  </ul>
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
                      <section class="tab-pane" id="tab_columnas">
                        <div id="jobForm" method="post" action="#">
                          <div class="text-center">
                            <h3 class="mb-2">Es hora de indicar que columnas se mostraran</h3>
                            <small class="text-muted">
                              Por favor, ingrese los columnas de la nómina.
                            </small>
                          </div>
                          <div class="row mt-4">
                            <div class="list-group">
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" disabled checked value="nombres">
                                Nombre
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" disabled checked value="cedula">
                                Cedula
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="nacionalidad">
                                Nacionalidad
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="otros_años">
                                Otros años
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="status">
                                Estatus
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="observacion">
                                Observación
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="cod_cargo">
                                Código del cargo
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="banco">
                                Banco
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="cuenta_bancaria">
                                Cuenta Bancaria
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="hijos">
                                Hijos
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="instruccion_academica">
                                Instrucción Académica
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="discapacidades">
                                Discapacidades
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="tipo_nomina">
                                Nomina
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="id_dependencia">
                                Dependencia
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="verificado">
                                Código Dependencia
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="correcion">
                                Corrección
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="beca">
                                Beca
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="fecha_ingreso">
                                Fecha de ingreso
                              </label>
                              <label class="list-group-item">
                                <input class="form-check-input me-1 campos" type="checkbox" value="sueldo">
                                Sueldo
                              </label>
                            </div>


                          </div>
                          <div class="d-flex w-100 mt-3">
                            <div class="d-flex m-a">
                              <div class=" me-2"><button class="previous btn btn-secondary" onclick="beforeStep('1')">Regresar</button></div>
                              <div class="next"><button class="previous btn btn-secondary mt-3 mt-md-0" onclick="nextStep('2')">Siguiente</button></div>
                            </div>
                          </div>
                        </div>
                      </section>
                      <section class="tab-pane" id="tab_configuracion">
                        <div class="row d-flex justify-content-center">
                          <div class="col-lg-6">
                            <div class="text-center"><i class="ph-duotone ph-gift f-50 text-danger"></i>
                              <h3 class="mt-4 mb-3">Configuración final</h3>
                            </div>
                          </div>
                        </div>
                        <div class="p-3">
                          <div class="row">


                            <div class="col-sm-6">
                              <div class="mb-3">
                                <label class="form-label">Formato del archivo</label>
                                <select class="form-control" id="formato">
                                  <option value="">Seleccione</option>
                                  <option value="pdf">PDF</option>
                                  <option value="xlsx">XLSX</option>
                                </select>
                              </div>
                            </div>


                            <div class="col-sm-6">
                              <div class="mb-3">
                                <label class="form-label">Almacenar reporte</label>
                                <select class="form-control" id="almacenar">
                                  <option value="">Seleccione</option>
                                  <option value="Si">Si</option>
                                  <option value="No">No</option>
                                </select>
                              </div>
                            </div>


                            <div class="col-sm-12">
                              <div class="mb-3">
                                <label class="form-label">Nombre del reporte</label>
                                <input type="text" class="form-control" id="nombre" placeholder="Indique el nombre con el que se va a guardar el archivo">
                              </div>
                            </div>


                          </div>
                        </div>
                        <div class="d-flex w-100 mt-3">
                          <div class="d-flex m-a">
                            <div class="me-2"><button class="previous btn btn-secondary" onclick="beforeStep('2')">Regresar
                                </button=>
                            </div>
                            <div class="next"><button onclick="guardarReporte()" class="btn btn-primary mt-3 mt-md-0"> <i class="bx bx-save"></i> Generar</button></div>
                          </div>
                        </div>
                      </section>

                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
        <!-- [ Recent Users ] end -->

        <div class="col-xl-6 col-md-6">
          <div class="card">
            <div class="card-header">
              <h5>Reportes guardados</h5>
            </div>
            <div class="card-body h-50">
              <table class="table">
                <thead>
                  <tr>
                    <th class="w-5"></th>
                    <th>Reporte</th>
                    <th class="w-5"></th>
                  </tr>
                </thead>
                <tbody id="table_reportes">

                </tbody>
              </table>

            </div>
          </div>
        </div>


        <!-- [ worldLow section ] start -->
        <div class="col-xl-6 col-md-6">
          <div class="card">
            <div class="card-header">
              <h5>Estado del sistema</h5>
            </div>
            <div class="card-body d-flex h-50">
              <div class="m-a">
                <div class="file-upload width-338p">
                  <div class="upload-area">
                    <div class="icon d-flex text-center w-100">
                      <i class='bx bx-cloud-upload'></i>
                    </div>
                    <p class="text-title">Ultima copia de seguridad</p>
                    <div class="mb-3">
                      <p class="mb-0" id="ultima_Act"><?php echo $ultima_Act ?></p>
                      <small class="text-muted" id="timeAgo"></small>
                    </div>


                    <button id="respaldar-btn" class="browse-btn">Respaldar</button>
                  </div>
                </div>
                <p class="text-sm text-muted mt-3 text-center width-338p">
                  * Después de 7 días se realizar una copia de seguridad automática
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ worldLow section ] end -->


    </div>
    <!-- [ Main Content ] end -->
  </div>
  </div>
  <!-- [ Main Content ] end -->

  <script>
    let textarea = 't_area-2';
  </script>
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script src="../../src/assets/js/ajax_class.js"></script>


  <script>
    /**
     * Retrieves and displays a list of nominas based on the selected tipo.
     */
    function getNominas() {
      let tipo = this.value;

      if (tipo == 'Ninguno') {
        document.getElementById('nominasFiltroSection').innerHTML = '';
      } else {
        document.getElementById('nominasFiltroSection').innerHTML = '';

        fetch('../../back/modulo_nomina/nom_lista_nominas.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              tipo: tipo
            })
          })
          .then(response => response.json())
          .then(data => {

            if (data.error) {
              console.error(data.error);
            } else {

              let html = document.getElementById('nominasFiltroSection');
              html.innerHTML = '';

              // Iterate over 'data' and replace 1 or 0 with 'si' and 'no', otherwise print the normal result
              data = data.map(value => {
                let val = value;
                // Add to the result div
                html.innerHTML += `<li class="list-group-item">
                  <label class="pointer d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                      <div class="fw-bold">${val.nombre}</div>
                    </div>
                    <input type="checkbox" class="form-check-input inputs-nominas" value="${val.id}"/>
                  </label>
                </li>`;
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
          });
      }
    }

    document.getElementById('filtrarXnomina').addEventListener('change', getNominas)


    let reportes = []

    /**
     * Fetches and populates a table with saved reports using AJAX.
     */
    function tabla_reportes() {
      $.ajax({
        url: '../../back/modulo_nomina/nom_tabla_reportes_guardados.php',
        type: 'POST',
        contentType: 'application/json',
        success: function(response) {
          reportes = response;
          let rows = '';
          for (let key in response) {
            if (response.hasOwnProperty(key)) {
              let item = response[key];
              rows += `
                <tr>
                  <td><img  src="../../src/assets/images/icons-png/${item.formato}.svg" width="26px" alt="activity-user"></td>
                  <td><h5 class="mb-0">${item.nombre}</h5>
                  <span class="text-muted">Por: ${item.u_nombre}</span>
                  </td>
                  <td><button type="button" onclick="generarReporteGuardado(${item.id})" class="btn btn-icon btn-light-primary"><i class="bx bx-download"></i></button></td>
                </tr>`;
            }
            document.getElementById('table_reportes').innerHTML = rows;
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
          try {
            const response = JSON.parse(jqXHR.responseText);
            console.error('Error del servidor:', response.mensaje, 'Archivo:', response.archivo, 'Línea:', response.linea);
            toast_s('error', 'Error del servidor: ' + response.mensaje);
          } catch (e) {
            console.error('Error al parsear la respuesta de error:', e);
            toast_s('error', 'Error en la solicitud: ' + textStatus);
          }
          if (typeof onError === 'function') {
            onError({
              textStatus,
              errorThrown
            });
          }
        },
        complete: function() {
          //  console.log('Solicitud AJAX completada');
        }
      });
    }

    tabla_reportes()



    // cuando cargue la pagina, agrega 'pc-sidebar-hide' a .pc-sidebar
    document.addEventListener('DOMContentLoaded', function() {
      if ("<?php echo $ultima_Act ?>" == 'Nunca') {
        actualizar()
      } else {
        // verificar si han pasado mas de 7 dias desde la ultima actualizacion
        var fecha = "<?php echo $ultima_Act ?>";
        var fecha = fecha.split('-');
        var fecha = new Date(fecha[2], fecha[1] - 1, fecha[0]);
        var hoy = new Date();
        var dias = Math.floor((hoy - fecha) / (1000 * 60 * 60 * 24));
        if (dias >= 7) {
          actualizar()
        }
        $('#timeAgo').html('Hace ' + dias + ' dias')
      }

    });

    /**
     * This function is responsible for updating the data.
     * It hides the sidebar, prepares the data object for an AJAX request,
     * sends the AJAX request to add the employee, and handles the success and error responses.
     */
    function actualizar() {
      document.querySelector('.pc-sidebar').classList.add('pc-sidebar-hide');
      document.querySelector('.pc-sidebar-collapse').classList.add('hide');
      // Prepare data object for AJAX request
      let data = {
        accion: 'get_data'
      }
      $('#cargando').show()


      // Send AJAX request to add the employee
      const ajaxRequest = new AjaxRequest('application/json', data, '../../back/modulo_nomina/copia_seguridad.php');
      const onSuccess = (response) => {
        console.log(response)
        if (response.status == 'ok') {
          swal('success', 'Actualizado correctamente')
          let fecha = new Date();
          document.getElementById('ultima_Act').innerHTML = fecha.getDate() + '-' + (fecha.getMonth() + 1) + '-' + fecha.getFullYear()
          $('#timeAgo').html('Hace 0 dias')
          $('#cargando').hide()

        } else {
          swal('error', response.mensaje)
        }
        document.querySelector('.pc-sidebar').classList.remove('pc-sidebar-hide');
        document.querySelector('.pc-sidebar-collapse').classList.remove('hide');
      };
      const onError = (response) => {
        console.log('Error:', response);
        toast_s('error', 'Error: ' + response);
        // actualizar()
      };

      ajaxRequest.send(onSuccess, onError);
    }

    document.getElementById('respaldar-btn').addEventListener('click', actualizar)







    /** 
     * Adds an event listener to the 'btn-obtener' button and performs a specific action when clicked.
     * 
     * @param {Event} event - The event object.
     * @returns {void}
     */
    function validarFormula(area, result_list) {
      let condicion = $('#' + area).val();

      //console.log(result_list)

      if (condicion == '') {
        return toast_s('error', 'Debe indicar una condición');
      } else { //emp_pre_seleccionados-list
        /*  if (result_list == 'emp_pre_seleccionados-list') {
  
        } else {
          let accion = 'todos'; // Definir 'accion' aquí si es necesario
          tbl_emp_seleccionados(condicion, accion); // Pasar 'accion' como parámetro
        }*/

        $('#tabla_empleados-conceptos').removeClass('hide')
        aplicar_filtro(2, condicion, result_list);

      }
    }





    /**
     * Applies a filter to retrieve employees based on the specified type and filter criteria.
     *
     * @param {string} tipo - The type of filter to apply.
     * @param {string} filtro - The filter criteria.
     * @param {string} result_list - The ID of the HTML element where the filtered employee list will be displayed.
     */
    let empleadosFiltro = []

    function aplicar_filtro(tipo, filtro, result_list) {
      empleadosFiltro = []
      $.ajax({
        url: '../../back/modulo_nomina/nom_formulacion_back',
        type: 'POST',
        data: {
          tipo_filtro: tipo,
          filtro: filtro.trim(),
          tabla_empleados: true
        },
        success: function(response) {
          console.log(response)
          let empleados = JSON.parse(response);
          let tabla = '';

          empleados.forEach(e => {
            empleadosFiltro[e.id] = [e.id];
            tabla += '<tr>';
            tabla += '<td>' + e.cedula + '</td>';
            tabla += '<td>' + e.nombres + '</td>';
            tabla += '<td class="text-center"><input class="form-check-input itemCheckbox"  type="checkbox" value="' + e.id + '"></td>';
            tabla += '</tr>';
          });

          document.getElementById(result_list).innerHTML = tabla;
        }
      });
    }



    /**
     * Generates a saved report based on the provided ID.
     *
     * @param {number} id - The ID of the report.
     */
    function generarReporteGuardado(id) {
    let formato = reportes[id]['formato'];
    let almacenar = 'No';
    let nombre = reportes[id]['nombre'];
    let condicion = reportes[id]['furmulacion'];
    let columnasArray = reportes[id]['columnas'];
    let nominas = reportes[id]['nominas'];
    let tipoFiltro = reportes[id]['tipoFiltro'];

    let data = {
        formato: formato,
        almacenar: almacenar,
        nombre: nombre,
        columnas: JSON.parse(columnasArray),
        condicion: condicion,
        tipoFiltro: tipoFiltro,
        nominas: (nominas != '' ? JSON.parse(nominas) : [])
    };

    $('#cargando').show();

    console.log(data);

    // Send data to server to generate report
    fetch('../../back/modulo_nomina/nom_reportes_form.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            data: data
        })
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
    })
    .then(blob => {
        $('#cargando').hide();

        // Verificar si la respuesta es un archivo ZIP
        let contentType = blob.type;
        if (contentType === 'application/zip') {
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = nombre + '.zip'; // Usa el nombre proporcionado en el backend
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url); // Limpiar el URL creado
        } else {
            throw new Error('El contenido recibido no es un archivo ZIP');
        }
    })
    .catch(error => {
        $('#cargando').hide();
        console.error('Error:', error);
        toast_s('error', 'Error al enviar la solicitud');
    });
}




    /**
     * Function to save a report.
     * 
     * This function retrieves the values from the form inputs and sends them to the server to generate a report.
     * The generated report is downloaded as a ZIP file if the response from the server is a ZIP file.
     * 
     * @returns {void}
     */
    function guardarReporte() {
      // Retrieve values from form inputs
      let formato = document.getElementById('formato').value;
      let almacenar = document.getElementById('almacenar').value;
      let nombre = document.getElementById('nombre').value;
      let condicion = document.getElementById('t_area-2').value;
      let tipoFiltro = document.getElementById('filtrarXnomina').value;
      let campos = document.querySelectorAll('.inputs-nominas');

      let nominas_filtrar = [];
      let columnas = document.querySelectorAll('.campos');
      let columnasArray = [];

      // Obtener columnas seleccionadas
      columnas.forEach(input => {
        if (input.checked) {
          columnasArray.push(input.value);
        }
      });

      // Verificar si el tipo de filtro no es 'Ninguno'
      if (tipoFiltro != 'Ninguno') {
        campos.forEach(campo => {
          if (campo.checked) {
            nominas_filtrar.push(campo.value);
          }
        });

        // Si no hay checkboxes seleccionados, mostrar mensaje de error
        if (nominas_filtrar.length == 0) {
          return toast_s('error', 'Debe seleccionar al menos una nómina o grupo');
        }
      }

      // Verificar si la condición y los checkboxes están vacíos
      if (condicion == '' && nominas_filtrar.length == 0) {
        return toast_s('error', 'Debe indicar una condición');
      }

      let data = {
        formato: formato,
        almacenar: almacenar,
        nombre: nombre,
        columnas: columnasArray,
        condicion: condicion,
        tipoFiltro: tipoFiltro,
        nominas: nominas_filtrar
      };
      $('#cargando').show()
      console.log(data)


     // Send data to server to generate report
fetch('../../back/modulo_nomina/nom_reportes_form.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        data: data
    })
})
.then(response => {
    if (response.ok) {
        return response.blob();
    } else {
        throw new Error('Error en la respuesta del servidor');
    }
})
.then(blob => {
    $('#cargando').hide();
    if (almacenar == 'Si') {
        tabla_reportes();
    }

    // Verificar si la respuesta es un archivo ZIP
    let contentType = blob.type;
    if (contentType === 'application/zip') {
        let url = window.URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = nombre + '.zip'; // Usa el nombre proporcionado en el backend
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url); // Limpiar el URL creado
    } else {
        throw new Error('El contenido recibido no es un archivo ZIP');
    }
})
.catch(error => {
    $('#cargando').hide();
    console.error('Error:', error);
    toast_s('error', 'Error al enviar la solicitud');
});

    }










    /**
     * This function is used to navigate to the next step in a form.
     * 
     * @param {string} step - The current step of the form.
     * 
     * @returns {void} - This function does not return anything.
     */
    async function nextStep(step) {
      if (step == '1') {
        let condicion = document.getElementById('t_area-2').value;
        let tipoFiltro = document.getElementById('filtrarXnomina').value;
        let campos = document.querySelectorAll('.inputs-nominas');
        let camposArray = [];

        // Verificar si el tipo de filtro no es 'Ninguno'
        if (tipoFiltro != 'Ninguno') {
          campos.forEach(campo => {
            if (campo.checked) {
              camposArray.push(campo.value);
            }
          });

          // Si no hay checkboxes seleccionados, mostrar mensaje de error
          if (camposArray.length == 0) {
            return toast_s('error', 'Debe seleccionar al menos una nómina o grupo');
          }
        }

        // Verificar si la condición y los checkboxes están vacíos
        if (condicion == '' && camposArray.length == 0) {
          return toast_s('error', 'Debe indicar una condición');
        }

        // Continuar con la siguiente parte del proceso
        toggleStep('informacion', 'columnas');
        document.getElementById('progressbar').style.width = '60%';
      } else if (step == '2') {

        toggleStep('columnas', 'configuracion');
        document.getElementById('progressbar').style.width = '100%';
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
          link: 'link_informacion',
          tab: 'tab_informacion',
          progressbar: 30
        },
        '2': {
          link: 'link_columnas',
          tab: 'tab_columnas',
          progressbar: 60
        },
        '3': {
          link: 'link_configuracion',
          tab: 'tab_configuracion',
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
  </script>


</body>

</html>