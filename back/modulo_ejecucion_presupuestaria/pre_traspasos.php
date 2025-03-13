<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
require_once 'pre_compromisos.php'; // Agregado
require_once 'pre_dispo_presupuestaria.php'; // Agregado

header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

function registrarTraspasoPartida($data)
{
    global $conexion;
    global $remote_db;

    $conexion = $remote_db;

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

        // Datos del traspaso
        $info = $data['info'];
        $añadir = $data['añadir'];
        $restar = $data['restar'];
        $tipo = $info['tipo'];
        $fecha_actual = date("Y-m-d");

        // Obtener el año del ejercicio fiscal
        $sqlEjercicio = "SELECT ano FROM ejercicio_fiscal WHERE id = ?";
        $stmtEjercicio = $conexion->prepare($sqlEjercicio);
        $stmtEjercicio->bind_param("i", $info['id_ejercicio']);
        $stmtEjercicio->execute();
        $resultadoEjercicio = $stmtEjercicio->get_result();

        if ($resultadoEjercicio->num_rows === 0) {
            throw new Exception("No se encontró el ejercicio fiscal con ID " . $info['id_ejercicio']);
        }

        $anoEjercicio = $resultadoEjercicio->fetch_assoc()['ano'];

        // Validación adicional para los `id_distribucion` en `añadir`
        foreach ($añadir as $item) {
            // Obtener el mes actual
            $fecha_actual_dt = new DateTime($fecha_actual);
            $mesActual = (int) $fecha_actual_dt->format('m'); // Mes actual en número

            // Solo validar si el mes está entre enero (1) y septiembre (8)
            if ($mesActual >= 1 && $mesActual <= 9) {
                $sqlValidacion = "
            SELECT 
                ti.id_distribucion, 
                t.id_ejercicio, 
                pp.partida 
            FROM 
                traspaso_informacion ti
            JOIN 
                traspasos t ON ti.id_traspaso = t.id
            JOIN 
                distribucion_presupuestaria dp ON ti.id_distribucion = dp.id
            JOIN 
                partidas_presupuestarias pp ON dp.id_partida = pp.id
            WHERE 
                ti.id_distribucion = ? 
                AND t.id_ejercicio = ?
                AND ti.tipo = 'D'";

                $stmtValidacion = $conexion->prepare($sqlValidacion);
                $stmtValidacion->bind_param("ii", $item['id_distribucion'], $info['id_ejercicio']);
                $stmtValidacion->execute();
                $resultadoValidacion = $stmtValidacion->get_result();

                if ($resultadoValidacion->num_rows > 0) {
                    $filaValidacion = $resultadoValidacion->fetch_assoc();
                    throw new Exception("La partida " . $filaValidacion['partida'] . " no está disponible para recibir dinero porque anteriormente traspasó dinero.");
                }
            }
        }




        // Procesar los datos de `restar` para verificar el 20%
        $partidaMontos = [];
        foreach ($restar as $item) {

            // Consulta en distribucion_entes para obtener el monto
            $id_distribucion = $item['id_distribucion'];
            $sqlDistribucionEnte = "SELECT id, distribucion FROM distribucion_entes WHERE id_ejercicio = ? AND distribucion LIKE ?";
            $likePattern = '%"id_distribucion":"' . $id_distribucion . '"%';
            $stmtDistribucionEnte = $conexion->prepare($sqlDistribucionEnte);
            $stmtDistribucionEnte->bind_param("is", $info['id_ejercicio'], $likePattern);
            $stmtDistribucionEnte->execute();
            $resultadoDistribucionEnte = $stmtDistribucionEnte->get_result();

            if ($distribucionEnte = $resultadoDistribucionEnte->fetch_assoc()) {
                $id_distribucion_ente = $distribucionEnte['id'];
                $distribucionData = json_decode($distribucionEnte['distribucion'], true);

                $montoDistribucion = 0; // Inicializar monto
                foreach ($distribucionData as $dist) {
                    if ($dist['id_distribucion'] == $id_distribucion) {
                        $montoDistribucion = $dist['monto']; // Asignar monto encontrado
                        break;
                    }
                }
            }

            // Si no se encuentra el monto en distribucion_entes, se puede manejar una excepción o asignar un valor predeterminado
            if ($montoDistribucion === 0) {
                throw new Exception("No se encontró la distribución correspondiente al ID " . $id_distribucion . " en distribucion_entes.");
            }

            // Continuar con la consulta en distribucion_presupuestaria para obtener id_partida
            $sqlDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucion = $conexion->prepare($sqlDistribucion);
            $stmtDistribucion->bind_param("i", $id_distribucion);
            $stmtDistribucion->execute();
            $resultadoDistribucion = $stmtDistribucion->get_result();

            if ($resultadoDistribucion->num_rows === 0) {
                throw new Exception("No se encontró la distribución presupuestaria con ID " . $id_distribucion);
            }

            $filaDistribucion = $resultadoDistribucion->fetch_assoc();
            $idPartida = $filaDistribucion['id_partida'];

            // Consulta la partida presupuestaria para obtener su clave
            $sqlPartida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $conexion->prepare($sqlPartida);
            $stmtPartida->bind_param("i", $idPartida);
            $stmtPartida->execute();
            $resultadoPartida = $stmtPartida->get_result();

            if ($resultadoPartida->num_rows === 0) {
                throw new Exception("No se encontró la partida presupuestaria con ID " . $idPartida);
            }

            $partida = $resultadoPartida->fetch_assoc()['partida'];
            $clavePartida = substr($partida, 0, 3);

            // Agrupar los montos por clave de partida
            if (!isset($partidaMontos[$clavePartida])) {
                $partidaMontos[$clavePartida] = 0;
            }

            // Usar el monto obtenido de distribucion_entes en lugar del monto_actual de distribucion_presupuestaria
            $partidaMontos[$clavePartida] += $montoDistribucion;

            // Calcular el 20% del monto total agrupado por los primeros tres dígitos de la partida
            $esValido = false;
            foreach ($partidaMontos as $clave => $montoTotal) {
                $limite = $montoTotal * 0.2;
                if ($info['monto_total'] <= $limite) {
                    $esValido = true;
                    break;
                }
            }


            // Determinar el formato de n_orden
            if ($tipo == 1) {
                if (!$esValido) {
                    throw new Exception("Un traslado no puede ser mayor al 20 por ciento de la agrupación de las partidas seleccionadas");
                } else {
                    $nOrden = "T" . $anoEjercicio . "-" . $info['n_orden'];
                }
            } elseif ($tipo == 2) {
                if (!$esValido) {
                    $nOrden = $info['n_orden'];
                } else {
                    throw new Exception("Un Traspaso no puede ser menor al 20 por ciento de la agrupación de las partidas seleccionadas");
                }
            } else {
                throw new Exception("Tipo inválido: " . $tipo);
            }

            // Insertar el registro principal en la tabla `traspasos`
            $sqlTraspaso = "INSERT INTO traspasos (n_orden, id_ejercicio, monto_total, fecha, status, tipo) VALUES (?, ?, ?, ?, 0, ?)";
            $stmtTraspaso = $conexion->prepare($sqlTraspaso);

            $stmtTraspaso->bind_param("sidsi", $nOrden, $info['id_ejercicio'], $info['monto_total'], $fecha_actual, $tipo);
            $stmtTraspaso->execute();

            if ($stmtTraspaso->affected_rows === 0) {
                throw new Exception("No se pudo registrar el traspaso principal.");
            }

            $id_traspaso = $conexion->insert_id;

            // Registrar información de traspasos en `traspaso_informacion`
            foreach ($restar as $item) {
                $sqlTraspasoInfo = "INSERT INTO traspaso_informacion (id_traspaso, id_distribucion, monto, tipo) VALUES (?, ?, ?, 'D')";
                $stmtTraspasoInfo = $conexion->prepare($sqlTraspasoInfo);
                $stmtTraspasoInfo->bind_param("iid", $id_traspaso, $item['id_distribucion'], $item['monto']);
                $stmtTraspasoInfo->execute();

                if ($stmtTraspasoInfo->affected_rows === 0) {
                    throw new Exception("No se pudo registrar la información del traspaso en 'restar' con ID distribución " . $item['id_distribucion']);
                }
            }

            foreach ($añadir as $item) {
                $sqlTraspasoInfo = "INSERT INTO traspaso_informacion (id_traspaso, id_distribucion, monto, tipo) VALUES (?, ?, ?, 'A')";
                $stmtTraspasoInfo = $conexion->prepare($sqlTraspasoInfo);
                $stmtTraspasoInfo->bind_param("iid", $id_traspaso, $item['id_distribucion'], $item['monto']);
                $stmtTraspasoInfo->execute();

                if ($stmtTraspasoInfo->affected_rows === 0) {
                    throw new Exception("No se pudo registrar la información del traspaso en 'añadir' con ID distribución " . $item['id_distribucion']);
                }
            }

            // Confirmar la transacción
            $conexion->commit();
            return json_encode(["success" => "El traspaso se registró correctamente."]);

        }
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


function consultarTodosTraspasos($id_ejercicio)
{
    global $conexion;
    global $remote_db;

    $conexion = $remote_db;

    // Consultar los traspasos principales filtrando por id_ejercicio
    $sql = "SELECT t.id, t.n_orden, t.id_ejercicio, t.monto_total, t.fecha, t.status, t.tipo 
            FROM traspasos t
            WHERE t.id_ejercicio = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_ejercicio);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $traspasos = $resultado->fetch_all(MYSQLI_ASSOC);

        // Agregar la información de traspaso_informacion para cada traspaso
        foreach ($traspasos as &$traspaso) {
            $sqlInfo = "SELECT ti.id_distribucion, ti.monto, ti.tipo 
                        FROM traspaso_informacion ti 
                        WHERE ti.id_traspaso = ?";
            $stmtInfo = $conexion->prepare($sqlInfo);
            $stmtInfo->bind_param("i", $traspaso['id']);
            $stmtInfo->execute();
            $resultadoInfo = $stmtInfo->get_result();

            if ($resultadoInfo->num_rows > 0) {
                $detalles = $resultadoInfo->fetch_all(MYSQLI_ASSOC);
                foreach ($detalles as &$detalle) {
                    // Obtener la información de distribucion_presupuestaria
                    $sqlDistribucion = "SELECT dp.* 
                                        FROM distribucion_presupuestaria dp 
                                        WHERE dp.id = ?";
                    $stmtDistribucion = $conexion->prepare($sqlDistribucion);
                    $stmtDistribucion->bind_param("i", $detalle['id_distribucion']);
                    $stmtDistribucion->execute();
                    $resultadoDistribucion = $stmtDistribucion->get_result();

                    if ($resultadoDistribucion->num_rows > 0) {
                        $detalle['distribucion_presupuestaria'] = $resultadoDistribucion->fetch_assoc();

                        // Obtener la información de partidas_presupuestarias usando id_partida
                        $id_partida = $detalle['distribucion_presupuestaria']['id_partida'];
                        $sqlPartida = "SELECT pp.* 
                                       FROM partidas_presupuestarias pp 
                                       WHERE pp.id = ?";
                        $stmtPartida = $conexion->prepare($sqlPartida);
                        $stmtPartida->bind_param("i", $id_partida);
                        $stmtPartida->execute();
                        $resultadoPartida = $stmtPartida->get_result();

                        if ($resultadoPartida->num_rows > 0) {
                            $detalle['distribucion_presupuestaria']['partida_presupuestaria'] = $resultadoPartida->fetch_assoc();
                        } else {
                            $detalle['distribucion_presupuestaria']['partida_presupuestaria'] = [];
                        }
                    } else {
                        $detalle['distribucion_presupuestaria'] = [];
                    }

                    // Consultar la información de distribucion_entes usando id_distribucion y id_ejercicio
                    $sqlDistribucionEnte = "SELECT de.* 
                                            FROM distribucion_entes de 
                                            WHERE de.distribucion LIKE ? AND de.id_ejercicio = ?";
                    $likePattern = '%"id_distribucion":"' . $detalle['id_distribucion'] . '"%';
                    $stmtDistribucionEnte = $conexion->prepare($sqlDistribucionEnte);
                    $stmtDistribucionEnte->bind_param("si", $likePattern, $id_ejercicio);
                    $stmtDistribucionEnte->execute();
                    $resultadoDistribucionEnte = $stmtDistribucionEnte->get_result();

                    if ($resultadoDistribucionEnte->num_rows > 0) {
                        $detalle['distribucion_entes'] = $resultadoDistribucionEnte->fetch_assoc();
                    } else {
                        $detalle['distribucion_entes'] = [];
                    }
                }
                $traspaso['detalles'] = $detalles;
            } else {
                $traspaso['detalles'] = [];
            }
        }

        return json_encode(['success' => $traspasos]);
    } else {
        return json_encode(["success" => []]);
    }
}

