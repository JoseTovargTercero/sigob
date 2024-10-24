<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php'; 
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado
require_once 'pre_dispo_presupuestaria.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para crear un nuevo gasto
function crearGasto($id_tipo, $descripcion, $monto, $id_ejercicio) {
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id_tipo) || empty($descripcion) || empty($monto) || empty($id_ejercicio)) {
            throw new Exception("Faltaron uno o más valores (id_tipo, descripción, monto, id_ejercicio)");
        }

        // Paso 1: Buscar id_partida en la tabla tipo_gastos usando id_tipo
        $sqlTipoGasto = "SELECT id_partida FROM tipo_gastos WHERE id = ?";
        $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
        $stmtTipoGasto->bind_param("i", $id_tipo);
        $stmtTipoGasto->execute();
        $resultadoTipoGasto = $stmtTipoGasto->get_result();

        if ($resultadoTipoGasto->num_rows === 0) {
            throw new Exception("El tipo de gasto con el ID proporcionado no existe");
        }

        $filaTipoGasto = $resultadoTipoGasto->fetch_assoc();
        $id_partida = $filaTipoGasto['id_partida'];


         // Verificar si el presupuesto es suficiente
         $disponible = consultarDisponibilidad($id_partida, $id_ejercicio, $monto);
        if (!$disponible) {
            throw new Exception("El presupuesto actual es inferior al monto del gasto. No se puede registrar el gasto.");
        } 

    

        // Paso 4: Insertar el gasto si el presupuesto es suficiente
        $sqlInsertGasto = "INSERT INTO gastos (id_tipo, descripcion, monto, status, id_ejercicio) VALUES (?, ?, ?, 0, ?)";
        $stmtInsertGasto = $conexion->prepare($sqlInsertGasto);
        $stmtInsertGasto->bind_param("isdi", $id_tipo, $descripcion, $monto, $id_ejercicio);
        $stmtInsertGasto->execute();

        if ($stmtInsertGasto->affected_rows > 0) {
            return json_encode(["success" => "Gasto registrado correctamente"]);
        } else {
            throw new Exception("No se pudo registrar el gasto");
        }

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}
// Función para aceptar o rechazar un gasto
function gestionarGasto($idGasto, $accion) {
    global $conexion;

    try {
        // Validar que se proporcionen los parámetros necesarios
        if (empty($idGasto) || empty($accion)) {
            throw new Exception("Faltan uno o más valores necesarios (idGasto, accion)");
        }

        // Obtener el registro de la tabla gastos con el id proporcionado
        $sqlGasto = "SELECT id_tipo, descripcion, monto, id_ejercicio, status FROM gastos WHERE id = ?";
        $stmtGasto = $conexion->prepare($sqlGasto);
        $stmtGasto->bind_param("i", $idGasto);
        $stmtGasto->execute();
        $resultadoGasto = $stmtGasto->get_result();

        if ($resultadoGasto->num_rows === 0) {
            throw new Exception("No se encontró un gasto con el ID proporcionado");
        }

        $filaGasto = $resultadoGasto->fetch_assoc();
        $id_tipo = $filaGasto['id_tipo'];
        $descripcion = $filaGasto['descripcion'];
        $monto = $filaGasto['monto'];
        $id_ejercicio = $filaGasto['id_ejercicio'];
        $status = $filaGasto['status'];

        // Verificar si el gasto ya ha sido procesado
        if ($status !== 0) {
            throw new Exception("El gasto ya ha sido procesado anteriormente");
        }

        if ($accion === "aceptar") {
            // Paso 1: Obtener el id_partida de la tabla tipo_gastos usando id_tipo
            $sqlTipoGasto = "SELECT id_partida FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();

            if ($resultadoTipoGasto->num_rows === 0) {
                throw new Exception("No se encontró el tipo de gasto correspondiente al ID proporcionado");
            }

            $filaTipoGasto = $resultadoTipoGasto->fetch_assoc();
            $id_partida = $filaTipoGasto['id_partida'];


            // Llamar a la función y obtener el resultado
    $resultado = consultarDisponibilidad($id_partida, $id_ejercicio, $monto);

    if ($resultado['exito']) {
        $monto_actual = $resultado['monto_actual'];
    } else {
        throw new Exception("El presupuesto actual es inferior al monto del gasto. No se puede registrar el gasto.");
        $monto_actual = $resultado['monto_actual'];
    }


            // Paso 3: Actualizar el status del gasto a 1 (aceptado)
            $sqlUpdateGasto = "UPDATE gastos SET status = 1 WHERE id = ?";
            $stmtUpdateGasto = $conexion->prepare($sqlUpdateGasto);
            $stmtUpdateGasto->bind_param("i", $idGasto);
            $stmtUpdateGasto->execute();

            if ($stmtUpdateGasto->affected_rows > 0) {
                // Paso 4: Registrar el compromiso
                $resultadoCompromiso = registrarCompromiso($idGasto, 'gastos', $descripcion);

                // Paso 5: Actualizar el monto_actual en distribucion_presupuestaria
                $nuevoMontoActual = $monto_actual - $monto;
                $sqlUpdateDistribucion = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id_partida = ? AND id_ejercicio = ?";
                $stmtUpdateDistribucion = $conexion->prepare($sqlUpdateDistribucion);
                $stmtUpdateDistribucion->bind_param("dii", $nuevoMontoActual, $id_partida, $id_ejercicio);
                $stmtUpdateDistribucion->execute();

                if ($stmtUpdateDistribucion->affected_rows > 0) {
                    return json_encode([
                        "success" => "El gasto ha sido aceptado, el compromiso se ha registrado y el presupuesto actualizado",
                        "compromiso" => $resultadoCompromiso
                    ]);
                } else {
                    throw new Exception("No se pudo actualizar el monto actual de la distribución presupuestaria");
                }
            } else {
                throw new Exception("No se pudo actualizar el gasto a aceptado");
            }

        } elseif ($accion === "rechazar") {
            // Si se selecciona "rechazar", solo se actualiza el status del gasto a 2 (rechazado)
            $sqlUpdateGasto = "UPDATE gastos SET status = 2 WHERE id = ?";
            $stmtUpdateGasto = $conexion->prepare($sqlUpdateGasto);
            $stmtUpdateGasto->bind_param("i", $idGasto);
            $stmtUpdateGasto->execute();

            if ($stmtUpdateGasto->affected_rows > 0) {
                return json_encode(["success" => "El gasto ha sido rechazado"]);
            } else {
                throw new Exception("No se pudo rechazar el gasto");
            }

        } else {
            throw new Exception("Acción no válida. Debe ser 'aceptar' o 'rechazar'.");
        }

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para obtener todos los gastos
function obtenerGastos() {
    global $conexion;

    try {
        $sql = "SELECT * FROM gastos";
        $resultado = $conexion->query($sql);

        $gastos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $gastos[] = $fila;
        }

        return json_encode($gastos);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para obtener un gasto por su ID
function obtenerGastoPorId($id) {
    global $conexion;

    try {
        // Consultar el registro de la tabla gastos por su ID
        $sqlGasto = "SELECT id_tipo, descripcion, monto, status FROM gastos WHERE id = ?";
        $stmtGasto = $conexion->prepare($sqlGasto);
        $stmtGasto->bind_param("i", $id);
        $stmtGasto->execute();
        $resultadoGasto = $stmtGasto->get_result();

        if ($gasto = $resultadoGasto->fetch_assoc()) {
            $id_tipo = $gasto['id_tipo'];
            $descripcion = $gasto['descripcion'];
            $monto = $gasto['monto'];
            $status = $gasto['status'];

            // Verificar el status para cambiarlo a un valor legible
            $estado = ($status == 0) ? 'Pendiente' : (($status == 1) ? 'Ejecutado' : 'Rechazado');

            // Consultar la tabla tipo_gastos para obtener nombre e id_partida
            $sqlTipoGasto = "SELECT nombre, id_partida FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();

            if ($tipoGasto = $resultadoTipoGasto->fetch_assoc()) {
                $nombreTipoGasto = $tipoGasto['nombre'];
                $id_partida = $tipoGasto['id_partida'];

                // Consultar la tabla partidas_presupuestarias para obtener partida, nombre y descripcion
                $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $id_partida);
                $stmtPartida->execute();
                $resultadoPartida = $stmtPartida->get_result();

                if ($partidaInfo = $resultadoPartida->fetch_assoc()) {
                    $partida = $partidaInfo['partida'];
                    $nombrePartida = $partidaInfo['nombre'];
                    $descripcionPartida = $partidaInfo['descripcion'];

                    // Construir el array con los datos obtenidos
                    $resultado = [
                        'nombre_tipo_gasto' => $nombreTipoGasto,
                        'partida' => $partida,
                        'nombre_partida' => $nombrePartida,
                        'descripcion_partida' => $descripcionPartida,
                        'descripcion_gasto' => $descripcion,
                        'monto_gasto' => $monto,
                        'status_gasto' => $estado
                    ];

                    return json_encode($resultado);
                } else {
                    throw new Exception("No se encontró la partida presupuestaria.");
                }
            } else {
                throw new Exception("No se encontró el tipo de gasto.");
            }
        } else {
            throw new Exception("Gasto no encontrado.");
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar un gasto
function actualizarGasto($id, $id_tipo, $descripcion, $monto, $status, $id_ejercicio) {
    global $conexion;

    try {
        // Validar que los campos obligatorios no estén vacíos
        if (empty($id) || empty($id_tipo) || empty($descripcion) || empty($monto) || empty($status) || empty($id_ejercicio)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        // Actualizar el registro en la tabla 'gastos'
        $sql = "UPDATE gastos SET id_tipo = ?, descripcion = ?, monto = ?, status = ?, id_ejercicio = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isdiii", $id_tipo, $descripcion, $monto, $status, $id_ejercicio, $id);

        if ($stmt->execute()) {
            return json_encode(['success' => 'Gasto actualizado exitosamente']);
        } else {
            throw new Exception("Error al actualizar el gasto.");
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un gasto
function eliminarGasto($id) {
    global $conexion;

    try {
        // Eliminar el registro de la tabla 'gastos'
        $sql = "DELETE FROM gastos WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return json_encode(['success' => 'Gasto eliminado exitosamente']);
        } else {
            throw new Exception("Error al eliminar el gasto.");
        }

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

// Verificar qué tipo de acción se solicita
if (isset($data["accion"])) {
    switch ($data["accion"]) {
        case 'crear':
            echo crearGasto($data["id_tipo"], $data["descripcion"], $data["monto"], $data["id_ejercicio"]);
            break;

        case 'obtener':
            echo obtenerGastos();
            break;

        case 'obtenerPorId':
            echo obtenerGastoPorId($data["id"]);
            break;

        case 'actualizar':
            echo actualizarGasto($data["id"], $data["id_tipo"], $data["descripcion"], $data["monto"], $data["status"], $data["id_ejercicio"]);
            break;

        case 'eliminar':
            echo eliminarGasto($data["id"]);
            break;

        case 'gestionar':  // Nueva opción para aceptar o rechazar
            echo gestionarGasto($data["id"], $data["accion_gestion"]);
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    echo json_encode(['error' => 'No se especificó ninguna acción']);
}

