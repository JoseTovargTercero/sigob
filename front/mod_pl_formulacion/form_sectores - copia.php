<?php
require_once '../../back/sistema_global/conexion.php';
require_once '../../back/sistema_global/session.php';

function contar($table, $condicion)
{
  global $conexion;

  $stmt = $conexion->prepare("SELECT count(*) FROM " . $table . " WHERE " . $condicion);
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

$stmt = mysqli_prepare($conexion, "SELECT * FROM `ejercicio_fiscal` WHERE ano = ?");
$stmt->bind_param('s', $annio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ejercicio_fiscal = $row['id']; // formato: dd-mm-YY
    $situado = $row['situado']; // formato: dd-mm-YY
    $status_ejercicio = $row['status']; // formato: dd-mm-YY
  }
} else {
  $ejercicio_fiscal = 'No';
  $situado = 0; // formato: dd-mm-YY
}
$stmt->close();


$stmt = mysqli_prepare($conexion, "SELECT * FROM plan_inversion WHERE id_ejercicio=?");
$stmt->bind_param('i', $ejercicio_fiscal);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $plan_inversion_monto = $row['monto_total'];
    $plan_inversion = number_format($plan_inversion_monto, 0, '.', '.');
    $id_plan_inversion = number_format($row['id'], 0, '.', ',');

    $proyectos = contar("proyecto_inversion", 'id_plan=' . $id_plan_inversion);
    $proyectos_ejecutados = contar("proyecto_inversion", 'id_plan=' . $id_plan_inversion . ' AND status=1');
  }
} else {
  $id_plan_inversion = 0;
  $plan_inversion = 'Sin asignación';
  $proyectos = 0;
  $proyectos_ejecutados = 0;
}
$stmt->close();




