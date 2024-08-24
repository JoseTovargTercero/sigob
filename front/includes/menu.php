<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <img src="<?php echo constant('URL') ?>/src/assets/images/logo.png" width="40px" class="img-fluid logo-lg"
        alt="logo">
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <?php if ($_SESSION["u_oficina_id"] == 1) { //nomina 
        ?>

          <li class="pc-item pc-caption">
            <label>Nómina</label>
            <i data-feather="sidebar"></i>
          </li>

          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon">
                <i class='bx bx-cog'></i>
              </span>
              <span class="pc-mtext">Mantenimiento</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_nomina/index">Inicio</a>
              </li>
              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_errores">Estatus</a></li>

              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_columnas">Nuevos campos</a></li>
              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_valores">Asignar valores</a></li>
            </ul>
          </li>




          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon">
                <i class='bx bx-objects-vertical-bottom'></i>
              </span>
              <span class="pc-mtext">Movimientos</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_nomina/nom_conceptos">Conceptos</a></li>
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_nomina/nom_tabulador_tabla">Tabuladores</a></li>
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_nomina/nom_empleados_tabla">Empleados</a></li>
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_nomina/nom_estatus_empleados">Estatus de empleados</a>
              </li>

              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_dependencias_tabla">Unidades</a></li>
              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_categorias_tabla">Categorias</a></li>
              <!-- <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_empleados_registrar">Registrar Personal</a>
              </li> -->
              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_bancos">Bancos</a>
              </li>

            </ul>
          </li>


          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon">
                <i class='bx bx-wallet-alt'></i>
              </span>
              <span class="pc-mtext">Nómina</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_grupos">Registro de nominas</a></li>
              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_peticiones_form">Pagar nomina</a></li>
              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_peticiones_historico">Consulta de histórico</a></li>
              <li class="pc-item"><a class="pc-link"
                  href="<?php echo constant('URL') ?>front/mod_nomina/nom_resumen">Recibo de pago</a></li>
            </ul>
          </li>
        <?php } elseif ($_SESSION["u_oficina_id"] == 2) { //_registro_control 
        ?>

          <li class="pc-item pc-caption">
            <label>Registro y control</label>
            <i data-feather="sidebar"></i>
          </li>



          <li class="pc-item">
            <a href="<?php echo constant('URL') ?>front/mod_registro_control/index" class="pc-link">
            <span class="pc-micon">
                <i class='bx bx-file'></i>
              </span>
            <span class="pc-mtext">Pagos de Nómina</span></a>
          </li>

          <li class="pc-item">
            <a href="<?php echo constant('URL') ?>front/mod_registro_control/regcom_reintegros" class="pc-link">
            <span class="pc-micon">
               <i class='bx bx-refresh'></i>
              </span>
            <span class="pc-mtext">Reintegros</span></a>
          </li>



        <?php } elseif ($_SESSION["u_oficina_id"] == 3) {  //_relaciones_laborales 
        ?>
          <li class="pc-item pc-caption">
            <label>Inicio</label>
            <i data-feather="sidebar"></i>
          </li>

          <li class="pc-item">
            <a href="<?php echo constant('URL') ?>front/mod_relaciones_laborales/index" class="pc-link">
              <span class="pc-micon"><i class='bx bx-detail'></i></span>
              <span class="pc-mtext">Netos de pago</span>
            </a>
          </li>


        <?php } elseif ($_SESSION["u_oficina_id"] == 4) { //_atencion_trabajador 
        ?>

        <?php } ?>




        <!--


        banco='Venezuela' OR banco='Tesoro' 
        valor: 15.5

        -->



        <?php if ($_SESSION["u_nivel"] == '1') { ?>

          <li class="pc-item pc-caption">
            <label>Usuarios</label>
            <i data-feather="sidebar"></i>
          </li>
          <li class="pc-item">
            <a href="<?php echo constant('URL') ?>front/mod_global/global_users" class="pc-link">
              <span class="pc-micon"><i class='bx bx-user'></i></span>
              <span class="pc-mtext">Usuarios</span>
            </a>
          </li>

        <?php } ?>



      </ul>

    </div>
  </div>
</nav>