function obtenerUltimosOrdenes($id_ejercicio)
{
    global $conexion;
    global $remote_db;

    $conexion = $remote_db;

    try {
        // Consultar el último n_orden para tipo 1 (traslado)
        $sqlTraslado = "SELECT n_orden 
                        FROM traspasos 
                        WHERE id_ejercicio = ? AND tipo = 1 
                        ORDER BY id DESC LIMIT 1";
        $stmtTraslado = $conexion->prepare($sqlTraslado);
        $stmtTraslado->bind_param("i", $id_ejercicio);
        $stmtTraslado->execute();
        $resultadoTraslado = $stmtTraslado->get_result();
        $traslado = $resultadoTraslado->num_rows > 0 ? $resultadoTraslado->fetch_assoc()['n_orden'] : null;

        // Consultar el último n_orden para tipo 2 (traspaso)
        $sqlTraspaso = "SELECT n_orden 
                        FROM traspasos 
                        WHERE id_ejercicio = ? AND tipo = 2 
                        ORDER BY id DESC LIMIT 1";
        $stmtTraspaso = $conexion->prepare($sqlTraspaso);
        $stmtTraspaso->bind_param("i", $id_ejercicio);
        $stmtTraspaso->execute();
        $resultadoTraspaso = $stmtTraspaso->get_result();
        $traspaso = $resultadoTraspaso->num_rows > 0 ? $resultadoTraspaso->fetch_assoc()['n_orden'] : null;

        // Devolver los resultados
        return json_encode([
            "ultimo_traslado" => $traslado,
            "ultimo_traspaso" => $traspaso
        ]);

    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

function gestionarTraspaso($id, $accion)
{
    global $conexion;
    global $remote_db;

    $conexion = $remote_db;

    $conexion->begin_transaction();


    try {
        // Validar la acción
        $nuevoStatus = null;
        if ($accion === 'aceptar') {
            $nuevoStatus = 1;
        } elseif ($accion === 'rechazar') {
            $nuevoStatus = 2;
        } else {
            throw new Exception("Acción inválida: $accion");
        }

        if ($nuevoStatus === 1) {
            // Consultar los registros de la tabla `traspaso_informacion` asociados al `id_traspaso`
            $sqlInfo = "SELECT id_distribucion, monto, tipo FROM traspaso_informacion WHERE id_traspaso = ?";
            $stmtInfo = $conexion->prepare($sqlInfo);
            $stmtInfo->bind_param("i", $id);
            $stmtInfo->execute();
            $resultInfo = $stmtInfo->get_result();

            if ($resultInfo->num_rows === 0) {
                throw new Exception("No se encontraron registros en `traspaso_informacion` para el traspaso con ID $id.");
            }

            // Obtener el `id_ejercicio` desde la tabla `traspasos`
            $sqlEjercicio = "SELECT id_ejercicio FROM traspasos WHERE id = ?";
            $stmtEjercicio = $conexion->prepare($sqlEjercicio);
            $stmtEjercicio->bind_param("i", $id);
            $stmtEjercicio->execute();
            $resultEjercicio = $stmtEjercicio->get_result();

            if ($resultEjercicio->num_rows === 0) {
                throw new Exception("No se encontró el registro en `traspasos` para el traspaso con ID $id.");
            }

            $rowEjercicio = $resultEjercicio->fetch_assoc();
            $idEjercicio = $rowEjercicio['id_ejercicio'];

            // Procesar cada registro de `traspaso_informacion`
            while ($row = $resultInfo->fetch_assoc()) {
                $idDistribucion = $row['id_distribucion'];
                $tipo = $row['tipo'];

                // Consultar la tabla `distribucion_entes` para obtener el monto de la distribución específica con el filtro adicional por `id_ejercicio`
                $sqlDistribucionEnte = "SELECT distribucion FROM distribucion_entes WHERE distribucion LIKE ? AND id_ejercicio = ?";
                $likePattern = '%"id_distribucion":"' . $idDistribucion . '"%';
                $stmtDistribucionEnte = $conexion->prepare($sqlDistribucionEnte);
                $stmtDistribucionEnte->bind_param("si", $likePattern, $idEjercicio);
                $stmtDistribucionEnte->execute();
                $resultDistribucionEnte = $stmtDistribucionEnte->get_result();

                if ($resultDistribucionEnte->num_rows === 0) {
                    throw new Exception("No se encontró el registro en `distribucion_entes` para el ID de distribución $idDistribucion y el ID de ejercicio $idEjercicio.");
                }

                $distribucionEnte = $resultDistribucionEnte->fetch_assoc();
                $distribucionData = json_decode($distribucionEnte['distribucion'], true);

                // Encontrar el monto correspondiente en el array de distribuciones
                $montoDistribucion = null;
                foreach ($distribucionData as $dist) {
                    if ($dist['id_distribucion'] == $idDistribucion) {
                        $montoDistribucion = $dist['monto'];
                        break;
                    }
                }

                if ($montoDistribucion === null) {
                    throw new Exception("No se encontró el monto asociado al ID de distribución $idDistribucion en `distribucion_entes`.");
                }

                // Actualizar el `monto` en el JSON de la columna `distribucion`
                if ($tipo === 'D') {
                    $nuevoMonto = $montoDistribucion - $row['monto']; // Resta el monto
                } elseif ($tipo === 'A') {
                    $nuevoMonto = $montoDistribucion + $row['monto']; // Suma el monto
                } else {
                    throw new Exception("Tipo inválido: $tipo.");
                }

                // Reemplazar el valor actualizado en el JSON
                foreach ($distribucionData as &$dist) {
                    if ($dist['id_distribucion'] == $idDistribucion) {
                        $dist['monto'] = $nuevoMonto; // Actualizamos el monto correspondiente
                    }
                }

                // Guardar el JSON actualizado en la tabla `distribucion_entes`
                $nuevoDistribucionJSON = json_encode($distribucionData);
                $sqlUpdateDistribucionEnte = "UPDATE distribucion_entes SET distribucion = ? WHERE distribucion LIKE ? AND id_ejercicio = ?";
                $stmtUpdateDistribucionEnte = $conexion->prepare($sqlUpdateDistribucionEnte);
                $stmtUpdateDistribucionEnte->bind_param("ssi", $nuevoDistribucionJSON, $likePattern, $idEjercicio);
                $stmtUpdateDistribucionEnte->execute();

                if ($stmtUpdateDistribucionEnte->affected_rows === 0) {
                    throw new Exception("No se pudo actualizar el monto en la distribución con ID $idDistribucion y el ID de ejercicio $idEjercicio.");
                }
            }
        }

        // Preparar la consulta de actualización para el `status`
        $sql = "UPDATE traspasos SET status = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $nuevoStatus, $id);

        // Ejecutar la consulta
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("No se encontró el traspaso con ID $id o no se pudo actualizar el estado.");
        }

        if ($accion === 'aceptar') {
            $conexion->commit();
            return json_encode(["success" => "Se ha aceptado el registro correctamente."]);
        }

        if ($accion === 'rechazar') {
            $conexion->commit();
            return json_encode(["success" => "Se ha rechazado el registro correctamente."]);
        }


    } catch (Exception $e) {
        $conexion->rollback();
        return json_encode(['error' => $e->getMessage()]);
    }

}


function consultarTraspasoPorId($id)
{
    global $conexion;
    global $remote_db;

    $conexion = $remote_db;

    // Consultar el traspaso principal por su ID
    $sql = "SELECT t.id, t.n_orden, t.id_ejercicio, t.monto_total, t.fecha, t.status, t.tipo 
            FROM traspasos t 
            WHERE t.id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $traspaso = $resultado->fetch_assoc();

        // Consultar los detalles del traspaso desde traspaso_informacion
        $sqlInfo = "SELECT ti.id_distribucion, ti.monto, ti.tipo 
                    FROM traspaso_informacion ti 
                    WHERE ti.id_traspaso = ?";
        $stmtInfo = $conexion->prepare($sqlInfo);
        $stmtInfo->bind_param("i", $id);
        $stmtInfo->execute();
        $resultadoInfo = $stmtInfo->get_result();

        if ($resultadoInfo->num_rows > 0) {
            $detalles = $resultadoInfo->fetch_all(MYSQLI_ASSOC);
            foreach ($detalles as &$detalle) {
                $idDistribucion = $detalle['id_distribucion'];

                // Obtener información de distribucion_entes
                $sqlDistribucionEnte = "SELECT de.id AS id_distribucion_ente, de.monto_total, de.distribucion, de.id_ejercicio, de.actividad_id 
                                        FROM distribucion_entes de 
                                        WHERE de.distribucion LIKE ? AND de.id_ejercicio = ?";
                $likePattern = '%"id_distribucion":"' . $idDistribucion . '"%';
                $stmtDistribucionEnte = $conexion->prepare($sqlDistribucionEnte);
                $stmtDistribucionEnte->bind_param("si", $likePattern, $traspaso['id_ejercicio']);
                $stmtDistribucionEnte->execute();
                $resultadoDistribucionEnte = $stmtDistribucionEnte->get_result();
                $informacionDistribucionEnte = $resultadoDistribucionEnte->fetch_assoc();


                $distribucionPartidas = json_decode($informacionDistribucionEnte["distribucion"], true);



                // Consulta para obtener detalles de la distribución presupuestaria
                $sqlDistribucion = "SELECT dp.id_partida, dp.id AS id_distribucion_, dp.id_sector, dp.id_programa, dp.id_proyecto, dp.id_actividad,
                                           pp.partida AS partida, pp.descripcion AS partida_descripcion, 
                                           ps.sector AS sector_denominacion, pg.programa AS programa_denominacion, 
                                           pr.proyecto_id AS proyecto_denominacion
                                    FROM distribucion_presupuestaria dp
                                    LEFT JOIN partidas_presupuestarias pp ON dp.id_partida = pp.id
                                    LEFT JOIN pl_sectores ps ON dp.id_sector = ps.id
                                    LEFT JOIN pl_programas pg ON dp.id_programa = pg.id
                                    LEFT JOIN pl_proyectos pr ON dp.id_proyecto = pr.id
                                    WHERE dp.id = ?";
                $stmtDistribucion = $conexion->prepare($sqlDistribucion);
                $stmtDistribucion->bind_param("i", $idDistribucion);
                $stmtDistribucion->execute();
                $resultadoDistribucion = $stmtDistribucion->get_result();

                if ($resultadoDistribucion->num_rows > 0) {
                    $informacionDistribucion = $resultadoDistribucion->fetch_assoc();
                    $detalle['sector_denominacion'] = $informacionDistribucion["sector_denominacion"];
                    $detalle['programa_denominacion'] = $informacionDistribucion["programa_denominacion"];
                    $detalle['programa_denominacion'] = $informacionDistribucion["programa_denominacion"];
                    $detalle['proyecto_denominacion'] = $informacionDistribucion["proyecto_denominacion"];
                    $detalle['partida'] = $informacionDistribucion["partida"];
                    $detalle['partida_descripcion'] = $informacionDistribucion["partida_descripcion"];
                    $detalle['id_actividad'] = $informacionDistribucion["id_actividad"];
                    $detalle['monto_traspaso'] = $detalle['monto'];


                    foreach ($distribucionPartidas as $distribucionItem) {
                        if ($idDistribucion == $distribucionItem['id_distribucion']) {
                            $detalle['monto'] = $distribucionItem['monto'];
                        }

                    }

                } else {
                    $detalle = [];
                }
            }
            $traspaso['detalles'] = $detalles;
        } else {
            $traspaso['detalles'] = [];
        }

        return json_encode(['success' => $traspaso]);
    } else {
        return json_encode(["error" => "No se encontró el traspaso."]);
    }
}





