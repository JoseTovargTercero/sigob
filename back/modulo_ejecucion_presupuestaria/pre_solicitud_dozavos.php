<?php

require_once '../sistema_global/conexion.php';

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
            return consultarSolicitudes();
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
function consultarSolicitudes()
{
    global $conexion;

    $sql = "SELECT id, numero_orden, numero_compromiso, descripcion, monto, fecha, partidas, id_ente, tipo, mes,  status, id_ejercicio FROM solicitud_dozavos";
    $result = $conexion->query($sql);

    if ($result->num_rows > 0) {
        $solicitudes = [];

        while ($row = $result->fetch_assoc()) {
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
        return json_encode(["error" => "No se ha especificado ID para consulta."]);
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

    try {
        if (!isset($data['descripcion']) || !isset($data['monto']) || !isset($data['tipo']) || !isset($data['partidas']) || !isset($data['id_ente']) || !isset($data['id_ejercicio']) || !isset($data['mes'])) {
            return json_encode(["error" => "Faltan datos obligatorios para registrar la solicitud."]);
        }

        // Iniciar una transacción
        $conexion->begin_transaction();

        $mesActual = date("n"); // Mes actual (1-12)
        $mesSolicitado = $data['mes']; // Mes solicitado
        $idEnte = $data['id_ente'];
        $idEjercicio = $data['id_ejercicio'];

        // Verificar si ya existe una solicitud pendiente (status = 1)
        $sqlPendiente = "SELECT COUNT(*) AS total FROM solicitud_dozavos WHERE id_ente = ? AND status = 1 AND id_ejercicio = ?";
        $stmtPendiente = $conexion->prepare($sqlPendiente);
        $stmtPendiente->bind_param("ii", $idEnte, $idEjercicio);
        $stmtPendiente->execute();
        $resultadoPendiente = $stmtPendiente->get_result();
        $filaPendiente = $resultadoPendiente->fetch_assoc();

        if ($filaPendiente['total'] > 0) {
            $conexion->rollback();
            return json_encode(["error" => "No se puede registrar la solicitud porque hay una pendiente."]);
        }

        // Verificar la existencia de solicitudes para el mes actual
        $sqlMesActual = "SELECT COUNT(*) AS total FROM solicitud_dozavos WHERE id_ente = ? AND mes = ? AND id_ejercicio = ? AND status != '3'";
        $stmtMesActual = $conexion->prepare($sqlMesActual);
        $stmtMesActual->bind_param("iii", $idEnte, $mesActual, $idEjercicio);
        $stmtMesActual->execute();
        $resultadoMesActual = $stmtMesActual->get_result();
        $filaMesActual = $resultadoMesActual->fetch_assoc();
        $existeMesActual = $filaMesActual['total'] > 0;

        // Condiciones para permitir el registro
        if ($mesSolicitado == $mesActual && !$existeMesActual) {
            // Permitido registrar para el mes en curso si aún no existe
        } elseif ($mesSolicitado == ($mesActual + 1) && $existeMesActual) {
            // Permitido registrar para el siguiente mes si el mes actual ya existe
        } else {
            $conexion->rollback();
            return json_encode(["error" => "No se puede registrar la solicitud. Condiciones no cumplidas."]);
        }

        // Generar el numero_orden automáticamente
        $numero_orden = generarNumeroOrden();
        $fecha = date("Y-m-d");

        // Insertar en solicitud_dozavos (numero_compromiso siempre será 0 inicialmente)
        $sqlInsertar = "INSERT INTO solicitud_dozavos (numero_orden, numero_compromiso, descripcion, tipo, monto, fecha, partidas, id_ente, status, id_ejercicio, mes) VALUES (?, 0, ?, ?, ?, ?, ?, ?, 1, ?, ?)";
        $stmtInsertar = $conexion->prepare($sqlInsertar);
        $partidasJson = json_encode($data['partidas']); // Convertir partidas a formato JSON
        $stmtInsertar->bind_param("sssssssss", $numero_orden, $data['descripcion'], $data['tipo'], $data['monto'], $fecha, $partidasJson, $idEnte, $idEjercicio, $mesSolicitado);
        $stmtInsertar->execute();

        if ($stmtInsertar->affected_rows > 0) {
            // Confirmar la transacción
            $conexion->commit();
            return json_encode(["success" => "Registro exitoso"]);
        } else {
            $conexion->rollback();
            return json_encode(["error" => "No se pudo registrar la solicitud."]);
        }
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conexion->rollback();
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

    try {
        if (empty($idSolicitud) || empty($accion)) {
            throw new Exception("Faltan uno o más valores necesarios (idSolicitud, accion)");
        }

        // Iniciar la transacción
        $conexion->begin_transaction();

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

                // Actualizar el monto en distribucion_entes
                $sqlUpdatePartida = "UPDATE distribucion_entes SET distribucion = ? WHERE id_ente = ? AND id_ejercicio = ? AND distribucion LIKE '%\"id_distribucion\":\"$id_distribucion\"%'";
                $stmtUpdatePartida = $conexion->prepare($sqlUpdatePartida);
                $stmtUpdatePartida->bind_param("sii", $nuevaDistribucion, $id_ente, $id_ejercicio);
                $stmtUpdatePartida->execute();

                if ($stmtUpdatePartida->affected_rows === 0) {
                    throw new Exception("No se pudo actualizar el monto de distribución para el ID de distribución proporcionado");
                }
            }

            // Actualizar el estado de la solicitud a aceptado
            $sqlUpdateSolicitud = "UPDATE solicitud_dozavos SET status = 0 WHERE id = ?";
            $stmtUpdateSolicitud = $conexion->prepare($sqlUpdateSolicitud);
            $stmtUpdateSolicitud->bind_param("i", $idSolicitud);
            $stmtUpdateSolicitud->execute();

            if ($stmtUpdateSolicitud->affected_rows > 0) {
                $resultadoCompromiso = registrarCompromiso($idSolicitud, 'solicitud_dozavos', $descripcion, $id_ejercicio, $codigo);
                if (isset($resultadoCompromiso['success']) && $resultadoCompromiso['success']) {
                    // Confirmar la transacción
                    $conexion->commit();

                    return json_encode([
                        "success" => "La solicitud ha sido aceptada, el compromiso se ha registrado y el presupuesto actualizado",
                        "compromiso" => [
                            "correlativo" => $resultadoCompromiso['correlativo'],
                            "id_compromiso" => $resultadoCompromiso['id_compromiso']
                        ]
                    ]);
                } else {
                    throw new Exception("No se pudo registrar el compromiso");
                }
            } else {
                throw new Exception("No se pudo actualizar la solicitud a aceptada");
            }
        } elseif ($accion === "rechazar") {
            // Actualizar el estado de la solicitud a rechazado
            $sqlUpdateSolicitud = "UPDATE solicitud_dozavos SET status = 3 WHERE id = ?";
            $stmtUpdateSolicitud = $conexion->prepare($sqlUpdateSolicitud);
            $stmtUpdateSolicitud->bind_param("i", $idSolicitud);
            $stmtUpdateSolicitud->execute();

            if ($stmtUpdateSolicitud->affected_rows > 0) {
                // Confirmar la transacción
                $conexion->commit();

                return json_encode(["success" => "La solicitud ha sido rechazada"]);
            } else {
                throw new Exception("No se pudo rechazar la solicitud");
            }
        } else {
            throw new Exception("Acción no válida. Debe ser 'aceptar' o 'rechazar'.");
        }
    } catch (Exception $e) {
        // Si ocurre algún error, deshacer todas las operaciones anteriores
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}









// Función para actualizar una solicitud
function actualizarSolicitudozavo($data)
{
    global $conexion;

    if (!isset($data['id'], $data['numero_orden'], $data['numero_compromiso'], $data['descripcion'], $data['monto'], $data['fecha'], $data['partidas'], $data['id_ente'], $data['status'], $data['id_ejercicio'], $data['mes'])) {
        return json_encode(["error" => "Faltan datos o el ID para actualizar la solicitud."]);
    }

    $sql = "UPDATE solicitud_dozavos SET numero_orden = ?, numero_compromiso = ?, descripcion = ?, monto = ?, fecha = ?, partidas = ?, id_ente = ?, status = ?, id_ejercicio = ?, mes = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("issdsssisss", $data['numero_orden'], $data['numero_compromiso'], $data['descripcion'], $data['monto'], $data['fecha'], json_encode($data['partidas']), $data['id_ente'], $data['status'], $data['id_ejercicio'], $data['mes'], $data['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return json_encode(["success" => "Solicitud actualizada con éxito."]);
    } else {
        return json_encode(["error" => "No se pudo actualizar la solicitud."]);
    }
}

// Función para rechazar una solicitud
function rechazarSolicitud($data)
{
    global $conexion;

    if (!isset($data['id'])) {
        return json_encode(["error" => "No se ha especificado ID para rechazar la solicitud."]);
    }

    $sql = "DELETE FROM solicitud_dozavos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        notificar(['nomina'], 11);
        return json_encode(["success" => "Solicitud rechazada y eliminada con éxito."]);
    } else {
        return json_encode(["error" => "No se pudo rechazar la solicitud."]);
    }
}


