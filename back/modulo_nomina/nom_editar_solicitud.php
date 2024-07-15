<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

// Recibir el array de valores desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$empleado_id = 0;
$movimiento = "Se han modificado los campos: ";

// Iterar sobre el array recibido e insertar cada conjunto de valores
foreach ($data as $item) {
    $empleado_id = $item[0];
    $campo = $item[1];
    $valor = $item[2];
    $movimiento .= "$campo: $valor. ";
    
    $stmt2 = mysqli_prepare($conexion, "UPDATE empleados SET $campo = ? WHERE id = ?");
    $errores = array();
    $stmt2->bind_param('si', $valor, $empleado_id);
    if (!$stmt2->execute()) {
        array_push($errores, $campo);
    }
}


// inserta en la tabla movimientos

$id_nomina = '0';
$action = 'UPDATE';

$stmt_o = $conexion->prepare("INSERT INTO movimientos (id_empleado, id_nomina, accion, descripcion) VALUES (?,?,?,?)");
$stmt_o->bind_param("ssss", $empleado_id, $id_nomina, $action, $movimiento);
$stmt_o->execute();

// Cerrar la declaración de inserción
$conexion->close();
// Devolver una respuesta en JSONEC
echo json_encode(["errores" => $errores ]);
// $errores cuenta los campos que no se agregaron porque ya existia una peticion previa para ese usuario y ese campo
// verifica si tines algo dentro de errors, si esta vacio... Muestras success, si hay algun error, verifacas la cantidad de campos que no se actualizaron 
?>