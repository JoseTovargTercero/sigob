<?php
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para insertar una nueva distribución en la tabla distribucion_ente
function insertarDistribucion($id_ente, $partidas, $id_ejercicio) {
    global $conexion;
    $status = 0;

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
        if ($tipo_ente === 'J' || $tipo_ente === 'D') {
            $num_partidas = count($partidas);
            if ($tipo_ente === 'D' && $num_partidas > 1) {
                throw new Exception("El tipo de ente Descentralizado solo permite una partida.");
            }
        } else {
            throw new Exception("Tipo de ente no válido.");
        }

        // Convertir el array de partidas al formato requerido
        $partidas_json = json_encode($partidas);

        // Consultar el monto_total de la tabla asignacion_ente
        $sqlMontoTotal = "SELECT monto_total FROM asignacion_ente WHERE id_ente = ? AND id_ejercicio = ?";
        $stmtMontoTotal = $conexion->prepare($sqlMontoTotal);
        $stmtMontoTotal->bind_param("ii", $id_ente, $id_ejercicio);
        $stmtMontoTotal->execute();
        $resultadoMontoTotal = $stmtMontoTotal->get_result();

        if ($resultadoMontoTotal->num_rows === 0) {
            throw new Exception("No se encontró una asignación presupuestaria para el ente y ejercicio especificados.");
        }

        $filaMontoTotal = $resultadoMontoTotal->fetch_assoc();
        $monto_total = $filaMontoTotal['monto_total'];

        // Insertar los datos en la tabla distribucion_ente
        $sqlInsert = "INSERT INTO distribucion_ente (id_ente, partidas, monto_total, status, id_ejercicio) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        $stmtInsert->bind_param("isdii", $id_ente, $partidas_json, $monto_total, $status, $id_ejercicio);
        $stmtInsert->execute();

        if ($stmtInsert->affected_rows > 0) {
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

// Función para aprobar la distribución
function aprobarDistribucion($id) {
    global $conexion;

    try {
        $conexion->begin_transaction();

        $sqlUpdate = "UPDATE distribucion_ente SET status = 1 WHERE id = ?";
        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->bind_param("i", $id);
        $stmtUpdate->execute();

        if ($stmtUpdate->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Distribución aprobada correctamente"]);
        } else {
            throw new Exception("No se encontró el registro de distribución o ya estaba aprobado.");
        }

    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar un registro en la tabla distribucion_entes
function actualizarDistribucionEntes($id, $id_ente, $partidas, $id_ejercicio) {
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
function eliminarDistribucionEntes($id) {
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
function consultarDistribucionPorId($id) {
    global $conexion;

    $sqlSelectById = "SELECT id, id_ente, partidas, monto_total, status, id_ejercicio FROM distribucion_entes WHERE id = ?";
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
            $distribucion['tipo_ente'] = ($ente['tipo_ente'] == 'J') ? 'Juridico' : 'Descentralizado';
        } else {
            $distribucion['ente_nombre'] = null;
            $distribucion['tipo_ente'] = null;
        }

        // Formatear el status
        $distribucion['status'] = ($distribucion['status'] == 0) ? 'En espera' : 'Aprobado';

        // Formatear las partidas
        $partidasArray = json_decode($distribucion['partidas'], true); // Asumimos que está guardado como JSON
        $partidasDetalles = [];

        if (!empty($partidasArray)) {
            $sqlPartidas = "SELECT id, partida, descripcion FROM partidas_presupuestarias WHERE id IN (" . implode(",", $partidasArray) . ")";
            $resultPartidas = $conexion->query($sqlPartidas);

            while ($partida = $resultPartidas->fetch_assoc()) {
                $partidasDetalles[] = [
                    'id' => $partida['id'],
                    'partida' => $partida['partida'],
                    'descripcion' => $partida['descripcion']
                ];
            }
        }

        $distribucion['partidas'] = $partidasDetalles;

        // Devolver la respuesta final con monto_total incluido
        return json_encode($distribucion);
    } else {
        return json_encode(["error" => "No se encontró la distribución con el ID especificado."]);
    }
}


// Función para consultar todos los registros en la tabla distribucion_entes
function consultarTodasDistribuciones() {
    global $conexion;

    $sqlSelectAll = "SELECT id, id_ente, partidas, monto_total, status, id_ejercicio FROM distribucion_entes";
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
                $fila['tipo_ente'] = ($ente['tipo_ente'] == 'J') ? 'Juridico' : 'Descentralizado';
            } else {
                $fila['ente_nombre'] = null;
                $fila['tipo_ente'] = null;
            }

            // Formatear el status
            $fila['status'] = ($fila['status'] == 0) ? 'En espera' : 'Aprobado';

            // Formatear las partidas
            $partidasArray = json_decode($fila['partidas'], true); // Asumimos que está guardado como JSON
            $partidasDetalles = [];

            if (!empty($partidasArray)) {
                $sqlPartidas = "SELECT id, partida, descripcion FROM partidas_presupuestarias WHERE id IN (" . implode(",", $partidasArray) . ")";
                $resultPartidas = $conexion->query($sqlPartidas);

                while ($partida = $resultPartidas->fetch_assoc()) {
                    $partidasDetalles[] = [
                        'id' => $partida['id'],
                        'partida' => $partida['partida'],
                        'descripcion' => $partida['descripcion']
                    ];
                }
            }

            $fila['partidas'] = $partidasDetalles;
            $distribuciones[] = $fila;
        }

        return json_encode($distribuciones);
    } else {
        return json_encode(["error" => "No se encontraron distribuciones registradas."]);
    }
}


// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    // Insertar datos
    if ($accion === "insert" && isset($data["id_ente"]) && isset($data["partidas"]) && isset($data["id_ejercicio"])) {
        $id_ente = $data["id_ente"];
        $partidas = $data["partidas"];
        $id_ejercicio = $data["id_ejercicio"];
        echo insertarDistribucionEntes($id_ente, $partidas, $id_ejercicio);

    // Actualizar datos
    } elseif ($accion === "update" && isset($data["id"]) && isset($data["id_ente"]) && isset($data["partidas"]) && isset($data["id_ejercicio"])) {
        $id = $data["id"];
        $id_ente = $data["id_ente"];
        $partidas = $data["partidas"];
        $id_ejercicio = $data["id_ejercicio"];
        echo actualizarDistribucionEntes($id, $id_ente, $partidas, $id_ejercicio);

    // Eliminar datos
    } elseif ($accion === "delete" && isset($data["id"])) {
        $id = $data["id"];
        echo eliminarDistribucionEntes($id);

    // Consultar por ID
    } elseif ($accion === "consultar_por_id" && isset($data["id"])) {
        $id = $data["id"];
        echo consultarDistribucionPorId($id);

    // Consultar todos los registros
    } elseif ($accion === "consultar_todas") {
        echo consultarTodasDistribuciones();

    // Acción no válida o faltan datos
    } else {
        echo json_encode(['error' => "Acción no válida o faltan datos"]);
    }
} else {
    echo json_encode(['error' => "No se recibió ninguna acción"]);
}
?>

?>
