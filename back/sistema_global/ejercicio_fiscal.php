<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Definir divisor como una variable
$divisor = 12;

// Función para guardar en la tabla ejercicio_fiscal en ambas bases de datos
function guardarEjercicioFiscal($ano, $situado, $divisor)
{
    global $conexion, $remote_db;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($ano) || empty($situado)) {
            throw new Exception("Faltaron uno o más valores (ano, situado)");
        }

        // Preparar la consulta SQL
        $sql = "INSERT INTO ejercicio_fiscal (ano, situado, divisor) VALUES (?, ?, ?)";

        // Insertar en la base de datos local
        $stmt_local = $conexion->prepare($sql);
        $stmt_local->bind_param("sss", $ano, $situado, $divisor);
        $stmt_local->execute();

        // Insertar en la base de datos remota
        $stmt_remote = $remote_db->prepare($sql);
        $stmt_remote->bind_param("sss", $ano, $situado, $divisor);
        $stmt_remote->execute();

        // Verificar si ambas inserciones fueron exitosas
        if ($stmt_local->affected_rows > 0 && $stmt_remote->affected_rows > 0) {
            return json_encode(["success" => "Datos de ejercicio fiscal guardados correctamente en ambas bases de datos"]);
        } else {
            throw new Exception("No se pudo guardar los datos de ejercicio fiscal en una o ambas bases de datos");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Función para actualizar los datos en la tabla ejercicio_fiscal
function actualizarEjercicioFiscal($id, $ano, $situado, $divisor, $status)
{
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($id) || empty($ano) || empty($situado) || $status === null) {
            throw new Exception("Faltaron uno o más valores (id, ano, situado, status)");
        }

        // Verificar el status actual del ejercicio fiscal
        $sql = "SELECT status FROM ejercicio_fiscal WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $registro = $result->fetch_assoc();

        if ($registro['status'] == 0) {
            throw new Exception("El ejercicio fiscal está cerrado y no se puede actualizar.");
        }

        // Actualizar los datos en la tabla, incluyendo el campo status
        $sql = "UPDATE ejercicio_fiscal SET ano = ?, situado = ?, divisor = ?, status = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssii", $ano, $situado, $divisor, $status, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Datos de ejercicio fiscal actualizados correctamente"]);
        } else {
            throw new Exception("No se pudo actualizar los datos de ejercicio fiscal");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar un registro en la tabla ejercicio_fiscal
function eliminarEjercicioFiscal($id)
{
    global $conexion;
    $user_id = $_SESSION['u_id']; // Obtener el user_id de la sesión actual

    try {
        if (empty($id)) {
            throw new Exception("Debe proporcionar un ID para eliminar");
        }

        // Verificar el status del ejercicio fiscal
        $sql = "SELECT status FROM ejercicio_fiscal WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $registro = $result->fetch_assoc();

        if ($registro['status'] == 0) {
            throw new Exception("El ejercicio fiscal está cerrado y no se puede eliminar.");
        }

        // Eliminar el registro de la tabla
        $sqlEliminar = "DELETE FROM ejercicio_fiscal WHERE id = ?";
        $stmtEliminar = $conexion->prepare($sqlEliminar);
        $stmtEliminar->bind_param("i", $id);
        $stmtEliminar->execute();

        if ($stmtEliminar->affected_rows > 0) {
            // Insertar un registro en audit_logs después de la eliminación
            $sqlAudit = "INSERT INTO audit_logs (action_type, table_name, situation, affected_rows, user_id, timestamp) 
                         VALUES (?, ?, ?, ?, ?, NOW())";
            $stmtAudit = $conexion->prepare($sqlAudit);
            $action_type = 'DELETE';
            $table_name = 'ejercicio_fiscal';
            $situation = "id=$id";
            $affected_rows = $stmtEliminar->affected_rows;
            $stmtAudit->bind_param("sssii", $action_type, $table_name, $situation, $affected_rows, $user_id);
            $stmtAudit->execute();

            return json_encode(["success" => "Datos de ejercicio fiscal eliminados correctamente"]);
        } else {
            throw new Exception("No se pudo eliminar el registro de ejercicio fiscal");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


function obtenerTodosEjerciciosFiscales()
{
    global $conexion;

    try {
        $sql = "SELECT id, ano, situado, divisor, status FROM ejercicio_fiscal";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $ejercicios = [];

            while ($row = $result->fetch_assoc()) {
                $id_ejercicio = $row['id'];
                $situado = $row['situado'];

                // Calcular la sumatoria de los montos iniciales en distribucion_presupuestaria
                $sqlSum = "SELECT id, id_partida, monto_inicial, monto_actual, id_sector, id_programa, id_proyecto, id_actividad FROM distribucion_presupuestaria WHERE id_ejercicio=$id_ejercicio";
                $stmtSum = $conexion->prepare($sqlSum);
                if (!$stmtSum) {
                    throw new Exception("Error en la preparación de la consulta SQL para distribucion_presupuestaria: " . $conexion->error);
                }
                $stmtSum->execute();
                $resultSum = $stmtSum->get_result();

                $totalMontoInicial = 0;
                $distribucionPartidas = [];

                if ($resultSum->num_rows > 0) {
                    while ($sumRow = $resultSum->fetch_assoc()) {
                        $montoInicial = isset($sumRow['monto_inicial']) ? (float) $sumRow['monto_inicial'] : 0;
                        $totalMontoInicial += $montoInicial;

                        // Consultar partidas_presupuestarias
                        $sqlPartida = "SELECT id, partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                        $stmtPartida = $conexion->prepare($sqlPartida);
                        if (!$stmtPartida) {
                            throw new Exception("Error en la preparación de la consulta SQL para partidas_presupuestarias: " . $conexion->error);
                        }
                        $stmtPartida->bind_param("i", $sumRow['id_partida']);
                        $stmtPartida->execute();
                        $resultPartida = $stmtPartida->get_result();

                        if ($resultPartida->num_rows > 0) {
                            $partidaRow = $resultPartida->fetch_assoc();

                            // Consultar pl_sectores
                            $sqlSector = "SELECT * FROM pl_sectores WHERE id = ?";
                            $stmtSector = $conexion->prepare($sqlSector);
                            if (!$stmtSector) {
                                throw new Exception("Error en la preparación de la consulta SQL para pl_sectores: " . $conexion->error);
                            }
                            $stmtSector->bind_param("i", $sumRow['id_sector']);
                            $stmtSector->execute();
                            $resultSector = $stmtSector->get_result();
                            $sectorInformacion = $resultSector->num_rows > 0 ? $resultSector->fetch_assoc() : null;
                            $stmtSector->close();

                            // Consultar pl_programas
                            $sqlPrograma = "SELECT * FROM pl_programas WHERE id = ?";
                            $stmtPrograma = $conexion->prepare($sqlPrograma);
                            if (!$stmtPrograma) {
                                throw new Exception("Error en la preparación de la consulta SQL para pl_programas: " . $conexion->error);
                            }
                            $stmtPrograma->bind_param("i", $sumRow['id_programa']);
                            $stmtPrograma->execute();
                            $resultPrograma = $stmtPrograma->get_result();
                            $programaInformacion = $resultPrograma->num_rows > 0 ? $resultPrograma->fetch_assoc() : null;
                            $stmtPrograma->close();

                            // Consultar pl_proyectos solo si id_proyecto es diferente de 0
                            $proyectoInformacion = 0;
                            if ($sumRow['id_proyecto'] != 0) {
                                $sqlProyecto = "SELECT * FROM pl_proyectos WHERE id = ?";
                                $stmtProyecto = $conexion->prepare($sqlProyecto);
                                if (!$stmtProyecto) {
                                    throw new Exception("Error en la preparación de la consulta SQL para pl_proyectos: " . $conexion->error);
                                }
                                $stmtProyecto->bind_param("i", $sumRow['id_proyecto']);
                                $stmtProyecto->execute();
                                $resultProyecto = $stmtProyecto->get_result();
                                $proyectoInformacion = $resultProyecto->num_rows > 0 ? $resultProyecto->fetch_assoc() : 0;
                                $stmtProyecto->close();
                            }

                            // Añadir la partida a distribucionPartidas
                            $distribucionPartidas[] = [
                                'id' => $sumRow['id'],
                                'id_partida' => $partidaRow['id'],
                                'partida' => $partidaRow['partida'],
                                'nombre' => $partidaRow['nombre'],
                                'descripcion' => $partidaRow['descripcion'],
                                'monto_inicial' => $sumRow['monto_inicial'],
                                'monto_actual' => $sumRow['monto_actual'],
                                'sector_informacion' => $sectorInformacion,
                                'programa_informacion' => $programaInformacion,
                                'proyecto_informacion' => $proyectoInformacion,
                                'id_actividad' => $sumRow['id_actividad'],
                            ];
                        }
                        $stmtPartida->close();
                    }
                }
                $stmtSum->close();

                // Consulta total de asignacion en asignacion_ente
                $sqlAsignacion = "SELECT SUM(monto_total) AS total_asignacion FROM asignacion_ente WHERE id_ejercicio = ?";
                $stmtAsignacion = $conexion->prepare($sqlAsignacion);
                if (!$stmtAsignacion) {
                    throw new Exception("Error en la preparación de la consulta SQL para asignacion_ente: " . $conexion->error);
                }
                $stmtAsignacion->bind_param("i", $id_ejercicio);
                $stmtAsignacion->execute();
                $resultAsignacion = $stmtAsignacion->get_result();
                $totalAsignacion = $resultAsignacion->num_rows > 0 ? $resultAsignacion->fetch_assoc()['total_asignacion'] ?? 0 : 0;
                $stmtAsignacion->close();

                // Calcular valores restantes y añadir al array
                $restante = $situado - $totalMontoInicial;
                $restanteSituadoAsignacion = $situado - $totalAsignacion;

                $row['restante'] = $restante;
                $row['distribuido'] = $totalMontoInicial;
                $row['restante_situado_asignacion'] = $restanteSituadoAsignacion;
                $row['distribucion_partidas'] = $distribucionPartidas;

                $ejercicios[] = $row;
            }
            return json_encode(["success" => $ejercicios]);
        } else {
            return json_encode(["success" => []]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}
function obtenerDistribucionPositiva($id_ejercicio)
{
    global $conexion;

    try {
        if (empty($id_ejercicio)) {
            return json_encode(['error' => "Debe proporcionar un ID para la consulta"]);
        }

        // Consulta para obtener los registros de distribucion_presupuestaria y sumar los montos iniciales
        $sqlDistribucion = "SELECT id, id_partida, monto_inicial, monto_actual, id_sector, id_programa, id_proyecto, id_actividad 
                                FROM distribucion_presupuestaria 
                                WHERE id_ejercicio = ?";
        $stmtDistribucion = $conexion->prepare($sqlDistribucion);
        if (!$stmtDistribucion) {
            throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
        }
        $stmtDistribucion->bind_param("i", $id_ejercicio);
        $stmtDistribucion->execute();
        $resultDistribucion = $stmtDistribucion->get_result();

        $totalMontoInicial = 0;
        $distribucionPartidas = [];

        if ($resultDistribucion->num_rows > 0) {
            while ($rowDistribucion = $resultDistribucion->fetch_assoc()) {
                $montoInicial = isset($rowDistribucion['monto_inicial']) ? (float) $rowDistribucion['monto_inicial'] : 0;
                $totalMontoInicial += $montoInicial;

                // Obtener el valor de partida de la tabla partidas_presupuestarias
                $sqlPartida = "SELECT id, partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($sqlPartida);
                if (!$stmtPartida) {
                    throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
                }
                $stmtPartida->bind_param("i", $rowDistribucion['id_partida']);
                $stmtPartida->execute();
                $resultPartida = $stmtPartida->get_result();
                $stmtPartida->close();

                if ($resultPartida->num_rows > 0) {
                    $partidaRow = $resultPartida->fetch_assoc();

                    // Consultas para sector, programa, y proyecto
                    $sqlSector = "SELECT * FROM pl_sectores WHERE id = ?";
                    $stmtSector = $conexion->prepare($sqlSector);
                    if (!$stmtSector) {
                        throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
                    }
                    $stmtSector->bind_param("i", $rowDistribucion['id_sector']);
                    $stmtSector->execute();
                    $resultSector = $stmtSector->get_result();
                    $sectorInformacion = $resultSector->num_rows > 0 ? $resultSector->fetch_assoc() : null;
                    $stmtSector->close();

                    $sqlPrograma = "SELECT * FROM pl_programas WHERE id = ?";
                    $stmtPrograma = $conexion->prepare($sqlPrograma);
                    if (!$stmtPrograma) {
                        throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
                    }
                    $stmtPrograma->bind_param("i", $rowDistribucion['id_programa']);
                    $stmtPrograma->execute();
                    $resultPrograma = $stmtPrograma->get_result();
                    $programaInformacion = $resultPrograma->num_rows > 0 ? $resultPrograma->fetch_assoc() : null;
                    $stmtPrograma->close();

                    $proyectoInformacion = 0;
                    if ($rowDistribucion['id_proyecto'] != 0) {
                        $sqlProyecto = "SELECT * FROM pl_proyectos WHERE id = ?";
                        $stmtProyecto = $conexion->prepare($sqlProyecto);
                        if (!$stmtProyecto) {
                            throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
                        }
                        $stmtProyecto->bind_param("i", $rowDistribucion['id_proyecto']);
                        $stmtProyecto->execute();
                        $resultProyecto = $stmtProyecto->get_result();
                        $proyectoInformacion = $resultProyecto->num_rows > 0 ? $resultProyecto->fetch_assoc() : 0;
                        $stmtProyecto->close();
                    }

                    $distribucionPartidas[] = [
                        'id' => $rowDistribucion['id'],
                        'id_partida' => $partidaRow['id'],
                        'partida' => $partidaRow['partida'],
                        'nombre' => $partidaRow['nombre'],
                        'descripcion' => $partidaRow['descripcion'],
                        'monto_inicial' => $rowDistribucion['monto_inicial'],
                        'monto_actual' => $rowDistribucion['monto_actual'],
                        'sector_informacion' => $sectorInformacion,
                        'programa_informacion' => $programaInformacion,
                        'proyecto_informacion' => $proyectoInformacion,
                        'id_actividad' => $rowDistribucion['id_actividad'],
                    ];
                }
            }
            $stmtDistribucion->close();


            return json_encode(["success" => $distribucionPartidas]);
        } else {
            return json_encode(["error" => "No se encontró un registro con el ID proporcionado."]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}







function obtenerEjercicioFiscalPorId($id)
{
    global $conexion;

    try {
        if (empty($id)) {
            return json_encode(['error' => "Debe proporcionar un ID para la consulta"]);
        }

        // Consulta el ejercicio fiscal por ID
        $sql = "SELECT id, ano, situado, divisor, status FROM ejercicio_fiscal WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $ejercicio = $result->fetch_assoc();

            // Consulta para obtener los registros de distribucion_presupuestaria y sumar los montos iniciales
            $sqlDistribucion = "SELECT id, id_partida, monto_inicial, monto_actual, id_sector, id_programa, id_proyecto, id_actividad
                                FROM distribucion_presupuestaria 
                                WHERE id_ejercicio = ?";
            $stmtDistribucion = $conexion->prepare($sqlDistribucion);
            if (!$stmtDistribucion) {
                throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
            }
            $stmtDistribucion->bind_param("i", $id);
            $stmtDistribucion->execute();
            $resultDistribucion = $stmtDistribucion->get_result();

            $totalMontoInicial = 0;
            $distribucionPartidas = [];

            if ($resultDistribucion->num_rows > 0) {
                while ($rowDistribucion = $resultDistribucion->fetch_assoc()) {
                    $montoInicial = isset($rowDistribucion['monto_inicial']) ? (float) $rowDistribucion['monto_inicial'] : 0;
                    $totalMontoInicial += $montoInicial;

                    // Obtener el valor de partida de la tabla partidas_presupuestarias
                    $sqlPartida = "SELECT id, partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                    $stmtPartida = $conexion->prepare($sqlPartida);
                    if (!$stmtPartida) {
                        throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
                    }
                    $stmtPartida->bind_param("i", $rowDistribucion['id_partida']);
                    $stmtPartida->execute();
                    $resultPartida = $stmtPartida->get_result();
                    $stmtPartida->close();

                    if ($resultPartida->num_rows > 0) {
                        $partidaRow = $resultPartida->fetch_assoc();

                        // Consultas para sector, programa, y proyecto
                        $sqlSector = "SELECT * FROM pl_sectores WHERE id = ?";
                        $stmtSector = $conexion->prepare($sqlSector);
                        if (!$stmtSector) {
                            throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
                        }
                        $stmtSector->bind_param("i", $rowDistribucion['id_sector']);
                        $stmtSector->execute();
                        $resultSector = $stmtSector->get_result();
                        $sectorInformacion = $resultSector->num_rows > 0 ? $resultSector->fetch_assoc() : null;
                        $stmtSector->close();

                        $sqlPrograma = "SELECT * FROM pl_programas WHERE id = ?";
                        $stmtPrograma = $conexion->prepare($sqlPrograma);
                        if (!$stmtPrograma) {
                            throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
                        }
                        $stmtPrograma->bind_param("i", $rowDistribucion['id_programa']);
                        $stmtPrograma->execute();
                        $resultPrograma = $stmtPrograma->get_result();
                        $programaInformacion = $resultPrograma->num_rows > 0 ? $resultPrograma->fetch_assoc() : null;
                        $stmtPrograma->close();

                        $proyectoInformacion = 0;
                        if ($rowDistribucion['id_proyecto'] != 0) {
                            $sqlProyecto = "SELECT * FROM pl_proyectos WHERE id = ?";
                            $stmtProyecto = $conexion->prepare($sqlProyecto);
                            if (!$stmtProyecto) {
                                throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
                            }
                            $stmtProyecto->bind_param("i", $rowDistribucion['id_proyecto']);
                            $stmtProyecto->execute();
                            $resultProyecto = $stmtProyecto->get_result();
                            $proyectoInformacion = $resultProyecto->num_rows > 0 ? $resultProyecto->fetch_assoc() : 0;
                            $stmtProyecto->close();
                        }

                        $distribucionPartidas[] = [
                            'id' => $rowDistribucion['id'],
                            'id_partida' => $partidaRow['id'],
                            'partida' => $partidaRow['partida'],
                            'nombre' => $partidaRow['nombre'],
                            'descripcion' => $partidaRow['descripcion'],
                            'monto_inicial' => $rowDistribucion['monto_inicial'],
                            'monto_actual' => $rowDistribucion['monto_actual'],
                            'sector_informacion' => $sectorInformacion,
                            'programa_informacion' => $programaInformacion,
                            'proyecto_informacion' => $proyectoInformacion,
                            'id_actividad' => $rowDistribucion['id_actividad'],
                        ];
                    }
                }
            }
            $stmtDistribucion->close();

            // Consulta para obtener los registros en asignacion_ente con el mismo id_ejercicio y sumar monto_total
            $sqlAsignacion = "SELECT monto_total FROM asignacion_ente WHERE id_ejercicio = ?";
            $stmtAsignacion = $conexion->prepare($sqlAsignacion);
            if (!$stmtAsignacion) {
                throw new Exception("Error en la preparación de la consulta SQL: " . $conexion->error);
            }
            $stmtAsignacion->bind_param("i", $id);
            $stmtAsignacion->execute();
            $resultAsignacion = $stmtAsignacion->get_result();

            $totalMontoAsignacion = 0;
            while ($rowAsignacion = $resultAsignacion->fetch_assoc()) {
                $totalMontoAsignacion += $rowAsignacion['monto_total'];
            }
            $stmtAsignacion->close();

            // Calcular el restante y el restante_situado_asignacion
            $restante = $ejercicio['situado'] - $totalMontoInicial;
            $restanteSituadoAsignacion = $ejercicio['situado'] - $totalMontoAsignacion;

            // Añadir el restante, restante_situado_asignacion, distribuido y distribucion_partidas al array de respuesta
            $ejercicio['restante'] = $restante;
            $ejercicio['restante_situado_asignacion'] = $restanteSituadoAsignacion;
            $ejercicio['distribuido'] = $totalMontoInicial;
            $ejercicio['distribucion_partidas'] = $distribucionPartidas;

            return json_encode(["success" => $ejercicio]);
        } else {
            return json_encode(["error" => "No se encontró un registro con el ID proporcionado."]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

function obtenerDistribucionEntesPorEjercicio($id_ejercicio)
{
    global $remote_db;

    $conexion = $remote_db;
    $sql = "SELECT 
                de.*, 
                e.* 
            FROM distribucion_entes de
            JOIN entes e ON de.id_ente = e.id
            WHERE de.id_ejercicio = ?";


    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_ejercicio);
    $stmt->execute();
    $resultado = $stmt->get_result();




    if ($resultado->num_rows === 0) {
        return json_encode(["error" => "No se encontraron registros en distribucion_entes para el id_ejercicio proporcionado."]);
    }

    $informacion = $resultado->fetch_all(MYSQLI_ASSOC);
    foreach ($informacion as &$registro) {
        $decoded = json_decode($registro['distribucion'], true);

        $registro['distribucion'] = $decoded;

    }

    return json_encode(['success' => $informacion]);
}


function obtenerTodosLosEntes()
{
    global $remote_db;

    $conexion = $remote_db;
    $sql = "SELECT 
                e.*, 
                s.*, 
                p.*, 
                a.*, 
                pr.* 
            FROM entes e
            LEFT JOIN pl_sectores s ON e.id_sector = s.id
            LEFT JOIN pl_programas p ON e.id_programa = p.id
            LEFT JOIN pl_actividades a ON e.id_actividad = a.id
            LEFT JOIN pl_proyectos pr ON e.id_proyecto = pr.id";

    $resultado = $conexion->query($sql);

    // Si hay error en la consulta, mostrarlo
    if (!$resultado) {
        return json_encode(["error" => "Error en la consulta: " . $conexion->error]);
    }

    if ($resultado->num_rows === 0) {
        return json_encode(["error" => "No se encontraron registros en la tabla entes."]);
    }

    return json_encode($resultado->fetch_all(MYSQLI_ASSOC));
}







// Función para modificar las partidas en distribucion_presupuestaria
function modificarPartida($partida1, $partida2, $monto)
{
    global $conexion;

    try {
        // Iniciar transacción
        $conexion->begin_transaction();

        // Verificar si partida1 existe y obtener datos
        $sql1 = "SELECT id, monto_actual, id_ejercicio FROM distribucion_presupuestaria WHERE id = ?";
        $stmt1 = $conexion->prepare($sql1);
        $stmt1->bind_param("i", $partida1);
        $stmt1->execute();
        $result1 = $stmt1->get_result();

        if ($result1->num_rows === 0) {
            throw new Exception("La partida a designar no existe.");
        }

        $registro1 = $result1->fetch_assoc();
        $montoActual1 = $registro1['monto_actual'];
        $idEjercicio = $registro1['id_ejercicio'];

        // Verificar que el monto a restar no sea mayor que el monto actual de partida1
        if ($monto > $montoActual1) {
            throw new Exception("El monto a transferir excede el monto actual de la partida a designar.");
        }

        // Verificar si partida2 existe
        $sql2 = "SELECT id, monto_actual FROM distribucion_presupuestaria WHERE id = ?";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bind_param("i", $partida2);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows === 0) {
            // Insertar partida2 si no existe
            $sqlInsert = "INSERT INTO distribucion_presupuestaria (id_partida, monto_inicial, id_ejercicio, monto_actual, status) VALUES (?, ?, ?, ?, 1)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bind_param("isis", $partida2, $monto, $idEjercicio, $monto);
            $stmtInsert->execute();
        } else {
            // Actualizar monto_actual en partida2
            $registro2 = $result2->fetch_assoc();
            $montoActual2 = $registro2['monto_actual'] + $monto;

            $sqlUpdate2 = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id = ?";
            $stmtUpdate2 = $conexion->prepare($sqlUpdate2);
            $stmtUpdate2->bind_param("di", $montoActual2, $partida2);
            $stmtUpdate2->execute();
        }

        // Restar el monto en partida1
        $nuevoMonto1 = $montoActual1 - $monto;
        $sqlUpdate1 = "UPDATE distribucion_presupuestaria SET monto_actual = ? WHERE id = ?";
        $stmtUpdate1 = $conexion->prepare($sqlUpdate1);
        $stmtUpdate1->bind_param("di", $nuevoMonto1, $partida1);
        $stmtUpdate1->execute();

        // Confirmar la transacción
        $conexion->commit();

        return json_encode(["success" => "La modificación de partidas se realizó correctamente."]);
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}



function consultarDistribucionPresupuestaria($id_ejercicio)
{
    global $conexion;

    try {
        // Consulta SQL para obtener la información con los datos relacionados
        $sql = "SELECT 
                    dp.*, 
                    se.sector AS sector_denominacion, 
                    prg.programa AS programa_denominacion, 
                    pr.denominacion AS proyecto_denominacion
                FROM distribucion_presupuestaria dp
                LEFT JOIN pl_sectores se ON dp.id_sector = se.id
                LEFT JOIN pl_programas prg ON dp.id_programa = prg.id
                LEFT JOIN pl_proyectos pr ON dp.id_proyecto = pr.id
                WHERE dp.monto_actual > 0 AND dp.id_ejercicio = ?";

        $stmt = $conexion->prepare($sql);

        // Validar si se preparó correctamente
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }

        // Asociar parámetros
        $stmt->bind_param("i", $id_ejercicio);

        // Ejecutar la consulta
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Verificar si hay resultados
        if ($resultado->num_rows > 0) {
            // Convertir los resultados en un array asociativo
            $datos = [];
            while ($fila = $resultado->fetch_assoc()) {
                $datos[] = $fila;
            }
            return json_encode(["success" => $datos]);
        } else {
            return json_encode(["success" => []]);
        }
    } catch (Exception $e) {
        // Manejo de errores
        registrarError($e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}




// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    if ($accion === "insert") {
        if (empty($data["ano"]) || empty($data["situado"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (ano, situado)"]);
        } else {
            echo guardarEjercicioFiscal($data["ano"], $data["situado"], $divisor);
        }
    } elseif ($accion === "update") {
        if (empty($data["id"]) || empty($data["ano"]) || empty($data["situado"]) || !isset($data["status"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (id, ano, situado, status)"]);
        } else {
            echo actualizarEjercicioFiscal($data["id"], $data["ano"], $data["situado"], $divisor, $data["status"]);
        }
    } elseif ($accion === "delete") {
        if (empty($data["id"])) {
            echo json_encode(['error' => "Debe proporcionar un ID para eliminar"]);
        } else {
            echo eliminarEjercicioFiscal($data["id"]);
        }
    } elseif ($accion === "obtener_todos") {
        echo obtenerTodosEjerciciosFiscales();
    } elseif ($accion === "obtener_distribuciones_entes") {
        if (empty($data["id_ejercicio"])) {
            echo json_encode(['error' => "Debe proporcionar un ejercicio fiscal para la consulta"]);
        } else {
            echo obtenerDistribucionEntesPorEjercicio($data["id_ejercicio"]);
        }
    } elseif ($accion === "obtener_entes") {
        echo obtenerTodosLosEntes();
    } elseif ($accion === "obtener_positiva") {
        echo obtenerDistribucionPositiva();
    } elseif ($accion === "obtener_por_id") {
        if (empty($data["id"])) {
            echo json_encode(['error' => "Debe proporcionar un ID para la consulta"]);
        } else {
            echo obtenerEjercicioFiscalPorId($data["id"]);
        }
    } elseif ($accion === "obtener_distribucion_positiva") {
        if (empty($data["id_ejercicio"])) {
            echo json_encode(['error' => "Debe proporcionar un ejercicio fiscal para la consulta"]);
        } else {
            echo consultarDistribucionPresupuestaria($data["id_ejercicio"]);
        }
    } elseif (isset($data["accion"]) && $data["accion"] === "modificar_partida") {
        if (empty($data["partida1"]) || empty($data["partida2"]) || empty($data["monto"])) {
            echo json_encode(['error' => "Faltaron uno o más valores (partida1, partida2, monto)"]);
        } else {
            echo modificarPartida($data["partida1"], $data["partida2"], $data["monto"]);
        }
    } else {
        echo json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    echo json_encode(['error' => "No se especificó ninguna acción"]);
}

