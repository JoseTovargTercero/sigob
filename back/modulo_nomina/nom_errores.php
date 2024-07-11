<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';


if (isset($_POST["corregir_cargo"])) {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $grado = $_POST['grado'];

    $sql = "SELECT * FROM cargos_grados WHERE cod_cargo = '$codigo'";
    $result = $conexion->query($sql);
    if ($result->num_rows > 0) {
        echo json_encode(['status' => false, 'mensaje' => 'El código de cargo ya existe']);
    } else {
        $stmt_o = $conexion->prepare("INSERT INTO cargos_grados (cod_cargo, cargo, grado) VALUES (?, ?, ?)");
        $stmt_o->bind_param("sss", $codigo, $nombre, $grado);
        if ($stmt_o->execute()) {
            echo json_encode(['status' => true, 'mensaje' => 'Cargo registrado correctamente']);
        }else {
            echo json_encode(['status' => false, 'mensaje' => 'Error al registrar el cargo']);
        }
        $stmt_o->close();
    }
}else {
    echo json_encode(['status' => false, 'mensaje' => 'No se recibio ninguna accion']);
}

$conexion->close();
