<?php
    function actualizarDistribucion2($distribuciones, $id_ejercicio)
    {
        global $conexion;
        $conexion->begin_transaction();

        try {
            foreach ($distribuciones as $distribucion) {
                $id_distribucion = $distribucion['id_distribucion'];
                $monto_solicitado = $distribucion['monto'];

                // Consultar el campo 'distribucion' en la tabla 'distribucion_entes'
                $sql = "SELECT id, distribucion FROM distribucion_entes WHERE distribucion LIKE '%\"id_distribucion\":\"$id_distribucion\"%' AND id_ejercicio = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $id_ejercicio);
                $stmt->execute();
                $resultado = $stmt->get_result();

                if ($resultado->num_rows === 0) {
                    $conexion->rollback();
                    return ["error" => "No se hallaron las distribuciones indicadas"];
                }

                $fila = $resultado->fetch_assoc();
                $id_distribucion_entes = $fila['id'];
                $distribucion_json = json_decode($fila['distribucion'], true);

                $disponible = false;

                // Restar el monto de la distribución
                foreach ($distribucion_json as &$item) {
                    if ($item['id_distribucion'] == $id_distribucion) {
                        if ($item['monto'] >= $monto_solicitado) {
                            $item['monto'] -= $monto_solicitado;
                            $disponible = true;
                            break;
                        }
                    }
                }

                if (!$disponible) {
                    $conexion->rollback();
                    return ["error" => "Alguna de las distribuciones no posee monto suficiente para registrar el gasto."];
                }

                // Convertir el JSON actualizado a string
                $distribucion_actualizada = json_encode($distribucion_json, JSON_UNESCAPED_UNICODE);

                // Actualizar la distribución en la base de datos
                $sql_update = "UPDATE distribucion_entes SET distribucion = ? WHERE id = ?";
                $stmt_update = $conexion->prepare($sql_update);
                $stmt_update->bind_param("si", $distribucion_actualizada, $id_distribucion_entes);
                $stmt_update->execute();

                if ($stmt_update->affected_rows === 0) {
                    $conexion->rollback();
                    return ["error" => "No se pudo actualizar la distribución."];
                }
            }

            $conexion->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $conexion->rollback();
            registrarError($e->getMessage());
            return ["error" => $e->getMessage()];
        }
    }

    function consultarDisponibilidad2($distribuciones, $id_ejercicio)
    {
        $conexion->begin_transaction();

        try {
            foreach ($distribuciones as $distribucion) {
                $id_distribucion = $distribucion['id_distribucion'];
                $monto_solicitado = $distribucion['monto'];

                // Consultar el campo 'distribucion' en la tabla 'distribucion_entes' filtrando por id_distribucion e id_ejercicio
                $sql = "SELECT distribucion FROM distribucion_entes WHERE distribucion LIKE '%\"id_distribucion\":\"$id_distribucion\"%' AND id_ejercicio = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $id_ejercicio);
                $stmt->execute();
                $resultado = $stmt->get_result();

                if ($resultado->num_rows === 0) {
                    $conexion->rollback();
                    return ["error" => "No se hallaron las distribuciones indicadas"];
                    // No se encontró la distribución para el id_distribucion dado
                }

                $disponible = false;
                $distribucionSinMonto = null;
                while ($fila = $resultado->fetch_assoc()) {
                    $distribucion_json = json_decode($fila['distribucion'], true);

                    foreach ($distribucion_json as $item) {
                        if ($item['id_distribucion'] == $id_distribucion && $item['monto'] >= $monto_solicitado) {
                            $disponible = true;
                            $distribucionSinMonto = $id_distribucion;
                            break 2; // Salir de ambos bucles si se encuentra disponibilidad suficiente
                        }
                    }
                }

                if (!$disponible) {
                    $conexion->rollback();
                    return ["error" => "Alguna de las distribuciones no posee monto suficiente para registrar el gasto."];
                    // Si alguna distribución no tiene suficiente monto, retornamos false
                }
            }

            $conexion->commit();
            return ["success" => true]; // Todas las distribuciones tienen suficiente monto disponible
        } catch (Exception $e) {
            $conexion->rollback();
            registrarError($e->getMessage());
            return ["error" => false];
        }
    }



 ?>