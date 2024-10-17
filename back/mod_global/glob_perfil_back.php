<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// header json
header('Content-Type: application/json');
// recibir data
$data = json_decode(file_get_contents('php://input'), true);

$current_pass = $data["current_pass"];
$new_pass = $data["new_pass"];
$new_pass_again = $data["new_pass_again"];
$id = $_SESSION["u_id"];



$stmt = mysqli_prepare($conexion, "SELECT * FROM `system_users` WHERE u_id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (password_verify($current_pass, $row['u_contrasena'])) {
        } else {
            echo json_encode(['error' => 'La contraseña actual no es correcta']);
            exit();
        }
    }
}

if ($new_pass != $new_pass_again) {
    echo json_encode(['error' => 'Las contraseñas no coinciden']);
    exit();
}




$passEncrypted = password_hash($new_pass, PASSWORD_BCRYPT);

$stmt2 = $conexion->prepare("UPDATE `system_users` SET `u_contrasena`=? WHERE u_id='$id'");
$stmt2->bind_param("s", $passEncrypted);
if ($stmt2->execute()) {
    echo json_encode(['success' => 'Contraseña actualizada con éxito']);
}
$stmt2->close();
