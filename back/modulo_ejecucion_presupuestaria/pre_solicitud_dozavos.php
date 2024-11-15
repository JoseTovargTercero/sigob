<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
header('Content-Type: application/json');
require_once '../sistema_global/errores.php';
require_once 'pre_compromisos.php';

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

    $sql = "SELECT id, numero_orden, numero_compromiso, descripcion, monto, fecha, partidas, id_ente FROM solicitud_dozavos";
    $result = $conexion->query($sql);

    if ($result->num_rows > 0) {
        $solicitudes = [];

        while ($row = $result->fetch_assoc()) {
            $partidasArray = json_decode($row['partidas'], true);

            foreach ($partidasArray as &$partida) {
                $idPartida = $partida['id'];
                $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $idPartida);
                $stmtPartida->execute();
                $stmtPartida->bind_result($partidaCod, $nombre, $descripcion);
                $stmtPartida->fetch();
                $stmtPartida->close();

                $partida['partida'] = $partidaCod;
                $partida['nombre'] = $nombre;
                $partida['descripcion'] = $descripcion;
            }

            $row['partidas'] = $partidasArray;
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
    $sql = "SELECT id, numero_orden, numero_compromiso, descripcion, monto, fecha, partidas, id_ente FROM solicitud_dozavos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $partidasArray = json_decode($row['partidas'], true);

        foreach ($partidasArray as &$partida) {
            $idPartida = $partida['id'];
            $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $conexion->prepare($sqlPartida);
            $stmtPartida->bind_param("i", $idPartida);
            $stmtPartida->execute();
            $stmtPartida->bind_result($partidaCod, $nombre, $descripcion);
            $stmtPartida->fetch();
            $stmtPartida->close();

            $partida['partida'] = $partidaCod;
            $partida['nombre'] = $nombre;
            $partida['descripcion'] = $descripcion;
        }

        $row['partidas'] = $partidasArray;
        return json_encode(["success" => $row]);
    } else {
        return json_encode(["error" => "No se encontró el registro con el ID especificado."]);
    }
}

// Función para registrar solicitud dozavos
function registrarSolicitudozavo($data)
{
    global $conexion;

    try {
        if (!isset($data['descripcion']) || !isset($data['monto']) || !isset($data['fecha']) || !isset($data['partidas']) || !isset($data['id_ente'])) {
            return ["error" => "Faltan datos obligatorios para registrar la solicitud."];
        }

        // Generar el numero_orden automáticamente
        $numero_orden = generarNumeroOrden();

        // Insertar en solicitud_dozavos (numero_compromiso siempre será 0 inicialmente)
        $sql = "INSERT INTO solicitud_dozavos (numero_orden, numero_compromiso, descripcion, monto, fecha, partidas, id_ente, status) VALUES (?, 0, ?, ?, ?, ?, ?, 0)";
        $stmt = $conexion->prepare($sql);
        $partidasJson = json_encode($data['partidas']); // Convertir partidas a formato JSON
        $stmt->bind_param("isdsss", $numero_orden, $data['descripcion'], $data['monto'], $data['fecha'], $partidasJson, $data['id_ente']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $idRegistro = $stmt->insert_id; // Obtener el ID del registro insertado

            // Llamar a la función registrarCompromiso para actualizar numero_compromiso
            $resultadoCompromiso = registrarCompromiso($idRegistro, 'solicitud_dozavos', $data['descripcion']);

            // Verificar si la función registrarCompromiso fue exitosa
            if (isset($resultadoCompromiso['error'])) {
                return ["error" => $resultadoCompromiso['error']];
            }

            return ["success" => "Registro exitoso y compromiso generado.", "compromiso" => $resultadoCompromiso];
        } else {
            return ["error" => "No se pudo registrar la solicitud."];
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return ["error" => $e->getMessage()];
    }
}

// Función para generar el número de orden
function generarNumeroOrden() {
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

// Función para actualizar una solicitud
function actualizarSolicitudozavo($data)
{
    global $conexion;

    if (!isset($data['id'], $data['numero_orden'], $data['numero_compromiso'], $data['descripcion'], $data['monto'], $data['fecha'], $data['partidas'], $data['id_ente'], $data['status'])) {
        return json_encode(["error" => "Faltan datos o el ID para actualizar la solicitud."]);
    }

    $sql = "UPDATE solicitud_dozavos SET numero_orden = ?, numero_compromiso = ?, descripcion = ?, monto = ?, fecha = ?, partidas = ?, id_ente = ?, status = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("issdsssii", $data['numero_orden'], $data['numero_compromiso'], $data['descripcion'], $data['monto'], $data['fecha'], json_encode($data['partidas']), $data['id_ente'], $data['status'], $data['id']);
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