function actualizarTraspasoPartida($id_traspaso, $id_partida_t, $id_partida_r, $id_ejercicio, $monto)
{
    global $conexion;
    global $remote_db;

    $conexion = $remote_db;

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

        // Verificar el estado de la partida receptora
        $sqlPartidaReceptora = "SELECT status FROM partidas_presupuestarias WHERE id = ?";
        $stmtPartidaReceptora = $conexion->prepare($sqlPartidaReceptora);
        $stmtPartidaReceptora->bind_param("i", $id_partida_r);
        $stmtPartidaReceptora->execute();
        $resultadoPartidaReceptora = $stmtPartidaReceptora->get_result();

        if ($resultadoPartidaReceptora->num_rows === 0) {
            throw new Exception("No se encontró la partida presupuestaria receptora.");
        }

        $filaPartidaReceptora = $resultadoPartidaReceptora->fetch_assoc();
        $statusPartidaReceptora = $filaPartidaReceptora['status'];

        if ($statusPartidaReceptora !== 0) {
            throw new Exception("La partida presupuestaria receptora no está disponible para recibir traspasos.");
        }

        // Consultar la tabla distribucion_presupuestaria para validar el monto actual de la partida transferente
        $sqlDistribucion = "SELECT monto_actual FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        $stmtDistribucion->bind_param("ii", $id_partida_t, $id_ejercicio);
        $stmtDistribucion->execute();
        $resultadoDistribucion = $stmtDistribucion->get_result();

        if ($resultadoDistribucion->num_rows === 0) {
            throw new Exception("No se encontró una distribución presupuestaria con el id_partida y id_ejercicio proporcionados.");
        }

        $filaDistribucion = $resultadoDistribucion->fetch_assoc();
        $monto_actual = $filaDistribucion['monto_actual'];

        // Validar que el monto actual sea suficiente
        if ($monto_actual < $monto) {
            throw new Exception("El monto recibido es superior al monto actual de la partida presupuestaria transferente.");
        }

        // Obtener el traspaso existente para comparar montos anteriores
        $sqlTraspaso = "SELECT monto FROM traspasos WHERE id = ?";
        $stmtTraspaso = $conexion->prepare($sqlTraspaso);
        $stmtTraspaso->bind_param("i", $id_traspaso);
        $stmtTraspaso->execute();
        $resultadoTraspaso = $stmtTraspaso->get_result();

        if ($resultadoTraspaso->num_rows === 0) {
            throw new Exception("No se encontró el traspaso con el ID proporcionado.");
        }

        $traspasoActual = $resultadoTraspaso->fetch_assoc();
        $montoAnterior = $traspasoActual['monto'];

        // Actualizar el traspaso
        $fecha_actual = date("Y-m-d");
        $monto_actual_nuevo = $monto_actual + $monto - $montoAnterior;

        $sqlActualizarTraspaso = "UPDATE traspasos SET id_partida_t = ?, id_partida_r = ?, id_ejercicio = ?, monto = ?, fecha = ?, monto_anterior = ?, monto_actual = ? WHERE id = ?";
        $stmtActualizarTraspaso = $conexion->prepare($sqlActualizarTraspaso);
        $stmtActualizarTraspaso->bind_param("iiidsddi", $id_partida_t, $id_partida_r, $id_ejercicio, $monto, $fecha_actual, $monto_actual, $monto_actual_nuevo, $id_traspaso);

        $stmtActualizarTraspaso->execute();

        if ($stmtActualizarTraspaso->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "El traspaso se actualizó correctamente."]);
        } else {
            throw new Exception("No se pudo actualizar el traspaso.");
        }
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


