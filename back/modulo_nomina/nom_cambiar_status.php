<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Verificar si algún campo está vacío
foreach ($data as $key => $value) {
    if ($value === '') {
        echo "Error: el campo $key no puede estar vacío.";
        exit;
    }
}

// Verificar si el ID está presente y no está vacío
if (empty($data['id'])) {
    echo "Error: el campo id no puede estar vacío.";
    exit;
}

$id = $data['id'];
$valor = $data['value'];

// Actualizar el campo status en la tabla empleados
$sql = "UPDATE empleados SET status = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error en la preparación de la declaración UPDATE: " . $conexion->error);
}

$stmt->bind_param('ss', $valor, $id);

if (!$stmt->execute()) {
    echo "Error al actualizar el status del empleado: " . $conexion->error;
    exit;
}

$stmt->close();

// Si valor es 'R', eliminar al empleado de conceptos_aplicados
if ($valor === 'R') {
    // Consultar y actualizar conceptos_aplicados
    $stmt = $conexion->prepare("SELECT id, nombre_nomina, empleados FROM conceptos_aplicados");
    if (!$stmt) {
        die("Error en la preparación de la declaración SELECT: " . $conexion->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $empleados_array = json_decode($row['empleados'], true);

        // Verificar si el id está en el array de empleados
        if (in_array($id, $empleados_array)) {
            // Eliminar el id del array de empleados
            $new_empleados_array = array_diff($empleados_array, array($id));

            // Convertir el array de empleados de vuelta a JSON
            $empleados_json = json_encode(array_values($new_empleados_array));

            // Actualizar la tabla conceptos_aplicados con el nuevo array de empleados
            $update_stmt = $conexion->prepare("UPDATE conceptos_aplicados SET empleados = ? WHERE id = ?");
            if (!$update_stmt) {
                die("Error en la preparación de la declaración UPDATE: " . $conexion->error);
            }

            $update_stmt->bind_param('si', $empleados_json, $row['id']);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }

    $stmt->close();
}

$conexion->close();

echo json_encode(["status" => "success", "mensaje" => "El status del empleado fue modificado correctamente"]);
?>
