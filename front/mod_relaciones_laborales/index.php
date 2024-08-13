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
              <h5>Datos del empleado</h5>
            </div>
            <div class="card-body">
              <div class="row mb-3">
                <div class="col-lg-5 m-a">
                  <div class="form-group text-center">
                    <label for="cedula" class="mb-2">Cédula de identidad</label>
                    <div class="input-group">
                      <input type="text" class="form-control text-center" id="cedula" placeholder="Cédula a consultar" required>
                      <button class="btn btn-primary" id="btn-consultar"><i class="feather icon-download-cloud"></i> Consultar</button>
                    </div>
                  </div>
                </div>
              </div>
              <hr>

              <div class="d-flex justify-content-between p-3 mt-3 ">
                <div>
                  <h5 class="mb-0">Resultado de la consulta</h5>
                  <small class="text-muted">Busqueda por cedula para el calculo de prestaciones laborales</small>
                </div>
                <!-- btn icon detail que le quite le haga show a vista_detallada -->


                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item" role="presentation"><a class="nav-link text-uppercase active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Vista trimestral</a></li>
                  <li class="nav-item" role="presentation"><a class="nav-link text-uppercase" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Vista mensual</a></li>
                </ul>
              </div>

              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade active show" id="home" role="tabpanel" aria-labelledby="home-tab">

                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>Año</th>
                          <th>Trimestre</th>
                          <th class="text-center">Asignaciones</th>
                          <th class="text-center">Deducciones</th>
                          <th class="text-center">Aportes</th>
                          <th class="text-center">Integral</th>
                          <th class="text-center"></th>
                        </tr>
                      </thead>
                      <tbody id="tabla-datos">
                        <!-- Aquí se mostrarán los datos -->
                      </tbody>
                    </table>
                  </div>


                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>Año</th>
                          <th>Mes</th>
                          <th class="text-center">Asignaciones</th>
                          <th class="text-center">Deducciones</th>
                          <th class="text-center">Aportes</th>
                          <th class="text-center">Integral</th>
                          <th class="text-center"></th>
                        </tr>
                      </thead>
                      <tbody id="tabla-datos-mes">
                        <!-- Aquí se mostrarán los datos -->
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <!-- [ Recent Users ] end -->
          </div>
          <!-- [ worldLow section ] end -->
        </div>
        <!-- [ Main Content ] end -->
      </div>
    </div>




    <div class="dialogs">
      <div class="dialogs-content " style="width: 75%;">
        <span class="close-button">×</span>
        <h5 class="mb-1">Detalles del pago</h5>
        <hr>

        <div class="card-body">


          <div class="w-100 d-flex justify-content-between mb-2">
            <h5 class="text-primary" id="info-pago"></h5>

            <button id="btn-donwload" class="btn btn-sm btn-info"> <i class="bx bx-download"></i> Descargar</button>
          </div>


          <table class="table table-hover">
            <thead>
              <tr>
                <th>nom_concepto</th>
                <th class="text-center">Asignacion</th>
                <th class="text-center">Deducción</th>
                <th class="text-center">Aporte</th>
                <th class="text-center">TOTAL</th>
              </tr>
            </thead>
            <tbody id="tabla-detalles">
              <!-- Aquí se mostrarán los datos -->
            </tbody>
          </table>
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
      let informacion_neto, cedula_consulta

      const nomenclaturaTrimestre = {
        'Q1': 'Primer trimestre',
        'Q2': 'Segundo trimestre',
        'Q3': 'Tercer trimestre',
        'Q4': 'Cuarto trimestre'
      }

      const nomenclaturaMensual = {
        '01': 'Enero',
        '02': 'Febrero',
        '03': 'Marzo',
        '04': 'Abril',
        '05': 'Mayo',
        '06': 'Junio',
        '07': 'Julio',
        '08': 'Agosto',
        '09': 'Septiembre',
        '10': 'Octubre',
        '11': 'Noviembre',
        '12': 'Diciembre'
      };
      /**
       * Retrieves the key from an associative array based on its corresponding value.
       *
       * @param mixed $value The value to search for in the associative array.
       * @return mixed|null The key corresponding to the given value, or null if the value is not found.
       */
      function getKeyByValue(value) {
        const key = Object.keys(nomenclaturaMensual).find(key => nomenclaturaMensual[key] === value);
        return key;
      }



      /**
       * Calculates the total values for assignments, deductions, and contributions based on the provided data.
       *
       * @param {Object} datos - The data object containing assignments, deductions, and contributions.
       * @returns {Object} - An object containing the calculated total values for assignments, deductions, and contributions.
       */
      function calcularValores(datos, esTrimestre) {
        let valorAsignaciones = 0;
        let valorDeducciones = 0;
        let valorAportes = 0;

        // Iterar sobre cada tipo de dato (asignaciones, deducciones, aportes)
        for (let tipo in datos) {
          if (tipo === 'asignaciones' || tipo === 'deducciones' || tipo === 'aportes') {
            if (datos.hasOwnProperty(tipo)) {

              // Verificar si es un objeto (no una lista) y recorrerlo
              if (typeof datos[tipo] === 'object' || Array.isArray(datos[tipo])) {
                for (let key in datos[tipo]) {
                  if (datos[tipo].hasOwnProperty(key)) {
                    let item = datos[tipo][key];
                    switch (tipo) {
                      case 'asignaciones':
                        valorAsignaciones += item.valor || 0;
                        break;
                      case 'deducciones':
                        valorDeducciones += item.valor || 0;
                        break;
                      case 'aportes':
                        valorAportes += item.valor || 0;
                        break;
                    }
                  }
                }
              }
            }
          }
        }

        // Formatear los valores para ser mostrados
        valorAsignaciones = valorAsignaciones === 0 ? '<span class="text-opacity">0 Bs</span>' : `${valorAsignaciones} Bs`;
        valorDeducciones = valorDeducciones === 0 ? '<span class="text-opacity">0 Bs</span>' : `${valorDeducciones} Bs`;
        valorAportes = valorAportes === 0 ? '<span class="text-opacity">0 Bs</span>' : `${valorAportes} Bs`;

        return {
          valorAsignaciones,
          valorDeducciones,
          valorAportes
        };
      }

      /**
       * Generates HTML rows based on the provided data.
       *
       * @param {Object} datos - The data object containing the information.
       * @param {string} tipo - The type of data to generate rows for.
       * @param {boolean} esTrimestre - Indicates whether the data is for trimesters or not.
       * @returns {string} - The generated HTML rows.
       */
      function generarFilas(datos, year, esTrimestre) {
        if (!datos) {
          console.error('Datos no definidos:', datos);
          return ''; // Retorna una cadena vacía si los datos no están definidos
        }

        let row = '';

        // Obtener los datos para el año especificado
        const datosPorPeriodo = datos[year];

        // Verificar que los datos para el año existan
        if (!datosPorPeriodo) {
          console.error(`Datos no definidos para el año ${year}:`, datosPorPeriodo);
          return ''; // Retorna una cadena vacía si los datos para el año no están definidos
        }

        const numeroDePeriodos = Object.keys(datosPorPeriodo).length;
        let firstPeriodo = true;

        // Iterar sobre cada periodo en los datos del año especificado
        for (let periodo in datosPorPeriodo) {
          if (datosPorPeriodo.hasOwnProperty(periodo)) {
            const periodoDatos = datosPorPeriodo[periodo];


            // Calcular los valores de asignaciones, deducciones y aportes para el periodo actual
            const {
              valorAsignaciones,
              valorDeducciones,
              valorAportes
            } = calcularValores(periodoDatos, esTrimestre);




            row += `<tr>`;
            if (firstPeriodo) {
              row += `<td rowspan="${numeroDePeriodos}">${year}</td>`;
              firstPeriodo = false;
            }
            row += `<td>${esTrimestre ? nomenclaturaTrimestre[periodo] : periodo}</td>`;
            row += `<td class="text-center">${valorAsignaciones}</td>`;
            row += `<td class="text-center">${valorDeducciones}</td>`;
            row += `<td class="text-center">${valorAportes}</td>`;
            row += `<td class="text-center">${periodoDatos.sueldo_total} Bs</td>`;
            row += `<td class="text-center"><button onclick="detallesPeriodo('${year}', '${periodo}', ${esTrimestre})" type="button" class="btn btn-sm btn-outline-info d-inline-flex">Info</button></td>`;
            row += `</tr>`;
          }
        }

        return row;
      }


      /**
       * Function to display details of a specific period.
       *
       * @param {string} anio - The year of the period.
       * @param {string} tipo - The type of period ('datos_por_trimestre' or 'datos_por_ano_mes').
       * @param {string} periodo - The period to display details for.
       */
      function detallesPeriodo(anio, mes_trimestre, esTrimestre) {

        let info_pago = document.getElementById('info-pago');
        if (esTrimestre) {
          info_pago.innerHTML = nomenclaturaTrimestre[mes_trimestre] + ' del ' + anio;
          $('#btn-donwload').hide();
        } else {
          info_pago.innerHTML = mes_trimestre + ' del ' + anio;
          let mes_anio = getKeyByValue(mes_trimestre) + '-' + anio;

          $('#btn-donwload').show();
          $('#btn-donwload').attr('onclick', 'descarga("' + cedula_consulta + '", "' + mes_anio + '")');
        }


        // Display the information of the period
        const tablaDetalles = document.getElementById('tabla-detalles');
        tablaDetalles.innerHTML = ''; // Clear the table before adding new data


        

        



        if (informacion_neto[(esTrimestre ? 'datos_por_trimestre':'datos_por_ano_mes')][anio][mes_trimestre]) {
          const datosDelPeriodo = informacion_neto[(esTrimestre ? 'datos_por_trimestre':'datos_por_ano_mes')][anio][mes_trimestre];
          let row = '';
          let totalAsignaciones = 0;
          let totalDeducciones = 0;
          let totalAportes = 0;

          /**
           * Function to add concept rows to the table.
           *
           * @param {object} conceptos - The concept data for a specific type (asignaciones, deducciones, or aportes).
           * @param {string} tipoConcepto - The type of concept (asignaciones, deducciones, or aportes).
           */
          const agregarFilasDeConceptos = (conceptos, tipoConcepto) => {
            if (typeof conceptos === 'object') {
              for (let key in conceptos) {
                if (conceptos.hasOwnProperty(key)) {
                  const item = conceptos[key];
                  const valor = item.valor;
                  const nomConcepto = item.nom_concepto;

                  row += `<tr>`;
                  row += `<td>${nomConcepto}</td>`;
                  row += `<td class="text-center">${tipoConcepto === 'asignaciones' ? valor + ' Bs' : '<span class="text-opacity">0 Bs</span>'}</td>`;
                  row += `<td class="text-center">${tipoConcepto === 'deducciones' ? valor + ' Bs' : '<span class="text-opacity">0 Bs</span>'}</td>`;
                  row += `<td class="text-center">${tipoConcepto === 'aportes' ? valor + ' Bs' : '<span class="text-opacity">0 Bs</span>'}</td>`;
                  row += `<td class="text-center"></td>`;
                  row += `</tr>`;

                  // Accumulate totals
                  if (tipoConcepto === 'asignaciones') {
                    totalAsignaciones += valor;
                  } else if (tipoConcepto === 'deducciones') {
                    totalDeducciones += valor;
                  } else if (tipoConcepto === 'aportes') {
                    totalAportes += valor;
                  }
                }
              }
            }
          };

          // Add rows for asignaciones, deducciones, and aportes
          agregarFilasDeConceptos(datosDelPeriodo.asignaciones, 'asignaciones');
          agregarFilasDeConceptos(datosDelPeriodo.deducciones, 'deducciones');
          agregarFilasDeConceptos(datosDelPeriodo.aportes, 'aportes');

          // Add subtotal row
          row += `<tr>`;
          row += `<td><strong>SUBTOTAL</strong></td>`;
          row += `<td class="text-center"><strong>${totalAsignaciones} Bs</strong></td>`;
          row += `<td class="text-center"><strong>${totalDeducciones} Bs</strong></td>`;
          row += `<td class="text-center"><strong>${totalAportes} Bs</strong></td>`;
          row += `<td class="text-center"><strong>${datosDelPeriodo.sueldo_total} Bs</strong></td>`;
          row += `</tr>`;

          tablaDetalles.innerHTML = row;
          toggleDialogs();

        } else {
          console.error('Periodo no encontrado en los datos.');
        }
      }

      /**
       * Function to download a report.
       *
       * @param {string} cedula - The identification number.
       * @param {string} periodo - The period to generate the report for.
       */
      function descarga(cedula, periodo) {
        // Show loading spinner and toggle dialogs
        $('#cargando').show();
        toggleDialogs();

        // Send data to server to generate report
        fetch('../../back/modulo_relaciones_laborales/rela_neto_pdf.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              cedula: cedula,
              fecha_pagar: periodo
            })
          })
          .then(response => {
            if (response.ok) {
              let contentType = response.headers.get('content-type');

              // Check if the content type is JSON
              if (contentType && contentType.includes('application/json')) {
                return response.json().then(json => {
                  // Handle JSON error
                  console.error('Error JSON received:', json);
                  throw new Error('Error en la respuesta del servidor: ' + json.message);
                });
              } else if (contentType && contentType === 'application/zip') {
                return response.blob();
              } else {
                throw new Error('El contenido recibido no es un archivo ZIP ni JSON');
              }
            } else {
              throw new Error('Error en la respuesta del servidor');
            }
          })
          .then(blob => {
            // Hide loading spinner and toggle dialogs
            $('#cargando').hide();
            toggleDialogs();

            // Check if the response is a ZIP file
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'reportes_' + new Date().toISOString().replace(/[-:.]/g, '') + '.zip'; // ZIP file name
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url); // Clean up the created URL
          })
          .catch(error => {
            // Hide loading spinner and toggle dialogs
            $('#cargando').hide();
            toggleDialogs();

            console.error('Error:', error);
            toast_s('error', 'Error al enviar la solicitud');
          });
      }
      /**
       * Function to request data from the server based on the provided ID.
       */
      function solicitarDatos() {
        let cedula = document.getElementById('cedula').value;
        cedula_consulta = cedula;
        if (cedula == '') {
          toast_s('error', 'Debe ingresar una cédula para consultar');
          return;
        }

        fetch('../../back/modulo_relaciones_laborales/rela_neto_informacion_front.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              cedula: cedula
            })
          })
          .then(response => response.json()) // Change to text() to verify the content
          .then(responseText => {
            const resumen_pagos = responseText; 
            informacion_neto = responseText; // TO ACCESS FROM DETAIL / BY QUARTER OR MONTH
            try {
              if (resumen_pagos.error) {
                console.error(resumen_pagos.error);
                toast_s('error', 'Error al generar el reporte');
              } else {
                // Generate rows for the quarterly table
                let tablaTrimestre = document.getElementById('tabla-datos');
                tablaTrimestre.innerHTML = generarFilas(resumen_pagos.datos_por_trimestre, '2024', true);

                // Generate rows for the monthly table
                let tablaMes = document.getElementById('tabla-datos-mes');
                tablaMes.innerHTML = generarFilas(resumen_pagos.datos_por_ano_mes, '2024', false);
              }
            } catch (error) {
              console.error('Error al analizar la respuesta:', error);
              toast_s('error', 'Error al procesar la respuesta del servidor');
            }
          })
          .catch(error => {
            console.error('Error en la solicitud:', error);
            toast_s('error', 'Error al comunicarse con el servidor');
          });
      }

      document.getElementById('btn-consultar').addEventListener('click', solicitarDatos)
    </script>

</body>

</html>