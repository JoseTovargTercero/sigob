<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';



if ($_SESSION["u_nivel"] != 1) {
    header("Location:" . constant('URL'));
}


$u_oficina_id = $_SESSION["u_oficina_id"];
$u_oficina = $_SESSION["u_oficina"];

function listaPermisosUser($id)
{ // cargar los permisos que tiene el usuario
    global $conexion;
    $permisos = [];

    $stmt = mysqli_prepare($conexion, "SELECT sup.id_item_menu, menu.nombre, menu.categoria FROM `system_users_permisos` AS sup
    LEFT JOIN menu ON menu.id = sup.id_item_menu
     WHERE id_user = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($permisos, [$row['id_item_menu'], $row['nombre'], $row['categoria']]);
        }
    }
    $stmt->close();
    return $permisos;
}

function verificarPermiso($item, $user)
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `system_users_permisos` WHERE id_item_menu=? AND id_user = ?");
    $stmt->bind_param('ii', $item, $user);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return true;
    }
    $stmt->close();
    return false;
}







if (@$_POST["tabla"]) { // tabla de usuarios

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
                "permisos" => listaPermisosUser($row["u_id"]),
                "creado" => $row["creado"]
            );
        }
    }
    $stmt->close();
    echo json_encode($datos);
} elseif (@$_POST['permisos']) {  // cargar los permisos que le pueden asignar/quitar al usuario

    $user = $_POST['user'];

    $datos = array();
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `menu` WHERE oficina = ?");
    $stmt->bind_param('s', $u_oficina);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $datos[] =  array(
                "id" => $row["id"],
                "categoria" => $row["categoria"],
                "nombre" => $row["nombre"],
                "icono" => $row["icono"],
                "permisos" => verificarPermiso($row['id'], $user)
            );
        }
    }
    $stmt->close();
    echo json_encode($datos, JSON_PRETTY_PRINT);
} elseif (@$_POST["set_permisos"]) {
    $user = $_POST["user"];
    $permiso = $_POST["permiso"];
    $status = $_POST["status"];


    // verificar al usuario administrador
    $stmt = mysqli_prepare($conexion, "SELECT u_oficina FROM `system_users` WHERE u_id = ?");
    $stmt->bind_param('i', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($u_oficina != $row['u_oficina']) {
                echo json_encode(['error' => 'No tiene permisos para modificar el nivel acceso de este usuario']);
                exit;
            }
        }
    }
    $stmt->close();


    // Modificar el acceso
    if ($status == 'true') { // eliminar

        $stmt = $conexion->prepare("DELETE FROM `system_users_permisos` WHERE id_user = ? AND id_item_menu = ?");
        $stmt->bind_param("ii", $user, $permiso);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Se quito el acceso al modulo']);
        } else {
            echo json_encode(['error' => 'No se pudo quitar el acceso al modulo']);
        }
        $stmt->close();
    } else { // registrar

        $stmt_o = $conexion->prepare("INSERT INTO `system_users_permisos` (id_user, id_item_menu) VALUES (?, ?)");
        $stmt_o->bind_param("ss", $user, $permiso);

        if ($stmt_o->execute()) {
            echo json_encode(['success' => 'Se agrego permisos nuevos al usuario']);
        } else {
            echo json_encode(['error' => 'No se pudo agregar el permiso']);
        }
        $stmt_o->close();
    }
}
