<?php
require_once '../sistema_global/conexion.php';

// LINEA DE SESIÓN EVITABA QUE AVANZARA EN EL FORMULARIO
// require_once '../sistema_global/session.php';


$accion = $_POST["accion"];

// Función para generar un token único
function generateToken()
{
    //return bin2hex(random_bytes(32));
    return '123456';
}

// Función para guardar el token en la base de datos
function storeToken($conexion, $userEmail, $token)
{
    // Establecer el tiempo de expiración (por ejemplo, 1 hora a partir de ahora)
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Eliminar cualquier token previo del usuario
    $stmt = $conexion->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $stmt->close();

    // Insertar el nuevo token en la base de datos
    $stmt = $conexion->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $userEmail, $token, $expires);
    $stmt->execute();
    $stmt->close();
}
// Función para enviar el token por correo electrónico
function sendTokenByEmail($userEmail, $token)
{
    $resetLink = "https://yourwebsite.com/reset_password.php?token=" . $token;
    $subject = "Solicitud de restablecimiento de contraseña";
    $message = "Haga clic en el siguiente enlace para restablecer su contraseña: " . $resetLink;
    $headers = "From: no-reply@yourwebsite.com";

    //  mail($userEmail, $subject, $message, $headers);
    return true;
}




// verificar status del usuario
if (isset($_POST["email"])) {
    $email = $_POST["email"];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `system_users` WHERE u_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['u_status'] != '1') {
                echo json_encode(["response" => "baneado"]);
                exit();
            }
        }
    }
    $stmt->close();

}



// Función para validar el token con límite de intentos
function validateToken($conexion, $email, $token)
{
    $max_attempts = 5;  // Número máximo de intentos permitidos

    $stmt = $conexion->prepare("SELECT token, expires, attempts, last_attempt FROM password_resets WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $db_token = $row['token'];
        $expires = $row['expires'];
        $attempts = $row['attempts'];
        $last_attempt = $row['last_attempt'];

        // Verificar si el número de intentos ha excedido el límite
        if ($attempts >= $max_attempts) {

            $stmt2 = $conexion->prepare("UPDATE `system_users` SET `u_status`='0' WHERE u_email=?");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $stmt2->close();

            $stmt_d = $conexion->prepare("DELETE FROM `password_resets` WHERE email = ?");
            $stmt_d->bind_param("i", $email);
            $stmt_d->execute();
            $stmt_d->close();



            return ['valid' => false, 'message' => 'Maximo intentos'];

        }

        // Verificar si el token es correcto y ha expirado
        if ($db_token === $token && strtotime($expires) > time()) {
            // Actualizar el número de intentos y la hora del último intento
            $attempts++;
            $last_attempt = date('Y-m-d H:i:s');
            $stmt2 = $conexion->prepare("UPDATE password_resets SET attempts = ?, last_attempt = ? WHERE email = ?");
            $stmt2->bind_param('iss', $attempts, $last_attempt, $email);
            $stmt2->execute();
            $stmt2->close();

            return ['valid' => true, 'message' => 'token valido'];
        } else {
            // Incrementar el número de intentos aunque el token sea incorrecto
            $attempts++;
            $last_attempt = date('Y-m-d H:i:s');
            $stmt2 = $conexion->prepare("UPDATE password_resets SET attempts = ?, last_attempt = ? WHERE email = ?");
            $stmt2->bind_param('iss', $attempts, $last_attempt, $email);
            $stmt2->execute();
            $stmt2->close();

            return ['valid' => false, 'message' => 'token invalido o expirado'];
        }
    } else {
        return ['valid' => false, 'message' => 'email invalido'];
    }

    $stmt->close();
}




// Función para actualizar la contraseña
function updatePassword($conexion, $email, $new_password)
{
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $conexion->prepare("UPDATE system_users SET u_contrasena = ? WHERE u_email = ?");
    $stmt->bind_param('ss', $hashed_password, $email);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
    $stmt->close();
}





// MANEJAR SOLICITUDES


if (isset($accion) && $accion == 'consulta') {
    $email = $_POST["email"];

    $stmt = mysqli_prepare($conexion, "SELECT u_id FROM `system_users` WHERE u_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Generar el token
        $token = generateToken();

        // Enviar el token por correo electrónico
        if (sendTokenByEmail($email, $token)) {
            // Guardar el token en la base de datos
            storeToken($conexion, $email, $token);
            echo json_encode(["response" => "email valido", 'valid' => true]);
        } else {
            echo json_encode(["response" => "error conexion"]);
        }


    } else {
        echo json_encode(["response" => "email no existe", 'valid' => false]);
    }
    $stmt->close();

    // Cerrar la conexión
    $conexion->close();

}// Recibir el token por POST
elseif ($accion == 'token' && isset($_POST['token'])) {
    $token = $_POST['token'];
    $email = $_POST["email"];

    // Validar el token
    $validationResult = validateToken($conexion, $email, $token);

    if ($validationResult['valid']) {
        // Token válido, proceder con la recuperación de la cuenta o el restablecimiento de la contraseña
        echo json_encode(["response" => $validationResult['message'], "valid" => $validationResult['valid']]);
        $_SESSION['email'] = $email;
        // Aquí puedes redirigir al usuario a la página de restablecimiento de contraseña
    } else {
        // Token no válido o expirado
        echo json_encode(["response" => $validationResult['message'], "valid" => $validationResult['valid']]);
    }
} elseif ($accion == 'pass' && isset($_SESSION['email']) && isset($_POST['token']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $email = $_SESSION['email'];
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validar si las contraseñas coinciden
    if ($password !== $confirm_password) {
        echo json_encode(["response" => 'contraseñas no coinciden']);

        exit();
    }

    // Validar el token
    $validationResult = validateToken($conexion, $email, $token);

    if ($validationResult['valid']) {
        // Token válido, actualizar la contraseña
        // if (updatePassword($conexion, $validationResult['email'], $password)) {

        if (updatePassword($conexion, $email, $password)) {
            echo json_encode(["response" => 'contraseña actualizada', "valid" => $validationResult['valid']]);
            // Aquí puedes redirigir al usuario a la página de inicio de sesión o a otra página
        } else {
            echo json_encode(["response" => 'no se pudo actualizar la contraseña', "valid" => $validationResult['valid']]);
        }
    } else {
        // Token no válido o expirado
        echo $validationResult['message'];
    }
}
?>