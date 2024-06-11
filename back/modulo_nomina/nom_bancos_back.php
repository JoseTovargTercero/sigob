<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

if (isset($_POST["tabla"])) {

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `bancos` ORDER BY nombre");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();

    if (@$data) {
        echo json_encode($data);
    }
} elseif (isset($_POST["registro"])) {

    $prefijo = clear($_POST["prefijo"]);
    $nombre = clear($_POST["nombre"]);

    $nombre = strtoupper($nombre); 


    $cuenta_matriz = clear($_POST["cuenta_matriz"]);
    $afiliado = clear($_POST["afiliado"]);


    //Comprobar que no exist
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `bancos` WHERE prefijo = ? LIMIT 1");
    $stmt->bind_param("s", $prefijo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode('ye');
    } else {
        $stmt->close();
        $stmt = mysqli_prepare($conexion, "INSERT INTO `bancos` (prefijo, nombre, matriz, afiliado) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $prefijo, $nombre, $cuenta_matriz, $afiliado);

        if ($stmt->execute()) {
            echo json_encode('ok');
        } else {
            echo json_encode("E: " . $stmt->error);
        }
        $stmt->close();
    }


} elseif (isset($_POST["eliminar"])) {

$id = $_POST["id"];


    $stmt = mysqli_prepare($conexion, "SELECT nombre FROM `bancos` WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombreBanco = $row['nombre'];
        $stmt->close();

      
        $stmt = mysqli_prepare($conexion, "SELECT count(*) FROM empleados WHERE banco = ?");
        $stmt->bind_param('s', $nombreBanco);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            // Prepara una declaración para eliminar el banco con el id dado
            $stmt_d = mysqli_prepare($conexion, "DELETE FROM `bancos` WHERE id = ?");
            $stmt_d->bind_param("i", $id);
            $stmt_d->execute();
            $stmt_d->close();
            echo json_encode('ok');
        }else {
            echo json_encode('negado');
        }
    } else {
        $stmt->close(); 
        echo json_encode('error');
    }

}
$conexion->close();

exit();