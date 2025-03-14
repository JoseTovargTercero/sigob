<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';

require_once '../sistema_global/notificaciones.php';
header('Content-Type: application/json');
require_once '../sistema_global/errores.php';
require_once 'pre_compromisos.php';
require_once 'pre_dispo_presupuestaria.php'; // Agregado
// Función para gestionar la solicitud y compromisos
function gestionarSolicitudDozavos($data)
{
    global $conexion;

    try {
        if (!isset($data['accion'])) {
            return json_encode(["error" => "No se ha especificado acción."]);
        }

        $accion = $data['accion'];

        // Acción: Consultar todos los registros
        if ($accion === 'consulta') {
            return consultarSolicitudes($data);
        }

        // Acción: Consultar un registro por ID
        if ($accion === 'consulta_id') {
            return consultarSolicitudPorId($data);
        }

        // Acción: Registrar una nueva solicitud
        if ($accion === 'registrar') {
            return registrarSolicitudozavo($data);
        }

        // Acción: Actualizar un registro
        if ($accion === 'update') {
            return actualizarSolicitudozavo($data);
        }

        // Acción: Actualizar un registro
        if ($accion === 'update_status') {
            return actualizarStatusSolicitud($data);
        }

        // Acción: Eliminar un registro (rechazar)
        if ($accion === 'rechazar') {
            return rechazarSolicitud($data);
        }
        // Acción: Eliminar un registro (rechazar)
        if ($accion === 'delete') {
            return eliminarSolicitudozavo($data);
        }
        if ($accion === 'gestionar') {
            return gestionarSolicitudDozavos2($data["id"], $data["accion_gestion"], $data["codigo"] ?? '');
        }

        // Otras acciones...

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para consultar todas las solicitudes
function consultarSolicitudes($data)
{
    global $conexion;
    if (!isset($data['id_ejercicio'])) {
        return json_encode(["error" => "No se ha especificado Un Ejercicio Fiscal para consulta."]);
    }

    $id_ejercicio = $data['id_ejercicio'];

    $sql = "SELECT id, numero_orden, numero_compromiso, descripcion, monto, fecha, partidas, id_ente, tipo, mes, status, id_ejercicio FROM solicitud_dozavos WHERE id_ejercicio = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_ejercicio);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $solicitudes = [];

        while ($row = $result->fetch_assoc()) {
            // Validar el valor de numero_compromiso
            if ($row['numero_compromiso'] == 0) {
                $row['numero_compromiso'] = null;
            }

            // Procesar las partidas asociadas
            $partidasArray = json_decode($row['partidas'], true);

            foreach ($partidasArray as &$partida) {
                $idDistribucion = $partida['id'];
                $sqlPartida = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $idDistribucion);
                $stmtPartida->execute();
                $stmtPartida->bind_result($id_partida2);
                $stmtPartida->fetch();
                $stmtPartida->close();

                $id_partida = $id_partida2;

                $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $id_partida);
                $stmtPartida->execute();
                $stmtPartida->bind_result($partidaCod, $nombre, $descripcion);
                $stmtPartida->fetch();
                $stmtPartida->close();

                $partida['partida'] = $partidaCod;
                $partida['nombre'] = $nombre;
                $partida['descripcion'] = $descripcion;
            }

            // Agregar las partidas procesadas al registro
            $row['partidas'] = $partidasArray;

            // Consultar la información del ente asociado
            $idEnte = $row['id_ente'];
            $sqlEnte = "SELECT * FROM entes WHERE id = ?";
            $stmtEnte = $conexion->prepare($sqlEnte);
            $stmtEnte->bind_param("i", $idEnte);
            $stmtEnte->execute();
            $resultEnte = $stmtEnte->get_result();
            $dataEnte = $resultEnte->fetch_assoc();
            $stmtEnte->close();

            // Agregar la información del ente como un ítem más
            if ($dataEnte) {
                $row['ente'] = $dataEnte;
            } else {
                $row['ente'] = null; // Si no se encuentra, se asigna como null
            }

            // Añadir la solicitud completa a la lista de solicitudes
            $solicitudes[] = $row;
        }

        return json_encode(["success" => $solicitudes]);
    } else {
        return json_encode(["success" => "No se encontraron registros en solicitud_dozavos."]);
    }
}


