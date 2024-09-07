<header class="pc-header">
  <div class="header-wrapper">
    <div class="me-auto pc-mob-drp">
      <ul class="list-unstyled">
        <li class="pc-h-item pc-sidebar-collapse">
          <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
            <i data-feather="menu"></i>
          </a>
        </li>
        <li class="pc-h-item pc-sidebar-popup">
          <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
            <i data-feather="menu"></i>
          </a>
        </li>
        <li class=" pc-h-item">
          <a class="pc-link btn btn-light" href="<?php echo constant('URL') ?>front/mod_nomina/nom_tasa_vista">
            Tasa del día (BCV) $: &nbsp;
            <?php
            require_once '../../back/sistema_global/conexion.php';



            $sql = "SELECT * FROM tasa ORDER BY id DESC LIMIT 1";

            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result === false) {
              throw new Exception("Error en la consulta: $conexion->error");
            }

            if ($result->num_rows > 0) {
              $datos = $result->fetch_assoc();
              $response = "$datos[valor] Bs.";
            } else {
              $response = "No se ha registrado una tasa";
            }

            echo $response;

            $stmt->close();



            ?>
          </a>

        </li>
      </ul>
    </div>
    <div class="ms-auto">
      <ul class="list-unstyled">
        <li class="pc-h-item">
          <div class="custom-dropdown custom-dropdown-toggle">
            <i data-feather="bell"></i>
            <span id="badge_notifications_number"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger"><span
                id="notifications_number"></span><span class="visually-hidden">Notificaciones pendientes</span></span>
            <div class="custom-dropdown-menu">

              <div class="dropdown-header  align-items-center justify-content-between p-3">
                <h5 class="m-0">Notificaciones</h5>
              </div>
              <ul class="p-0 list-unstyled d-block" id="notifications"></ul>
            </div>
          </div>
        </li>

        <li class="pc-h-item ms-3">
          <div class="custom-dropdown custom-dropdown-toggle">
            <i data-feather="user"></i>
            <div class="custom-dropdown-menu">
              <div class="dropdown-header  align-items-center justify-content-between p-3">
                <h5 class="m-0">Notificaciones</h5>
              </div>
              <ul class="p-0 list-unstyled d-block">
                <li class="border-bottom">
                  <a href="<?php echo constant('URL') ?>perfil" class="p-3 d-flex align-items-center">
                    <i data-feather="user"></i>
                    <span class="ms-2">Perfil</span>
                  </a>
                </li>
                <li>
                  <a href="<?php echo constant('URL') ?>back/sistema_login/login_salir.php"
                    class="text-danger p-3 d-flex align-items-center">
                    <i data-feather="log-out"></i>
                    <span class="ms-2">Cerrar sesión</span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </li>





      </ul>
    </div>
  </div>
</header>