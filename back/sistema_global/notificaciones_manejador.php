<?php
include 'conexion.php';
include 'session.php';


if (isset($_POST["tabla"])) {
  $notificaciones = array();

  $user = $_SESSION["u_id"];
  $stmt = mysqli_prepare($conexion, "SELECT 
  notificaciones.guia, notificaciones.date, notificaciones.comentario, notificaciones.id AS id_notificacion,
  system_users.u_oficina, system_users.u_nombre 
  FROM `notificaciones` 
  LEFT JOIN system_users ON system_users.u_id = notificaciones.user_1
  WHERE user_2 = ? AND visto = '0'");
  $stmt->bind_param('s', $user);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      array_push($notificaciones, $row);
    }
  }
  $stmt->close();

  echo json_encode($notificaciones);


}elseif (isset($_POST["visto"])) {

  $id = $_POST["notificacion"];

  $stmt2 = $conexion->prepare("UPDATE `notificaciones` SET `visto`='1' WHERE id=?");
  $stmt2->bind_param("s", $id);
  if ($stmt2->execute()) {
    echo json_encode(['text'=>'ok']);
  }else {
    echo json_encode(['text'=>'error']);
  }
  $stmt2 -> close();


}