<?php


/**
 * Returns the text for a given notification type.
 *
 * @param int $type The type of notification.
 * @return string The text for the given notification type.
 */
function textNotification($type)
{
  $msgTextNotification = array(
    '1' => ['Inicio el pago de una nomina', 'front/mod_registro_control/regcon_nomina_comparar'],
    '2' => ['Solicito modificar los datos de un empleado', 'front/mod_registro_control/regcon_modificacion_empleados'],
    '3' => ['Envió a corrección los datos de un empleado', 'front/mod_nomina/nom_empleados_tabla'],
    '4' => ['Rechazo la creación de un empleado', ''],
    '5' => ['Aprobó la creación de un empleado', 'front/mod_nomina/nom_empleados_tabla'],
    '6' => ['Rechazo la modificación de un empleado', ''],
    '7' => ['Aprobó la modificación de un empleado', 'front/mod_nomina/nom_empleados_tabla'],
    '8' => ['Rechazó la petición de nomina', 'front/mod_nomina/nom_peticiones_tabla'],

    // mas opciones
  );
  return $msgTextNotification[$type];
}



/**
 * Notifies the specified users of the specified event.
 *
 * @param array $user_2 The users to notify.
 * @param string $type The type of event.
 */


function notificar($user_2, $type)
{
  global $conexion;
  $t_notifacion = textNotification($type, 1);

  $texto = $t_notifacion[0];
  $guia = constant('URL') . $t_notifacion[1];

  $user_1 = $_SESSION["u_id"];
  $stmt_o = $conexion->prepare("INSERT INTO notificaciones (user_1,user_2,tipo,guia,comentario) VALUES (?, ?, ?, ?, ?)");

  foreach ($user_2 as $user_item) {
    if ($user_item != $_SESSION["u_id"]) {
      $array_users = usuarios_x_oficinas($user_item);

      foreach ($array_users as $item) {
        $stmt_o->bind_param("sssss", $user_1, $item, $type, $guia, $texto);
        $stmt_o->execute();
      }


    }
  }

  $stmt_o->close();
}


function usuarios_x_oficinas($oficina = null)
{
  global $conexion;

  $usrs = array();
  $stmt = mysqli_prepare($conexion, "SELECT u_id FROM `system_users` WHERE u_oficina = ?");
  $stmt->bind_param('s', $oficina);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      array_push($usrs, $row['u_id']);
    }
  }
  $stmt->close();

  return $usrs;
}
