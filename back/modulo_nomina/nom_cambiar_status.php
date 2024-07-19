<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Array para almacenar los IDs de nómina
$tipo_nomina = array();

foreach ($data as $item) {
    if (empty($item['id']) || empty($item['value'])) {
        echo "Error: el campo 'id' o 'value' no puede estar vacío.";
        exit;
    }

    $id = $item['id'];
    $valor = $item['value'];

    // Verificar en la tabla conceptos_aplicados
    $sql_conceptos = "SELECT DISTINCT nombre_nomina FROM conceptos_aplicados WHERE JSON_CONTAINS(empleados, '\"$id\"', '$')";
    $result_conceptos = $conexion->query($sql_conceptos);

    if ($result_conceptos->num_rows > 0) {
        while ($row = $result_conceptos->fetch_assoc()) {
            $nombre_nomina = $row['nombre_nomina'];

            // Buscar en la tabla nomina
            $sql_nomina = "SELECT id FROM nominas WHERE nombre = ?";
            $stmt_nomina = $conexion->prepare($sql_nomina);

            if (!$stmt_nomina) {
                die("Error en la preparación de la declaración SELECT nomina: " . $conexion->error);
            }

            $stmt_nomina->bind_param('s', $nombre_nomina);
            $stmt_nomina->execute();
            $result_nomina = $stmt_nomina->get_result();

            if ($result_nomina->num_rows > 0) {
                $row_nomina = $result_nomina->fetch_assoc();
                $id_nomina = $row_nomina['id'];

                // Agregar el id_nomina al array si no está ya presente
                if (!in_array($id_nomina, $tipo_nomina)) {
                    $tipo_nomina[] = $id_nomina;
                }
            }
            $stmt_nomina->close();
        }
    }

    // Actualizar el campo status en la tabla empleados
    $sql = "UPDATE empleados SET status = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error en la preparación de la declaración UPDATE: " . $conexion->error);
    }

    $stmt->bind_param('si', $valor, $id);

    if (!$stmt->execute()) {
        echo "Error al actualizar el status del empleado con id: $id" . $conexion->error;
        exit;
    }

    $stmt->close();
}

// Insertar en la tabla movimientos una sola vez
if ($valor == 'A') {
    $fecha_movimiento = date('Y-m-d H:i:s');
    $accion = 'UPDATE';
    $descripcion = "Cambio de Status a Activo de empleado: $id";
    $status = 1;
    $tipo_nomina_json = json_encode($tipo_nomina);

    $stmt_mov = $conexion->prepare("INSERT INTO movimientos (id_empleado, id_nomina, fecha_movimiento, accion, descripcion, status) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt_mov) {
        die("Error en la preparación de la declaración INSERT movimientos: " . $conexion->error);
    }
    $stmt_mov->bind_param("issssi", $id, $tipo_nomina_json, $fecha_movimiento, $accion, $descripcion, $status);
    $stmt_mov->execute();
    $stmt_mov->close();
} elseif ($valor == 'S') {
    $fecha_movimiento = date('Y-m-d H:i:s');
    $accion = 'UPDATE';
    $descripcion = "Cambio de Status a Suspendido de empleado: $id";
    $status = 1;
    $tipo_nomina_json = json_encode($tipo_nomina);

    $stmt_mov = $conexion->prepare("INSERT INTO movimientos (id_empleado, id_nomina, fecha_movimiento, accion, descripcion, status) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt_mov) {
        die("Error en la preparación de la declaración INSERT movimientos: " . $conexion->error);
    }
    $stmt_mov->bind_param("issssi", $id, $tipo_nomina_json, $fecha_movimiento, $accion, $descripcion, $status);
    $stmt_mov->execute();
    $stmt_mov->close();
} elseif ($valor == 'C') {
   $fecha_movimiento = date('Y-m-d H:i:s');
    $accion = 'UPDATE';
    $descripcion = "Cambio de Status a Comision de Servicio de empleado: $id";
    $status = 1;
    $tipo_nomina_json = json_encode($tipo_nomina);

    $stmt_mov = $conexion->prepare("INSERT INTO movimientos (id_empleado, id_nomina, fecha_movimiento, accion, descripcion, status) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt_mov) {
        die("Error en la preparación de la declaración INSERT movimientos: " . $conexion->error);
    }
    $stmt_mov->bind_param("issssi", $id, $tipo_nomina_json, $fecha_movimiento, $accion, $descripcion, $status);
    $stmt_mov->execute();
    $stmt_mov->close();
} else {
   $fecha_movimiento = date('Y-m-d H:i:s');
    $accion = 'UPDATE';
    $descripcion = "Cambio de Status a Retirado de empleado: $id";
    $status = 1;
    $tipo_nomina_json = json_encode($tipo_nomina);

    $stmt_mov = $conexion->prepare("INSERT INTO movimientos (id_empleado, id_nomina, fecha_movimiento, accion, descripcion, status) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt_mov) {
        die("Error en la preparación de la declaración INSERT movimientos: " . $conexion->error);
    }
    $stmt_mov->bind_param("issssi", $id, $tipo_nomina_json, $fecha_movimiento, $accion, $descripcion, $status);
    $stmt_mov->execute();
    $stmt_mov->close();
}




       

// Si valor es 'R', eliminar al empleado de conceptos_aplicados
foreach ($data as $item) {
    if ($item['value'] === 'R') {
        $id = $item['id'];
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
}

$conexion->close();

echo json_encode(["status" => "success", "mensaje" => "Los status de los empleados fueron modificados correctamente"]);
?>