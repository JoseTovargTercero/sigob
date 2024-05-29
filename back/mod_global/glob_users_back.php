<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

if ($_SESSION["u_nivel"] != 1) {
    header("Location:".constant('URL'));
}


$u_oficina_id = $_SESSION["u_oficina_id"];
$u_oficina = $_SESSION["u_oficina"];

function verificarPermiso($id){
    global $conexion;
    global $u_oficina_id;

    $stmt = mysqli_prepare($conexion, "SELECT u_oficina_id FROM `system_users` WHERE u_id = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stmt->close();
            return ($u_oficina_id == $row['u_oficina_id'] ? true : false);
        }
    }
    $stmt->close();
    return false;
}


if (@$_POST["tabla"]) {
    
    $datos = array();
    
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `system_users` WHERE u_oficina_id = ? AND u_nivel!='1'");
    $stmt->bind_param('s', $u_oficina_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $datos[] =  array(
                "u_id" => $row["u_id"],
                "u_nombre" => $row["u_nombre"],
                "u_status" => $row["u_status"],
                "creado" => $row["creado"]
            );
        }
    }
    $stmt->close();
    echo json_encode($datos);
    
}elseif (@$_POST["eliminar"]) {
    $id = $_POST["id"];
    if (verificarPermiso($id)) {
        $stmt = $conexion->prepare("DELETE FROM `system_users` WHERE u_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        if ($stmt) {
            echo 'ok';
        } else {
            echo 'error';
        }
    }
}elseif (@$_POST["bloquear"]) {
    $id = $_POST["id"];
    if (verificarPermiso($id)) {

        $stmt = mysqli_prepare($conexion, "SELECT * FROM `system_users` WHERE u_id = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $n_estatus =  ($row['u_status'] == '1' ? '0' : '1');
        }
        }
        $stmt->close();


        $stmt2 = $conexion->prepare("UPDATE `system_users` SET `u_status`='$n_estatus' WHERE u_id=?");
        $stmt2->bind_param("s", $id);
        $stmt2->execute();
        if ($stmt2) {
            echo 'ok';
        }
        $stmt2 -> close();
    }
}elseif (@$_POST["registro"]) {
    $nombre = $_POST["nombre"];
    $mail = $_POST["mail"];
    $pass1 = $_POST["pass1"];
    $pass2 = $_POST["pass2"];

    if ($pass1 != $pass2) {
        echo 'pass';
        exit();
    }


    $stmt = mysqli_prepare($conexion, "SELECT u_email FROM `system_users` WHERE u_email = ?");
    $stmt->bind_param('s', $mail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
    echo 'existe';
    exit();
    }
    $stmt->close();








	$pass = password_hash($pass1, PASSWORD_BCRYPT);

    $stmt_o = $conexion->prepare("INSERT INTO system_users (u_nombre, u_oficina_id, u_oficina, u_email, u_contrasena, u_nivel) VALUES (?,?,?,?,?,'2')");
    $stmt_o->bind_param("sssss", $nombre, $u_oficina_id, $u_oficina, $mail, $pass);
    $stmt_o->execute();

    if ($stmt_o) {
    echo "ok";
    }else {
    echo "error";
    }
    $stmt_o->close();



}


?>
