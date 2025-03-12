<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado
require_once 'pre_dispo_presupuestaria.php'; // Agregado


header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

function crearGasto($id_tipo, $descripcion, $monto, $id_ejercicio, $beneficiario, $identificador, $distribuciones, $fecha, $codigo)
{
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id_tipo) || empty($descripcion) || empty($monto) || empty($id_ejercicio) || empty($beneficiario) || empty($identificador) || empty($codigo) || empty($distribuciones) || empty($fecha)) {
            throw new Exception("Faltaron uno o más valores (id_tipo, descripción, monto, id_ejercicio, beneficiario, identificador, codigo, distribuciones, fecha)");
        }

        // Decodificar el JSON de distribuciones si es necesario
        $distribucionesArray = $distribuciones;
        if (!is_array($distribucionesArray)) {
            throw new Exception("El formato de distribuciones no es válido");
        }

        // Calcular la sumatoria de los montos en distribuciones
        $sumaDistribuciones = array_sum(array_column($distribucionesArray, 'monto'));
        
        if ($sumaDistribuciones > $monto) {
            throw new Exception("La sumatoria de las partidas ({$sumaDistribuciones}) no puede ser superior al monto total ({$monto})");
        }
        if ($sumaDistribuciones < $monto) {
            throw new Exception("La sumatoria de las partidas ({$sumaDistribuciones}) no puede ser menor al monto total ({$monto})");
        }

        require_once '../sistema_global/sigob_api.php';
        $disponible = consultarDisponibilidadApi($distribuciones, $id_ejercicio);

        if (isset($disponible['error'])) {
            throw new Exception($disponible['error']);
        }
        if (!isset($disponible['success'])) {
            throw new Exception('Error al acceder a la respuesta');
        }

        if (!$disponible) {
            throw new Exception('No se pudo verificar la disponibilidad presupuestaria');
        }

        // Insertar el gasto si el presupuesto es suficiente para todas las distribuciones
        $sqlInsertGasto = "INSERT INTO gastos (id_tipo, descripcion, monto, status, id_ejercicio, beneficiario, identificador, distribuciones, fecha) VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?)";
        $stmtInsertGasto = $conexion->prepare($sqlInsertGasto);
        $jsonDistribuciones = json_encode($distribucionesArray);  // Convertir el array de distribuciones de nuevo a JSON
        $stmtInsertGasto->bind_param("ississss", $id_tipo, $descripcion, $monto, $id_ejercicio, $beneficiario, $identificador, $jsonDistribuciones, $fecha);
        $stmtInsertGasto->execute();

        if ($stmtInsertGasto->affected_rows > 0) {
            $id_insertado = $stmtInsertGasto->insert_id; // Obtener el ID insertado
             $resultadoCompromiso = registrarCompromiso($id_insertado, 'gastos', $descripcion, $id_ejercicio, $codigo);

                if (isset($resultadoCompromiso['success']) && $resultadoCompromiso['success']) {
                   
                    return json_encode([
                        "success" => "El gasto ha sido registrado, el compromiso se ha registrado",
                        "compromiso" => [
                            "correlativo" => $resultadoCompromiso['correlativo'],
                            "id_compromiso" => $resultadoCompromiso['id_compromiso']
                        ]
                    ]);
                } else {
                    throw new Exception("No se pudo registrar el compromiso");
                }
        } else {
            throw new Exception("No se pudo registrar el gasto");
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

    $conexion->begin_transaction();

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

            require_once '../sistema_global/sigob_api.php';
            require_once 'pre_dispo_presupuestaria2.php'; // Agregado
            $disponible = consultarDisponibilidadApi($distribuciones, $id_ejercicio);
            $disponible2 = consultarDisponibilidad2($distribuciones, $id_ejercicio, $conexion);

            if (isset($disponible['error'])) {
                throw new Exception($disponible['error']);
            }
            if (!isset($disponible['success'])) {
                throw new Exception('Error al acceder a la respuesta');
            }

            if (!$disponible) {
                throw new Exception('No se pudo verificar la disponibilidad presupuestaria');
            }

            $actualizar = actualizarDistribucionApi($distribuciones, $id_ejercicio);
            $actualizar2 = actualizarDistribucion2($distribuciones, $id_ejercicio, $conexion);
            if (isset($actualizar['error']) || isset($actualizar2['error']) ) {
                throw new Exception($actualizar['error']);
            }

            if (!isset($actualizar['success']) || !isset($actualizar2['success'])) {
                throw new Exception('Error al acceder a la respuesta al actualizar monto');
            }

            if (!$actualizar || !$actualizar2) {
                throw new Exception('No se pudo actualizar el mondo en la distribucion');
            }



            // Actualizar el estado del gasto a aceptado
            $sqlUpdateGasto = "UPDATE gastos SET status = 1 WHERE id = ?";
            $stmtUpdateGasto = $conexion->prepare($sqlUpdateGasto);
            $stmtUpdateGasto->bind_param("i", $idGasto);
            $stmtUpdateGasto->execute();

            if ($stmtUpdateGasto->affected_rows > 0) {
                 $conexion->commit();
                 return json_encode(["success" => "El gasto ha sido aceptado y el presupuesto actualizado",]);
               
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

                $conexion->commit();
                return json_encode(["success" => "El gasto ha sido rechazado"]);
            } else {
                throw new Exception("No se pudo rechazar el gasto");
            }
        } else {
            throw new Exception("Acción no válida. Debe ser 'aceptar' o 'rechazar'.");
        }
    } catch (Exception $e) {
        $conexion->rollback();
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
                WHERE id_ejercicio = ?
                ORDER BY fecha";
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
                $sqlDistribucion = "SELECT id, id_partida, id_sector, id_programa, id_proyecto, id_actividad FROM distribucion_presupuestaria WHERE id = ?";
                $stmtDistribucion = $conexion->prepare($sqlDistribucion);
                $stmtDistribucion->bind_param("i", $id_distribucion);
                $stmtDistribucion->execute();
                $resultadoDistribucion = $stmtDistribucion->get_result();
                $distribucionInfo = $resultadoDistribucion->fetch_assoc();

                if ($distribucionInfo) {
                    $id_partida = $distribucionInfo['id_partida'];
                    $id_sector = $distribucionInfo['id_sector'];
                    $id_programa = $distribucionInfo['id_programa'];
                    $id_proyecto = $distribucionInfo['id_proyecto'];
                    $id_actividad = $distribucionInfo['id_actividad'];

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

                    $proyectoInfo = null;
                    // Consultar proyecto
                if ($id_proyecto) {
                $queryProyecto = "SELECT proyecto_id FROM pl_proyectos WHERE id = ?";
                $stmtProyecto = $conexion->prepare($queryProyecto);
                $stmtProyecto->bind_param('i', $id_proyecto);
                $stmtProyecto->execute();
                $resultProyecto = $stmtProyecto->get_result();
                $proyectoInfo = $resultProyecto->fetch_assoc();
                }

                    // Añadir sector y programa a la información de distribución
                    $distribucionInfo['sector'] = $sectorInfo['sector_numero'] ?? null;
                    $distribucionInfo['programa'] = $programaInfo['programa_numero'] ?? null;
                    $distribucionInfo['proyecto'] = $proyectoInfo['proyectoInfo'] ?? null;
                    $distribucionInfo['actividad'] = $id_actividad ?? null;

                    // Agregar la distribución al array de detalles
                    $informacionDistribuciones[] = [
                        'id_distribucion' => $id_distribucion,
                        'monto' => $montoDistribucion,
                        'partida' => $partidaInfo['partida'] ?? null,
                        'nombre_partida' => $partidaInfo['nombre'] ?? null,
                        'descripcion_partida' => $partidaInfo['descripcion'] ?? null,
                        'sector' => $sectorInfo['sector_numero'] ?? null,
                        'programa' => $programaInfo['programa_numero'] ?? null,
                        'proyecto' => $programaInfo['proyecto'] ?? null,
                        'actividad' => $id_actividad ?? null,
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
function obtenerSumatoriaPorTrimestre($id_ejercicio)
{
    global $conexion;
    // Inicializar las sumatorias por trimestre
    $trimestres = [
        'T1' => 0, // Enero a Marzo
        'T2' => 0, // Abril a Junio
        'T3' => 0, // Julio a Septiembre
        'T4' => 0  // Octubre a Diciembre
    ];


    try {

        // Consultar los montos de la tabla `gastos` para el ejercicio especificado
        $sql = "SELECT monto, fecha FROM gastos WHERE id_ejercicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_ejercicio);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            // Procesar los resultados
            while ($row = $result->fetch_assoc()) {
                $monto = $row['monto'];
                $fecha = $row['fecha'];
                $mes = (int) date('m', strtotime($fecha)); // Extraer el mes de la fecha

                // Determinar el trimestre y sumar el monto
                if ($mes >= 1 && $mes <= 3) {
                    $trimestres['T1'] += $monto;
                } elseif ($mes >= 4 && $mes <= 6) {
                    $trimestres['T2'] += $monto;
                } elseif ($mes >= 7 && $mes <= 9) {
                    $trimestres['T3'] += $monto;
                } elseif ($mes >= 10 && $mes <= 12) {
                    $trimestres['T4'] += $monto;
                }
            }
        }

        return json_encode([
            "success" => true,
            "data" => $trimestres
        ]);
    } catch (Exception $e) {
        return json_encode([
            "success" => false,
            "error" => $e->getMessage()
        ]);
    }
}





function obtenerGastoPorId($id)
{
    global $conexion;

    try {
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

            $sqlTipoGasto = "SELECT id, nombre FROM tipo_gastos WHERE id = ?";
            $stmtTipoGasto = $conexion->prepare($sqlTipoGasto);
            $stmtTipoGasto->bind_param("i", $id_tipo);
            $stmtTipoGasto->execute();
            $resultadoTipoGasto = $stmtTipoGasto->get_result();
            $tipoGastoFila = $resultadoTipoGasto->fetch_assoc();
            $idTipoGasto = $tipoGastoFila['id'] ?? null;
            $nombreTipoGasto = $tipoGastoFila['nombre'] ?? null;

            $informacionDistribuciones = [];
            foreach ($distribuciones as $distribucion) {
                $id_distribucion = $distribucion['id_distribucion'];

                $sqlDistribucionEnte = "SELECT id, distribucion FROM distribucion_entes WHERE id_ejercicio = ? AND distribucion LIKE ?";
                $likePattern = '%"id_distribucion":"' . $id_distribucion . '"%';
                $stmtDistribucionEnte = $conexion->prepare($sqlDistribucionEnte);
                $stmtDistribucionEnte->bind_param("is", $id_ejercicio, $likePattern);
                $stmtDistribucionEnte->execute();
                $resultadoDistribucionEnte = $stmtDistribucionEnte->get_result();

                if ($distribucionEnte = $resultadoDistribucionEnte->fetch_assoc()) {
                    $id_distribucion_ente = $distribucionEnte['id'];
                    $distribucionData = json_decode($distribucionEnte['distribucion'], true);

                    foreach ($distribucionData as $dist) {
                        if ($dist['id_distribucion'] == $id_distribucion) {
                            $montoDistribucion = $dist['monto'];
                            break;
                        }
                    }

                    $sqlDistribucion = "SELECT id, id_partida, id_sector, id_programa, id_proyecto, id_actividad FROM distribucion_presupuestaria WHERE id = ?";
                    $stmtDistribucion = $conexion->prepare($sqlDistribucion);
                    $stmtDistribucion->bind_param("i", $id_distribucion);
                    $stmtDistribucion->execute();
                    $resultadoDistribucion = $stmtDistribucion->get_result();
                    $distribucionInfo = $resultadoDistribucion->fetch_assoc();

                    if ($distribucionInfo) {
                        $id_partida = $distribucionInfo['id_partida'];
                        $id_sector = $distribucionInfo['id_sector'];
                        $id_programa = $distribucionInfo['id_programa'];
                        $id_proyecto = $distribucionInfo['id_proyecto'];
                        $id_actividad = $distribucionInfo['id_actividad'];


                        $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                        $stmtPartida = $conexion->prepare($sqlPartida);
                        $stmtPartida->bind_param("i", $id_partida);
                        $stmtPartida->execute();
                        $resultadoPartida = $stmtPartida->get_result();
                        $partidaInfo = $resultadoPartida->fetch_assoc();

                        $sqlSector = "SELECT sector AS sector_numero FROM pl_sectores WHERE id = ?";
                        $stmtSector = $conexion->prepare($sqlSector);
                        $stmtSector->bind_param("i", $id_sector);
                        $stmtSector->execute();
                        $resultadoSector = $stmtSector->get_result();
                        $sectorInfo = $resultadoSector->fetch_assoc();

                        $sqlPrograma = "SELECT programa AS programa_numero FROM pl_programas WHERE id = ?";
                        $stmtPrograma = $conexion->prepare($sqlPrograma);
                        $stmtPrograma->bind_param("i", $id_programa);
                        $stmtPrograma->execute();
                        $resultadoPrograma = $stmtPrograma->get_result();
                        $programaInfo = $resultadoPrograma->fetch_assoc();

                         $proyectoInfo = null;
                     if ($id_proyecto) {
                $queryProyecto = "SELECT proyecto_id FROM pl_proyectos WHERE id = ?";
                $stmtProyecto = $conexion->prepare($queryProyecto);
                $stmtProyecto->bind_param('i', $id_proyecto);
                $stmtProyecto->execute();
                $resultProyecto = $stmtProyecto->get_result();
                $proyectoInfo = $resultProyecto->fetch_assoc();
                }

                        $informacionDistribuciones[] = [
                            'id_distribucion' => $distribucionInfo["id"],
                            'monto' => $montoDistribucion,
                            'partida' => $partidaInfo['partida'] ?? null,
                            'nombre_partida' => $partidaInfo['nombre'] ?? null,
                            'descripcion_partida' => $partidaInfo['descripcion'] ?? null,
                            'sector' => $sectorInfo['sector_numero'] ?? null,
                            'programa' => $programaInfo['programa_numero'] ?? null
                            'proyecto' => $proyectoInfo['proyecto'] ?? null,
                            'actividad' => $id_actividad ?? null,
                        ];
                    }
                }
            }

            $sqlCompromiso = "SELECT id, correlativo, numero_compromiso FROM compromisos WHERE id_registro = ? AND tabla_registro = 'gastos'";
            $stmtCompromiso = $conexion->prepare($sqlCompromiso);
            $stmtCompromiso->bind_param("i", $id);
            $stmtCompromiso->execute();
            $resultadoCompromiso = $stmtCompromiso->get_result();
            $compromiso = $resultadoCompromiso->fetch_assoc();

            $resultado = [
                'id' => $id_gasto,
                'nombre_tipo_gasto' => $nombreTipoGasto,
                'id_tipo' => $idTipoGasto,
                'descripcion_gasto' => $descripcion,
                'monto_gasto' => $monto,
                'fecha' => $fecha,
                'correlativo' => $compromiso['correlativo'] ?? null,
                'numero_compromiso' => $compromiso['numero_compromiso'] ?? null,
                'status_gasto' => $status,
                'beneficiario' => $beneficiario,
                'identificador' => $identificador,
                'id_compromiso' => $compromiso['id'] ?? null,
                'id_ejercicio' => $id_ejercicio,
                'informacion_distribuciones' => $informacionDistribuciones
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





function actualizarGasto($id, $id_tipo, $descripcion, $monto, $id_ejercicio, $beneficiario, $identificador, $distribuciones)
{
    global $conexion;

    try {
        // Validar que los campos obligatorios no estén vacíos
        if (empty($id) || empty($id_tipo) || empty($descripcion) || empty($monto) || empty($id_ejercicio) || empty($beneficiario) || empty($identificador) || empty($distribuciones)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        // Convertir el array de distribuciones en JSON
        $distribuciones_json = json_encode($distribuciones);


        // Actualizar el registro en la tabla 'gastos' con los campos adicionales
        $sql = "UPDATE gastos SET id_tipo = ?, descripcion = ?, monto = ?, status = 0, id_ejercicio = ?, beneficiario = ?, identificador = ?, distribuciones = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isdiissi", $id_tipo, $descripcion, $monto, $id_ejercicio, $beneficiario, $identificador, $distribuciones_json, $id);

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
        // Iniciar una transacción para asegurar la integridad de los datos
        $conexion->begin_transaction();

        // Eliminar el registro de la tabla 'compromisos'
        $sqlCompromisos = "DELETE FROM compromisos WHERE id_registro = ? AND tabla_registro = 'gastos'";
        $stmtCompromisos = $conexion->prepare($sqlCompromisos);
        $stmtCompromisos->bind_param("i", $id);
        if (!$stmtCompromisos->execute()) {
            throw new Exception("Error al eliminar el compromiso asociado.");
        }

        // Eliminar el registro de la tabla 'gastos'
        $sqlGastos = "DELETE FROM gastos WHERE id = ?";
        $stmtGastos = $conexion->prepare($sqlGastos);
        $stmtGastos->bind_param("i", $id);
        if (!$stmtGastos->execute()) {
            throw new Exception("Error al eliminar el gasto.");
        }

        // Confirmar la transacción
        $conexion->commit();

        return json_encode(['success' => 'Gasto y compromiso asociado eliminados exitosamente']);
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
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
                $data["fecha"],
                $data["codigo"]
            );
            break;

        case 'obtener':
            echo obtenerGastos($data["id_ejercicio"]);
            break;

        case 'obtener_trimestre':
            echo obtenerSumatoriaPorTrimestre($data["id_ejercicio"]);
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

            echo gestionarGasto($data["id"], $data["accion_gestion"]);
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    echo json_encode(['error' => 'No se especificó ninguna acción']);
}
