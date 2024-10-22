<?php
require_once '../../back/sistema_global/session.php';
?>

<?php require_once '../includes/header.php' ?>

<head>
  <link rel="stylesheet" href="../src/styles/style.css">

  <title>PERSONAL</title>

  <link rel="stylesheet" href="../../src/assets/css/chosen.min.css">

  <script src="../../src/assets/js/chosen.jquery.min.js"></script>
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
            </div>
            <div class="card-body d-block" id="employee-table-container">
              <table id="employee-table" class="table table-striped" style="width:100%">
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
              <div class="row mb-4">
                <picture>

                  <img id="empleado-foto" src="../../front/src/assets/img/default.jpg" class="img-thumbnail" alt="..."
                    style="height: 100px;">
                  <figcaption>Foto personal cargada</figcaption>
                </picture>


              </div>
              <div class="form-group">
                <div class="row">


                  <div class="col-sm-4">
                    <div class="mb-3">
                      <label for="empleado-foto-input" class="form-label">Foto personal</label>
                      <input class="form-control" type="file" id="empleado-foto-input">
                    </div>

                  </div>

                  <div class="col-sm">
                    <label class="form-label" class="form-label" for="nombres">NOMBRE
                      COMPLETO</label>
                    <input class="form-control employee-input" type="text" name="nombres" id="nombres"
                      placeholder="NOMBRE COMPLETO" />
                  </div>
                  <div class="col-sm-4 d-none">
                    <label class="form-label" for="tipo_nomina">TIPO NÓMINA</label>
                    <input class="form-control employee-input" type="number" name="tipo_nomina" id="tipo_nomina"
                      placeholder="NOMBRE COMPLETO" value=0 />
                    <!-- <select name="tipo_nomina" class="form-select employee-select" id="tipo_nomina">
                      <option selected value="">ELEGIR...</option>
                      <option value="1">OPCIÓN 1</option>
                      <option value="2">OPCIÓN 2</option>
                    </select> -->
                  </div>



                </div>

              </div>

              <div class=" form-group">
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
                    <label class="form-label" for="status">ESTATUS DEL TRABAJADOR</label>
                    <select name="status" id="status" class="form-select employee-select">
                      <option value="" selected>ELEGIR...</option>
                      <option value="A">ACTIVO</option>
                      <option value="R">RETIRADO</option>
                      <option value="S">SUSPENDIDO</option>
                      <option value="C">COMISIÓN DE SERVICIO</option>
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

                  <div class="col-sm">
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
                    <label class="form-label" for="otros_años">OTROS AÑOS LABORALES</label>
                    <input class="employee-input form-control" type="number" name="otros_años"
                      placeholder="Cantidad de años" id="otros_años" />
                  </div>
                  <div class="col-sm">
                    <label class="form-label" for="">HIJOS</label>
                    <input class="employee-input form-control " type="number" name="hijos"
                      placeholder="CANTIDAD DE HIJOS...">
                  </div>
                  <div class="col-sm">
                    <label class="form-label" for="">Becas</label>
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
                <div class="row ">
                  <div class="col-sm ">
                    <label class="form-label" for="id_dependencia">UNIDAD</label>
                    <div class="input-group">
                      <div class="w-80">
                        <select class="form-select employee-select" name="id_dependencia"
                          id="search-select-dependencias">
                        </select>
                      </div>
                      <div class="input-group-prepend">

                        <button type="button" id="add-dependency" class="input-group-text btn btn-primary">+</button>
                      </div>

                    </div>
                  </div>
                  <div class="col-sm ">
                    <label class="form-label" for="id_categoria">CATEGORIA</label>
                    <div class="input-group">
                      <div class="w-80">
                        <select class="form-select employee-select" name="id_categoria" id="search-select-categorias">
                        </select>
                      </div>
                      <div class="input-group-prepend">
                        <button type="button" id="add-category" class="input-group-text btn btn-primary">+</button>
                      </div>


                    </div>
                  </div>
                  <div class="col-sm ">
                    <label class="form-label" for="id_partida">PARTIDA</label>
                    <input class="employee-input form-control" type="text" name="id_partida" placeholder="Partida..."
                      id="id_partida" list="partidas-list">
                    <!-- <div class="input-group">
                      <div class="w-80">
                      </div>

                      <div class="input-group-prepend">
                        <button type="button" id="add-dependency" class="input-group-text btn btn-primary">+</button>
                      </div>

                    </div> -->
                  </div>
                  <datalist id="partidas-list">

                  </datalist>
                  <!-- <div class="col-sm ">
                    <div class="form-group">

                      <label class="form-label" for="cod_dependencia">UNIDAD</label>

                      <div class="input-group">
                        <input type="text" class="input-group-text mb-auto" name="cod_dependencia" id="cod_dependencia"
                          placeholder="Código" disabled />
                        <div>
                          <select class="form-select employee-select" name="id_dependencia"
                            id="search-select-dependencias">
                          </select>
                        </div>

                        <button type="button" id="add-dependency"
                          class="input-group-text btn btn-primary mb-auto">+</button>
                      </div>
                    </div>
                  </div> -->
                </div>
              </div>

              <div class="form-group">
                <div class="row">
                  <div class="form-group">
                    <button class="btn btn-secondary" id="actualizar-opciones">ACTUALIZAR OPCIONES</button>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <div class="row">
                  <div class="form-group">
                    <label for="observacion">OBSERVACIONES</label>
                    <textarea class="form-control employee-input" name="observacion"
                      placeholder="Observación sobre el empleado..." id="observacion" style="height: 50px"></textarea>
                  </div>
                </div>
              </div>




              <!-- AÑADIR NUEVA DEPENDENCIA -->


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
<!-- <script type="module" src="../src/controllers/empleadosTable.js"> -->
</script>

<!-- DATATABLES -->
<!-- <script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script> -->
<script src="../../src/assets/js/plugins/simplebar.min.js"></script>
<script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
<script src="../../src/assets/js/pcoded.js"></script>
<script src="../../src/assets/js/plugins/feather.min.js"></script>

</html>