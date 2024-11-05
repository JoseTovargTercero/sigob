<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Definir divisor como una variable
$divisor = 12;

// Función para guardar en la tabla ejercicio_fiscal
function guardarEjercicioFiscal($ano, $situado, $divisor)
{
    global $conexion;

    try {
        // Validar que todos los campos no estén vacíos
        if (empty($ano) || empty($situado)) {
            throw new Exception("Faltaron uno o más valores (ano, situado)");
        }

        // Insertar los datos en la tabla
        $sql = "INSERT INTO ejercicio_fiscal (ano, situado, divisor) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $ano, $situado, $divisor);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Datos de ejercicio fiscal guardados correctamente"]);
        } else {
            throw new Exception("No se pudo guardar los datos de ejercicio fiscal");
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
        $sql = "DELETE FROM ejercicio_fiscal WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
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

            // Recorrer todos los ejercicios fiscales
            while ($row = $result->fetch_assoc()) {
                $id_ejercicio = $row['id'];
                $situado = $row['situado'];

                // Calcular la sumatoria de los montos iniciales en distribucion_presupuestaria para este id_ejercicio
                $sqlSum = "SELECT id, id_partida, monto_inicial, monto_actual, id_sector, id_programa, id_proyecto FROM distribucion_presupuestaria WHERE id_ejercicio = ?";
                $stmtSum = $conexion->prepare($sqlSum);
                $stmtSum->bind_param("i", $id_ejercicio);
                $stmtSum->execute();
                $resultSum = $stmtSum->get_result();

                $totalMontoInicial = 0;
                $distribucionPartidas = []; // Cambiado a un array para almacenar múltiples registros

                if ($resultSum->num_rows > 0) {
                    // Recorrer los registros de distribucion_presupuestaria
                    while ($sumRow = $resultSum->fetch_assoc()) {
                        $totalMontoInicial += $sumRow['monto_inicial'];

                        // Consultar la tabla partidas_presupuestarias para obtener la partida asociada
                        $sqlPartida = "SELECT id, partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                        $stmtPartida = $conexion->prepare($sqlPartida);
                        $stmtPartida->bind_param("i", $sumRow['id_partida']);
                        $stmtPartida->execute();
                        $resultPartida = $stmtPartida->get_result();

                        if ($resultPartida->num_rows > 0) {
                            $partidaRow = $resultPartida->fetch_assoc();
                            $partidaNombre = $partidaRow['nombre'];
                            $partidaDescripcion = $partidaRow['descripcion'];
                            $partidaPartida = $partidaRow['partida'];
                            $partidaId = $partidaRow['id'];

                            // Realizar la consulta en pl_sectores_presupuestarios utilizando id_sector
                            $sqlSector = "SELECT * FROM pl_sectores WHERE id = ?";
                            $stmtSector = $conexion->prepare($sqlSector);
                            $stmtSector->bind_param("i", $sumRow['id_sector']);
                            $stmtSector->execute();
                            $resultSector = $stmtSector->get_result();

                            $sectorInformacion = null;
                            if ($resultSector->num_rows > 0) {
                                $sectorInformacion = $resultSector->fetch_assoc();
                            }

                            $sqlPrograma = "SELECT * FROM pl_programas WHERE id = ?";
                            $stmtPrograma = $conexion->prepare($sqlPrograma);
                            $stmtPrograma->bind_param("i", $sumRow['id_programa']);
                            $stmtPrograma->execute();
                            $resultPrograma = $stmtPrograma->get_result();

                            $programaInformacion = null;
                            if ($resultPrograma->num_rows > 0) {
                                $programaInformacion = $resultPrograma->fetch_assoc();
                            }

                            if ($sumRow['id_proyecto'] == 0) {
                                $proyectoInformacion = 0;
                            } else {
                                $sqlProyecto = "SELECT * FROM pl_proyectos WHERE id = ?";
                                $stmtProyecto = $conexion->prepare($sqlProyecto);
                                $stmtProyecto->bind_param("i", $sumRow['id_proyecto']);
                                $stmtProyecto->execute();
                                $resultProyecto = $stmtProyecto->get_result();

                                if ($resultProyecto->num_rows > 0) {
                                    $proyectoInformacion = $resultProyecto->fetch_assoc();
                                }
                            }

                            $distribucionPartidas[] = [
                                'id' => $sumRow['id'],
                                'id_partida' => $partidaId,
                                'partida' => $partidaPartida,
                                'nombre' => $partidaNombre,
                                'descripcion' => $partidaDescripcion,
                                'monto_inicial' => $sumRow['monto_inicial'],
                                'monto_actual' => $sumRow['monto_actual'],
                                'sector_informacion' => $sectorInformacion,
                                'programa_informacion' => $programaInformacion,
                                'proyecto_informacion' => $proyectoInformacion,
                            ];

                            $stmtSector->close();
                            $stmtPrograma->close();
                        }

                        $stmtPartida->close();
                    }
                }

                // Consulta para obtener la suma de monto_total en asignacion_ente para el id_ejercicio actual
                $sqlAsignacion = "SELECT SUM(monto_total) AS total_asignacion FROM asignacion_ente WHERE id_ejercicio = ?";
                $stmtAsignacion = $conexion->prepare($sqlAsignacion);
                $stmtAsignacion->bind_param("i", $id_ejercicio);
                $stmtAsignacion->execute();
                $resultAsignacion = $stmtAsignacion->get_result();

                $totalAsignacion = 0;
                if ($resultAsignacion->num_rows > 0) {
                    $rowAsignacion = $resultAsignacion->fetch_assoc();
                    $totalAsignacion = $rowAsignacion['total_asignacion'] ?? 0;
                }

                $stmtAsignacion->close();

                // Calcular los valores restantes
                $restante = $situado - $totalMontoInicial;
                $restanteSituadoAsignacion = $situado - $totalAsignacion;

                // Añadir el restante, distribuido, restante_situado_asignacion y distribucion_partidas al array del ejercicio
                $row['restante'] = $restante;
                $row['distribuido'] = $totalMontoInicial;
                $row['restante_situado_asignacion'] = $restanteSituadoAsignacion;
                $row['distribucion_partidas'] = $distribucionPartidas;

                $ejercicios[] = $row;

                $stmtSum->close();
            }
            return json_encode(["success" => $ejercicios]);
        } else {
            return json_encode(["success" => "No se encontraron registros en ejercicio_fiscal."]);
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
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $ejercicio = $result->fetch_assoc();

            // Consulta para obtener los registros de distribucion_presupuestaria y sumar los montos iniciales
            $sqlDistribucion = "SELECT id, id_partida, monto_inicial, monto_actual, id_sector, id_programa, id_proyecto 
                                FROM distribucion_presupuestaria 
                                WHERE id_ejercicio = ?";
            $stmtDistribucion = $conexion->prepare($sqlDistribucion);
            $stmtDistribucion->bind_param("i", $id);
            $stmtDistribucion->execute();
            $resultDistribucion = $stmtDistribucion->get_result();

            $totalMontoInicial = 0;
            $distribucionPartidas = [];

            if ($resultDistribucion->num_rows > 0) {
                while ($rowDistribucion = $resultDistribucion->fetch_assoc()) {
                    $totalMontoInicial += $rowDistribucion['monto_inicial'];

                    // Obtener el valor de partida de la tabla partidas_presupuestarias
                    $sqlPartida = "SELECT id, partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                    $stmtPartida = $conexion->prepare($sqlPartida);
                    $stmtPartida->bind_param("i", $rowDistribucion['id_partida']);
                    $stmtPartida->execute();
                    $resultPartida = $stmtPartida->get_result();

                    if ($resultPartida->num_rows > 0) {
                        $partidaRow = $resultPartida->fetch_assoc();

                        // Consultas para sector, programa, y proyecto
                        $sqlSector = "SELECT * FROM pl_sectores WHERE id = ?";
                        $stmtSector = $conexion->prepare($sqlSector);
                        $stmtSector->bind_param("i", $rowDistribucion['id_sector']);
                        $stmtSector->execute();
                        $resultSector = $stmtSector->get_result();
                        $sectorInformacion = $resultSector->num_rows > 0 ? $resultSector->fetch_assoc() : null;
                        
                        $sqlPrograma = "SELECT * FROM pl_programas WHERE id = ?";
                        $stmtPrograma = $conexion->prepare($sqlPrograma);
                        $stmtPrograma->bind_param("i", $rowDistribucion['id_programa']);
                        $stmtPrograma->execute();
                        $resultPrograma = $stmtPrograma->get_result();
                        $programaInformacion = $resultPrograma->num_rows > 0 ? $resultPrograma->fetch_assoc() : null;

                        $proyectoInformacion = 0;
                        if ($rowDistribucion['id_proyecto'] != 0) {
                            $sqlProyecto = "SELECT * FROM pl_proyectos WHERE id = ?";
                            $stmtProyecto = $conexion->prepare($sqlProyecto);
                            $stmtProyecto->bind_param("i", $rowDistribucion['id_proyecto']);
                            $stmtProyecto->execute();
                            $resultProyecto = $stmtProyecto->get_result();
                            $proyectoInformacion = $resultProyecto->num_rows > 0 ? $resultProyecto->fetch_assoc() : null;
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
                        ];

                        $stmtSector->close();
                        $stmtPrograma->close();
                    }

                    $stmtPartida->close();
                }
            }

            // Consulta para obtener los registros en asignacion_ente con el mismo id_ejercicio y sumar monto_total
            $sqlAsignacion = "SELECT monto_total FROM asignacion_ente WHERE id_ejercicio = ?";
            $stmtAsignacion = $conexion->prepare($sqlAsignacion);
            $stmtAsignacion->bind_param("i", $id);
            $stmtAsignacion->execute();
            $resultAsignacion = $stmtAsignacion->get_result();

            $totalMontoAsignacion = 0;
            while ($rowAsignacion = $resultAsignacion->fetch_assoc()) {
                $totalMontoAsignacion += $rowAsignacion['monto_total'];
            }

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






// Función para modificar las partidas en distribucion_presupuestaria
function modificarPartida($partida1, $partida2, $monto) {
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
    } elseif ($accion === "obtener_por_id") {
        if (empty($data["id"])) {
            echo json_encode(['error' => "Debe proporcionar un ID para la consulta"]);
        } else {
            echo obtenerEjercicioFiscalPorId($data["id"]);
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

