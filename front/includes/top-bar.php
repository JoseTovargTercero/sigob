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
        <li class="dropdown pc-h-item">
          <a class="pc-head-link dropdown-toggle arrow-none m-0 trig-drp-search" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
            <i data-feather="search"></i>
          </a>
          <div class="dropdown-menu pc-h-dropdown drp-search">
            <form class="px-3 py-2">
              <input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . ." />
            </form>
          </div>
        </li>
      </ul>
    </div>
    <div class="ms-auto">
      <ul class="list-unstyled">
        <li class="pc-h-item">
          <div class="custom-dropdown custom-dropdown-toggle">
            <i data-feather="bell"></i>
            <span id="badge_notifications_number" class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger"><span id="notifications_number"></span><span class="visually-hidden">Notificaciones pendientes</span></span>
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
              <ul class="p-0 list-unstyled d-block" >
                <li class="border-bottom">
                  <a href="<?php echo constant('URL') ?>perfil" class="p-3 d-flex align-items-center">
                    <i data-feather="user"></i>
                    <span class="ms-2">Perfil</span>
                  </a>
                </li>
                <li>
                  <a href="<?php echo constant('URL') ?>back/sistema_login/login_salir.php" class="text-danger p-3 d-flex align-items-center">
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