// Función para consultar una solicitud por ID
function consultarSolicitudPorId($data)
{
    global $conexion;

    if (!isset($data['id'])) {
        return json_encode(["error" => "No se ha especificado ID o un Ejercicio Fiscal para consulta."]);
    }

    $id = $data['id'];

    // Consultar la solicitud principal
    $sql = "SELECT id, numero_orden, numero_compromiso, descripcion, monto, fecha, partidas, id_ente, tipo, mes, status, id_ejercicio FROM solicitud_dozavos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Validar el valor de numero_compromiso
        if ($row['numero_compromiso'] == 0) {
            $row['numero_compromiso'] = null;
        }

        // Procesar las partidas asociadas
        $partidasArray = json_decode($row['partidas'], true);

        foreach ($partidasArray as &$partida) {
            $idDistribucion = $partida['id'];

            // Obtener el id_partida desde distribucion_presupuestaria
            $sqlPartida = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
            $stmtPartida = $conexion->prepare($sqlPartida);
            $stmtPartida->bind_param("i", $idDistribucion);
            $stmtPartida->execute();
            $stmtPartida->bind_result($id_partida2);
            $stmtPartida->fetch();
            $stmtPartida->close();

            $id_partida = $id_partida2;

            // Obtener información de la partida presupuestaria
            $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $conexion->prepare($sqlPartida);
            $stmtPartida->bind_param("i", $id_partida);
            $stmtPartida->execute();
            $stmtPartida->bind_result($partidaCod, $nombre, $descripcion);
            $stmtPartida->fetch();
            $stmtPartida->close();

            $partida['partida'] = $partidaCod;
            $partida['nombre'] = $nombre;
            $partida['descripcion'] = $descripcion;
        }

        // Agregar las partidas procesadas
        $row['partidas'] = $partidasArray;

        // Consultar la información del ente asociado
        $idEnte = $row['id_ente'];
        $sqlEnte = "SELECT * FROM entes WHERE id = ?";
        $stmtEnte = $conexion->prepare($sqlEnte);
        $stmtEnte->bind_param("i", $idEnte);
        $stmtEnte->execute();
        $resultEnte = $stmtEnte->get_result();
        $dataEnte = $resultEnte->fetch_assoc();
        $stmtEnte->close();

        // Agregar la información del ente como un ítem más
        $row['ente'] = $dataEnte ?: null; // Si no se encuentra, se asigna como null

        return json_encode(["success" => $row]);
    } else {
        return json_encode(["error" => "No se encontró el registro con el ID especificado."]);
    }
}



