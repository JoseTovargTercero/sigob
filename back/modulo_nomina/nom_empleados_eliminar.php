<?php

require_once '../sistema_global/conexion.php';

// Verificar si el parámetro 'id' está presente en la URL
$conexion->autocommit(false);
$conexion->begin_transaction();



try {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $tipo_nomina = array();
        // Consultar la tabla conceptos_aplicados
        $stmt = $conexion->prepare("SELECT id, nombre_nomina, empleados FROM conceptos_aplicados");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la declaración SELECT: $conexion->error");
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $empleados_array = json_decode($row['empleados'], true);

            // Verificar si el id está en el array de empleados
            if (in_array($id, $empleados_array)) {
                // Obtener el id de la tabla nominas basado en el nombre_nomina
                $nomina_stmt = $conexion->prepare("SELECT id FROM nominas WHERE nombre = ?");
                if (!$nomina_stmt) {
                    throw new Exception("Error en la preparación de la declaración SELECT nominas:  $conexion->error");
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

                // Eliminar el id del array de empleados
                $new_empleados_array = array_diff($empleados_array, array($id));

                // Convertir el array de empleados de vuelta a JSON
                $empleados_json = json_encode(array_values($new_empleados_array));

                // Actualizar la tabla conceptos_aplicados con el nuevo array de empleados
                $update_stmt = $conexion->prepare("UPDATE conceptos_aplicados SET empleados = ? WHERE id = ?");
                if (!$update_stmt) {
                    throw new Exception("Error en la preparación de la declaración UPDATE: $conexion->error");
                }
                $update_stmt->bind_param('si', $empleados_json, $row['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
        $stmt->close();

        // Verificación adicional para asegurarse de que no haya espacios en blanco o caracteres extraños
        $tipo_nomina = array_map('intval', $tipo_nomina);

        // Asegúrate de que el array tipo_nomina está correctamente formado antes de convertirlo a JSON
        $tipo_nomina2 = json_encode(array_values($tipo_nomina));

        // Consultar la tabla empleados
        $stmt = $conexion->prepare("SELECT *, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_años AS antiguedad_total FROM empleados WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la declaración SELECT empleados: $conexion->error");
        }
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $empleado = $result->fetch_assoc();
        }
        $stmt->close();
        $status_empleado = "R";

        // Insertar en empleados_pasados
        $stmt = $conexion->prepare("INSERT INTO empleados_pasados (nacionalidad, cedula, nombres, otros_años, status, observacion, cod_cargo, banco, cuenta_bancaria, hijos, instruccion_academica, discapacidades, tipo_nomina, id_dependencia, verificado, correcion, beca, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la declaración INSERT empleados_pasados: $conexion->error");
        }
        $stmt->bind_param(
            "ssssssssssssssssss",
            $empleado['nacionalidad'],
            $empleado['cedula'],
            $empleado['nombres'],
            $empleado['antiguedad_total'],
            $status_empleado,
            $empleado['observacion'],
            $empleado['cod_cargo'],
            $empleado['banco'],
            $empleado['cuenta_bancaria'],
            $empleado['hijos'],
            $empleado['instruccion_academica'],
            $empleado['discapacidades'],
            $tipo_nomina2, // Convertir el array a JSON para almacenarlo
            $empleado['id_dependencia'],
            $empleado['verificado'],
            $empleado['correcion'],
            $empleado['beca'],
            $empleado['fecha_ingreso']
        );
        $stmt->execute();

        // Preparar la declaración SQL para eliminar el registro
        $sql = "UPDATE empleados SET  status='R' WHERE id = ?";

        // Preparar la declaración SQL
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la declaración DELETE: $conexion->error");
        }

        // Vincular el parámetro y ejecutar la consulta
        $stmt->bind_param("i", $id);

        // Ejecutar la consulta preparada
        if ($stmt->execute()) {


            // Insertar en la tabla movimientos una sola vez
            $fecha_movimiento = date('Y-m-d H:i:s');
            $accion = 'Eliminar';
            $descripcion = "Desactivacion de empleado: $id";
            $status = 1;
            $tipo_nomina_json = json_encode($tipo_nomina);

            $stmt_mov = $conexion->prepare("INSERT INTO movimientos (id_empleado, id_nomina, fecha_movimiento, accion, descripcion, status) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt_mov) {
                throw new Exception("Error en la preparación de la declaración INSERT movimientos: $conexion->error");

            }
            $stmt_mov->bind_param("issssi", $id, $tipo_nomina_json, $fecha_movimiento, $accion, $descripcion, $status);
            $stmt_mov->execute();
            $stmt_mov->close();
        } else {
            throw new Exception("Error al eliminar el registro: $stmt->error");
        }
        // Cerrar la declaración
        $stmt->close();

        // ACTUALIZAR RESPUESTA EXITOSA
        $conexion->commit();
        $response = json_encode(["success" => "Empleado desactivado correctamente."]);

    } else {
        throw new Exception('No se ha proporcionado un ID.');
    }
} catch (\Throwable $e) {
    // En caso de error, revertir la transacción
    $conexion->rollback();
    // Devolver una respuesta de error al cliente
    $response = json_encode(['error' => $e->getMessage()]);
}

echo $response;