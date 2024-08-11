<?php
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');




$data = [];

$stmt = mysqli_prepare($conexion, "SELECT users.u_nombre, reportes.* FROM `reportes`
LEFT JOIN system_users AS users ON users.u_id = reportes.user
");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[$row['id']] = $row;

    }
}
$stmt->close();

echo json_encode($data);



?>