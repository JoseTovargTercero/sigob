<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
  <link rel="stylesheet" href="../src/styles/style.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.dataTables.css" />
  <title>PERSONAL</title>
</head>

<body>
  <?php require_once '../includes/menu.php' ?>
  <!-- [ MENU ] -->

  <?php require_once '../includes/top-bar.php' ?>
  <!-- [ top bar ] -->




  <div class="pc-container">
    <div class="pc-content">
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="mb-0">Empleados</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-lg-12 mb-3" id="employee-table-view">
          <div class="card">
            <div class="card-header">
              <div class="mb-2">
                <h5 class="mb-0">Empleados</h5>
                <small class="mt-0 text-muted">Administre su personal</small>


              </div>
              <nav class="nav nav-pills nav-justified">

                <button class="nav-link active" data-tableid="employee-table-verificados">Verificados</button>
                <button class="nav-link" data-tableid="employee-table-corregir">Por
                  correciones</button>
                <button class="nav-link" data-tableid="employee-table-revision">En revisión</button>
              </nav>
            </div>
            <div class="card-body d-block" id="employee-table-verificados-container">
              <table id="employee-table-verificados" class="table table-striped" style="width:100%">
                <thead class="w-100">
                  <th>NOMBRES</th>
                  <th>CEDULA</th>
                  <th>DEPENDENCIA</th>
                  <th>NOMINA</th>
                  <th>ACCIONES</th>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
            <div class="card-body d-none" id="employee-table-corregir-container">
              <table id="employee-table-corregir" class="table table-striped" style="width:100%">
                <thead class="w-100">
                  <th>NOMBRES</th>
                  <th>CEDULA</th>
                  <th>DEPENDENCIA</th>
                  <th>NOMINA</th>
                  <th>ACCIONES</th>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
            <div class="card-body d-none" id="employee-table-revision-container">
              <table id="employee-table-revision" class="table table-striped" style="width:100%">
                <thead class="w-100">
                  <th>NOMBRES</th>
                  <th>CEDULA</th>
                  <th>DEPENDENCIA</th>
                  <th>NOMINA</th>
                  <th>ACCIONES</th>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- [ worldLow section ] end -->
        <!-- [ Recent Users ] end -->
      </div>

      <!-- FORMULARIO DE REGISTRO -->

      <div class="modal-window hide" id="modal-employee-form">

        <div class="card modal-box">
          <div class="card-header modal-box-header">
            <div>
              <h5 class="mb-0">Nuevo empleado</h5>
              <small class="text-muted mt-0">Registre la información del empleado</small>
            </div>
          </div>
          <div class="card-body modal-box-content">

            <form class="row overflow-x-hidden employee-form" id="employee-form" autocomplete="off">
              <div class="form-group">
                <div class="row">
                  <div class="col-sm">
                    <label class="form-label" class="form-label" for="nombres">NOMBRE
                      COMPLETO</label>
                    <input class="form-control employee-input" type="text" name="nombres" id="nombres"
                      placeholder="NOMBRE COMPLETO" />
                  </div>
                  <div class="col-sm-4">
                    <label class="form-label" for="tipo_nomina">TIPO NÓMINA</label>
                    <select name="tipo_nomina" class="form-select employee-select" id="tipo_nomina">
                      <option selected value="">ELEGIR...</option>
                      <option value="1">OPCIÓN 1</option>
                      <option value="2">OPCIÓN 2</option>
                    </select>
                  </div>

                </div>

              </div>

              <div class="form-group">
                <div class="row">
                  <div class="col-sm">
                    <label class="form-label" for="nacionalidad">NACIONALIDAD</label>
                    <select name="nacionalidad" class="form-select employee-select" id="nacionalidad">
                      <option selected value="">NACIONALIDAD</option>
                      <option value="V">V</option>
                      <option value="E">E</option>
                    </select>
                  </div>
                  <div class="col-sm">
                    <label class="form-label" for="cedula">CÉDULA</label>
                    <input class="employee-input form-control" type="text" name="cedula" id="cedula"
                      placeholder="CEDULA..." maxlength="9" />
                  </div>
                  <div class="col-sm">
                    <label class="form-label" for="status">ESTADO DEL TRABAJADOR</label>
                    <select name="status" id="status" class="form-select employee-select">
                      <option value="" selected>ELEGIR...</option>
                      <option value="A">ACTIVO</option>
                      <option value="R">RETIRADO</option>
                      <option value="S">SUSPENDIDO</option>
                      <option value="C">COMOSION DE SERVICIO</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <div class="row">
                  <div class="col-sm">
                    <label class="form-label" for="instruccion_academica">INSTRUCCIÓN
                      ACADÉMICA</label>
                    <select class="form-select employee-select" name="instruccion_academica"
                      id="search-select-instruccion_academica">
                      <option value="" selected>ELEGIR...</option>
                    </select>

                  </div>

                  <div class="col-sm" tabindex="0">
                    <label class="form-label" for="cargo">CARGO AL QUE OPTA</label>
                    <select class="form-select employee-select" name="cod_cargo" id="search-select-cargo">
                      <option value="" selected>ELEGIR...</option>
                    </select>
                  </div>
                  <div class="col-sm">
                    <label class="form-label" for="fecha_ingreso">FECHA DE INGRESO</label>
                    <input class="employee-input form-control" type="date" name="fecha_ingreso"
                      placeholder="Fecha de ingreso" id="fecha_ingreso" />
                  </div>
                </div>
              </div>

              <div class="form-group">
                <div class="row">
                  <div class="col-sm">
                    <label class="form-label" for="otros_años">AÑOS LABORALES</label>
                    <input class="employee-input form-control" type="number" name="otros_años"
                      placeholder="Cantidad de años" id="otros_años" />
                  </div>
                  <div class="col-sm">
                    <label class="form-label" for="">HIJOS</label>
                    <input class="employee-input form-control " type="number" name="hijos"
                      placeholder="CANTIDAD DE HIJOS...">
                  </div>
                  <div class="col-sm">
                    <label class="form-label" for="">Becas cursadas</label>
                    <input class="employee-input form-control " type="number" name="beca"
                      placeholder="CANTIDAD DE BECAS...">
                  </div>
                </div>
              </div>

              <div class="form-group">
                <div class="row">
                  <div class="col-sm-2">
                    <label class="form-label" for="discapacidades">DISCAPACIDAD</label>
                    <select name="discapacidades" class="form-select employee-select" id="discapacidades">
                      <option value="" selected>ELEGIR...</option>
                      <option value="1">SÍ POSEE</option>
                      <option value="0">NO POSEE</option>
                    </select>
                  </div>

                  <div class="col-sm-3">
                    <label class="form-label" for="banco">BANCO</label>
                    <select name="banco" class="form-select employee-select" id="search-select-bancos">
                      <option value="" selected>ELEGIR...</option>

                    </select>
                  </div>
                  <div class="col-sm">
                    <label class="form-label" for="cuenta_bancaria">N° DE CUENTA</label>
                    <input class="employee-input form-control" type="text" name="cuenta_bancaria"
                      placeholder="0000 0000 00 0000" id="cuenta_bancaria" maxlength="20">
                  </div>

                </div>
              </div>

              <div class="form-group">
                <div class="row">
                  <label class="form-label" for="id_dependencia">DEPENDENCIAS
                    LABORALES</label>
                  <div class="col-sm-9">
                    <select class="form-select employee-select" name="id_dependencia" id="search-select-dependencias">

                    </select>
                  </div>
                  <div class="col-sm-1">
                    <button type="button" id="add-dependency" class="btn btn-primary">+</button>
                  </div>
                </div>
              </div>


              <div class="form-group">
                <label for="observacion">OBSERVACIONES</label>
                <textarea class="form-control employee-input" name="observacion"
                  placeholder="Observación sobre el empleado..." id="observacion" style="height: 50px"></textarea>
              </div>

              <!-- AÑADIR NUEVA DEPENDENCIA -->

              <div id="modal-dependency" class="modal-window hide">
                <div class="modal-box short slide-up-animation">
                  <header class="modal-box-header">
                    <h4>AÑADIR NUEVA DEPENDENCIA</h4>
                    <button id="btn-close-dependency" type="button" class="btn btn-danger" aria-label="Close">
                      &times;
                    </button>
                  </header>

                  <div class="modal-box-content">
                    <input class="employee-input form-control" type="text" name="dependencia"
                      placeholder="NUEVA DEPENDENCIA..." id="dependencia">
                  </div>

                  <div class="modal-box-footer">
                    <button class="btn-form btn btn-primary" id="dependency-save-btn">GUARDAR
                      DEPENDENCIA</button>
                  </div>



                </div>
              </div>
            </form>

          </div>
          <div class="modal-box-footer card-footer d-flex align-items-center justify-content-center gap-2 py-0">
            <button class="btn btn-primary " id="btn-employee-save">GUARDAR</button>
            <button class="btn btn-danger " id="btn-employee-form-close">CERRAR</button>
          </div>
        </div>


      </div>
      <!-- [ Main Content ] end -->
    </div>











  </div>


</body>

<script type="module" src="../app.js"></script>
<script src="../../src/assets/js/notificaciones.js"></script>
<script type="module" src="../src/controllers/empleadosTable.js">
</script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/fonts/custom-font.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>