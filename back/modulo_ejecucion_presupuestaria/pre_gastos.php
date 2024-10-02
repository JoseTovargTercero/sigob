<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php'; 
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para crear un nuevo gasto
function crearGasto($id_tipo, $descripcion, $monto, $id_ejercicio) {
    global $conexion;

    try {
        // Validar que los campos obligatorios no estén vacíos
        if (empty($id_tipo) || empty($descripcion) || empty($monto) || empty($id_ejercicio)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        // El status siempre será 0 (Pendiente) al registrar
        $status = 0;

        // Insertar el nuevo registro en la tabla 'gastos'
        $sql = "INSERT INTO gastos (id_tipo, descripcion, monto, status, id_ejercicio) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isdis", $id_tipo, $descripcion, $monto, $status, $id_ejercicio);

        if ($stmt->execute()) {
            return json_encode(['success' => 'Gasto creado exitosamente']);
        } else {
            throw new Exception("Error al crear el gasto.");
        }

    } catch (Exception $e) {
        // Registrar el error
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

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    echo json_encode(['error' => 'No se especificó ninguna acción']);
}