function eliminarTraspaso($id_traspaso)
{
    global $conexion;
    global $remote_db;

    $conexion = $remote_db;

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

        // Verificar si existen registros en traspaso_informacion para el traspaso
        $sqlTraspasoInfo = "SELECT id FROM traspaso_informacion WHERE id_traspaso = ?";
        $stmtTraspasoInfo = $conexion->prepare($sqlTraspasoInfo);
        $stmtTraspasoInfo->bind_param("i", $id_traspaso);
        $stmtTraspasoInfo->execute();
        $resultadoTraspasoInfo = $stmtTraspasoInfo->get_result();

        if ($resultadoTraspasoInfo->num_rows === 0) {
            throw new Exception("No se encontro informacion de distribuciones para el traspaso proporcionado.");
        }

        // Eliminar registros de traspaso_informacion
        $sqlEliminarTraspasoInfo = "DELETE FROM traspaso_informacion WHERE id_traspaso = ?";
        $stmtEliminarTraspasoInfo = $conexion->prepare($sqlEliminarTraspasoInfo);
        $stmtEliminarTraspasoInfo->bind_param("i", $id_traspaso);
        $stmtEliminarTraspasoInfo->execute();

        if ($stmtEliminarTraspasoInfo->affected_rows === 0) {
            throw new Exception("No se pudo eliminar la informacion del traspaso.");
        }

        // Eliminar el traspaso
        $sqlEliminarTraspaso = "DELETE FROM traspasos WHERE id = ?";
        $stmtEliminarTraspaso = $conexion->prepare($sqlEliminarTraspaso);
        $stmtEliminarTraspaso->bind_param("i", $id_traspaso);
        $stmtEliminarTraspaso->execute();

        if ($stmtEliminarTraspaso->affected_rows === 0) {
            throw new Exception("No se pudo eliminar el traspaso.");
        }

        // Confirmar la transacción
        $conexion->commit();
        return json_encode(["success" => "El traspaso se elimino correctamente."]);

    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    switch ($data["accion"]) {
        case 'registrar':
            if (isset($data["info"]["n_orden"], $data["info"]["id_ejercicio"], $data["info"]["monto_total"], $data["añadir"], $data["restar"])) {
                // Se pasa todo el array $data a la función registrarTraspasoPartida
                echo registrarTraspasoPartida($data);
            } else {
                echo json_encode(["error" => "Faltan datos para registrar el traspaso"]);
            }
            break;

        case 'consultar_todos':
            echo consultarTodosTraspasos($data["id_ejercicio"]);
            break;

        case 'ultima_orden':
            echo obtenerUltimosOrdenes($data["id_ejercicio"]);
            break;

        case 'aceptar':
            echo gestionarTraspaso($data["id"], 'aceptar');
            break;

        case 'rechazar':
            echo gestionarTraspaso($data["id"], 'rechazar');
            break;

        case 'consultar_por_id':
            if (isset($data["id"])) {
                echo consultarTraspasoPorId($data["id"]);
            } else {
                echo json_encode(["error" => "Faltan datos para consultar el traspaso"]);
            }
            break;

        case 'actualizar':
            if (isset($data["id"], $data["info"]["n_orden"], $data["info"]["id_ejercicio"], $data["info"]["monto_total"], $data["añadir"], $data["restar"])) {
                // Se pasa todo el array $data a la función actualizarTraspasoPartida
                echo actualizarTraspasoPartida($data);
            } else {
                echo json_encode(["error" => "Faltan datos para actualizar el traspaso"]);
            }
            break;

        case 'eliminar':
            if (isset($data["id"])) {
                echo eliminarTraspaso($data["id"]);
            } else {
                echo json_encode(["error" => "Faltan datos para eliminar el traspaso"]);
            }
            break;

        default:
            echo json_encode(["error" => "Acción no válida"]);
            break;
    }
} else {
    echo json_encode(["error" => "No se especificó ninguna acción"]);
}