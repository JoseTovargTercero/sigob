<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php'; 
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado
require_once 'pre_dispo_presupuestaria.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para crear un nuevo gasto
function crearGasto($id_tipo, $descripcion, $monto, $id_ejercicio, $tipo_beneficiario, $id_beneficiario, $id_distribucion) {
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id_tipo) || empty($descripcion) || empty($monto) || empty($id_ejercicio) || empty($tipo_beneficiario) || empty($id_beneficiario) || empty($id_distribucion)) {
            throw new Exception("Faltaron uno o más valores (id_tipo, descripción, monto, id_ejercicio, tipo_beneficiario, id_beneficiario, id_distribucion)");
        }

        // Paso 1: Buscar id_partida en la tabla distribucion_presupuestaria usando id_distribucion
        $sqlDistribucionPresupuestaria = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
        $stmtDistribucionPresupuestaria = $conexion->prepare($sqlDistribucionPresupuestaria);
        $stmtDistribucionPresupuestaria->bind_param("i", $id_distribucion);
        $stmtDistribucionPresupuestaria->execute();
        $resultadoDistribucionPresupuestaria = $stmtDistribucionPresupuestaria->get_result();

        if ($resultadoDistribucionPresupuestaria->num_rows === 0) {
            throw new Exception("No existe una distribución presupuestaria con el ID proporcionado");
        }

        $filaDistribucionPresupuestaria = $resultadoDistribucionPresupuestaria->fetch_assoc();
        $id_partida = $filaDistribucionPresupuestaria['id_partida'];

        // Verificar si el presupuesto es suficiente
        $disponible = consultarDisponibilidad($id_partida, $id_ejercicio, $monto);
        if (!$disponible) {
            throw new Exception("El presupuesto actual es inferior al monto del gasto. No se puede registrar el gasto.");
        }else{
             // Paso 4: Insertar el gasto si el presupuesto es suficiente
            $sqlInsertGasto = "INSERT INTO gastos (id_tipo, descripcion, monto, status, id_ejercicio, tipo_beneficiario, id_beneficiario, id_distribucion) VALUES (?, ?, ?, 0, ?, ?, ?, ?)";
            $stmtInsertGasto = $conexion->prepare($sqlInsertGasto);
            $stmtInsertGasto->bind_param("issisii", $id_tipo, $descripcion, $monto, $id_ejercicio, $tipo_beneficiario, $id_beneficiario, $id_distribucion);
            $stmtInsertGasto->execute();

            if ($stmtInsertGasto->affected_rows > 0) {
            return json_encode(["success" => "Gasto registrado correctamente"]);
            } else {
            throw new Exception("No se pudo registrar el gasto");
            }
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
        $sqlGasto = "SELECT id_tipo, descripcion, monto, id_ejercicio, id_distribucion, status, tipo_beneficiario, id_beneficiario FROM gastos WHERE id = ?";
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
        $id_distribucion = $filaGasto['id_distribucion'];
        $status = $filaGasto['status'];

        // Verificar si el gasto ya ha sido procesado
        if ($status !== 0) {
            throw new Exception("El gasto ya ha sido procesado anteriormente");
        }

        if ($accion === "aceptar") {
            // Paso 1: Obtener el id_partida de la tabla distribucion_presupuestaria usando id_distribucion
            $sqlDistribucionPresupuestaria = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucionPresupuestaria = $conexion->prepare($sqlDistribucionPresupuestaria);
            $stmtDistribucionPresupuestaria->bind_param("i", $id_distribucion);
            $stmtDistribucionPresupuestaria->execute();
            $resultadoDistribucionPresupuestaria = $stmtDistribucionPresupuestaria->get_result();

            if ($resultadoDistribucionPresupuestaria->num_rows === 0) {
                throw new Exception("No se encontró una distribución presupuestaria con el ID proporcionado");
            }

            $filaDistribucionPresupuestaria = $resultadoDistribucionPresupuestaria->fetch_assoc();
            $id_partida = $filaDistribucionPresupuestaria['id_partida'];

            // Llamar a la función para verificar disponibilidad presupuestaria
            $resultado = consultarDisponibilidad($id_partida, $id_ejercicio, $monto);

            if ($resultado['exito']) {
                $monto_actual = $resultado['monto_actual'];
            } else {
                throw new Exception("El presupuesto actual es inferior al monto del gasto. No se puede registrar el gasto.");
            }

            // Paso 3: Actualizar el status del gasto a 1 (aceptado)
            $sqlUpdateGasto = "UPDATE gastos SET status = 1 WHERE id = ?";
            $stmtUpdateGasto = $conexion->prepare($sqlUpdateGasto);
            $stmtUpdateGasto->bind_param("i", $idGasto);
            $stmtUpdateGasto->execute();

            if ($stmtUpdateGasto->affected_rows > 0) {
                // Paso 4: Registrar el compromiso
                $resultadoCompromiso = registrarCompromiso($idGasto, 'gastos', $descripcion, $tipo_beneficiario, $id_beneficiario);

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
        $sql = "SELECT id, id_tipo, descripcion, monto, status, tipo_beneficiario, id_beneficiario, id_distribucion FROM gastos";
        $resultado = $conexion->query($sql);

        $gastos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $id_tipo = $fila['id_tipo'];
            $tipo_beneficiario = $fila['tipo_beneficiario'];
            $id_beneficiario = $fila['id_beneficiario'];
            $id_distribucion = $fila['id_distribucion'];

            // Consultar nombre de tipo de gasto
            $sqlTipoGasto = "SELECT nombre FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();
            $nombreTipoGasto = $resultadoTipoGasto->fetch_assoc()['nombre'] ?? null;

            // Obtener id_partida desde distribucion_presupuestaria
            $sqlDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucion = $conexion->prepare($sqlDistribucion);
            $stmtDistribucion->bind_param("i", $id_distribucion);
            $stmtDistribucion->execute();
            $resultadoDistribucion = $stmtDistribucion->get_result();
            $id_partida = $resultadoDistribucion->fetch_assoc()['id_partida'] ?? null;

            // Consultar información de partida
            $partidaInfo = null;
            if ($id_partida) {
                $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $id_partida);
                $stmtPartida->execute();
                $resultadoPartida = $stmtPartida->get_result();
                $partidaInfo = $resultadoPartida->fetch_assoc();
            }

            // Consultar información del beneficiario según el tipo_beneficiario
            if ($tipo_beneficiario == 0) {
                $sqlBeneficiario = "SELECT * FROM entes WHERE id = ?";
            } else {
                $sqlBeneficiario = "SELECT * FROM empleados WHERE id = ?";
            }

            $stmtBeneficiario = $conexion->prepare($sqlBeneficiario);
            $stmtBeneficiario->bind_param("i", $id_beneficiario);
            $stmtBeneficiario->execute();
            $resultadoBeneficiario = $stmtBeneficiario->get_result();
            $informacionBeneficiario = $resultadoBeneficiario->fetch_assoc();

            // Construir el array con la información completa del gasto
            $gasto = [
                'id' => $fila['id'],
                'nombre_tipo_gasto' => $nombreTipoGasto,
                'partida' => $partidaInfo['partida'] ?? null,
                'nombre_partida' => $partidaInfo['nombre'] ?? null,
                'descripcion_partida' => $partidaInfo['descripcion'] ?? null,
                'descripcion_gasto' => $fila['descripcion'],
                'monto_gasto' => $fila['monto'],
                'status_gasto' => $fila['status'],
                'informacion_beneficiario' => $informacionBeneficiario
            ];

            $gastos[] = $gasto;
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
        $sqlGasto = "SELECT id_tipo, descripcion, monto, status, tipo_beneficiario, id_beneficiario, id_distribucion FROM gastos WHERE id = ?";
        $stmtGasto = $conexion->prepare($sqlGasto);
        $stmtGasto->bind_param("i", $id);
        $stmtGasto->execute();
        $resultadoGasto = $stmtGasto->get_result();

        if ($gasto = $resultadoGasto->fetch_assoc()) {
            $id_tipo = $gasto['id_tipo'];
            $descripcion = $gasto['descripcion'];
            $monto = $gasto['monto'];
            $status = $gasto['status'];
            $tipo_beneficiario = $gasto['tipo_beneficiario'];
            $id_beneficiario = $gasto['id_beneficiario'];
            $id_distribucion = $gasto['id_distribucion'];

            // Consultar la tabla tipo_gastos para obtener el nombre del tipo de gasto
            $sqlTipoGasto = "SELECT nombre FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();

            if ($tipoGasto = $resultadoTipoGasto->fetch_assoc()) {
                $nombreTipoGasto = $tipoGasto['nombre'];

                // Obtener el id_partida de distribucion_presupuestaria utilizando el id_distribucion
                $sqlDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
                $stmtDistribucion = $conexion->prepare($sqlDistribucion);
                $stmtDistribucion->bind_param("i", $id_distribucion);
                $stmtDistribucion->execute();
                $resultadoDistribucion = $stmtDistribucion->get_result();

                if ($distribucion = $resultadoDistribucion->fetch_assoc()) {
                    $id_partida = $distribucion['id_partida'];

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

                        // Obtener información del beneficiario según el tipo_beneficiario
                        if ($tipo_beneficiario == 0) {
                            // Consultar en la tabla entes
                            $sqlBeneficiario = "SELECT * FROM entes_dependencias WHERE id = ?";
                        } else {
                            // Consultar en la tabla empleados
                            $sqlBeneficiario = "SELECT * FROM empleados WHERE id = ?";
                        }

                        $stmtBeneficiario = $conexion->prepare($sqlBeneficiario);
                        $stmtBeneficiario->bind_param("i", $id_beneficiario);
                        $stmtBeneficiario->execute();
                        $resultadoBeneficiario = $stmtBeneficiario->get_result();

                        if ($informacionBeneficiario = $resultadoBeneficiario->fetch_assoc()) {
                            // Construir el array con los datos obtenidos
                            $resultado = [
                                'nombre_tipo_gasto' => $nombreTipoGasto,
                                'partida' => $partida,
                                'nombre_partida' => $nombrePartida,
                                'descripcion_partida' => $descripcionPartida,
                                'descripcion_gasto' => $descripcion,
                                'monto_gasto' => $monto,
                                'status_gasto' => $status,
                                'informacion_beneficiario' => $informacionBeneficiario
                            ];

                            return json_encode($resultado);
                        } else {
                            throw new Exception("No se encontró la información del beneficiario.");
                        }
                    } else {
                        throw new Exception("No se encontró la partida presupuestaria.");
                    }
                } else {
                    throw new Exception("No se encontró la distribución presupuestaria correspondiente.");
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

function actualizarGasto($id, $id_tipo, $descripcion, $monto, $status, $id_ejercicio, $tipo_beneficiario, $id_beneficiario, $id_distribucion) {
    global $conexion;

    try {
        // Validar que los campos obligatorios no estén vacíos
        if (empty($id) || empty($id_tipo) || empty($descripcion) || empty($monto) || empty($status) || empty($id_ejercicio) || empty($tipo_beneficiario) || empty($id_beneficiario) || empty($id_distribucion)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        // Actualizar el registro en la tabla 'gastos' con los campos adicionales
        $sql = "UPDATE gastos SET id_tipo = ?, descripcion = ?, monto = ?, status = ?, id_ejercicio = ?, tipo_beneficiario = ?, id_beneficiario = ?, id_distribucion = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isdiisiii", $id_tipo, $descripcion, $monto, $status, $id_ejercicio, $tipo_beneficiario, $id_beneficiario, $id_distribucion, $id);

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
            echo crearGasto(
                $data["id_tipo"],
                $data["descripcion"],
                $data["monto"],
                $data["id_ejercicio"],
                $data["tipo_beneficiario"],
                $data["id_beneficiario"],
                $data["id_distribucion"]
            );
            break;

        case 'obtener':
            echo obtenerGastos();
            break;

        case 'obtenerPorId':
            echo obtenerGastoPorId($data["id"]);
            break;

        case 'actualizar':
            echo actualizarGasto(
                $data["id"],
                $data["id_tipo"],
                $data["descripcion"],
                $data["monto"],
                $data["status"],
                $data["id_ejercicio"],
                $data["tipo_beneficiario"],
                $data["id_beneficiario"],
                $data["id_distribucion"]
            );
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