?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Sectores</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">



  <style>
    table tr td:nth-child(1),
    table tr td:nth-child(2),
    table tr th:nth-child(1),
    table tr th:nth-child(2) {
      text-align: center !important;
      /* Alineación al centro, puedes cambiarla a 'left' o 'right' */
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




      <div class=" d-flex justify-content-between">
        <h4 class="fw-bold py-3 mb-4">
          <span class="text-muted fw-light">Formulación /</span> Sectores presupuestarios
        </h4>
      </div>





      <div class="row ">
        <div class="col-lg-12 hide" id="vista_registro">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0">Nuevo proyecto</h5>

                </div>


                <div class="mt-2 card-body">

                  <div class="mb-3">
                    <label for="partida" class="form-label">Nombre del sector</label>
                    <input type="text" id="nombre" class="form-control" placeholder="Nombre del sector">
                  </div>



                  <div class="mb-4 row">
                    <div class="col-lg-4">
                      <label for="sector" class="form-label">Sector</label>
                      <input type="number" min="0" class="form-control" id="sector" name="sector" placeholder="Sector">
                    </div>
                    <div class="col-lg-4">
                      <label for="programa" class="form-label">Programa</label>
                      <input type="number" min="0" class="form-control" id="programa" name="programa" placeholder="Programa">

                    </div>
                    <div class="col-lg-4">
                      <label for="proyecto" class="form-label">Proyecto</label>
                      <input type="number" min="0" class="form-control" id="proyecto" name="proyecto" placeholder="Proyecto">
                    </div>
                  </div>


                  <div class="mb-3 d-flex justify-content-between">
                    <button class="btn btn-secondary" id="btn-cancelar-registro">Cancelar</button>
                    <button class="btn btn-primary" id="btn-registro">Guardar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <div class="col-lg-12" id="vista-tabla">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto d-flex justify-content-between">
                  <h5 class="mb-0">Sectores</h5>
                  <button class="btn btn-info btn-sm" onclick="nuevoProyecto()">
                    <i class="bx bx-plus"></i>
                    Nuevo sector
                  </button>
                </div>


                <div class="mt-2 card-body">

                  <table class="table" id="table">
                    <thead>
                      <tr>
                        <th style="width: 5%;">#</th>
                        <th>Sector</th>
                        <th>Nombre</th>
                        <th style="width: 5%;"></th>
                        <th style="width: 5%;"></th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="dialogs">
        <div class="dialogs-content " style="width: 35%;">
          <span class="close-button">×</span>
          <h5 class="mb-1">Ejecución de proyecto</h5>
          <hr>
          <p class="text-danger">
            * Una vez marcado el proyecto como "Ejecutado" este no podrá ser modificado ni eliminado.
          </p>
          <div class="card-body">
            <div class="mb-3">
              <label for="comentario" class="form-label">Comentario</label>
              <textarea id="comentario" class="form-control"></textarea>
            </div>
            <div class="mb-2 d-flex justify-content-between">
              <button type="submit" class="btn btn-primary" id="btn-ejecutar">Marcar como ejecutado</button>
              <button type="button" class="btn btn-secondary" onclick="toggleDialogs()">Cancelar</button>
            </div>
          </div>
        </div>
      </div>


      <datalist id="partidas"></datalist>

      <!-- [ Main Content ] end -->
      <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
      <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
      <script src="../../src/assets/js/pcoded.js"></script>
      <script src="../../src/assets/js/plugins/feather.min.js"></script>
      <script src="../../src/assets/js/notificaciones.js"></script>
      <script src="../../src/assets/js/main.js"></script>
      <script src="../../src/assets/js/ajax_class.js"></script>

      <script src="../../src/assets/js/amcharts5/index.js"></script>
      <script src="../../src/assets/js/amcharts5/themes/Animated.js"></script>
      <script src="../../src/assets/js/amcharts5/xy.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0"></script>


      <script>
        const url_back = '../../back/modulo_pl_formulacion/form_sectores_back.php'
        let sectores = []

        // DATA TABLE
        var DataTable = $("#table").DataTable({
          language: lenguaje_datat
        });


        let accion

        // iniciar nuevo registro de sector
        function nuevoProyecto() {
          accion = 'registrar_proyecto'
          $('#vista_registro').removeClass('hide')
          $('#vista-tabla').addClass('hide')
          $('#vista_datalles').addClass('hide')
        }


        var edt
        // Mostrar interfaz para editar proyecto existente
        function editarProyecto(id) {
          $('#vista_datalles').addClass('hide')
          edt = id
          accion = 'update_sector'
          $('#nombre').val(sectores[id]['0'])
          $('#sector').val(sectores[id]['1'])
          $('#programa').val(sectores[id]['2'])
          $('#proyecto').val(sectores[id]['3'])
          $('#vista_registro').removeClass('hide')
          $('#vista-tabla').addClass('hide')
        }



        function cancelarRegistro() {
          $('#vista_registro').addClass('hide')
          $('#vista-tabla').removeClass('hide')
          $('.form-control').val('')
        }
        document.getElementById('btn-cancelar-registro').addEventListener('click', cancelarRegistro)

        // Registrrar nuevo sector
        function guardarSector() {
          let errors = false

          const nombre = $("#nombre").val();
          const sector = $("#sector").val();
          const programa = $("#programa").val();
          const proyecto = $("#proyecto").val();

          (!validarCampo('nombre') ? errors = true : '');
          (!validarCampo('sector') ? errors = true : '');
          (!validarCampo('programa') ? errors = true : '');
          (!validarCampo('proyecto') ? errors = true : '');

          if (sector < 0 || sector.length != 2) {
            $('#sector').addClass('border-danger')
            toast_s('error', 'El valor del campo es incorrecto')
            errors = true;
          }
          if (programa < 0 || programa.length != 2) {
            $('#programa').addClass('border-danger')
            toast_s('error', 'El valor del campo es incorrecto')
            errors = true;
          }
          if (proyecto < 0 || proyecto.length != 2) {
            $('#proyecto').addClass('border-danger')
            toast_s('error', 'El valor del campo es incorrecto')
            errors = true;
          }

          // Si hay errores, detener la ejecución
          if (errors) {
            return;
          }

          const data = {
            nombre: nombre.trim(),
            sector: sector,
            programa: programa,
            proyecto: proyecto,
            id: null
          }

          // enviar los datos al back con el nuevo formato
          if (accion == 'update_sector') {
            data.id = edt
          }

          $.ajax({
            url: url_back,
            type: "json",
            contentType: 'application/json',
            data: JSON.stringify({
              data: data,
              accion: accion
            }),
            success: function(response) {
              if (response.success) {
                toast_s('success', 'Sector ' + (accion == 'update_proyecto' ? 'actualizado' : 'registrado') + ' con éxito')
                get_tabla()
                cancelarRegistro()
              } else {
                console.log(response)
                toast_s('error', response.error)
              }
            },
            error: function(xhr, status, error) {
              console.log(xhr.responseText);
            },
          });
        }

        // addevenlister btn-registro click

        document.getElementById('btn-registro').addEventListener('click', guardarSector);


        /**
         * Deletes a record with the specified ID.
         * @param {number} id - The ID of the record to be deleted.
         * @returns {boolean} - Returns true if the record is deleted successfully, false otherwise.
         */
        function eliminar(id) {
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
            console.log(result)
            if (result.isConfirmed) {
              $.ajax({
                url: url_back,
                type: "json",
                contentType: 'application/json',
                data: JSON.stringify({
                  accion: 'eliminar_sector',
                  id: id
                }),
                success: function(response) {
                  if (response.success) {
                    get_tabla()
                    toast_s("success", "Eliminado con éxito");
                  } else {
                    toast_s("error", response.error);
                  }
                },
                error: function(xhr, status, error) {
                  console.error(xhr.responseText);
                  console.error(error);
                },
              });
            }
          });
        }

        document.addEventListener('click', function(event) {

          if (event.target.closest('.btn-delete')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.btn-delete').getAttribute('data-delete-id');
            eliminar(id);
          }
          if (event.target.closest('.btn-edit')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.btn-edit').getAttribute('data-edit-id');
            editarProyecto(id);
          }
          if (event.target.closest('.btn-detalles-p')) { // ACCION DE ELIMINAR
            const id = event.target.closest('.btn-detalles-p').getAttribute('data-id-proyecto');
            getDetallesProyecto(id);
          }
        });



        function get_tabla() {
          $.ajax({
            url: url_back,
            type: "json",
            contentTyrepe: 'application/json',
            data: JSON.stringify({
              accion: 'get_sectores'
            }),
            success: function(response) {
              let data_tabla = [] // Informacion de la tabla
              console.log(response)

              if (response.success) {
                let count = 1;
                DataTable.clear()

                response.success.forEach(function(item) {

                  sectores[item.id] = [
                    item.nombre,
                    item.sector,
                    item.programa,
                    item.proyecto,
                    item.id
                  ]

                  data_tabla.push([
                    count++,
                    item.sector + '.' + item.programa + '.' + item.proyecto,
                    item.nombre,
                    `<button class="btn btn-edit btn-sm bg-brand-color-2 text-white " data-edit-id="${item.id}"><i class="bx bx-edit-alt"></i></button>`,
                    `<button class="btn btn-danger btn-sm btn-delete" data-delete-id="${item.id}"><i class="bx bx-trash"></i></button>`
                  ]);



                });


                DataTable.rows.add(data_tabla).draw()
              }
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
              console.error(error);
            },
          });
        }
        get_tabla()
      </script>

</body>

</html>