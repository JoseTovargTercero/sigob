<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <img src="../../src/assets/images/logo.png" width="40px" class="img-fluid logo-lg"
        alt="logo">
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">

        <?php
        require_once '../../back/sistema_global/conexion.php';

        $oficinas = [
          1 => 'Nomina',
          2 => 'Registro y control',
          3 => 'Relaciones laborales',
          4 => 'Formulación',
          5 => 'Ejecución Presupuestaria'
        ];

        echo ' <li class="pc-item pc-caption">
            <label>' . $oficinas[$_SESSION["u_oficina_id"]] . '</label>
            <i data-feather="sidebar"></i>
          </li>';


        $menu = [];
        $oficina = $_SESSION["u_oficina"];
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `menu` WHERE oficina = ?"); // SACAR TODOS LOS ITEMS DEL MODULO
        $stmt->bind_param('s', $oficina);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $listar = false;


            $listar = ($_SESSION["u_nivel"] == 1 || isset($_SESSION['permisos'][$row['id']])); // Verificar tipo de usuario o nivel de acceso


            if ($listar) { // Si tiene acceso
              // Configuración base para cada item o sub-item
              $item = [
                "tipo" => "item",
                "categoria" => $row['categoria'],
                "icono" => $row['icono'] ?? 'bx-error',
                "enlace" => $row['dir'] ?? '#',
                "nombre" => $row['nombre'] ?? 'Sin nombre',
                "sub-item" => null
              ];

              if (is_null($row['categoria'])) {
                // Es un item normal
                $menu[$row['id']] = $item;
              } else {
                // Es un sub-item
                if (!isset($menu[$row['categoria']])) {
                  // Crear la categoría si no existe
                  $menu[$row['categoria']] = [
                    "tipo" => "categoria",
                    "categoria" => $row['categoria'],
                    "icono" => $row['icono'] ?? 'bx-error',
                    "enlace" => null,
                    "nombre" => null,
                    "sub-item" => []
                  ];
                }

                // Agregar el sub-item a la categoría existente
                $menu[$row['categoria']]['sub-item'][] = [
                  "enlace" => $row['dir'] ?? '#',
                  "nombre" => $row['nombre'] ?? 'Sin nombre'
                ];
              }
            }
          }
        }
        $stmt->close();



        function generarMenu($menu)
        {
          $url_base = constant('URL') . 'front/';

          foreach ($menu as $item) {
            if ($item['tipo'] == 'item') {
              // Es un item normal
              echo '<li class="pc-item">';
              echo '<a href="' . $url_base . htmlspecialchars($item['enlace']) . '" class="pc-link">';
              echo '<span class="pc-micon"><i class="bx ' . htmlspecialchars($item['icono']) . '"></i></span>';
              echo '<span class="pc-mtext">' . htmlspecialchars($item['nombre']) . '</span>';
              echo '</a>';
              echo '</li>';
            } elseif ($item['tipo'] == 'categoria') {
              // Es una categoría con sub-items
              echo '<li class="pc-item pc-hasmenu">';
              echo '<a class="pc-link">';
              echo '<span class="pc-micon"><i class="bx ' . htmlspecialchars($item['icono']) . '"></i></span>';
              echo '<span class="pc-mtext">' . htmlspecialchars($item['categoria']) . '</span>';
              echo '<span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>';
              echo '</a>';
              echo '<ul class="pc-submenu">';

              // Recorrer los sub-items de la categoría
              foreach ($item['sub-item'] as $subItem) {
                echo '<li class="pc-item">';
                echo '<a class="pc-link" href="' . $url_base . htmlspecialchars($subItem['enlace']) . '">';
                echo htmlspecialchars($subItem['nombre']);
                echo '</a>';
                echo '</li>';
              }

              echo '</ul>';
              echo '</li>';
            }
          }
        }
        generarMenu($menu);

        if ($_SESSION["u_nivel"] == '1') { ?>

          <li class="pc-item pc-caption">
            <label>Administrativo</label>
            <i data-feather="sidebar"></i>
          </li>

          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon">
                <i class='bx bx-user'></i>
              </span>
              <span class="pc-mtext">Usuarios</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_global/global_users">Nuevos</a>
              </li>
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_global/global_users_access">Permisos</a>
              </li>
              <li class="pc-item"><a class="pc-link" href="<?php echo constant('URL') ?>front/mod_global/global_user_logs">Acciones</a>
              </li>
            </ul>
          </li>
        <?php } ?>
      </ul>
    </div>
  </div>
</nav>


<!--
pruebas generales en red
tasa del dia en red