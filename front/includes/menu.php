<nav class="pc-sidebar">
    <div class="navbar-wrapper">
      <div class="m-header">
          <img src="<?php echo constant('URL') ?>/src/assets/images/logo.png" width="40px" class="img-fluid logo-lg" alt="logo">
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
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_nomina/nom_tabulador">Tabuladores</a></li>
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_nomina/nom_conceptos">Conceptos</a></li>
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_nomina/nom_personal">Personal</a></li>
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
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>/nom_Formulación">Formulación</a></li>
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>/nom_Pagar">Pagar</a></li>
            </ul>
          </li>







          <li class="pc-item pc-caption">
            <label>Usuarios</label>
            <i data-feather="sidebar"></i>
          </li>

          <li class="pc-item">
            <a href="<?php echo constant('URL') ?>/adm_usuarios" class="pc-link">
              <span class="pc-micon"><i data-feather="sidebar"></i></span>
              <span class="pc-mtext">Usuarios</span>
            </a>
          </li>








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