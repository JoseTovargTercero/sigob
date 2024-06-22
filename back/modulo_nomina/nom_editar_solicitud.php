<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

// Recibir el array de valores desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Preparar la consulta SQL para la inserción
$sql_insert = "INSERT INTO modificaciones_empleados (empleado, campo, valor) VALUES (?, ?, ?)";
$stmt_insert = $conexion->prepare($sql_insert);

// Verificar si la preparación de la consulta fue exitosa
if ($stmt_insert === false) {
    die("Error en la preparación de la consulta de inserción: " . $conexion->error);
}

$errores = array();

// Iterar sobre el array recibido e insertar cada conjunto de valores
foreach ($data as $item) {
    $empleado_id = $item[0];
    $campo = $item[1];
    $valor = $item[2];

    // Verificar si existe una modificación pendiente
    $sql_check = "SELECT COUNT(*) FROM modificaciones_empleados WHERE empleado = ? AND campo = ?";
    $stmt_check = $conexion->prepare($sql_check);
    
    if ($stmt_check === false) {
        die("Error en la preparación de la consulta de verificación: " . $conexion->error);
    }

    $stmt_check->bind_param("is", $empleado_id, $campo);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count == 0) {
        $stmt_insert->bind_param("iss", $empleado_id, $campo, $valor);

        // Ejecutar la consulta
        if (!$stmt_insert->execute()) {p
            echo "Error al insertar: " . $stmt_insert->error;
        }
    } else {
        array_push($errores, $campo);
    }
}

if ($conexion->affected_rows > 0) { // en caso de que alguna modificacion se haya insertado
    notificar(['registro_control'], 2);
}

// Cerrar la declaración de inserción
$stmt_insert->close();
$conexion->close();

// Devolver una respuesta en JSON
echo json_encode(["errores" => $errores]);
// $errores cuenta los campos que no se agregaron porque ya existia una peticion previa para ese usuario y ese campo
// verifica si tines algo dentro de errors, si esta vacio... Muestras success, si hay algun error, verifacas la cantidad de campos que no se actualizaron 
?>
