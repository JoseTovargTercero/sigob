<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';


    $stmt = mysqli_prepare($conexion, "SELECT prefijo, nombre, id FROM `bancos` ORDER BY nombre");
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
