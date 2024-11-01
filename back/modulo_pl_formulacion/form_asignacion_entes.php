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

            // Consulta para obtener los detalles de actividades_entes asociados al id_asignacion y id_ejercicio
            $sqlActividades = "SELECT de.id AS actividad_id, de.id_ente, de.distribucion, de.monto_total, de.status, de.id_ejercicio,
                                      ed.actividad, ed.ente_nombre
                               FROM distribucion_entes de
                               LEFT JOIN entes_dependencias ed ON de.actividad_id = ed.id
                               WHERE de.id_asignacion = ? AND de.id_ejercicio = ?";
            $stmtActividades = $conexion->prepare($sqlActividades);
            $stmtActividades->bind_param("ii", $id, $asignacion['id_ejercicio']);
            $stmtActividades->execute();
            $resultActividades = $stmtActividades->get_result();

            $actividadesEntes = [];
            while ($actividad = $resultActividades->fetch_assoc()) {
                if (!empty($actividad['distribucion'])) {
                    $actividad["distribucion_partidas"] = json_decode($actividad['distribucion'], true);

                    foreach ($actividad["distribucion_partidas"] as &$distribucionItem) {
                        $idDistribucion = $distribucionItem['id_distribucion'];

                        // Consulta para obtener el id_partida y id_sector de distribucion_presupuestaria
                        $sqlDistribucionDetalles = "SELECT id_partida, id_sector, id_programa FROM distribucion_presupuestaria WHERE id = ?";
                        $stmtDistribucionDetalles = $conexion->prepare($sqlDistribucionDetalles);
                        $stmtDistribucionDetalles->bind_param("i", $idDistribucion);
                        $stmtDistribucionDetalles->execute();
                        $resultDistribucionDetalles = $stmtDistribucionDetalles->get_result();

                        if ($resultDistribucionDetalles->num_rows > 0) {
                            $distribucionDetalles = $resultDistribucionDetalles->fetch_assoc();
                            $distribucionItem['id_partida'] = $distribucionDetalles['id_partida'];
                            $distribucionItem['id_sector'] = $distribucionDetalles['id_sector'];
                            $distribucionItem['id_programa'] = $distribucionDetalles['id_programa'];

                            // Obtener detalles del sector
                            $sqlSector = "SELECT * FROM pl_sectores WHERE id = ?";
                            $stmtSector = $conexion->prepare($sqlSector);
                            $stmtSector->bind_param("i", $distribucionDetalles['id_sector']);
                            $stmtSector->execute();
                            $resultSector = $stmtSector->get_result();

                            $distribucionItem['sector_informacion'] = $resultSector->num_rows > 0 ? $resultSector->fetch_assoc() : null;

                            // Obtener detalles del sector
                            $sqlPrograma = "SELECT * FROM pl_programas WHERE id = ?";
                            $stmtPrograma = $conexion->prepare($sqlPrograma);
                            $stmtPrograma->bind_param("i", $distribucionDetalles['id_programa']);
                            $stmtPrograma->execute();
                            $resultPrograma = $stmtPrograma->get_result();

                            $distribucionItem['programa_informacion'] = $resultPrograma->num_rows > 0 ? $resultPrograma->fetch_assoc() : null;

                            // Consulta para obtener los detalles de la partida
                            $sqlPartida = "SELECT * FROM partidas_presupuestarias WHERE id = ?";
                            $stmtPartida = $conexion->prepare($sqlPartida);
                            $stmtPartida->bind_param("i", $distribucionDetalles['id_partida']);
                            $stmtPartida->execute();
                            $resultPartida = $stmtPartida->get_result();

                            $distribucionItem += $resultPartida->num_rows > 0 ? $resultPartida->fetch_assoc() : ['partida_informacion' => null];
                        } else {
                            $distribucionItem['id_partida'] = null;
                            $distribucionItem['id_sector'] = null;
                        }
                    }
                } else {
                    $actividad["distribucion_partidas"] = [];
                }

                $actividadesEntes[] = $actividad;
            }

            $asignacion['actividades_entes'] = $actividadesEntes;

            // Verificar si hay dependencias
            $idEnte = $asignacion['id_ente'];
            $sqlDependencias = "SELECT * FROM entes_dependencias WHERE ue = ?";
            $stmtDependencias = $conexion->prepare($sqlDependencias);
            $stmtDependencias->bind_param("i", $idEnte);
            $stmtDependencias->execute();
            $resultDependencias = $stmtDependencias->get_result();

            // Guardar las dependencias en un array si existen registros, si no, devolver un array vacío
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