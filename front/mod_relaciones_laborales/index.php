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
              <div class="d-flex justify-content-between p-3 bg-light">
                <h5>Resultado de la búsqueda</h5>
                <!-- btn icon detail que le quite le haga show a vista_detallada -->
                <button class="btn btn-info btn-sm" id="btn-detail">
                  <i class="bx bx-detail"></i> Vista detallada</button>


              </div>
              <div class="row">
                <div class="col-lg-12 m-a">
                  <div class="table-responsive">
                    <table class="table table-hover table-borderless">
                      <thead>
                        <tr>
                          <th>Año</th>
                          <th>Mes</th>
                          <th class="vista_detallada">Código</th>
                          <th class="vista_detallada">Concepto</th>
                          <th class="vista_detallada">Tipo</th>
                          <th >Asignación</th>
                          <th >Deducción</th>
                          <th >Aporte</th>
                          <th>Integral</th>
                        </tr>
                      </thead>
                      <tbody id="tabla-datos">
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
  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/notificaciones.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script src="../../src/assets/js/ajax_class.js"></script>
  <style>
    .vista_detallada{
      display: none;
    }
    .colorize {
      background-color: #d1d1d1;
    }
    .colorize_2 {
      background-color: #f0f0f0;
    }
    td{
      padding: 7px !important;
    }
    .text-opacity{
      color: #d1d1d1 !important;
    }
  </style>

  <script>

    document.getElementById('btn-detail').addEventListener('click', function() {
      $('.vista_detallada').toggle()
     
    })


    function solicitarDatos() {
      let cedula = document.getElementById('cedula').value

 //     if (cedula == '') {
 //       toast_s('error', 'Debe ingresar una cédula para consultar')
 //       return
 //     }

      fetch('../../back/modulo_relaciones_laborales/rela_neto_pago.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            cedula: cedula
          })
        })
        .then(response => response.text()) // Cambiar a text() para verificar el contenido
        .then(responseText => {
          try {
            let data = JSON.parse(responseText);
            if (data.error) {
              console.error(data.error);
              toast_s('error', 'Error al generar el reporte');
            } else {

              let tabla = document.getElementById('tabla-datos')
              tabla.innerHTML = ''
              // recorre el json e itera los conceptos agregandolos en un tr con la clase 'vista_detallada'
              // posteriormente agrega un tr con el mes (key) y el integral
              for (let year in data) {
                for (let month in data[year]) {
                  let total_asignaciones = 0;
                  let total_deducciones = 0;
                  let total_aportes = 0;



                  for (let concepto in data[year][month].conceptos) {
                    let tr = document.createElement('tr')
                    // agrega al tr la clase 'vista_detallada' para que no sea visible
                    tr.classList.add('vista_detallada')
                    let monto_asignacion = 0
                    let monto_deduccion = 0
                    let monto_aporte = 0
                    switch (data[year][month].conceptos[concepto].tipo) {
                      case 'A':
                        monto_asignacion = data[year][month].conceptos[concepto].monto
                        total_asignaciones += data[year][month].conceptos[concepto].monto
                        break;
                      case 'D':
                        monto_deduccion = data[year][month].conceptos[concepto].monto
                        total_deducciones += data[year][month].conceptos[concepto].monto
                        break;
                      case 'P':
                        monto_aporte = data[year][month].conceptos[concepto].monto
                        total_aportes += data[year][month].conceptos[concepto].monto
                        break;

                    }
                    tr.innerHTML = `
                    <td class="vista_detallada"></td>
                    <td class="vista_detallada"></td>
                      <td class="vista_detallada">${data[year][month].conceptos[concepto].cod}</td>
                      <td class="vista_detallada">${concepto}</td>
                      <td class="vista_detallada">${data[year][month].conceptos[concepto].tipo}</td>
                      <td class="vista_detallada">${(monto_asignacion == 0 ? '<span class="text-opacity">0 Bs</span>' : monto_asignacion + ' Bs')}</td>
                      <td class="vista_detallada">${(monto_deduccion == 0 ? '<span class="text-opacity">0 Bs</span>' : monto_deduccion + ' Bs')}</td>
                      <td class="vista_detallada">${(monto_aporte == 0 ? '<span class="text-opacity">0 Bs</span>' : monto_aporte + ' Bs')}</td>
                      <td></td>
                    `
                    tabla.appendChild(tr)
                  }





                  const capitalizedMonth = capitalizeFirstLetter(month);
                  let tr = document.createElement('tr')
                  // en caso de que el mes sea finalizacion de trimestre, se debe agregar la clase 'colorize' al tr
                  // para que se muestre con un color diferente
                  if (month == 'marzo' || month == 'junio' || month == 'septiembre' || month == 'diciembre') {
                    tr.classList.add('colorize')
                    bg_b = 'text-black'
                  } else{
                    tr.classList.add('colorize_2')
                    bg_b = ''
                  }
                  tr.innerHTML = `
                    <td>${year}</td>
                    <td>${capitalizedMonth}</td>
                    <td class="vista_detallada"></td>
                    <td class="vista_detallada"></td>
                    <td class="vista_detallada"></td>
                    <td>${(total_asignaciones == 0 ? '<span class="text-opacity">0 Bs</span>' : total_asignaciones + ' Bs')}</td>
                    <td>${(total_deducciones == 0 ? '<span class="text-opacity">0 Bs</span>' : total_deducciones + ' Bs')}</td>
                    <td>${(total_aportes == 0 ? '<span class="text-opacity">0 Bs</span>' : total_aportes + ' Bs')}</td>
                    <td><b class="${bg_b}">${data[year][month].integral} Bs</b></td>
                  `
                  tabla.appendChild(tr)

                }
              }
             
              
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
    solicitarDatos()

    document.getElementById('btn-consultar').addEventListener('click', solicitarDatos)
/*
    let data = {
      2023 : {
        'enero': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'febrero': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'marzo': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'abril': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'mayo': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'junio': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'julio': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'agosto': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'septiembre': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'octubre': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'noviembre': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'diciembre': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        }

      },
      2024 : {
        'enero': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'febrero': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'marzo': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'abril': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'mayo': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        },
        'junio': {
          'conceptos': {
            'seguro_social': {
              'tipo': 'A',
              'monto': 100
            },
            'faov': {
              'tipo': 'D',
              'monto': 20
            },
          },
          'base': 800,
          'integral': 880
        }
      }
    }*/
  </script>


</body>

</html>