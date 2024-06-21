<?php
include('../config/conexion.php');
include('../config/funcione_globales.php');


if ($_SESSION["u_nivel"]) {
  $i = $_GET["i"];

  $stmt2 = $conexion->prepare("UPDATE `notificaciones` SET `visto`='1' WHERE id=?");
  $stmt2->bind_param("s", $i);
  $stmt2->execute();
  $stmt2 -> close();

 $user_id = $_SESSION["u_id"];




  function contenidoExtra($var,$id, $com){
    if ($var == 9) {
      return '';
    }
    global $conexion;

    $tabla = ''; 
    $extraCondicion = ''; 
    
    if ($var == '1' || $var == '4' || $var == 5){
      $tabla = 'go_operaciones';
      $campo = 'id';
    }elseif ($var == '2' || $var == '3' || $var == '6' ||  $var ==  '16' ||  $var ==  '24'){
      $tabla = 'go_tareas';
      $campo = 'id_tarea';
    }elseif ($var == '11'){
      $tabla = 'go_planes';
      $campo = 'id';
    }elseif ($var == '14' || $var == '7' || $var == '8'){
      $tabla = 'go_tareas_responsables';
      $campo = 'id';
    }elseif ($var == '15'){
      $tabla = 'notificaciones';
      $campo = 'guia';
      $extraCondicion = " AND tipo='15'"; 
    }elseif ($var == '18' || $var == '19' ){
      $tabla = 'go_solicitud_union';
      $campo = 'id';
    }elseif ($var == '20' || $var == '21'  || $var == '25'  || $var == '26' ) {
      $tabla = 'com_compras';
      $campo = 'id';
    }elseif ($var == '22' || $var == '23' || $var == '27' || $var == '28' || $var == '29' || $var == '30' ) {
      $tabla = 'veh_vehiculos';
      $campo = 'id';
    }elseif ($var == '10') {
      $tabla = 'go_operaciones';
      $campo = 'id';
    }
    

    $stmt = mysqli_prepare($conexion, "SELECT  * FROM `$tabla` WHERE $campo='$id' $extraCondicion ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {

        if ($var == '1' || $var == '4' || $var == 5){
          return ': ' . $row['nombre'] . '<br>' . $row['descripcion'];
        }elseif ($var == '2' || $var == '3' || $var == '6' ||  $var ==  '16' ||  $var ==  '24'){
          return ': ' . $row['tarea'] . '<br>' . $row['descripcion'];
          // asigno una tarea, modifico una tarea, ejecuto una tarea, realizo un comenario, pidio sumars, reporto avances

        }elseif ($var == '11'){
          return ': ' . $row['nombre'] . ' ' . $row['ano'].'';
          // creo un nuevo plan sectorial
        }elseif ($var == '14' || $var == '7'){
          return ': ' . $row['responsabilidad'].'';
          // Confirmo participacion y le asigno una responsabilidad
        }elseif ($var == '8'){
          return ': ' . $row['responsabilidad'].'<br>Comentarios del usuario: '.$row['comentario'].'';
          // Rechazo participacion
        }elseif ($var == '15'){
          return ': '.$row['comentario'].'';
          // No pasa URL
        }elseif ($var == '18' || $var == '19'){
          return ':'.$row['descripcion'];
          // No pasa URL
        }elseif ($var == '20' || $var == '21' || $var == '25' ||  $var == '26' ) {
          return ': <strong>'.$row['nombre'].'</strong> fecha: <strong>'.fechaCastellano($row['fecha']).'</strong>';
        }elseif ($var == '10') {
          return ': ' . $row['nombre'];
        }elseif ($var == '27' || $var == '28') {
          $contenido = ': <strong>'.$row['marca'].' '.$row['modelo'].'</strong> ';


          $in = 0;

          $stmta = mysqli_prepare($conexion, "SELECT * FROM `veh_reporte_fallas` WHERE vehiculo = ? AND status='0'");
          $stmta->bind_param('s', $id);
          $stmta->execute();
          $resulta = $stmta->get_result();
          if ($resulta->num_rows > 0) {
            while ($row = $resulta->fetch_assoc()) {

              if ($row['gravedad'] == '1' || $in == 1) {
                $in = 1;
              }else {
                $in = 2;
              }

            }
          }
          $stmta->close();

          if ($in == 0) {
            $contenido .= 'Actualmente <span class="badge bg-label-primary"> EN FUNCIONAMIENTO</span>, ';
          }elseif ($in == 1) {
            $contenido .= 'Actualmente <span class="badge bg-label-danger"> INOPERATIVO</span>, ';
          } else {
            $contenido .= 'Actualmente <span class="badge bg-label-warning"> PRESENTANDO FALLAS</span>, ';
          }




          $stmtk = mysqli_prepare($conexion, "SELECT * FROM `go_tareas` WHERE id_tarea = ?");
          $stmtk->bind_param('s', $com);
          $stmtk->execute();
          $resultk = $stmtk->get_result();
          if ($resultk->num_rows > 0) {
            while ($rowk = $resultk->fetch_assoc()) {
              $contenido .= ' para la tarea: <strong>'.$rowk['tarea'].'</strong> Descripción: <strong>'.$rowk['descripcion'].'</strong> ';
          }
          }
          $stmtk->close();

          return $contenido;

        }elseif ($var == '30') {
          $contenido = ': <strong>'.$row['marca'].' '.$row['modelo'].'</strong> ';
                    return $contenido;

        }elseif ($var == '29') {
          return ': '.$row['marca'].' '.$row['modelo'].'. <strong>Comentarios del usuario:</strong> '.$com;

        }
        
        else {
          return '';
        }
      }
    }
  }

  $stmt = mysqli_prepare($conexion, "SELECT veh_vehiculos_tarea.status AS status_veh, notificaciones.comentario, go_tareas_responsables.operacion, go_tareas_responsables.status, go_tareas_responsables.tarea AS tareaId, notificaciones.id, notificaciones.tipo, notificaciones.guia, system_users.u_id, system_users.u_ente, system_users.u_nombre, notificaciones.date, notificaciones.guia, notificaciones.user_1  FROM `notificaciones` 
  LEFT JOIN system_users ON system_users.u_id = notificaciones.user_1 
  LEFT JOIN go_tareas_responsables ON go_tareas_responsables.id = notificaciones.guia 
  LEFT JOIN veh_vehiculos_tarea ON veh_vehiculos_tarea.vehiculo = notificaciones.guia AND veh_vehiculos_tarea.tarea = notificaciones.comentario
  WHERE notificaciones.id='$i'");

  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $id = $row['id'];
      $tipo = $row['tipo'];
      $guia = $row['guia'];
      $status = $row['status'];


      if ($tipo == '1' || $tipo == '4' || $tipo == 5  || $tipo == '10'){
        $link = '<a class=" btn btn-outline-primary" href="go_operacion.php?i='.$guia.'">Ver operación</a>';
      }elseif ($tipo == '2' || $tipo == '3' || $tipo == '6' ||  $tipo ==  '16' ||  $tipo ==  '24'){
        $link = '<a class=" btn btn-outline-primary" href="go_tarea.php?t='.$row['guia'].'">Ver tarea</a>';
      }elseif ($tipo == '11'){
      //  $link = '<a class=" btn btn-outline-primary" href="go_adm_planes_detalles.php?p='.$guia.'">Ver plan</a>';
      $link = '';
      
      }elseif ($tipo == '7'){
        $link = '<a class=" btn btn-outline-primary" href="go_tarea.php?t='.$row['tareaId'].'">Ver tarea</a>';
      }elseif ($tipo == '8'){
        $link =  '<a class=" btn btn-outline-primary" href="go_operacion.php?i='.$row['operacion'].'&v='.$guia.'#involucrados_pendiente">Ver responsabilidad</a>';
      }elseif ($tipo == '9'){
        $link =  '<a class=" btn btn-outline-primary" href="go_operacion.php?i='.$row['guia'].'&m=m">Ver mensaje</a>';
      }elseif ($tipo == '20' || $tipo == '21' || $tipo == '25' || $tipo == '26') {
        $link =  '<a class=" btn btn-outline-primary" href="com_gestor_compras.php?i='.$row['guia'].'">Ver compras</a>';
      }elseif ($tipo == '22' || $tipo == '23' || $tipo == '30' ) {
        $link =  '<a class=" btn btn-outline-primary" href="veh_vehiculo.php?i='.$row['guia'].'">Ver vehículo</a>';
      }else{
        $link = '';
      }

      echo '<div class="list-group-item list-group-item-action dropdown-notifications-item mt-3">
      <div class="d-flex">
        <div class="flex-shrink-0 me-3">
          <div class="avatar">';
      if (file_exists('../../assets/img/avatars/' . $row['u_id'] . '.png')) {
        echo ' <img src="../../assets/img/avatars/' . $row['u_id'] . '.png" alt="logo ' . $row['u_ente'] . '" class="rounded-circle" title="' . $row['u_ente'] . '">';
      } else {
        echo '  <span class="avatar-initial rounded-circle bg-label-danger"> ' . substr($row['u_ente'], 0, 1) . '</span>';
      }
      echo '
          </div>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-1">' . $row['u_ente'] . '</h6>
          <p class="mb-0"><span class="text-primary">' . textNotification($tipo) . '</span>'.contenidoExtra($tipo, $guia,$row['comentario']);
          if ($tipo == '14') {
            

            $stmtt = mysqli_prepare($conexion, "SELECT tarea, fecha, tipo_ejecucion FROM `go_tareas` WHERE id_tarea = ?");
            $stmtt->bind_param('s', $guia);
            $stmtt->execute();
            $resultt = $stmtt->get_result();
            if ($resultt->num_rows > 0) {
              while ($row2 = $resultt->fetch_assoc()) {
                echo ' <strong>en la tarea: </strong>'.$row2['tarea'].($row2['tipo_ejecucion'] == '1' ? ' <b>el</b> '.fechaCastellano($row2['fecha']) : ' <b>(Tarea trimestral)</b>');
              }
            }
            $stmtt->close();
          }
          echo '</p>
          <div class=" d-flex justify-content-between">
          <small class="text-muted">' . dateToTimeAgo($row['date']) . '</small>
          </div>
        </div>';

        echo '
     </div>';

     if ($row['status'] == '0' && $tipo == '14') {
      echo '
      <hr>
      <div class="text-center">
        <button type="button" class="btn btn-outline-secondary me-2" onclick="pre_rechazar()">Rechazar participación</button>
        <button type="button" class="btn btn-primary" onclick="aceptar()">Confirmar participación</button>
      </div>';
    }elseif ($row['status_veh'] == '0' && $tipo == '27') {
     echo '
     <hr>
     <div class="text-center">
       <button type="button" class="btn btn-outline-secondary me-2" onclick="pre_rechazar_v(\''.$row['guia'].'\', \''.$row['comentario'].'\')">Rechazar</button>
       <button type="button" class="btn btn-primary" onclick="aceptar_v(\''.$row['guia'].'\', \''.$row['comentario'].'\')">Confirmar</button>
     </div>';
   }elseif ($row['status'] == '1' || $tipo != '') {
      echo '
      <hr>
      <div class="text-center">
      '.$link.'
        <button type="button" class="btn btn-outline-secondary me-2"  data-bs-dismiss="modal" aria-label="Close">Cerrar</button>
      </div>';
    }










    }
  }
  $stmt->close();


  /* Obtener el cuerpo de la notificacion*/

}else {
  header("Location: ../../public/index.php");
}
