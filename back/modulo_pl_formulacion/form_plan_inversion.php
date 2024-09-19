<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

require_once 'form_errores.php';

// Función para insertar datos en la tabla plan_inversion
function guardarPlanInversion($id_ejercicio, $monto_total, $dataArray) {
    global $conexion;

    try {
        // Verificar que el array no esté vacío
        if (empty($dataArray)) {
            throw new Exception("El array de datos está vacío");
        }

        // Calcular la suma de los montos en el array
        $sumaProyectos = 0;
        foreach ($dataArray as $registro) {
            if (count($registro) !== 4) {
                throw new Exception("El formato del array no es válido");
            }
            $sumaProyectos += $registro[1];
        }

        // Verificar que la suma de los montos sea igual a monto_total
        if ($sumaProyectos != $monto_total) {
            throw new Exception("El total de los proyectos no es igual al monto total de la inversión");
        }

        // Insertar cada registro en la tabla plan_inversion
        foreach ($dataArray as $registro) {
            list($proyecto, $monto, $id_partida, $id_ente) = $registro;
            $fecha = date('Y-m-d'); // Fecha actual

            $sql = "INSERT INTO plan_inversion (id_ejercicio, monto_total, proyecto, monto_proyecto, id_partida, id_ente, fecha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iisiiis", $id_ejercicio, $monto_total, $proyecto, $monto, $id_partida, $id_ente, $fecha);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                throw new Exception("Error al insertar en plan_inversion");
            }
            $stmt->close();
        }

        return json_encode(["success" => "Datos de la inversión guardados correctamente"]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar datos en la tabla plan_inversion
function actualizarPlanInversion($id, $id_ejercicio, $monto_total, $dataArray) {
    global $conexion;

    try {
        if (empty($dataArray)) {
            throw new Exception("El array de datos está vacío");
        }

        $sumaProyectos = 0;
        foreach ($dataArray as $registro) {
            if (count($registro) !== 4) {
                throw new Exception("El formato del array no es válido");
            }
            $sumaProyectos += $registro[1];
        }

        if ($sumaProyectos != $monto_total) {
            throw new Exception("El total de los proyectos no es igual al monto total de la inversión");
        }

        // Borrar registros anteriores para este id_ejercicio y luego insertar los nuevos
        $sqlDelete = "DELETE FROM plan_inversion WHERE id = ?";
        $stmt = $conexion->prepare($sqlDelete);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        foreach ($dataArray as $registro) {
            list($proyecto, $monto, $id_partida, $id_ente) = $registro;
            $fecha = date('Y-m-d');

            $sql = "INSERT INTO plan_inversion (id_ejercicio, monto_total, proyecto, monto_proyecto, id_partida, id_ente, fecha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iisiiis", $id_ejercicio, $monto_total, $proyecto, $monto, $id_partida, $id_ente, $fecha);
            $stmt->execute();
        }

        return json_encode(["success" => "Datos de la inversión actualizados correctamente"]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un registro en la tabla plan_inversion
function eliminarPlanInversion($id) {
    global $conexion;

    try {
        $sql = "DELETE FROM plan_inversion WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al eliminar el registro");
        }

        return json_encode(["success" => "Registro eliminado correctamente"]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar el monto y la partida de un proyecto en plan_inversion
function actualizarProyecto($id, $monto, $id_partida) {
    global $conexion;

    try {
        $sql = "UPDATE plan_inversion SET monto_proyecto = ?, id_partida = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iii", $monto, $id_partida, $id);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al actualizar el proyecto");
        }

        return json_encode(["success" => "Proyecto actualizado correctamente"]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    if ($accion === "insert" && isset($data["id_ejercicio"]) && isset($data["monto_total"]) && isset($data["arrayDatos"])) {
        echo guardarPlanInversion($data["id_ejercicio"], $data["monto_total"], $data["arrayDatos"]);

    } elseif ($accion === "update" && isset($data["id"]) && isset($data["id_ejercicio"]) && isset($data["monto_total"]) && isset($data["arrayDatos"])) {
        echo actualizarPlanInversion($data["id"], $data["id_ejercicio"], $data["monto_total"], $data["arrayDatos"]);

    } elseif ($accion === "delete" && isset($data["id"])) {
        echo eliminarPlanInversion($data["id"]);

    } elseif ($accion === "update_proyecto" && isset($data["id"]) && isset($data["monto"]) && isset($data["id_partida"])) {
        echo actualizarProyecto($data["id"], $data["monto"], $data["id_partida"]);

    } else {
        echo json_encode(['error' => "Acción no válida o faltan datos"]);
    }
} else {
    echo json_encode(['error' => "No se recibió ninguna acción"]);
}
?>
