<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <img src="<?php echo constant('URL') ?>/src/assets/images/logo.png" width="40px" class="img-fluid logo-lg"
        alt="logo">
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <li class="pc-item pc-caption">
          <label>Inicio</label>
        </li>
        <li class="pc-item">
          <a href="dashboard" class="pc-link">
            <span class="pc-micon">
              <i data-feather="home"></i>
            </span>
            <span class="pc-mtext">Dashboard</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Nómina</label>
          <i data-feather="sidebar"></i>
        </li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
            <span class="pc-micon">
              <i data-feather="align-right"></i>
            </span>
            <span class="pc-mtext">Movimientos</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
          </a>
          <ul class="pc-submenu">
            <li class="pc-item"><a class="pc-link"
                href="<?php echo constant('URL') ?>front/mod_nomina/nom_conceptos">Conceptos</a></li>
            <li class="pc-item"><a class="pc-link"
                href="<?php echo constant('URL') ?>front/mod_nomina/nom_tabulador_tabla">Tabuladores</a></li>
            <li class="pc-item"><a class="pc-link"
                href="<?php echo constant('URL') ?>front/mod_nomina/nom_empleados_tabla">Empleados</a>
            </li>
            <li class="pc-item"><a class="pc-link"
                href="<?php echo constant('URL') ?>front/mod_nomina/nom_empleados_registrar">Registrar Personal</a>
            </li>
          </ul>
        </li>


        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
            <span class="pc-micon">
              <i data-feather="align-right"></i>
            </span>
            <span class="pc-mtext">Nómina</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
          </a>
          <ul class="pc-submenu">
            <li class="pc-item"><a class="pc-link"
                href="<?php echo constant('URL') ?>front/mod_nomina/nom_grupos">Registro de nominas</a></li>
            <li class="pc-item"><a class="pc-link"
                href="<?php echo constant('URL') ?>front/mod_nomina/nom_Pagar">Pagar</a></li>
          </ul>
        </li>



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
              <span class="pc-micon"><i data-feather="sidebar"></i></span>
              <span class="pc-mtext">Usuarios</span>
            </a>
          </li>

          <?php } ?>







        <!--
          <li class="pc-item">
            <a href="other/sample-page.html" class="pc-link">
              <span class="pc-micon"><i data-feather="sidebar"></i></span>
              <span class="pc-mtext">Calculo</span>
            </a>
          </li>

-->







      </ul>

    </div>
  </div>
</nav>