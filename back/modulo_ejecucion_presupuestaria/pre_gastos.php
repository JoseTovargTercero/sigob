<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado
require_once 'pre_dispo_presupuestaria.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para crear un nuevo gasto
function crearGasto($id_tipo, $descripcion, $monto, $id_ejercicio, $beneficiario, $identificador, $distribuciones, $fecha)
{
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id_tipo) || empty($descripcion) || empty($monto) || empty($id_ejercicio) || empty($beneficiario) || empty($identificador) || empty($distribuciones) || empty($fecha)) {
            throw new Exception("Faltaron uno o más valores (id_tipo, descripción, monto, id_ejercicio, beneficiario, identificador, distribuciones, fecha)");
        }

        // Decodificar el JSON de distribuciones
        $distribucionesArray = $distribuciones;
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("El formato de distribuciones no es válido");
        }

        // Verificar cada distribución presupuestaria y si el presupuesto es suficiente
        foreach ($distribucionesArray as $distribucion) {
            $id_distribucion = $distribucion['id_distribucion'];
            $monto_distribucion = $distribucion['monto'];

            // Paso 1: Buscar id_partida en la tabla distribucion_presupuestaria usando id_distribucion
            $sqlDistribucionPresupuestaria = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucionPresupuestaria = $conexion->prepare($sqlDistribucionPresupuestaria);
            $stmtDistribucionPresupuestaria->bind_param("i", $id_distribucion);
            $stmtDistribucionPresupuestaria->execute();
            $resultadoDistribucionPresupuestaria = $stmtDistribucionPresupuestaria->get_result();

            if ($resultadoDistribucionPresupuestaria->num_rows === 0) {
                throw new Exception("No existe una distribución presupuestaria con el ID proporcionado: $id_distribucion");
            }

            $filaDistribucionPresupuestaria = $resultadoDistribucionPresupuestaria->fetch_assoc();
            $id_partida = $filaDistribucionPresupuestaria['id_partida'];

            // Verificar si el presupuesto es suficiente para cada distribución
            $disponible = consultarDisponibilidad($id_partida, $id_ejercicio, $monto_distribucion);
            if (!$disponible) {
                throw new Exception("El presupuesto actual es insuficiente para el monto del gasto en la distribución con ID: $id_distribucion");
            }
        }

        // Insertar el gasto si el presupuesto es suficiente para todas las distribuciones
        $sqlInsertGasto = "INSERT INTO gastos (id_tipo, descripcion, monto, status, id_ejercicio, beneficiario, identificador, distribuciones, fecha) VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?)";
        $stmtInsertGasto = $conexion->prepare($sqlInsertGasto);
        $jsonDistribuciones = json_encode($distribucionesArray);  // Convertir el array de distribuciones de nuevo a JSON
        $stmtInsertGasto->bind_param("ississss", $id_tipo, $descripcion, $monto, $id_ejercicio, $beneficiario, $identificador, $jsonDistribuciones, $fecha);
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



