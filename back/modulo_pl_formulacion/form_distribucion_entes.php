<?php
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para insertar una nueva distribución en la tabla distribucion_ente
function insertarDistribucion($id_ente, $partidas, $id_ejercicio, $id_asignacion)
{
    global $conexion;
    $status = 0;
    $comentario = "";  // Campo agregado con valor vacío
    $fecha = date("Y-m-d");  // Obtener la fecha actual

    try {
        $conexion->begin_transaction();

        // Consultar el tipo de ente para verificar si es 'J' o 'D'
        $sqlTipoEnte = "SELECT tipo_ente FROM entes WHERE id = ?";
        $stmtTipoEnte = $conexion->prepare($sqlTipoEnte);
        $stmtTipoEnte->bind_param("i", $id_ente);
        $stmtTipoEnte->execute();
        $resultadoTipoEnte = $stmtTipoEnte->get_result();

        if ($resultadoTipoEnte->num_rows === 0) {
            throw new Exception("No se encontró el ente especificado.");
        }

        $filaTipoEnte = $resultadoTipoEnte->fetch_assoc();
        $tipo_ente = $filaTipoEnte['tipo_ente'];

        // Verificar el formato de las partidas basado en el tipo_ente
        $num_partidas = count($partidas);
        if ($tipo_ente === 'D' && $num_partidas > 1) {
            throw new Exception("El tipo de ente Descentralizado solo permite una partida.");
        } elseif (!in_array($tipo_ente, ['J', 'D'])) {
            throw new Exception("Tipo de ente no válido.");
        }

        // Sumar los montos de las partidas
        $sumaMontos = 0;
        foreach ($partidas as $partida) {
            $sumaMontos += $partida['monto'];
        }

        // Consultar el monto_total de la tabla asignacion_ente usando el id_asignacion
        $sqlMontoTotal = "SELECT monto_total FROM asignacion_ente WHERE id = ?";
        $stmtMontoTotal = $conexion->prepare($sqlMontoTotal);
        $stmtMontoTotal->bind_param("i", $id_asignacion);
        $stmtMontoTotal->execute();
        $resultadoMontoTotal = $stmtMontoTotal->get_result();

        if ($resultadoMontoTotal->num_rows === 0) {
            throw new Exception("No se encontró una asignación presupuestaria para el ID especificado.");
        }

        $filaMontoTotal = $resultadoMontoTotal->fetch_assoc();
        $monto_total = $filaMontoTotal['monto_total'];

        // Verificar si la suma de los montos de las partidas es igual a monto_total
        if ($sumaMontos != $monto_total) {
            throw new Exception("La suma de los montos de las partidas no es igual al monto total.");
        }

        // Convertir el array de partidas a JSON
        $partidas_json = json_encode($partidas);

        // Insertar los datos en la tabla distribucion_ente
        $sqlInsert = "INSERT INTO distribucion_entes (id_ente, partidas, monto_total, status, id_ejercicio, comentario, fecha, id_asignacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        $stmtInsert->bind_param("isdisssi", $id_ente, $partidas_json, $monto_total, $status, $id_ejercicio, $comentario, $fecha, $id_asignacion);
        $stmtInsert->execute();

        if ($stmtInsert->affected_rows > 0) {
            // Actualizar el status de asignacion_ente a 1
            $sqlUpdateAsignacion = "UPDATE asignacion_ente SET status = 1 WHERE id = ?";
            $stmtUpdateAsignacion = $conexion->prepare($sqlUpdateAsignacion);
            $stmtUpdateAsignacion->bind_param("i", $id_asignacion);
            $stmtUpdateAsignacion->execute();

            $conexion->commit();
            return json_encode(["success" => "Distribución insertada correctamente"]);
        } else {
            throw new Exception("No se pudo insertar la distribución.");
        }

    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}



// Función para aprobar o rechazar la distribución
function actualizarEstadoDistribucion($id, $status, $comentario)
{
    global $conexion;

    try {
        $conexion->begin_transaction();

        // Verificar el valor de status para aprobar o rechazar
        if ($status == 1) {
            $sqlUpdate = "UPDATE distribucion_entes SET status = 1, comentario = '' WHERE id = ?";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bind_param("i", $id);
        } elseif ($status == 2) {
            $sqlUpdate = "UPDATE distribucion_entes SET status = 2, comentario = ? WHERE id = ?";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bind_param("si", $comentario, $id);

        } else {
            throw new Exception("Estado no válido. Utilice 1 para aprobar o 2 para rechazar.");
        }

        $stmtUpdate->execute();

        if ($stmtUpdate->affected_rows > 0) {
            $conexion->commit();
            $mensaje = ($status == 1) ? "Distribución aprobada correctamente" : "Distribución rechazada correctamente";
            return json_encode(["success" => $mensaje]);
        } else {
            throw new Exception("No se encontró el registro de distribución o el estado ya estaba configurado.");
        }

    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}




// Función para actualizar un registro en la tabla distribucion_entes
function actualizarDistribucionEntes($id, $id_ente, $partidas, $id_ejercicio)
{
    global $conexion;
    $conexion->begin_transaction();

    try {
        $partidasFormateadas = json_encode($partidas);

        $sqlUpdate = "UPDATE distribucion_entes SET id_ente = ?, partidas = ?, id_ejercicio = ? WHERE id = ?";
        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->bind_param("isii", $id_ente, $partidasFormateadas, $id_ejercicio, $id);
        $stmtUpdate->execute();

        if ($stmtUpdate->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Distribución actualizada correctamente"]);
        } else {
            throw new Exception("No se pudo actualizar la distribución.");
        }
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}

// Función para eliminar un registro en la tabla distribucion_entes
function eliminarDistribucionEntes($id)
{
    global $conexion;
    $conexion->begin_transaction();

    try {
        $sqlDelete = "DELETE FROM distribucion_entes WHERE id = ?";
        $stmtDelete = $conexion->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $id);
        $stmtDelete->execute();

        if ($stmtDelete->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Distribución eliminada correctamente"]);
        } else {
            throw new Exception("No se pudo eliminar la distribución.");
        }
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}

// Función para consultar un registro por ID en la tabla distribucion_entes y obtener detalles adicionales 
function consultarDistribucionPorId($id)
{
    global $conexion;

    $sqlSelectById = "SELECT id, id_ente, partidas, monto_total, status, id_ejercicio, id_asignacion FROM distribucion_entes WHERE id = ?";
    $stmtSelectById = $conexion->prepare($sqlSelectById);
    $stmtSelectById->bind_param("i", $id);
    $stmtSelectById->execute();
    $resultado = $stmtSelectById->get_result();

    if ($resultado->num_rows > 0) {
        $distribucion = $resultado->fetch_assoc();

        // Obtener los detalles del ente
        $sqlEnte = "SELECT ente_nombre, tipo_ente FROM entes WHERE id = ?";
        $stmtEnte = $conexion->prepare($sqlEnte);
        $stmtEnte->bind_param("i", $distribucion['id_ente']);
        $stmtEnte->execute();
        $resultEnte = $stmtEnte->get_result();

        if ($resultEnte->num_rows > 0) {
            $ente = $resultEnte->fetch_assoc();
            $distribucion['ente_nombre'] = $ente['ente_nombre'];
            $distribucion['tipo_ente'] = $ente['tipo_ente'];
        } else {
            $distribucion['ente_nombre'] = null;
            $distribucion['tipo_ente'] = null;
        }

        // Formatear las partidas
        $partidasArray = json_decode($distribucion['partidas'], true); // Asumimos que está guardado como JSON con id_partida y monto
        $partidasDetalles = [];

        if (!empty($partidasArray)) {
            $idsPartidas = array_column($partidasArray, 'id_partida'); // Extraer solo los IDs de las partidas
            $sqlPartidas = "SELECT id, partida, descripcion FROM partidas_presupuestarias WHERE id IN (" . implode(",", $idsPartidas) . ")";
            $resultPartidas = $conexion->query($sqlPartidas);

            while ($partida = $resultPartidas->fetch_assoc()) {
                // Buscar el monto correspondiente en el array de partidas original
                $monto = null;
                foreach ($partidasArray as $p) {
                    if ($p['id_partida'] == $partida['id']) {
                        $monto = $p['monto'];
                        break;
                    }
                }

                $partidasDetalles[] = [
                    'id' => $partida['id'],
                    'partida' => $partida['partida'],
                    'descripcion' => $partida['descripcion'],
                    'monto' => $monto
                ];
            }
        }

        $distribucion['partidas'] = $partidasDetalles;

        // Obtener los detalles de la asignación
        $sqlAsignacion = "SELECT id, id_ente, monto_total, id_ejercicio, status FROM asignacion_ente WHERE id = ?";
        $stmtAsignacion = $conexion->prepare($sqlAsignacion);
        $stmtAsignacion->bind_param("i", $distribucion['id_asignacion']);
        $stmtAsignacion->execute();
        $resultAsignacion = $stmtAsignacion->get_result();

        if ($resultAsignacion->num_rows > 0) {
            $distribucion['asignacion'] = $resultAsignacion->fetch_assoc();
        } else {
            $distribucion['asignacion'] = null;
        }

        // Devolver la respuesta final con monto_total y asignación incluida
        return json_encode(["success" => $distribucion]);
    } else {
        return json_encode(["error" => "No se encontró la distribución con el ID especificado."]);
    }
}



// Función para consultar todos los registros en la tabla distribucion_entes
function consultarTodasDistribuciones()
{
    global $conexion;

    $sqlSelectAll = "SELECT id, id_ente, partidas, monto_total, status, id_ejercicio, id_asignacion FROM distribucion_entes";
    $resultado = $conexion->query($sqlSelectAll);

    if ($resultado->num_rows > 0) {
        $distribuciones = [];

        while ($fila = $resultado->fetch_assoc()) {
            // Obtener detalles del ente
            $sqlEnte = "SELECT ente_nombre, tipo_ente FROM entes WHERE id = ?";
            $stmtEnte = $conexion->prepare($sqlEnte);
            $stmtEnte->bind_param("i", $fila['id_ente']);
            $stmtEnte->execute();
            $resultEnte = $stmtEnte->get_result();

            if ($resultEnte->num_rows > 0) {
                $ente = $resultEnte->fetch_assoc();
                $fila['ente_nombre'] = $ente['ente_nombre'];
                $fila['tipo_ente'] = $ente['tipo_ente'];
            } else {
                $fila['ente_nombre'] = null;
                $fila['tipo_ente'] = null;
            }

            // Formatear las partidas y obtener sus montos
            $partidasArray = json_decode($fila['partidas'], true); // Asumimos que está guardado como JSON con id_partida y monto
            $partidasDetalles = [];

            if (!empty($partidasArray)) {
                $idsPartidas = array_column($partidasArray, 'id_partida'); // Extraer solo los IDs de las partidas
                $sqlPartidas = "SELECT id, partida, descripcion FROM partidas_presupuestarias WHERE id IN (" . implode(",", $idsPartidas) . ")";
                $resultPartidas = $conexion->query($sqlPartidas);

                while ($partida = $resultPartidas->fetch_assoc()) {
                    // Buscar el monto correspondiente en el array de partidas original
                    $monto = null;
                    foreach ($partidasArray as $p) {
                        if ($p['id_partida'] == $partida['id']) {
                            $monto = $p['monto'];
                            break;
                        }
                    }

                    $partidasDetalles[] = [
                        'id' => $partida['id'],
                        'partida' => $partida['partida'],
                        'descripcion' => $partida['descripcion'],
                        'monto' => $monto
                    ];
                }
            }

            $fila['partidas'] = $partidasDetalles;

            // Obtener los detalles de la asignación
            $sqlAsignacion = "SELECT id, id_ente, monto_total, id_ejercicio, status FROM asignacion_ente WHERE id = ?";
            $stmtAsignacion = $conexion->prepare($sqlAsignacion);
            $stmtAsignacion->bind_param("i", $fila['id_asignacion']);
            $stmtAsignacion->execute();
            $resultAsignacion = $stmtAsignacion->get_result();

            if ($resultAsignacion->num_rows > 0) {
                $fila['asignacion'] = $resultAsignacion->fetch_assoc();
            } else {
                $fila['asignacion'] = null;
            }

            $distribuciones[] = $fila;
        }

        return json_encode(["success" => $distribuciones]);
    } else {
        return json_encode(["error" => "No se encontraron distribuciones registradas."]);
    }
}




// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    // Insertar datos
    if ($accion === "insert" && isset($data["id_ente"]) && isset($data["partidas"]) && isset($data["id_ejercicio"]) && isset($data["id_asignacion"])) {
        $id_ente = $data["id_ente"];
        $partidas = $data["partidas"]; // Asumimos que 'partidas' es un array de arrays con 'id_partida' y 'monto'
        $id_ejercicio = $data["id_ejercicio"];
        $id_asignacion = $data["id_asignacion"];
        echo insertarDistribucion($id_ente, $partidas, $id_ejercicio, $id_asignacion);

        // Actualizar datos
    } elseif ($accion === "update" && isset($data["id"]) && isset($data["id_ente"]) && isset($data["partidas"]) && isset($data["id_ejercicio"])) {
        $id = $data["id"];
        $id_ente = $data["id_ente"];
        $partidas = $data["partidas"]; // Asumimos que 'partidas' es un array de arrays con 'id_partida' y 'monto'
        $id_ejercicio = $data["id_ejercicio"];
        echo actualizarDistribucionEntes($id, $id_ente, $partidas, $id_ejercicio);

        // Eliminar datos
    } elseif ($accion === "delete" && isset($data["id"])) {
        $id = $data["id"];
        echo eliminarDistribucionEntes($id);

        // Consultar por ID
    } elseif ($accion === "consultar_id" && isset($data["id"])) {
        $id = $data["id"];
        echo consultarDistribucionPorId($id);

        // Consultar todos los registros
    } elseif ($accion === "consultar") {
        echo consultarTodasDistribuciones();

        // Aprobar o rechazar la distribución
    } elseif ($accion === "aprobar_rechazar" && isset($data["id"]) && isset($data["status"])) {
        $id = $data["id"];
        $status = $data["status"];
        $comentario = isset($data["comentario"]) ? $data["comentario"] : ""; // Comentario opcional
        echo actualizarEstadoDistribucion($id, $status, $comentario);

        // Acción no válida o faltan datos
    } else {
        echo json_encode(['error' => "Acción no válida o faltan datos"]);
    }
} else {
    echo json_encode(['error' => "No se recibió ninguna acción"]);
}

?>