function registrarSolicitudozavo($data)
{
    global $conexion;
    global $remote_db;

    try {
        if (!isset($data['descripcion']) || !isset($data['monto']) || !isset($data['tipo']) || !isset($data['partidas']) || !isset($data['id_ente']) || !isset($data['id_ejercicio']) || !isset($data['mes'])) {
            return json_encode(["error" => "Faltan datos obligatorios para registrar la solicitud."]);
        }

        // Iniciar transacción en ambas bases de datos
        $conexion->begin_transaction();
        $remote_db->begin_transaction();

        $mesActual = date("n") - 1;
        $mesSolicitado = $data['mes'];
        $idEnte = $data['id_ente'];
        $idEjercicio = $data['id_ejercicio'];

        // Verificar si ya existe una solicitud pendiente
        foreach ([$conexion, $remote_db] as $db) {
            $sqlPendiente = "SELECT COUNT(*) AS total FROM solicitud_dozavos WHERE id_ente = ? AND status = 1 AND id_ejercicio = ?";
            $stmtPendiente = $db->prepare($sqlPendiente);
            $stmtPendiente->bind_param("ii", $idEnte, $idEjercicio);
            $stmtPendiente->execute();
            $resultadoPendiente = $stmtPendiente->get_result();
            $filaPendiente = $resultadoPendiente->fetch_assoc();

            if ($filaPendiente['total'] > 0) {
                $conexion->rollback();
                $remote_db->rollback();
                return json_encode(["error" => "No se puede registrar la solicitud porque hay una pendiente."]);
            }
        }

        // Verificar existencia de solicitudes para el mes actual
        $existeMesActual = false;
        foreach ([$conexion, $remote_db] as $db) {
            $sqlMesActual = "SELECT COUNT(*) AS total FROM solicitud_dozavos WHERE id_ente = ? AND mes = ? AND id_ejercicio = ? AND status != '3'";
            $stmtMesActual = $db->prepare($sqlMesActual);
            $stmtMesActual->bind_param("iii", $idEnte, $mesActual, $idEjercicio);
            $stmtMesActual->execute();
            $resultadoMesActual = $stmtMesActual->get_result();
            $filaMesActual = $resultadoMesActual->fetch_assoc();
            
            if ($filaMesActual['total'] > 0) {
                $existeMesActual = true;
            }
        }

        if ($existeMesActual) {
            $conexion->rollback();
            $remote_db->rollback();
            return json_encode(["error" => "No se puede registrar la solicitud. Condiciones no cumplidas."]);
        }

        // Generar el numero_orden automáticamente
        $numero_orden = generarNumeroOrden();
        $fecha = date("Y-m-d");
        $partidasJson = json_encode($data['partidas']);

        // Insertar en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlInsertar = "INSERT INTO solicitud_dozavos (numero_orden, numero_compromiso, descripcion, tipo, monto, fecha, partidas, id_ente, status, id_ejercicio, mes) VALUES (?, 0, ?, ?, ?, ?, ?, ?, 1, ?, ?)";
            $stmtInsertar = $db->prepare($sqlInsertar);
            $stmtInsertar->bind_param("sssssssss", $numero_orden, $data['descripcion'], $data['tipo'], $data['monto'], $fecha, $partidasJson, $idEnte, $idEjercicio, $mesSolicitado);
            $stmtInsertar->execute();

            if ($stmtInsertar->affected_rows === 0) {
                $conexion->rollback();
                $remote_db->rollback();
                return json_encode(["error" => "No se pudo registrar la solicitud."]);
            }
        }

        // Confirmar la transacción en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();
        return json_encode(["success" => "Registro exitoso"]);
    } catch (Exception $e) {
        // Revertir transacciones en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        registrarError($e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}





// Función para generar el número de orden
function generarNumeroOrden()
{
    global $conexion;

    $anio_actual = date('Y');
    $prefijo = "O";
    $sql = "SELECT numero_orden FROM solicitud_dozavos WHERE numero_orden LIKE ? ORDER BY numero_orden DESC LIMIT 1";
    $like_param = $prefijo . "_____-" . $anio_actual; // Busca formato Oxxxxx-YYYY
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $like_param);
    $stmt->execute();
    $stmt->bind_result($ultimo_numero_orden);
    $stmt->fetch();
    $stmt->close();

    if ($ultimo_numero_orden) {
        // Extraer el número secuencial y sumarle 1
        $secuencia = (int) substr($ultimo_numero_orden, 1, 5); // Extrae los dígitos Oxxxxx
        $secuencia++;
    } else {
        $secuencia = 1; // Si no existe, comienza desde 1
    }

    // Formatear el nuevo número de orden
    $nuevo_numero_orden = sprintf("%s%05d-%s", $prefijo, $secuencia, $anio_actual);

    return $nuevo_numero_orden;
}

