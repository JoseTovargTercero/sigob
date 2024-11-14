<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado
require_once 'pre_dispo_presupuestaria.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para crear un nuevo gasto
function crearGasto($id_tipo, $descripcion, $monto, $id_ejercicio, $tipo_beneficiario, $id_beneficiario, $id_distribucion, $fecha)
{
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id_tipo) || empty($descripcion) || empty($monto) || empty($id_ejercicio) || $tipo_beneficiario == "" || empty($id_beneficiario) || empty($id_distribucion) || empty($fecha)) {
            throw new Exception("Faltaron uno o más valores (id_tipo, descripción, monto, id_ejercicio, tipo_beneficiario, id_beneficiario, id_distribucion, fecha_ultimo)");
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
        } else {
            // Paso 4: Insertar el gasto si el presupuesto es suficiente
            $sqlInsertGasto = "INSERT INTO gastos (id_tipo, descripcion, monto, status, id_ejercicio, tipo_beneficiario, id_beneficiario, id_distribucion, fecha) VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?)";
            $stmtInsertGasto = $conexion->prepare($sqlInsertGasto);
            $stmtInsertGasto->bind_param("issisiss", $id_tipo, $descripcion, $monto, $id_ejercicio, $tipo_beneficiario, $id_beneficiario, $id_distribucion, $fecha);
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


function gestionarGasto($idGasto, $accion)
{
    global $conexion;

    try {
        if (empty($idGasto) || empty($accion)) {
            throw new Exception("Faltan uno o más valores necesarios (idGasto, accion)");
        }

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
        $tipo_beneficiario = $filaGasto['tipo_beneficiario'];
        $id_beneficiario = $filaGasto['id_beneficiario'];

        if ($status !== 0) {
            throw new Exception("El gasto ya ha sido procesado anteriormente");
        }

        if ($accion === "aceptar") {
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

            $resultado = consultarDisponibilidad($id_partida, $id_ejercicio, $monto);

            if ($resultado['exito']) {
                $monto_actual = $resultado['monto_actual'];
            } else {
                throw new Exception("El presupuesto actual es inferior al monto del gasto. No se puede registrar el gasto.");
            }

            $sqlUpdateGasto = "UPDATE gastos SET status = 1 WHERE id = ?";
            $stmtUpdateGasto = $conexion->prepare($sqlUpdateGasto);
            $stmtUpdateGasto->bind_param("i", $idGasto);
            $stmtUpdateGasto->execute();

            if ($stmtUpdateGasto->affected_rows > 0) {
                $resultadoCompromiso = registrarCompromiso($idGasto, 'gastos', $descripcion, $tipo_beneficiario, $id_beneficiario, $id_ejercicio);

                if (isset($resultadoCompromiso['success']) && $resultadoCompromiso['success']) {
                    $idCompromiso = $resultadoCompromiso['id_compromiso'];
                    $nuevoMontoActual = $monto_actual - $monto;

                    $sqlUpdateDistribucion = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id_partida = ? AND id_ejercicio = ?";
                    $stmtUpdateDistribucion = $conexion->prepare($sqlUpdateDistribucion);
                    $stmtUpdateDistribucion->bind_param("dii", $nuevoMontoActual, $id_partida, $id_ejercicio);
                    $stmtUpdateDistribucion->execute();

                    if ($stmtUpdateDistribucion->affected_rows > 0) {
                        return json_encode([
                            "success" => "El gasto ha sido aceptado, el compromiso se ha registrado y el presupuesto actualizado",
                            "compromiso" => [
                                "correlativo" => $resultadoCompromiso['correlativo'],
                                "id_compromiso" => $idCompromiso
                            ]
                        ]);
                    } else {
                        throw new Exception("No se pudo actualizar el monto actual de la distribución presupuestaria");
                    }
                } else {
                    throw new Exception("No se pudo registrar el compromiso");
                }
            } else {
                throw new Exception("No se pudo actualizar el gasto a aceptado");
            }

        } elseif ($accion === "rechazar") {
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
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}



function obtenerGastos()
{
    global $conexion;

    try {
        $sql = "SELECT id, id_tipo, descripcion, monto, status, id_ejercicio, tipo_beneficiario, id_beneficiario, id_distribucion, fecha FROM gastos";
        $resultado = $conexion->query($sql);

        $gastos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $id = $fila['id'];
            $id_tipo = $fila['id_tipo'];
            $id_ejercicio = $fila['id_ejercicio'];
            $tipo_beneficiario = $fila['tipo_beneficiario'];
            $id_beneficiario = $fila['id_beneficiario'];
            $id_distribucion = $fila['id_distribucion'];
            $fecha = $fila['fecha'];

            // Consultar nombre de tipo de gasto
            $sqlTipoGasto = "SELECT nombre FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();
            $nombreTipoGasto = $resultadoTipoGasto->fetch_assoc()['nombre'] ?? null;

            // Obtener id_partida y demás información desde distribucion_presupuestaria
            $sqlDistribucion = "SELECT * FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucion = $conexion->prepare($sqlDistribucion);
            $stmtDistribucion->bind_param("i", $id_distribucion);
            $stmtDistribucion->execute();
            $resultadoDistribucion = $stmtDistribucion->get_result();
            $infoDistribucion = $resultadoDistribucion->fetch_assoc();
            $id_partida = $infoDistribucion['id_partida'] ?? null;
            $id_sector = $infoDistribucion['id_sector'] ?? null;
            $id_programa = $infoDistribucion['id_programa'] ?? null;

            // Consultar información de la partida
            $partidaInfo = null;
            if ($id_partida) {
                $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $id_partida);
                $stmtPartida->execute();
                $resultadoPartida = $stmtPartida->get_result();
                $partidaInfo = $resultadoPartida->fetch_assoc();
            }

            // Consultar información del sector
            $sectorInfo = null;
            if ($id_sector) {
                $sqlSector = "SELECT sector AS sector_numero FROM pl_sectores WHERE id = ?";
                $stmtSector = $conexion->prepare($sqlSector);
                $stmtSector->bind_param("i", $id_sector);
                $stmtSector->execute();
                $resultadoSector = $stmtSector->get_result();
                $sectorInfo = $resultadoSector->fetch_assoc();
            }

            // Consultar información del programa
            $programaInfo = null;
            if ($id_programa) {
                $sqlPrograma = "SELECT programa AS programa_numero FROM pl_programas WHERE id = ?";
                $stmtPrograma = $conexion->prepare($sqlPrograma);
                $stmtPrograma->bind_param("i", $id_programa);
                $stmtPrograma->execute();
                $resultadoPrograma = $stmtPrograma->get_result();
                $programaInfo = $resultadoPrograma->fetch_assoc();
            }

            // Añadir el sector y programa a la información de distribución
            $infoDistribucion['sector'] = $sectorInfo['sector_numero'] ?? null;
            $infoDistribucion['programa'] = $programaInfo['programa_numero'] ?? null;

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

            // Consultar id de compromiso relacionado
            $sqlCompromiso = "SELECT id, correlativo FROM compromisos WHERE id_registro = ? AND tabla_registro = 'gastos'";
            $stmtCompromiso = $conexion->prepare($sqlCompromiso);
            $stmtCompromiso->bind_param("i", $id);
            $stmtCompromiso->execute();
            $resultadoCompromiso = $stmtCompromiso->get_result();
            $compromiso = $resultadoCompromiso->fetch_assoc();
            $idCompromiso = $compromiso['id'] ?? null;
            $correlativo = $compromiso['correlativo'] ?? null;

            // Construir el array con la información completa del gasto
            $gasto = [
                'id' => $fila['id'],
                'fecha' => $fila['fecha'],
                'nombre_tipo_gasto' => $nombreTipoGasto,
                'tipo_beneficiario' => $tipo_beneficiario,
                'partida' => $partidaInfo['partida'] ?? null,
                'nombre_partida' => $partidaInfo['nombre'] ?? null,
                'descripcion_partida' => $partidaInfo['descripcion'] ?? null,
                'descripcion_gasto' => $fila['descripcion'],
                'monto_gasto' => $fila['monto'],
                'status_gasto' => $fila['status'],
                'id_ejercicio' => $id_ejercicio,
                'informacion_beneficiario' => $informacionBeneficiario,
                'id_compromiso' => $idCompromiso,
                'correlativo' => $correlativo,
                'informacion_distribucion' => $infoDistribucion // Incluye toda la información de distribucion_presupuestaria con sector y programa
            ];

            $gastos[] = $gasto;
        }

        return json_encode($gastos);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}



function obtenerGastoPorId($id)
{
    global $conexion;

    try {
        // Consultar el registro de la tabla gastos por su ID
        $sqlGasto = "SELECT id, id_tipo, descripcion, monto, status, tipo_beneficiario, id_beneficiario, id_distribucion, fecha FROM gastos WHERE id = ?";
        $stmtGasto = $conexion->prepare($sqlGasto);
        $stmtGasto->bind_param("i", $id);
        $stmtGasto->execute();
        $resultadoGasto = $stmtGasto->get_result();

        if ($gasto = $resultadoGasto->fetch_assoc()) {
            $id_gasto = $gasto['id'];
            $id_tipo = $gasto['id_tipo'];
            $descripcion = $gasto['descripcion'];
            $monto = $gasto['monto'];
            $status = $gasto['status'];
            $tipo_beneficiario = $gasto['tipo_beneficiario'];
            $id_beneficiario = $gasto['id_beneficiario'];
            $id_distribucion = $gasto['id_distribucion'];
            $fecha = $gasto['fecha'];

            // Consultar la tabla tipo_gastos para obtener el nombre del tipo de gasto
            $sqlTipoGasto = "SELECT nombre FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();
            $nombreTipoGasto = $resultadoTipoGasto->fetch_assoc()['nombre'] ?? null;

            // Obtener toda la información de distribucion_presupuestaria
            $sqlDistribucion = "SELECT * FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucion = $conexion->prepare($sqlDistribucion);
            $stmtDistribucion->bind_param("i", $id_distribucion);
            $stmtDistribucion->execute();
            $resultadoDistribucion = $stmtDistribucion->get_result();
            $distribucionInfo = $resultadoDistribucion->fetch_assoc();

            if ($distribucionInfo) {
                $id_partida = $distribucionInfo['id_partida'];
                $id_sector = $distribucionInfo['id_sector'];
                $id_programa = $distribucionInfo['id_programa'];

                // Consultar la tabla partidas_presupuestarias para obtener partida, nombre y descripcion
                $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $id_partida);
                $stmtPartida->execute();
                $resultadoPartida = $stmtPartida->get_result();
                $partidaInfo = $resultadoPartida->fetch_assoc();

                // Obtener el sector desde pl_sectores
                $sqlSector = "SELECT sector AS sector_numero FROM pl_sectores WHERE id = ?";
                $stmtSector = $conexion->prepare($sqlSector);
                $stmtSector->bind_param("i", $id_sector);
                $stmtSector->execute();
                $resultadoSector = $stmtSector->get_result();
                $sectorInfo = $resultadoSector->fetch_assoc();

                // Obtener el programa desde pl_programas
                $sqlPrograma = "SELECT programa AS programa_numero FROM pl_programas WHERE id = ?";
                $stmtPrograma = $conexion->prepare($sqlPrograma);
                $stmtPrograma->bind_param("i", $id_programa);
                $stmtPrograma->execute();
                $resultadoPrograma = $stmtPrograma->get_result();
                $programaInfo = $resultadoPrograma->fetch_assoc();

                // Añadir el sector y el programa a la información de distribución
                $distribucionInfo['sector'] = $sectorInfo['sector_numero'] ?? null;
                $distribucionInfo['programa'] = $programaInfo['programa_numero'] ?? null;

                // Obtener información del beneficiario según el tipo_beneficiario
                if ($tipo_beneficiario == 0) {
                    $sqlBeneficiario = "SELECT * FROM entes_dependencias WHERE id = ?";
                } else {
                    $sqlBeneficiario = "SELECT * FROM empleados WHERE id = ?";
                }
                $stmtBeneficiario = $conexion->prepare($sqlBeneficiario);
                $stmtBeneficiario->bind_param("i", $id_beneficiario);
                $stmtBeneficiario->execute();
                $resultadoBeneficiario = $stmtBeneficiario->get_result();
                $informacionBeneficiario = $resultadoBeneficiario->fetch_assoc();

                // Buscar el registro en la tabla compromisos
                $sqlCompromiso = "SELECT id, correlativo FROM compromisos WHERE id_registro = ? AND tabla_registro = 'gastos'";
                $stmtCompromiso = $conexion->prepare($sqlCompromiso);
                $stmtCompromiso->bind_param("i", $id);
                $stmtCompromiso->execute();
                $resultadoCompromiso = $stmtCompromiso->get_result();
                $compromiso = $resultadoCompromiso->fetch_assoc();
                $idCompromiso = $compromiso['id'] ?? null;
                $correlativo = $compromiso['correlativo'] ?? null;

                // Construir el array con los datos obtenidos
                $resultado = [
                    'id' => $id_gasto,
                    'nombre_tipo_gasto' => $nombreTipoGasto,
                    'partida' => $partidaInfo['partida'] ?? null,
                    'nombre_partida' => $partidaInfo['nombre'] ?? null,
                    'tipo_beneficiario' => $tipo_beneficiario,
                    'descripcion_partida' => $partidaInfo['descripcion'] ?? null,
                    'descripcion_gasto' => $descripcion,
                    'monto_gasto' => $monto,
                    'fecha' => $fecha,
                    'correlativo' => $correlativo,
                    'status_gasto' => $status,
                    'informacion_beneficiario' => $informacionBeneficiario,
                    'id_compromiso' => $idCompromiso,
                    'informacion_distribucion' => $distribucionInfo // Agregar la información completa de distribucion_presupuestaria con sector y programa
                ];

                return json_encode($resultado);
            } else {
                throw new Exception("No se encontró la distribución presupuestaria correspondiente.");
            }
        } else {
            throw new Exception("Gasto no encontrado.");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}




function actualizarGasto($id, $id_tipo, $descripcion, $monto, $status, $id_ejercicio, $tipo_beneficiario, $id_beneficiario, $id_distribucion)
{
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
function eliminarGasto($id)
{
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
                $data["id_distribucion"],
                $data["fecha"],
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


