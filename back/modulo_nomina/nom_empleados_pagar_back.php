<?php
require_once '../sistema_global/conexion.php';

if (isset($_POST["select"])) {
    $data = array();
    $grupo = $_POST["grupo"];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas` WHERE grupo_nomina = ?");
    $stmt->bind_param('s', $grupo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = array(
                'nombre' => $row['nombre'],
                'frecuencia' => $row['frecuencia']
            );
        }
    }
    $stmt->close();

    echo json_encode($data);
}
?>