function gestionarSolicitudDozavos2($idSolicitud, $accion, $codigo)
{
    global $conexion;
    global $remote_db;

    try {
        if (empty($idSolicitud) || empty($accion)) {
            throw new Exception("Faltan uno o más valores necesarios (idSolicitud, accion)");
        }

        // Iniciar las transacciones en ambas bases de datos
        $conexion->begin_transaction();
        $remote_db->begin_transaction();

        // Consultar los detalles de la solicitud, incluyendo el campo partidas
        $sqlSolicitud = "SELECT numero_orden, numero_compromiso, descripcion, tipo, monto, id_ente, partidas, status, id_ejercicio FROM solicitud_dozavos WHERE id = ?";
        $stmtSolicitud = $conexion->prepare($sqlSolicitud);
        $stmtSolicitud->bind_param("i", $idSolicitud);
        $stmtSolicitud->execute();
        $resultadoSolicitud = $stmtSolicitud->get_result();

        if ($resultadoSolicitud->num_rows === 0) {
            throw new Exception("No se encontró una solicitud con el ID proporcionado");
        }

        $filaSolicitud = $resultadoSolicitud->fetch_assoc();
        $numero_orden = $filaSolicitud['numero_orden'];
        $numero_compromiso = $filaSolicitud['numero_compromiso'];
        $descripcion = $filaSolicitud['descripcion'];
        $tipo = $filaSolicitud['tipo'];
        $montoTotal = $filaSolicitud['monto'];
        $id_ente = $filaSolicitud['id_ente'];
        $status = $filaSolicitud['status'];
        $id_ejercicio = $filaSolicitud['id_ejercicio'];

        // Decodificar el campo `partidas` como un array
        $partidas = json_decode($filaSolicitud['partidas'], true);

        if ($status !== 1) {
            throw new Exception("La solicitud ya ha sido procesada anteriormente");
        }

        if ($accion === "aceptar") {
            // Iterar sobre cada array de partidas
            foreach ($partidas as $partida) {
                $id_distribucion = $partida['id'];
                $monto = $partida['monto'];

                // Consultar el monto de distribución desde distribucion_entes
                $sqlMontoDistribucion = "SELECT distribucion 
                                         FROM distribucion_entes 
                                         WHERE id_ente = ? AND id_ejercicio = ? AND distribucion LIKE '%\"id_distribucion\":\"$id_distribucion\"%'";
                $stmtMontoDistribucion = $conexion->prepare($sqlMontoDistribucion);
                $stmtMontoDistribucion->bind_param("ii", $id_ente, $id_ejercicio);
                $stmtMontoDistribucion->execute();
                $resultadoMontoDistribucion = $stmtMontoDistribucion->get_result();

                if ($resultadoMontoDistribucion->num_rows === 0) {
                    throw new Exception("El ID de distribución no se encuentra en el campo 'distribucion' de distribucion_entes");
                }

                // Obtener la fila de resultados
                $filaMontoDistribucion = $resultadoMontoDistribucion->fetch_assoc();

                // Decodificar el campo JSON
                $distribuciones = json_decode($filaMontoDistribucion['distribucion'], true);

                // Buscar el monto correspondiente al id_distribucion
                $montoDistribucion = null;
                foreach ($distribuciones as &$distribucion) {
                    if ($distribucion['id_distribucion'] == $id_distribucion) {
                        $montoDistribucion = (float) $distribucion['monto'];
                        $nuevoMontoActual = $montoDistribucion - $monto;
                        $distribucion['monto'] = $nuevoMontoActual;  // Actualizar el monto
                        break;
                    }
                }

                // Verificar si se encontró el monto
                if ($montoDistribucion === null) {
                    throw new Exception("No se encontró el monto para el ID de distribución especificado.");
                }

                // Verificar si hay suficiente presupuesto disponible
                if ($montoDistribucion < $monto) {
                    throw new Exception("El presupuesto actual en distribucion_entes es insuficiente para el monto de la partida");
                }

                // Volver a codificar el array a formato JSON
                $nuevaDistribucion = json_encode($distribuciones);

                // Actualizar el monto en distribucion_entes en ambas bases de datos
                foreach ([$conexion, $remote_db] as $db) {
                    $sqlUpdatePartida = "UPDATE distribucion_entes SET distribucion = ? WHERE id_ente = ? AND id_ejercicio = ? AND distribucion LIKE '%\"id_distribucion\":\"$id_distribucion\"%'";
                    $stmtUpdatePartida = $db->prepare($sqlUpdatePartida);
                    $stmtUpdatePartida->bind_param("sii", $nuevaDistribucion, $id_ente, $id_ejercicio);
                    $stmtUpdatePartida->execute();

                    if ($stmtUpdatePartida->affected_rows === 0) {
                        $conexion->rollback();
                        $remote_db->rollback();
                        throw new Exception("No se pudo actualizar el monto de distribución para el ID de distribución proporcionado");
                    }
                }
            }

            // Actualizar el estado de la solicitud a aceptado en ambas bases de datos
            foreach ([$conexion, $remote_db] as $db) {
                $sqlUpdateSolicitud = "UPDATE solicitud_dozavos SET status = 0 WHERE id = ?";
                $stmtUpdateSolicitud = $db->prepare($sqlUpdateSolicitud);
                $stmtUpdateSolicitud->bind_param("i", $idSolicitud);
                $stmtUpdateSolicitud->execute();

                if ($stmtUpdateSolicitud->affected_rows > 0) {
                    $resultadoCompromiso = registrarCompromiso($conexion, $conexion_remota, $idSolicitud, 'solicitud_dozavos', $descripcion, $id_ejercicio, $codigo);
                    if (isset($resultadoCompromiso['success']) && $resultadoCompromiso['success']) {
                        // Confirmar la transacción en ambas bases de datos
                        $conexion->commit();
                        $remote_db->commit();

                        return json_encode([
                            "success" => "La solicitud ha sido aceptada, el compromiso se ha registrado y el presupuesto actualizado",
                            "compromiso" => [
                                "correlativo" => $resultadoCompromiso['correlativo'],
                                "id_compromiso" => $resultadoCompromiso['id_compromiso']
                            ]
                        ]);
                    } else {
                        $conexion->rollback();
                        $remote_db->rollback();
                        throw new Exception("No se pudo registrar el compromiso");
                    }
                } else {
                    $conexion->rollback();
                    $remote_db->rollback();
                    throw new Exception("No se pudo actualizar la solicitud a aceptada");
                }
            }
        } elseif ($accion === "rechazar") {
            // Actualizar el estado de la solicitud a rechazado en ambas bases de datos
            foreach ([$conexion, $remote_db] as $db) {
                $sqlUpdateSolicitud = "UPDATE solicitud_dozavos SET status = 3 WHERE id = ?";
                $stmtUpdateSolicitud = $db->prepare($sqlUpdateSolicitud);
                $stmtUpdateSolicitud->bind_param("i", $idSolicitud);
                $stmtUpdateSolicitud->execute();

                if ($stmtUpdateSolicitud->affected_rows > 0) {
                    // Confirmar la transacción en ambas bases de datos
                    $conexion->commit();
                    $remote_db->commit();

                    return json_encode(["success" => "La solicitud ha sido rechazada"]);
                } else {
                    $conexion->rollback();
                    $remote_db->rollback();
                    throw new Exception("No se pudo rechazar la solicitud");
                }
            }
        } else {
            throw new Exception("Acción no válida. Debe ser 'aceptar' o 'rechazar'.");
        }
    } catch (Exception $e) {
        // Revertir transacciones en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


function actualizarStatusSolicitud($data)
{
    global $conexion;
    global $remote_db;

    if (!isset($data['idSolicitud'])) {
        return json_encode(["error" => "No se ha especificado el ID de la solicitud."]);
    }

    $idSolicitud = $data['idSolicitud'];

    // Iniciar transacciones en ambas bases de datos
    $conexion->begin_transaction();
    $remote_db->begin_transaction();

    try {
        // Preparar la consulta para actualizar el status en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sql = "UPDATE solicitud_dozavos SET status = 4 WHERE id = ?";
            $stmt = $db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $db->error);
            }

            $stmt->bind_param("i", $idSolicitud);
            $stmt->execute();

            // Verificar si se actualizó algún registro
            if ($stmt->affected_rows === 0) {
                throw new Exception("No se encontró la solicitud con el ID proporcionado o ya tenía el status 4.");
            }
        }

        // Confirmar las transacciones en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();

        return json_encode(["success" => "El status de la solicitud se actualizó correctamente."]);

    } catch (Exception $e) {
        // Revertir las transacciones en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}



// Función para actualizar una solicitud
function actualizarSolicitudozavo($data)
{
    global $conexion;
    global $remote_db;

    if (!isset($data['id'], $data['numero_orden'], $data['numero_compromiso'], $data['descripcion'], $data['monto'], $data['fecha'], $data['partidas'], $data['id_ente'], $data['status'], $data['id_ejercicio'], $data['mes'])) {
        return json_encode(["error" => "Faltan datos o el ID para actualizar la solicitud."]);
    }

    // Iniciar una transacción para asegurar la integridad de los datos en ambas bases de datos
    $conexion->begin_transaction();
    $remote_db->begin_transaction();

    try {
        // Actualizar la solicitud en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sql = "UPDATE solicitud_dozavos SET numero_orden = ?, numero_compromiso = ?, descripcion = ?, monto = ?, fecha = ?, partidas = ?, id_ente = ?, status = ?, id_ejercicio = ?, mes = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("issdsssisss", $data['numero_orden'], $data['numero_compromiso'], $data['descripcion'], $data['monto'], $data['fecha'], json_encode($data['partidas']), $data['id_ente'], $data['status'], $data['id_ejercicio'], $data['mes'], $data['id']);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("No se pudo actualizar la solicitud en la base de datos.");
            }
        }

        // Confirmar la transacción en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();

        return json_encode(["success" => "Solicitud actualizada con éxito."]);

    } catch (Exception $e) {
        // Revertir la transacción en ambas bases de datos en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }
}