function gestionarGasto($idGasto, $accion, $codigo)
{
    global $conexion;

    try {
        if (empty($idGasto) || empty($accion)) {
            throw new Exception("Faltan uno o más valores necesarios (idGasto, accion)");
        }

        // Consultar los detalles del gasto, incluyendo el campo `distribuciones`
        $sqlGasto = "SELECT id_tipo, descripcion, monto, id_ejercicio, distribuciones, status FROM gastos WHERE id = ?";
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
        $montoTotal = $filaGasto['monto'];
        $id_ejercicio = $filaGasto['id_ejercicio'];
        $distribuciones = json_decode($filaGasto['distribuciones'], true);
        $status = $filaGasto['status'];

        if ($status !== 0) {
            throw new Exception("El gasto ya ha sido procesado anteriormente");
        }

        if ($accion === "aceptar") {
            // Iterar sobre cada distribución en el array `distribuciones`
            foreach ($distribuciones as $distribucion) {
                $id_distribucion = $distribucion['id_distribucion'];
                $monto = $distribucion['monto'];

                // Consultar `id_partida` en `distribucion_presupuestaria` usando `id_distribucion`
                $sqlDistribucionPresupuestaria = "SELECT id_partida, monto_actual FROM distribucion_presupuestaria WHERE id = ?";
                $stmtDistribucionPresupuestaria = $conexion->prepare($sqlDistribucionPresupuestaria);
                $stmtDistribucionPresupuestaria->bind_param("i", $id_distribucion);
                $stmtDistribucionPresupuestaria->execute();
                $resultadoDistribucionPresupuestaria = $stmtDistribucionPresupuestaria->get_result();

                if ($resultadoDistribucionPresupuestaria->num_rows === 0) {
                    throw new Exception("No se encontró una distribución presupuestaria con el ID proporcionado");
                }

                $filaDistribucionPresupuestaria = $resultadoDistribucionPresupuestaria->fetch_assoc();
                $id_partida = $filaDistribucionPresupuestaria['id_partida'];
                $monto_actual = $filaDistribucionPresupuestaria['monto_actual'];

                // Consultar disponibilidad presupuestaria para esta distribución
                $disponible = consultarDisponibilidad($id_partida, $id_ejercicio, $monto);

                if (!$disponible) {
                    throw new Exception("El presupuesto actual es insuficiente para el monto de la distribución con ID $id_distribucion");
                }

                // Calcular y actualizar el monto disponible en `distribucion_presupuestaria`
                $nuevoMontoActual = (float) $monto_actual - (float) $monto;
                $sqlUpdateDistribucion = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id = ?";
                $stmtUpdateDistribucion = $conexion->prepare($sqlUpdateDistribucion);
                $stmtUpdateDistribucion->bind_param("di", $nuevoMontoActual, $id_distribucion);
                $stmtUpdateDistribucion->execute();

                if ($stmtUpdateDistribucion->affected_rows === 0) {
                    throw new Exception("No se pudo actualizar el monto actual para la distribución con ID $id_distribucion");
                }
            }

            // Actualizar el estado del gasto a aceptado
            $sqlUpdateGasto = "UPDATE gastos SET status = 1 WHERE id = ?";
            $stmtUpdateGasto = $conexion->prepare($sqlUpdateGasto);
            $stmtUpdateGasto->bind_param("i", $idGasto);
            $stmtUpdateGasto->execute();

            if ($stmtUpdateGasto->affected_rows > 0) {
                $resultadoCompromiso = registrarCompromiso($idGasto, 'gastos', $descripcion, $id_ejercicio, $codigo);

                if (isset($resultadoCompromiso['success']) && $resultadoCompromiso['success']) {
                    return json_encode([
                        "success" => "El gasto ha sido aceptado, el compromiso se ha registrado y el presupuesto actualizado",
                        "compromiso" => [
                            "correlativo" => $resultadoCompromiso['correlativo'],
                            "id_compromiso" => $resultadoCompromiso['id_compromiso']
                        ]
                    ]);
                } else {
                    throw new Exception("No se pudo registrar el compromiso");
                }
            } else {
                throw new Exception("No se pudo actualizar el gasto a aceptado");
            }

        } elseif ($accion === "rechazar") {
            // Actualizar el estado del gasto a rechazado
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




function obtenerGastos($id_ejercicio)
{
    global $conexion;

    try {
        $sql = "SELECT id, id_tipo, descripcion, monto, status, id_ejercicio, distribuciones, fecha, beneficiario, identificador 
                FROM gastos 
                WHERE id_ejercicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_ejercicio);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $gastos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $id = $fila['id'];
            $id_tipo = $fila['id_tipo'];
            $descripcion = $fila['descripcion'];
            $monto = $fila['monto'];
            $status = $fila['status'];
            $fecha = $fila['fecha'];
            $beneficiario = $fila['beneficiario'];
            $identificador = $fila['identificador'];
            $id_ejercicio = $fila['id_ejercicio'];
            $distribuciones = json_decode($fila['distribuciones'], true);

            // Consultar el nombre del tipo de gasto
            $sqlTipoGasto = "SELECT nombre FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();
            $nombreTipoGasto = $resultadoTipoGasto->fetch_assoc()['nombre'] ?? null;

            // Obtener el correlativo del compromiso relacionado
            $sqlCompromiso = "SELECT id, correlativo, numero_compromiso FROM compromisos WHERE id_registro = ? AND tabla_registro = 'gastos'";
            $stmtCompromiso = $conexion->prepare($sqlCompromiso);
            $stmtCompromiso->bind_param("i", $id);
            $stmtCompromiso->execute();
            $resultadoCompromiso = $stmtCompromiso->get_result();
            $compromiso = $resultadoCompromiso->fetch_assoc();
            $idCompromiso = $compromiso['id'] ?? null;
            $correlativo = $compromiso['correlativo'] ?? null;
            $numero_compromiso = $compromiso['numero_compromiso'] ?? null;

            // Preparar los detalles de las distribuciones
            $informacionDistribuciones = [];
            foreach ($distribuciones as $distribucion) {
                $id_distribucion = $distribucion['id_distribucion'];
                $montoDistribucion = $distribucion['monto'];

                // Obtener detalles de la distribución
                $sqlDistribucion = "SELECT id, id_partida, id_sector, id_programa FROM distribucion_presupuestaria WHERE id = ?";
                $stmtDistribucion = $conexion->prepare($sqlDistribucion);
                $stmtDistribucion->bind_param("i", $id_distribucion);
                $stmtDistribucion->execute();
                $resultadoDistribucion = $stmtDistribucion->get_result();
                $distribucionInfo = $resultadoDistribucion->fetch_assoc();

                if ($distribucionInfo) {
                    $id_partida = $distribucionInfo['id_partida'];
                    $id_sector = $distribucionInfo['id_sector'];
                    $id_programa = $distribucionInfo['id_programa'];

                    // Consultar detalles de la partida
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

                    // Añadir sector y programa a la información de distribución
                    $distribucionInfo['sector'] = $sectorInfo['sector_numero'] ?? null;
                    $distribucionInfo['programa'] = $programaInfo['programa_numero'] ?? null;

                    // Agregar la distribución al array de detalles
                    $informacionDistribuciones[] = [
                        'id_distribucion' => $id_distribucion,
                        'monto' => $montoDistribucion,
                        'partida' => $partidaInfo['partida'] ?? null,
                        'nombre_partida' => $partidaInfo['nombre'] ?? null,
                        'descripcion_partida' => $partidaInfo['descripcion'] ?? null,
                        'sector' => $sectorInfo['sector_numero'] ?? null,
                        'programa' => $programaInfo['programa_numero'] ?? null,
                    ];
                }
            }

            // Construir el array con la información completa del gasto
            $gasto = [
                'id' => $id,
                'fecha' => $fecha,
                'nombre_tipo_gasto' => $nombreTipoGasto,
                'descripcion_gasto' => $descripcion,
                'monto_gasto' => $monto,
                'status_gasto' => $status,
                'id_ejercicio' => $id_ejercicio,
                'beneficiario' => $beneficiario,
                'identificador' => $identificador,
                'distribuciones' => $informacionDistribuciones,
                'correlativo' => $correlativo,  // Agregado el correlativo
                'numero_compromiso' => $numero_compromiso,  // Agregado el correlativo
                'id_compromiso' => $idCompromiso,  // Agregado el correlativo
            ];

            $gastos[] = $gasto;
        }

        // Devolver los datos en formato JSON
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
        $sqlGasto = "SELECT id, id_tipo, descripcion, monto, status, distribuciones, fecha, beneficiario, identificador, id_ejercicio FROM gastos WHERE id = ?";
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
            $fecha = $gasto['fecha'];
            $beneficiario = $gasto['beneficiario'];
            $identificador = $gasto['identificador'];
            $id_ejercicio = $gasto['id_ejercicio'];
            $distribuciones = json_decode($gasto['distribuciones'], true);

            // Consultar la tabla tipo_gastos para obtener el nombre del tipo de gasto
            $sqlTipoGasto = "SELECT nombre FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();
            $nombreTipoGasto = $resultadoTipoGasto->fetch_assoc()['nombre'] ?? null;

            // Procesar las distribuciones para obtener información detallada de cada distribución
            $informacionDistribuciones = [];
            foreach ($distribuciones as $distribucion) {
                $id_distribucion = $distribucion['id_distribucion'];
                $montoDistribucion = $distribucion['monto'];

                $sqlDistribucion = "SELECT id, id_partida, id_sector, id_programa FROM distribucion_presupuestaria WHERE id = ?";
                $stmtDistribucion = $conexion->prepare($sqlDistribucion);
                $stmtDistribucion->bind_param("i", $id_distribucion);
                $stmtDistribucion->execute();
                $resultadoDistribucion = $stmtDistribucion->get_result();
                $distribucionInfo = $resultadoDistribucion->fetch_assoc();

                if ($distribucionInfo) {
                    $id_partida = $distribucionInfo['id_partida'];
                    $id_sector = $distribucionInfo['id_sector'];
                    $id_programa = $distribucionInfo['id_programa'];

                    // Obtener detalles de la partida
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

                    // Agregar información completa de la distribución al array de resultados
                    $informacionDistribuciones[] = [
                        'id_distribucion' => $id_distribucion,
                        'monto' => $montoDistribucion,
                        'partida' => $partidaInfo['partida'] ?? null,
                        'nombre_partida' => $partidaInfo['nombre'] ?? null,
                        'descripcion_partida' => $partidaInfo['descripcion'] ?? null,
                        'sector' => $sectorInfo['sector_numero'] ?? null,
                        'programa' => $programaInfo['programa_numero'] ?? null
                    ];
                }
            }

            // Buscar el registro en la tabla compromisos
            $sqlCompromiso = "SELECT id, correlativo, numero_compromiso FROM compromisos WHERE id_registro = ? AND tabla_registro = 'gastos'";
            $stmtCompromiso = $conexion->prepare($sqlCompromiso);
            $stmtCompromiso->bind_param("i", $id);
            $stmtCompromiso->execute();
            $resultadoCompromiso = $stmtCompromiso->get_result();
            $compromiso = $resultadoCompromiso->fetch_assoc();
            $idCompromiso = $compromiso['id'] ?? null;
            $correlativo = $compromiso['correlativo'] ?? null;
            $numero_compromiso = $compromiso['numero_compromiso'] ?? null;

            // Construir el array con los datos obtenidos
            $resultado = [
                'id' => $id_gasto,
                'nombre_tipo_gasto' => $nombreTipoGasto,
                'descripcion_gasto' => $descripcion,
                'monto_gasto' => $monto,
                'fecha' => $fecha,
                'correlativo' => $correlativo,
                'numero_compromiso' => $numero_compromiso,
                'status_gasto' => $status,
                'beneficiario' => $beneficiario,
                'identificador' => $identificador,
                'id_compromiso' => $idCompromiso,
                'id_ejercicio' => $id_ejercicio,
                'informacion_distribuciones' => $informacionDistribuciones // Distribuciones con detalles
            ];

            return json_encode($resultado);
        } else {
            throw new Exception("Gasto no encontrado.");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}





function actualizarGasto($id, $id_tipo, $descripcion, $monto, $status, $id_ejercicio, $beneficiario, $identificador, $distribuciones)
{
    global $conexion;

    try {
        // Validar que los campos obligatorios no estén vacíos
        if (empty($id) || empty($id_tipo) || empty($descripcion) || empty($monto) || empty($status) || empty($id_ejercicio) || empty($beneficiario) || empty($identificador) || empty($distribuciones)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        // Convertir el array de distribuciones en JSON
        $distribuciones_json = json_encode($distribuciones);

        // Actualizar el registro en la tabla 'gastos' con los campos adicionales
        $sql = "UPDATE gastos SET id_tipo = ?, descripcion = ?, monto = ?, status = ?, id_ejercicio = ?, beneficiario = ?, identificador = ?, distribuciones = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isdiisssi", $id_tipo, $descripcion, $monto, $status, $id_ejercicio, $beneficiario, $identificador, $distribuciones_json, $id);

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

/// Procesar la solicitud
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
                $data["beneficiario"],
                $data["identificador"],
                $data["distribuciones"], // El parámetro distribuciones ahora es un array JSON
                $data["fecha"]
            );
            break;

        case 'obtener':
            echo obtenerGastos($data["id_ejercicio"]);
            break;

        case 'obtenerPorId':
            echo obtenerGastoPorId($data["id"], $data["id_ejercicio"]);
            break;

        case 'actualizar':
            echo actualizarGasto(
                $data["id"],
                $data["id_tipo"],
                $data["descripcion"],
                $data["monto"],
                $data["status"],
                $data["id_ejercicio"],
                $data["beneficiario"], // Se pasa beneficiario
                $data["identificador"], // Se pasa identificador
                $data["distribuciones"] // Se pasa distribuciones como JSON
            );
            break;

        case 'eliminar':
            echo eliminarGasto($data["id"]);
            break;

        case 'gestionar':  // Nueva opción para aceptar o rechazar
            echo gestionarGasto($data["id"], $data["accion_gestion"], $data["codigo"]);
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    echo json_encode(['error' => 'No se especificó ninguna acción']);
}