// Función para eliminar una solicitud y su compromiso relacionado
function eliminarSolicitudozavo($data)
{
    global $conexion;

    if (!isset($data['id'])) {
        return json_encode(["error" => "No se ha especificado ID para eliminar."]);
    }

    $idSolicitud = $data['id'];

    // Eliminar el compromiso relacionado
    $sqlCompromiso = "DELETE FROM compromisos WHERE id_registro = ?";
    $stmtCompromiso = $conexion->prepare($sqlCompromiso);
    $stmtCompromiso->bind_param("i", $idSolicitud);
    $stmtCompromiso->execute();

    // Eliminar la solicitud
    $sqlSolicitud = "DELETE FROM solicitud_dozavos WHERE id = ?";
    $stmtSolicitud = $conexion->prepare($sqlSolicitud);
    $stmtSolicitud->bind_param("i", $idSolicitud);
    $stmtSolicitud->execute();

    if ($stmtSolicitud->affected_rows > 0) {
        return json_encode(["success" => "Solicitud y compromiso eliminados con éxito."]);
    } else {
        return json_encode(["error" => "No se pudo eliminar la solicitud o el compromiso."]);
    }
}

// Ejecutar la función principal
$data = json_decode(file_get_contents("php://input"), true);
echo gestionarSolicitudDozavos($data);