// Función para rechazar una solicitud
function rechazarSolicitud($data)
{
    global $conexion;
    global $remote_db;

    if (!isset($data['id'])) {
        return json_encode(["error" => "No se ha especificado ID para rechazar la solicitud."]);
    }

    // Iniciar transacción en ambas bases de datos
    $conexion->begin_transaction();
    $remote_db->begin_transaction();

    try {
        // Eliminar solicitud en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sql = "DELETE FROM solicitud_dozavos WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $data['id']);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("No se pudo rechazar la solicitud en la base de datos.");
            }
        }

        // Confirmar la transacción en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();

        // Notificar tras el rechazo
        notificar(['nomina'], 11);

        return json_encode(["success" => "Solicitud rechazada y eliminada con éxito."]);

    } catch (Exception $e) {
        // Revertir la transacción en ambas bases de datos en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }
}

// Función para eliminar una solicitud y su compromiso relacionado
function eliminarSolicitudozavo($data)
{
    global $conexion;
    global $remote_db;

    if (!isset($data['id'])) {
        return json_encode(["error" => "No se ha especificado ID para eliminar."]);
    }

    $idSolicitud = $data['id'];

    // Iniciar transacciones en ambas bases de datos
    $conexion->begin_transaction();
    $remote_db->begin_transaction();

    try {
        // Eliminar el compromiso relacionado en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlCompromiso = "DELETE FROM compromisos WHERE id_registro = ?";
            $stmtCompromiso = $db->prepare($sqlCompromiso);
            $stmtCompromiso->bind_param("i", $idSolicitud);
            $stmtCompromiso->execute();

            // Verificar si el compromiso se eliminó
            if ($stmtCompromiso->affected_rows === 0) {
                throw new Exception("No se pudo eliminar el compromiso relacionado.");
            }

            // Eliminar la solicitud en ambas bases de datos
            $sqlSolicitud = "DELETE FROM solicitud_dozavos WHERE id = ?";
            $stmtSolicitud = $db->prepare($sqlSolicitud);
            $stmtSolicitud->bind_param("i", $idSolicitud);
            $stmtSolicitud->execute();

            // Verificar si la solicitud se eliminó
            if ($stmtSolicitud->affected_rows === 0) {
                throw new Exception("No se pudo eliminar la solicitud.");
            }
        }

        // Confirmar las transacciones en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();

        return json_encode(["success" => "Solicitud y compromiso eliminados con éxito."]);

    } catch (Exception $e) {
        // Revertir las transacciones en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        return json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}


// Ejecutar la función principal
$data = json_decode(file_get_contents("php://input"), true);
echo gestionarSolicitudDozavos($data);
