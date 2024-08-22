<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';


if (isset($_POST["movimientos"])) {

    $u_id = $_SESSION["u_id"];
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `movimientos` WHERE campo='status' AND usuario_id = '$u_id'");
    if ($stmt->execute()) {

        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        if (@$data) {
            echo json_encode($data);
        } else{
            echo json_encode(['status' => 'vacio']);
        }
    }else{
        echo json_encode(['error' => 'Error en la ejecución']);

    }
    $stmt->close();


} elseif (isset($_POST["eliminar"])) {
    $id_movimiento = $_POST["id"];


    $stmt = mysqli_prepare($conexion, "SELECT valor_anterior, id_empleado FROM `movimientos` WHERE `id` = '$id_movimiento'");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $valor_anterior = $row['valor_anterior'];
            $id_empleado = $row['id_empleado'];
        }
    }
    $stmt->close();


    // Update the status of an employee in the database to the previous value
    $stmt = MySQLi_prepare($conexion, "UPDATE `empleados` SET `status` = ? WHERE `id` = ?");
    MySQLi_stmt_bind_param($stmt, 'ss', $valor_anterior, $id_empleado);
    $stmt->execute();
    $stmt->close();




    // eliminar el movimiento
    $stmt = mysqli_prepare($conexion, "DELETE FROM `movimientos` WHERE id = '$id_movimiento'");
    $stmt->execute();
    $stmt->close();


    echo json_encode(['status' =>  "success"]);
}




$conexion->close();
