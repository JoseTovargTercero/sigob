<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

// Recibir el array de valores desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$empleado_id = 0;
$movimiento = "Se han modificado los campos: ";
$errores = array();

// Iterar sobre el array recibido e insertar cada conjunto de valores
foreach ($data as $item) {
    $empleado_id = $item[0];
    $campo = $item[1];
    $valor = $item[2];
    $movimiento .= "$campo: $valor. ";
    
    $stmt2 = mysqli_prepare($conexion, "UPDATE empleados SET $campo = ? WHERE id = ?");
    $stmt2->bind_param('si', $valor, $empleado_id);
    if (!$stmt2->execute()) {
        array_push($errores, $campo);
    }
    $stmt2->close();
}

// Array para almacenar los ids de nómina únicos
$tipo_nomina = array();

// Consultar la tabla conceptos_aplicados
$stmt = $conexion->prepare("SELECT id, nombre_nomina, empleados FROM conceptos_aplicados");
if (!$stmt) {
    die("Error en la preparación de la declaración SELECT: " . $conexion->error);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $empleados_array = json_decode($row['empleados'], true);

    // Verificar si el empleado_id está en el array de empleados
    if (in_array($empleado_id, $empleados_array)) {
        // Obtener el id de la tabla nominas basado en el nombre_nomina
        $nomina_stmt = $conexion->prepare("SELECT id FROM nominas WHERE nombre = ?");
        if (!$nomina_stmt) {
            die("Error en la preparación de la declaración SELECT nominas: " . $conexion->error);
        }
        $nomina_stmt->bind_param('s', $row['nombre_nomina']);
        $nomina_stmt->execute();
        $nomina_result = $nomina_stmt->get_result();

        if ($nomina_result->num_rows > 0) {
            $nomina_row = $nomina_result->fetch_assoc();
            $nomina_id = $nomina_row['id'];

            // Agregar el id de nomina al array tipo_nomina si no está ya presente
            if (!in_array($nomina_id, $tipo_nomina)) {
                $tipo_nomina[] = $nomina_id;
            }
        }
        $nomina_stmt->close();
    }
}
$stmt->close();

// Preparar la información para insertar en la tabla movimientos
$id_nomina = json_encode($tipo_nomina);
$accion = 'UPDATE';
$fecha_movimiento = date('Y-m-d H:i:s');

// Insertar en la tabla movimientos
$stmt_o = $conexion->prepare("INSERT INTO movimientos (id_empleado, id_nomina, fecha_movimiento, accion, descripcion, status) VALUES (?, ?, ?, ?, ?, 1)");
if (!$stmt_o) {
    die("Error en la preparación de la declaración INSERT movimientos: " . $conexion->error);
}
$stmt_o->bind_param("issss", $empleado_id, $id_nomina, $fecha_movimiento, $accion, $movimiento);
$stmt_o->execute();
$stmt_o->close();

// Cerrar la conexión
$conexion->close();

// Devolver una respuesta en JSON
echo json_encode(["errores" => $errores]);

?>
