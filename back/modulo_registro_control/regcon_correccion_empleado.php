<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

// Verificar si el parámetro 'id' está presente en la URL
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $comentario = $_POST['comentario'];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `empleados` WHERE id = ? AND verificado = '0'");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {

        $stmt2 = $conexion->prepare("UPDATE `empleados` SET `verificado`='2', `correcion`=? WHERE id=?");
        $stmt2->bind_param("ss", $comentario, $id);
        if ($stmt2->execute()) {
            echo json_encode("ok");
            notificar(['nomina'], 3);
        } else {
            echo json_encode("error");
        }
        $stmt2->close();

    } else {
        echo json_encode("No se ha podido completar la acción");
    }
    $stmt->close();
} else {
    echo json_encode("No se ha proporcionado un ID.");
}

// Cerrar la conexión
$conexion->close();
