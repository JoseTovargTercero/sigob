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

      let data = {
        2024: {
          "datos_por_fecha": {
            "07-2024": {
              "asignaciones": {
                "003": {
                  "nom_concepto": "PRIMA POR TRANSPORTE",
                  "valor": 16
                }
              },
              "deducciones": [],
              "aportes": [],
              "sueldo_total": 48.75
            },
            "08-2024": {
              "asignaciones": {
                "001": {
                  "nom_concepto": "SUELDO BASE",
                  "valor": 33.75
                },
                "003": {
                  "nom_concepto": "PRIMA POR TRANSPORTE",
                  "valor": 15
                }
              },
              "deducciones": [],
              "aportes": [],
              "sueldo_total": 48.75
            }
          },
          "datos_por_trimestre": {
            "Q1": {
              "asignaciones": [],
              "deducciones": [],
              "aportes": [],
              "sueldo_total": 0
            },
            "Q2": {
              "asignaciones": [],
              "deducciones": [],
              "aportes": [],
              "sueldo_total": 0
            },
            "Q3": {
              "asignaciones": {
                "003": {
                  "nom_concepto": "PRIMA POR TRANSPORTE",
                  "valor": 15
                }
              },
              "deducciones": [],
              "aportes": [],
              "sueldo_total": 48.75
            },
            "Q4": {
              "asignaciones": [],
              "deducciones": [],
              "aportes": [],
              "sueldo_total": 0
            }
          }
        }
      } // Quitar


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
       * Calculates the total values for assignments, deductions, and contributions based on the provided data.
       *
       * @param {Object} datos - The data object containing assignments, deductions, and contributions.
       * @returns {Object} - An object containing the calculated total values for assignments, deductions, and contributions.
       */
      function calcularValores(datos) {
        let valorAsignaciones = 0;
        let valorDeducciones = 0;
        let valorAportes = 0;

        for (let tipo in datos) {
          if (datos.hasOwnProperty(tipo)) {
            for (let item in datos[tipo]) {
              if (datos[tipo].hasOwnProperty(item)) {
                switch (tipo) {
                  case 'asignaciones':
                    valorAsignaciones += datos[tipo][item].valor;
                    break;
                  case 'deducciones':
                    valorDeducciones += datos[tipo][item].valor;
                    break;
                  case 'aportes':
                    valorAportes += datos[tipo][item].valor;
                    break;
                }
              }
            }
          }
        }

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
      function generarFilas(datos, tipo, esTrimestre) {
        let row = '';

        for (let year in datos) {
          if (datos.hasOwnProperty(year)) {
            const datosPorPeriodo = datos[year][tipo];
            const numeroDePeriodos = Object.keys(datosPorPeriodo).length;

            let firstPeriodo = true;
            for (let periodo in datosPorPeriodo) {
              if (datosPorPeriodo.hasOwnProperty(periodo)) {
                const {
                  valorAsignaciones,
                  valorDeducciones,
                  valorAportes
                } = calcularValores(datosPorPeriodo[periodo]);

                row += `<tr>`;
                if (firstPeriodo) {
                  row += `<td rowspan="${numeroDePeriodos}">${year}</td>`;
                  firstPeriodo = false;
                }
                row += `<td>${esTrimestre ? nomenclaturaTrimestre[periodo] : periodo}</td>`;
                row += `<td class="text-center">${valorAsignaciones}</td>`;
                row += `<td class="text-center">${valorDeducciones}</td>`;
                row += `<td class="text-center">${valorAportes}</td>`;
                row += `<td class="text-center">${datosPorPeriodo[periodo].sueldo_total} Bs</td>`;
                row += `<td class="text-center"><button onclick="detallesPeriodo('${year}', '${tipo}', '${esTrimestre ? periodo : periodo}')" type="button" class="btn btn-sm btn-outline-info d-inline-flex">Info</button></td>`;
                row += `</tr>`;
              }
            }
          }
        }
        return row;
      }

      /**
       * Function to display details of a specific period.
       *
       * @param {string} anio - The year of the period.
       * @param {string} tipo - The type of period ('datos_por_trimestre' or 'datos_por_mes').
       * @param {string} periodo - The period to display details for.
       */
      function detallesPeriodo(anio, tipo, periodo) {
        // alert(anio + ' - ' + tipo + ' - ' + trimestral)

        let info_pago = document.getElementById('info-pago');
        if (tipo == 'datos_por_trimestre') {
          info_pago.innerHTML = nomenclaturaTrimestre[periodo] + ' del ' + anio;
          $('#btn-donwload').hide();
        } else {
          let explode_periodo = periodo.split('-');
          info_pago.innerHTML = nomenclaturaMensual[explode_periodo[0]] + ' del ' + explode_periodo[1];
          $('#btn-donwload').show();
          $('#btn-donwload').attr('onclick', 'descarga("' + cedula_consulta + '", "' + periodo + '")');
        } // MOSTRAR LA INFORMACION DEL PERIODO

        const tablaDetalles = document.getElementById('tabla-detalles');
        tablaDetalles.innerHTML = ''; // Limpiar la tabla antes de agregar nuevos datos

        if (informacion_neto.hasOwnProperty(anio) && informacion_neto[anio].hasOwnProperty(tipo)) {
          const datosDelPeriodo = informacion_neto[anio][tipo][periodo];
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
            for (let concepto in conceptos) {
              if (conceptos.hasOwnProperty(concepto)) {
                const valor = conceptos[concepto].valor;
                const nomConcepto = conceptos[concepto].nom_concepto;

                row += `<tr>`;
                row += `<td>${nomConcepto}</td>`;
                row += `<td class="text-center">${tipoConcepto === 'asignaciones' ? valor + ' Bs' : '<span class="text-opacity">0 Bs</span>'}</td>`;
                row += `<td class="text-center">${tipoConcepto === 'deducciones' ? valor + ' Bs' : '<span class="text-opacity">0 Bs</span>'}</td>`;
                row += `<td class="text-center">${tipoConcepto === 'aportes' ? valor + ' Bs' : '<span class="text-opacity">0 Bs</span>'}</td>`;
                row += `<td class="text-center"></td>`;
                row += `</tr>`;

                // Acumulate totals
                if (tipoConcepto === 'asignaciones') {
                  totalAsignaciones += valor;
                } else if (tipoConcepto === 'deducciones') {
                  totalDeducciones += valor;
                } else if (tipoConcepto === 'aportes') {
                  totalAportes += valor;
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
        } else {
          console.error('Periodo no encontrado en los datos.');
        }

        // Show modal
        toggleDialogs();
      }


      function descarga(cedula, periodo) {

        $('#cargando').show()
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

              // Verificar si el tipo de contenido es JSON
              if (contentType && contentType.includes('application/json')) {
                return response.json().then(json => {
                  // Manejar JSON de error
                  console.error('Error JSON recibido:', json);
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
            $('#cargando').hide();
            toggleDialogs();


            // Verificar si la respuesta es un archivo ZIP
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'reportes_' + new Date().toISOString().replace(/[-:.]/g, '') + '.zip'; // Nombre del archivo ZIP
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url); // Limpiar el URL creado
          })
          .catch(error => {
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
        let cedula = document.getElementById('cedula').value
        cedula_consulta = cedula
        if (cedula == '') {
          toast_s('error', 'Debe ingresar una cédula para consultar')
          return
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
          .then(response => response.text()) // Change to text() to verify the content
          .then(responseText => {

            //const resumen_pagos = responseText;
            const resumen_pagos = data;


            informacion_neto = resumen_pagos; // TO ACCESS FROM DETAIL / BY QUARTER OR MONTH

            try {
              if (resumen_pagos.error) {
                console.error(resumen_pagos.error);
                toast_s('error', 'Error al generar el reporte');
              } else {
                // Generate rows for the quarterly table
                let tablaTrimestre = document.getElementById('tabla-datos');
                tablaTrimestre.innerHTML = generarFilas(resumen_pagos, 'datos_por_trimestre', true);

                // Generate rows for the monthly table
                let tablaMes = document.getElementById('tabla-datos-mes');
                tablaMes.innerHTML = generarFilas(resumen_pagos, 'datos_por_fecha', false);
              }
            } catch (error) {
              console.error('Error al analizar la respuesta:', error);
              toast_s('error', 'Error al procesar la respuesta del servidor');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            toast_s('error', 'Error al enviar la solicitud');
          });
      }

      document.getElementById('btn-consultar').addEventListener('click', solicitarDatos)

    </script>


</body>

</html>