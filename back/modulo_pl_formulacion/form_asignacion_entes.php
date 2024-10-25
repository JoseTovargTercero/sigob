<?php
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para insertar un registro en asignacion_ente
function insertarAsignacionEnte($id_ente, $monto_total, $id_ejercicio)
{
    global $conexion;

    $conexion->begin_transaction();

    try {
        $fecha = date('Y-m-d');
        $status = 0;

        $sql = "INSERT INTO asignacion_ente (id_ente, monto_total, id_ejercicio, fecha, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("idisi", $id_ente, $monto_total, $id_ejercicio, $fecha, $status);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Registro insertado correctamente."]);
        } else {
            throw new Exception("No se pudo insertar el registro.");
        }
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar un registro en asignacion_ente
function actualizarAsignacionEnte($id, $id_ente, $monto_total, $id_ejercicio)
{
    global $conexion;

    $conexion->begin_transaction();

    try {
        $sql = "UPDATE asignacion_ente SET id_ente = ?, monto_total = ?, id_ejercicio = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("idii", $id_ente, $monto_total, $id_ejercicio, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Registro actualizado correctamente."]);
        } else {
            throw new Exception("No se encontró el registro o no se hicieron cambios.");
        }
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un registro de asignacion_ente
function eliminarAsignacionEnte($id)
{
    global $conexion;

    $conexion->begin_transaction();

    try {
        $sql = "DELETE FROM asignacion_ente WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conexion->commit();
            return json_encode(["success" => "Registro eliminado correctamente."]);
        } else {
            throw new Exception("No se encontró el registro para eliminar.");
        }
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

function consultarAsignacionPorId($id)
{
    global $conexion;

    try {
        // Consulta principal para obtener los datos de asignacion_ente y sus detalles del ente
        $sql = "SELECT a.*, e.ente_nombre, e.tipo_ente 
                FROM asignacion_ente a
                JOIN entes e ON a.id_ente = e.id
                WHERE a.id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $asignacion = $result->fetch_assoc();

            // Consulta adicional para obtener los detalles de distribucion_entes asociados al id_asignacion
            $sqlDistribucion = "SELECT id, id_ente, distribucion, monto_total, status, id_ejercicio 
                                FROM distribucion_entes 
                                WHERE id_asignacion = ?";
            $stmtDistribucion = $conexion->prepare($sqlDistribucion);
            $stmtDistribucion->bind_param("i", $id);
            $stmtDistribucion->execute();
            $resultDistribucion = $stmtDistribucion->get_result();

            if ($resultDistribucion->num_rows > 0) {

                while ($distribucion = $resultDistribucion->fetch_assoc()) {
                    // Decodificar el campo 'distribucion' de JSON a array
                    if (!empty($distribucion['distribucion'])) {
                        $asignacion["distribucion_partidas"] = json_decode($distribucion['distribucion'], true);

                        // Iterar sobre cada distribución y obtener detalles adicionales de distribucion_presupuestarias
                        foreach ($asignacion["distribucion_partidas"] as &$distribucionItem) {
                            $idDistribucion = $distribucionItem['id_distribucion'];

                            // Consulta para obtener el id_partida y id_sector de distribucion_presupuestarias
                            $sqlDistribucionDetalles = "SELECT id_partida, id_sector FROM distribucion_presupuestaria WHERE id = ?";
                            $stmtDistribucionDetalles = $conexion->prepare($sqlDistribucionDetalles);
                            $stmtDistribucionDetalles->bind_param("i", $idDistribucion);
                            $stmtDistribucionDetalles->execute();
                            $resultDistribucionDetalles = $stmtDistribucionDetalles->get_result();

                            if ($resultDistribucionDetalles->num_rows > 0) {
                                $distribucionDetalles = $resultDistribucionDetalles->fetch_assoc();
                                $distribucionItem['id_partida'] = $distribucionDetalles['id_partida'];
                                $distribucionItem['id_sector'] = $distribucionDetalles['id_sector'];

                                // Obtener detalles del sector
                                $sqlSector = "SELECT * FROM pl_sectores_presupuestarios WHERE id = ?";
                                $stmtSector = $conexion->prepare($sqlSector);
                                $stmtSector->bind_param("i", $distribucionDetalles['id_sector']);
                                $stmtSector->execute();
                                $resultSector = $stmtSector->get_result();

                                if ($resultSector->num_rows > 0) {
                                    $distribucionItem['sector_informacion'] = $resultSector->fetch_assoc();
                                } else {
                                    $distribucionItem['sector_informacion'] = null;
                                }

                                // Consulta para obtener los detalles de la partida de la tabla partidas_presupuestarias
                                $sqlPartida = "SELECT * FROM partidas_presupuestarias WHERE id = ?";
                                $stmtPartida = $conexion->prepare($sqlPartida);
                                $stmtPartida->bind_param("i", $distribucionDetalles['id_partida']);
                                $stmtPartida->execute();
                                $resultPartida = $stmtPartida->get_result();

                                if ($resultPartida->num_rows > 0) {
                                    $distribucionItem += $resultPartida->fetch_assoc();
                                } else {
                                    $distribucionItem['partida_informacion'] = null;
                                }

                            } else {
                                $distribucionItem['id_partida'] = null;
                                $distribucionItem['id_sector'] = null;
                            }
                        }
                    } else {
                        $asignacion["distribucion_partidas"] = [];
                    }

                    $distribuciones = $distribucion;
                }

                $asignacion['distribucion'] = $distribuciones;
            } else {
                $asignacion['distribucion'] = false;
            }

            // Consulta adicional para obtener las dependencias de entes en entes_dependencias
            $idEnte = $asignacion['id_ente'];
            $sqlDependencias = "SELECT * FROM entes_dependencias WHERE ue = ?";
            $stmtDependencias = $conexion->prepare($sqlDependencias);
            $stmtDependencias->bind_param("i", $idEnte);
            $stmtDependencias->execute();
            $resultDependencias = $stmtDependencias->get_result();

            // Guardar las dependencias en un array de arrays
            $dependencias = [];
            while ($dependencia = $resultDependencias->fetch_assoc()) {
                $dependencias[] = $dependencia;
            }
            $asignacion['dependencias'] = $dependencias;

            return json_encode(["success" => $asignacion]);
        } else {
            return json_encode(["error" => "No se encontró el registro."]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}








// Función para consultar todos los registros en asignacion_ente
function consultarTodasAsignaciones()
{
    global $conexion;

    try {
        $sql = "SELECT a.*, e.ente_nombre, e.tipo_ente 
                FROM asignacion_ente a
                JOIN entes e ON a.id_ente = e.id";
        $result = $conexion->query($sql);

        $asignaciones = [];
        while ($row = $result->fetch_assoc()) {
            $asignaciones[] = $row;
        }

        return json_encode(["success" => $asignaciones]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    // Insertar datos
    if ($accion === "insert" && isset($data["id_ente"]) && isset($data["monto_total"]) && isset($data["id_ejercicio"])) {
        $id_ente = $data["id_ente"];
        $monto_total = $data["monto_total"];
        $id_ejercicio = $data["id_ejercicio"];
        echo insertarAsignacionEnte($id_ente, $monto_total, $id_ejercicio);

        // Actualizar datos
    } elseif ($accion === "update" && isset($data["id"]) && isset($data["id_ente"]) && isset($data["monto_total"]) && isset($data["id_ejercicio"])) {
        $id = $data["id"];
        $id_ente = $data["id_ente"];
        $monto_total = $data["monto_total"];
        $id_ejercicio = $data["id_ejercicio"];
        echo actualizarAsignacionEnte($id, $id_ente, $monto_total, $id_ejercicio);

        // Eliminar datos
    } elseif ($accion === "delete" && isset($data["id"])) {
        $id = $data["id"];
        echo eliminarAsignacionEnte($id);

        // Consultar por ID
    } elseif ($accion === "consultar_por_id" && isset($data["id"])) {
        $id = $data["id"];
        echo consultarAsignacionPorId($id);

        // Consultar todos los registros
    } elseif ($accion === "consultar") {
        echo consultarTodasAsignaciones();

        // Acción no válida o faltan datos
    } else {
        echo json_encode(['error' => "Acción no válida o faltan datos"]);
    }
} else {
    echo json_encode(['error' => "No se recibió ninguna acción"]);
}
?>