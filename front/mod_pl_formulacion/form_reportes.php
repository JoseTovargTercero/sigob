<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

function contar($table, $condicion)
{
  global $conexion;

  $stmt = $conexion->prepare("SELECT count(*) FROM $table WHERE $condicion");
  $stmt->execute();
  $row = $stmt->get_result()->fetch_row();
  $galTotal = $row[0];

  return $galTotal;
}


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
    $status = $row['status']; // formato: dd-mm-YY
  }
} else {
  $ejercicio_fiscal = 'No';
  $situado = 0; // formato: dd-mm-YY
}
$stmt->close();


$stmt = mysqli_prepare($conexion, "SELECT SUM(monto_inicial) AS total FROM distribucion_presupuestaria WHERE id_ejercicio=?");
$stmt->bind_param('i', $ejercicio_fiscal);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $distribuido = $row['total'];
  }
} else {
  $distribuido = 0;
}
$stmt->close();



$stmt = mysqli_prepare($conexion, "SELECT * FROM plan_inversion WHERE id_ejercicio=?");
$stmt->bind_param('i', $ejercicio_fiscal);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $plan_inversion_id = $row['id'];
    $plan_inversion = $row['monto_total'];
    $proyectos = contar("proyecto_inversion", 'id_plan=' . $plan_inversion_id);
  }
} else {
  $plan_inversion = 'no';
  $proyectos = 0;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Reportes</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <style>
    td {
      padding: 7px !important;
    }

    .h-15 {
      min-height: 15vh !important;
    }

    .img-ng {
      height: 54vh;
      width: min-content;
      margin: auto;
      opacity: 0.2;
    }

    .top-col>.card {
      height: 192px;
    }

    #situado_h2 {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-size: 3em;
      /* Tamaño inicial */
    }

    h5 {
      font-size: 1rem !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
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

  <style>
    #table td,
    #table th {
      text-align: center;
    }
  </style>


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
          <span class="text-muted fw-light">Formulación /</span> Reportes
        </h4>
      </div>



      <div class="row ">

        <div class="col-lg-4">
          <div class="card" style="min-height: 75vh;">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title  ">
                  <h5 class="mb-0">
                    Reportes disponibles
                  </h5>
                  <small>Seleccione el reportes que desea descargar</small>

                </div>
                <div class="list-group mt-3" style="max-height: 100vh !important;">
                  <a class="list-group-item list-group-item-action pointer active" data-tab-id="2015" data-bs-toggle="tab"><b>2015</b> SEC/PART</a>
                  <a class="list-group-item list-group-item-action pointer" data-tab-id="2002" data-bs-toggle="tab"><b>2002</b> RESUMEN DE LOS CRED. PRESP. SECTORES</a>
                  <a class="list-group-item list-group-item-action pointer" data-tab-id="2004" data-bs-toggle="tab"><b>2004</b> RESUMEN A NIVEL DE SECTORES. Y PROGRAMA</a>
                  <a class="list-group-item list-group-item-action pointer" data-tab-id="2005" data-bs-toggle="tab"><b>2005</b> RESM CRED A NIVEL DE PARTIDAS Y PROGRAMAS</a>
                  <a class="list-group-item list-group-item-action pointer" data-tab-id="2006" data-bs-toggle="tab"><b>2006</b> RESUM. CRED. PRES. A NIVEL PARTIDAS DE SECTORES</a>
                  <a class="list-group-item list-group-item-action pointer" data-tab-id="2009" data-bs-toggle="tab"><b>2009</b> GASTOS DE INVERSIÓN ESTIMADO</a>
                  <a class="list-group-item list-group-item-action pointer" data-tab-id="2010" data-bs-toggle="tab"><b>2010</b> TRASFERENCIAS Y DONACIONES</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="card mb-3" style="min-height: 75vh;">
            <div class="card-body">
              <div class="d-flex flex-column">

                <div class="card-title  d-flex justify-content-between">
                  <div>
                    <h5 class="mb-0">Porcentaje de distribución</h5>
                    <small>Del situado constitucional (Por partidas)</small>
                  </div>
                  <div style="width: 30%;">
                    <label for="ejercicio_fiscal" class="form-label mb-0">Ejercicio fiscal</label>
                    <select class="form-control form-control-sm" id="ejercicio_fiscal">
                      <?php
                      $stmt = mysqli_prepare($conexion, "SELECT * FROM `ejercicio_fiscal` ORDER BY ano DESC");
                      $stmt->execute();
                      $result = $stmt->get_result();
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          echo '<option value="' . $row['id'] . '">' . $row['ano'] . '</option>';
                        }
                      }
                      $stmt->close();


                      ?>
                    </select>
                  </div>
                </div>
                <div class="tab-content">
                  <p id="titulo" class="text-center mt-3 text-bold" style="min-height: 45px;">CRÉDITOS PRESUPUESTARIOS DEL SECTOR POR PROGRAMA A NIVEL DE PARTIDAS Y FUENTES DE FINANCIAMIENTO</p>
                  <div id="texto">
                    <div class="text-center text-info">En caso de querer exportar todos los sectores deje los campos vacíos.</div>
                  </div>
                  <div class="tab-pane fade active show" id="2015" role="tabpanel" aria-labelledby="list-home-list">
                    <div class="mb-3 sect-input" id="sect-sector">
                      <label for="sector" class="form-label">Sector</label>
                      <select type="text" class="form-control" id="sector">
                        <option value="">Seleccione</option>

                        <?php
                        $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_sectores");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                          while ($row = $result->fetch_assoc()) {
                            $sector = $row['sector'];
                            $denominacion = $row['denominacion'];
                            echo ' <option value="' . $sector . '">' . $sector . ' - ' . $denominacion . '</option>;';
                          }
                        }
                        $stmt->close();
                        ?>
                      </select>
                    </div>

                    <div class="mb-3 sect-input" id="sect-programa">
                      <label for="programa" class="form-label">Programa</label>
                      <select type="text" class="form-control" id="programa">
                        <option value="">Seleccione</option>
                      </select>
                    </div>
                  </div>

                  <div class="mt-3 text-center">
                    <button class="btn btn-info " id="btn-descargar">Descargar</button>
                  </div>
                </div>
              </div>
            </div>
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

      <script>
        let programas = []
        <?php

        $stmt = mysqli_prepare($conexion, "SELECT * FROM pl_programas");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $programa = $row['programa'];
            $sector = $row['sector'];
            $denominacion = $row['denominacion'];

            echo 'programas.push(["' . $sector . '", "' . $programa . '", "' . $denominacion . '"]);' . PHP_EOL;
          }
        }
        $stmt->close();

        ?>


        document.getElementById('sector').addEventListener('change', function(event) {
          let sector_s = this.value;

          document.getElementById('programa').innerHTML = '<option value="">Seleccione</option>'

          programas.forEach(element => {
            if (element[0] == sector_s) {
              document.getElementById('programa').innerHTML += `<option value="${element[1]}">${element[1]} - ${element[2]}</option>`
            }
          });
        })
      </script>

      <script>
        document.addEventListener('click', function(event) {
          if (event.target.closest('.list-group-item-action')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.list-group-item-action').getAttribute('data-tab-id');
            gestionarVista(id);
          }
        });

        const texto_nr = '<div class="mt-4 alert alert-success"><p class="text-center">No se requiere mas información.</p></div>'

        let options = {
          '2015': {
            'inputs': ['sect-sector', 'sect-programa'],
            'titulo': 'CRÉDITOS PRESUPUESTARIOS DEL SECTOR POR PROGRAMA A NIVEL DE PARTIDAS Y FUENTES DE FINANCIAMIENTO',
            'texto': '<div class="text-center text-info">En caso de querer exportar todos los sectores deje los campos vacíos.</div>',
            'autorun': false,
            'nombre_archivo': 'FORM. 2015 CRED. PRE. DEL SEC PRO. A NIVEL DE PAR'
          },
          '2002': {
            'inputs': [],
            'titulo': 'RESUMEN DE LOS CRÉDITOS PRESUPUESTARIOS A NIVEL DE SECTORES',
            'texto': texto_nr,
            'autorun': true,
            'nombre_archivo': 'FORMULARIO 2002 RESUMEN DE LOS CRED. PRESP. SECTORES'
          },
          '2004': {
            'inputs': [],
            'titulo': 'RESUMEN DE LOS CRÉDITOS PRESUPUESTARIOS A NIVEL DE SECTORES Y PROGRAMAS Y FUENTES DE FINANCIAMIENTO',
            'texto': texto_nr,
            'autorun': true,
            'nombre_archivo': 'FORMULARIO 2004 RESUMEN A NIVEL DE SECTORES Y PROGRAMA'
          },
          '2005': {
            'inputs': [],
            'titulo': 'RESUMEN DE LOS CRÉDITOS PRESUPUESTARIOS A NIVEL DE PARTIDAS Y FUENTES DE FINANCIAMIENTO',
            'texto': texto_nr, // Que pasa si una partida tiene asignación por diferentes sectores? se suma?
            'autorun': true,
            'nombre_archivo': 'FORMULARIO 2005 RESM CRED A NIVEL DE PARTIDAS Y PROGRAMAS 11-02-20-Copiar'
          },
          '2006': {
            'inputs': [],
            'titulo': 'RESUMEN DE LOS CRÉDITOS PRESUPUESTARIOS POR PARTIDAS A NIVEL DE SECTORES',
            'texto': texto_nr,
            'autorun': true,
            'nombre_archivo': 'FORMULARIO 2006 RESUM. CRED. PRES. A NIVEL  PARTIDAS DE SECTORES 11-02-20-Copiar'
          },
          '2009': {
            'inputs': [],
            'titulo': 'GASTOS DE INVERSIÓN ESTIMADOS POR EL ESTADO',
            'texto': texto_nr,
            'autorun': true,
            'nombre_archivo': 'FORMULARIO 2009 GASTOS DE INVERSIÓN ESTIMADO 11-02-20'
          },
          '2010': {
            'inputs': [],
            'titulo': ' TRANSFERENCIAS Y DONACIONES OTORGADAS A ORGANISMOS DEL SECTOR PUBLICO Y PRIVADO',
            'texto': texto_nr,
            'autorun': true,
            'nombre_archivo': 'FORMULARIO 2010 TRASFERENCIAS Y DONACIONES'
          }
        }

        function gestionarVista(vista) {
          // Modificar el titulo
          $('#titulo').html(options[vista].titulo)
          $('#texto').html(options[vista].texto)

          //Ocultar todos los campos
          $('.sect-input').addClass('hide')

          // Mostrar los campos para el tipo de reporte
          options[vista].inputs.forEach(element => {
            $('#' + element).removeClass('hide')
          });

          // verificar si es un reporte que no requiera información extra
          if (options[vista].autorun) {
            // generar reporte automaticamente
            generarReporte(vista)
          }
        }


        function generarReporte(reporte = null) {

          let ejercicio_fiscal = $('#ejercicio_fiscal').val()
          if (ejercicio_fiscal == '') {
            $('#ejercicio_fiscal').addClass('border-danger')
            toast_s('error', 'Indique el ejercicio fiscal')
            return
          }
          // Seleccionar todos los elementos con la clase 'list-group-item'
          const listItems = document.querySelectorAll('.list-group-item');

          // Recorrer los elementos para encontrar el que tenga la clase 'active'
          listItems.forEach(item => {
            if (item.classList.contains('active')) {
              // Obtener el valor de 'data-tab-id'
              const tabId = item.getAttribute('data-tab-id');
              let data = {
                'sector': '',
                'programa': '',
                'ejercicio_fiscal': ejercicio_fiscal,
                'tipo': tabId,
              }
              if (tabId == '2015') {
                /*if ($('#sector').val() == '' || $('#programa').val() == '') {

                  if ($('#sector').val() == '') {
                    $('#sector').addClass('border-danger')
                  }


                  if ($('#programa').val() == '') {
                    $('#programa').addClass('border-danger')
                  }

                  toast_s('error', 'Indique el sector y programa')
                  return
                } else {*/
                data['sector'] = $('#sector').val()
                data['programa'] = $('#programa').val()
                // }
              }

              sendData(data, tabId)
              return
            }
          });
        }

        document.getElementById('btn-descargar').addEventListener('click', generarReporte)

        function sendData(data, archivo) {

          $('#cargando').show();

          // Send data to server to generate report
          fetch('../../back/modulo_pl_formulacion/form_reportes.php', {
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
              let nombre = options[archivo]['nombre_archivo']

              let contentType = blob.type;
              if (contentType === 'application/zip') {
                let url = window.URL.createObjectURL(blob);
                let a = document.createElement('a');
                a.href = url;
                a.download = nombre + '.zip';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
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
      </script>
</body>

</html>