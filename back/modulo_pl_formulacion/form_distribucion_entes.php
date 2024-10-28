<?php
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para insertar varias distribuciones en la tabla distribucion_entes
function insertarDistribuciones($distribuciones)
{
    global $conexion;

    $fecha = date("Y-m-d"); // Obtener la fecha actual

    try {
        $conexion->begin_transaction();

        foreach ($distribuciones as $distribucionData) {
            // Extraer los datos del objeto actual
            $id_ente = $distribucionData['id_ente'];
            $actividad_id = isset($distribucionData['actividad_id']) ? $distribucionData['actividad_id'] : null;
            $distribucion = $distribucionData['distribuciones'];
            $id_ejercicio = $distribucionData['id_ejercicio'];
            $id_asignacion = $distribucionData['id_asignacion'];
            $status = 0;
            $comentario = ""; // Campo agregado con valor vacío

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

            // Verificar el formato de la distribución basado en el tipo_ente
            $num_distribuciones = count($distribucion);
            if ($tipo_ente === 'D' && $num_distribuciones > 1) {
                throw new Exception("El tipo de ente Descentralizado solo permite una distribución.");
            } elseif (!in_array($tipo_ente, ['J', 'D'])) {
                throw new Exception("Tipo de ente no válido.");
            }

            // Sumar los montos de las distribuciones
            $sumaMontos = 0;
            foreach ($distribucion as $item) {
                $sumaMontos += $item['monto'];
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

            // Verificar si la suma de los montos de las distribuciones es mayor a monto_total
            if ($sumaMontos > $monto_total) {
                throw new Exception("La suma de los montos de las distribuciones es mayor al monto total de la asignacion.");
            }

            // Convertir el array de distribuciones a JSON
            $distribucion_json = json_encode($distribucion);

            // Insertar los datos en la tabla distribucion_ente
            $sqlInsert = "INSERT INTO distribucion_entes (id_ente, actividad_id, distribucion, monto_total, status, id_ejercicio, comentario, fecha, id_asignacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            
            // Comprobación de actividad_id para bind_param
            if ($actividad_id === null) {
                $stmtInsert->bind_param("isdsisssi", $id_ente, $actividad_id, $distribucion_json, $monto_total, $status, $id_ejercicio, $comentario, $fecha, $id_asignacion);
            } else {
                $stmtInsert->bind_param("isdissssi", $id_ente, $actividad_id, $distribucion_json, $monto_total, $status, $id_ejercicio, $comentario, $fecha, $id_asignacion);
            }
            
            $stmtInsert->execute();

            if ($stmtInsert->affected_rows > 0) {
                // Actualizar el status de asignacion_ente a 1
                $sqlUpdateAsignacion = "UPDATE asignacion_ente SET status = 1 WHERE id = ?";
                $stmtUpdateAsignacion = $conexion->prepare($sqlUpdateAsignacion);
                $stmtUpdateAsignacion->bind_param("i", $id_asignacion);
                $stmtUpdateAsignacion->execute();
            } else {
                throw new Exception("No se pudo insertar la distribución para el ente ID: $id_ente.");
            }
        }

        $conexion->commit();
        return json_encode(["success" => "Distribuciones insertadas correctamente"]);
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}





// Función para aprobar o rechazar la distribución
function actualizarEstadoDistribucionPorAsignacion($id_asignacion, $status, $comentario = "")
{
    global $conexion;

    try {
        $conexion->begin_transaction();

        // Verificar el valor de status para aprobar o rechazar
        if ($status == 1) {
            $sqlUpdate = "UPDATE distribucion_entes SET status = 1, comentario = '' WHERE id_asignacion = ?";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bind_param("i", $id_asignacion);
        } elseif ($status == 2) {
            $sqlUpdate = "UPDATE distribucion_entes SET status = 2, comentario = ? WHERE id_asignacion = ?";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bind_param("si", $comentario, $id_asignacion);
        } else {
            throw new Exception("Estado no válido. Utilice 1 para aprobar o 2 para rechazar.");
        }

        $stmtUpdate->execute();

        if ($stmtUpdate->affected_rows > 0) {
            $conexion->commit();
            $mensaje = ($status == 1) ? "Distribuciones aprobadas correctamente" : "Distribuciones rechazadas correctamente";
            return json_encode(["success" => $mensaje]);
        } else {
            throw new Exception("No se encontraron registros de distribución con el id_asignacion especificado o los estados ya estaban configurados.");
        }

    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}





// Función para actualizar un registro en la tabla distribucion_entes
function actualizarDistribucionEntes($id, $id_ente, $actividad_id, $distribucion, $id_ejercicio)
{
    global $conexion;
    $conexion->begin_transaction();

    try {
        // Convertir el array de distribuciones a JSON
        $distribucionFormateada = json_encode($distribucion);

        // Actualizar el registro en la tabla distribucion_entes, incluyendo actividad_id
        $sqlUpdate = "UPDATE distribucion_entes SET id_ente = ?, actividad_id = ?, distribucion = ?, id_ejercicio = ? WHERE id = ?";
        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->bind_param("iisii", $id_ente, $actividad_id, $distribucionFormateada, $id_ejercicio, $id);
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

    $sqlSelectById = "SELECT id, id_ente, distribucion, monto_total, status, id_ejercicio, id_asignacion, actividad_id FROM distribucion_entes WHERE id = ?";
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

        // Obtener los detalles de la actividad
        $sqlActividad = "SELECT * FROM entes_dependencias WHERE id = ?";
        $stmtActividad = $conexion->prepare($sqlActividad);
        $stmtActividad->bind_param("i", $distribucion['actividad_id']);
        $stmtActividad->execute();
        $resultActividad = $stmtActividad->get_result();

        if ($resultActividad->num_rows > 0) {
            $distribucion['actividad_informacion'] = $resultActividad->fetch_assoc();
        } else {
            $distribucion['actividad_informacion'] = null;
        }

        // Formatear la distribucion
        $distribucionArray = json_decode($distribucion['distribucion'], true); // Asumimos que está guardado como JSON con id_distribucion y monto
        $partidasDetalles = [];

        if (!empty($distribucionArray)) {
            $idsDistribuciones = array_column($distribucionArray, 'id_distribucion'); // Extraer solo los IDs de distribuciones

            // Buscar en la tabla distribucion_presupuestaria los id_partida y id_sector
            $sqlDistribucionPresup = "SELECT id_partida, id_sector FROM distribucion_presupuestaria WHERE id IN (" . implode(",", $idsDistribuciones) . ")";
            $resultDistribucionPresup = $conexion->query($sqlDistribucionPresup);

            while ($distPresup = $resultDistribucionPresup->fetch_assoc()) {
                $idPartida = $distPresup['id_partida'];
                $idSector = $distPresup['id_sector'];

                // Obtener los detalles de la partida desde partidas_presupuestarias
                $sqlPartida = "SELECT id, partida, descripcion FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                $stmtPartida->bind_param("i", $idPartida);
                $stmtPartida->execute();
                $resultPartida = $stmtPartida->get_result();

                if ($resultPartida->num_rows > 0) {
                    $partida = $resultPartida->fetch_assoc();

                    // Buscar el monto correspondiente en el array de distribuciones original
                    $monto = null;
                    foreach ($distribucionArray as $d) {
                        if ($d['id_distribucion'] == $idPartida) {
                            $monto = $d['monto'];
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

                // Obtener la información del sector desde pl_sectores_presupuestarios
                $sqlSector = "SELECT * FROM pl_sectores_presupuestarios WHERE id = ?";
                $stmtSector = $conexion->prepare($sqlSector);
                $stmtSector->bind_param("i", $idSector);
                $stmtSector->execute();
                $resultSector = $stmtSector->get_result();

                if ($resultSector->num_rows > 0) {
                    $sectorInfo = $resultSector->fetch_assoc();
                    $distribucion['sector_informacion'] = $sectorInfo; // Agregar la información del sector
                } else {
                    $distribucion['sector_informacion'] = null;
                }
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

        // Devolver la respuesta final con monto_total, asignación y sector_informacion incluidos
        return json_encode(["success" => $distribucion]);
    } else {
        return json_encode(["error" => "No se encontró la distribución con el ID especificado."]);
    }
}




// Función para consultar todos los registros en la tabla distribucion_entes
function consultarTodasDistribuciones()
{
    global $conexion;

    $sqlSelectAll = "SELECT id, id_ente, distribucion, monto_total, status, id_ejercicio, id_asignacion, actividad_id FROM distribucion_entes";
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

            // Obtener detalles de la actividad
            $sqlActividad = "SELECT * FROM entes_dependencias WHERE id = ?";
            $stmtActividad = $conexion->prepare($sqlActividad);
            $stmtActividad->bind_param("i", $fila['actividad_id']);
            $stmtActividad->execute();
            $resultActividad = $stmtActividad->get_result();

            if ($resultActividad->num_rows > 0) {
                $fila['actividad_informacion'] = $resultActividad->fetch_assoc();
            } else {
                $fila['actividad_informacion'] = null;
            }

            // Formatear la distribucion y obtener sus detalles
            $distribucionArray = json_decode($fila['distribucion'], true); // Asumimos que está guardado como JSON con id_distribucion y monto
            $partidasDetalles = [];

            if (!empty($distribucionArray)) {
                $idsDistribuciones = array_column($distribucionArray, 'id_distribucion'); // Extraer solo los IDs de distribuciones

                // Buscar en la tabla distribucion_presupuestaria los id_partida y id_sector
                $sqlDistribucionPresup = "SELECT id_partida, id_sector FROM distribucion_presupuestaria WHERE id IN (" . implode(",", $idsDistribuciones) . ")";
                $resultDistribucionPresup = $conexion->query($sqlDistribucionPresup);

                while ($distPresup = $resultDistribucionPresup->fetch_assoc()) {
                    $idPartida = $distPresup['id_partida'];
                    $idSector = $distPresup['id_sector'];

                    // Obtener los detalles de la partida desde partidas_presupuestarias
                    $sqlPartida = "SELECT id, partida, descripcion FROM partidas_presupuestarias WHERE id = ?";
                    $stmtPartida = $conexion->prepare($sqlPartida);
                    $stmtPartida->bind_param("i", $idPartida);
                    $stmtPartida->execute();
                    $resultPartida = $stmtPartida->get_result();

                    if ($resultPartida->num_rows > 0) {
                        $partida = $resultPartida->fetch_assoc();

                        // Buscar el monto correspondiente en el array de distribuciones original
                        $monto = null;
                        foreach ($distribucionArray as $d) {
                            if ($d['id_distribucion'] == $idPartida) {
                                $monto = $d['monto'];
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

                    // Obtener la información del sector desde pl_sectores_presupuestarios
                    $sqlSector = "SELECT * FROM pl_sectores_presupuestarios WHERE id = ?";
                    $stmtSector = $conexion->prepare($sqlSector);
                    $stmtSector->bind_param("i", $idSector);
                    $stmtSector->execute();
                    $resultSector = $stmtSector->get_result();

                    if ($resultSector->num_rows > 0) {
                        $sectorInfo = $resultSector->fetch_assoc();
                        $fila['sector_informacion'] = $sectorInfo; // Agregar la información del sector
                    } else {
                        $fila['sector_informacion'] = null;
                    }
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
    if ($accion === "insert" && isset($data["informacion"])) {
        $distribuciones = $data["informacion"];

        // Llamar a la función de inserción de distribuciones
        echo insertarDistribuciones($distribuciones);

        // Actualizar datos
    } elseif ($accion === "update" && isset($data["id"]) && isset($data["id_ente"]) && isset($data["actividad_id"]) && isset($data["distribuciones"]) && isset($data["id_ejercicio"])) {
        $id = $data["id"];
        $id_ente = $data["id_ente"];
        $actividad_id = $data["actividad_id"];
        $distribuciones = $data["distribuciones"]; // Asumimos que 'distribuciones' es un array de arrays con 'id_distribucion' y 'monto'
        $id_ejercicio = $data["id_ejercicio"];

        // Llamar a la función de actualización de distribuciones
        echo actualizarDistribucionEntes($id, $id_ente, $actividad_id, $distribuciones, $id_ejercicio);

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
    } elseif ($accion === "aprobar_rechazar" && isset($data["id_asignacion"]) && isset($data["status"])) {
        $id_asignacion = $data["id_asignacion"];
        $status = $data["status"];
        $comentario = isset($data["comentario"]) ? $data["comentario"] : ""; // Comentario opcional
        echo actualizarEstadoDistribucionPorAsignacion($id_asignacion, $status, $comentario);

        // Acción no válida o faltan datos
    } else {
        echo json_encode(['error' => "Acción no válida o faltan datos"]);
    }
} else {
    echo json_encode(['error' => "No se recibió ninguna acción"]);
}


?>