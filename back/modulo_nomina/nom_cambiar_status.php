<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
$id_user = $_SESSION["u_id"];

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Array para almacenar los IDs de nómina
$tipo_nomina = array();

// Preparar Consultas
// Preparar Consultas

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


    // OBTENER DATOS DEL EMPLEADO
    $stmt_datos_empleado = mysqli_prepare($conexion, "SELECT status, cedula FROM empleados WHERE id = ?");
    $stmt_datos_empleado->bind_param('s', $id);
    $stmt_datos_empleado->execute();
    $stmt_datos_empleado->bind_result($valor_anterior, $cedula_e);
    $stmt_datos_empleado->fetch();
    $stmt_datos_empleado->close();
    // OBTENER DATOS DEL EMPLEADO

    // Actualizar el campo status en la tabla empleados
    $stmt_update = mysqli_prepare($conexion, "UPDATE empleados SET status = ? WHERE id = ?");
    $stmt_update->bind_param('si', $valor, $id);
    if (!$stmt_update->execute()) {
        echo "Error al actualizar el status del empleado con id $id" . $conexion->error;
        exit;
    }
    $stmt_update->close();


    // Actualizar el campo status en la tabla empleados

    /*
    if ($valor == 'A') {
        // Se elimina el movimiento si no ha pasado por revición (Que el movimiento este en status NO REVISADO)
        exit();
    }else{
    */
    $prefix = 'Modificó el estatus del empleado ';
    $tipo_nomina_json = json_encode($tipo_nomina);

    $valores = array(
        'A' => array(
            'accion' => 'ACTIVÓ',
            'descripcion' => $prefix . $cedula_e . ' a ACTIVO'
        ),
        'R' => array(
            'accion' => 'RETIRÓ',
            'descripcion' => $prefix . $cedula_e . ' a RETIRADO'
        ),
        'S' => array(
            'accion' => 'SUSPENDIÓ',
            'descripcion' => $prefix . $cedula_e . ' a SUSPENDIDO'
        ),
        'C' => array(
            'accion' => 'COLOCÓ EN COMISIÓN DE SERVICIO',
            'descripcion' => $prefix . $cedula_e . ' a COMISIÓN DE SERVICIO'
        )
    );

    $accion = $valores[$valor]['accion'];
    $descripcion = $valores[$valor]['descripcion'];
    $fecha_movimiento = date('Y-m-d H:i:s');
    $tabla_info = ['empleados', 'status'];

    // ISERTAR EL MOVIMIENTO
    $stmt_mov = mysqli_prepare($conexion,"INSERT INTO movimientos (id_empleado, id_nomina, fecha_movimiento, accion, tabla, campo, descripcion, valor_anterior, valor_nuevo, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_mov->bind_param("isssssssss", $id, $tipo_nomina_json, $fecha_movimiento, $accion, $tabla_info[0], $tabla_info[1], $descripcion, $valor_anterior, $valor,  $id_user);
    $stmt_mov->execute();
    $stmt_mov->close();
    // ISERTAR EL MOVIMIENTO
    //}
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

