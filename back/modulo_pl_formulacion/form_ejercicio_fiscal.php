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
                $sqlSum = "SELECT id_partida, monto_inicial, monto_actual FROM distribucion_presupuestaria WHERE id_ejercicio = ?";
                $stmtSum = $conexion->prepare($sqlSum);
                $stmtSum->bind_param("i", $id_ejercicio);
                $stmtSum->execute();
                $resultSum = $stmtSum->get_result();

                $totalMontoInicial = 0;
                $partidasArray = [];

                if ($resultSum->num_rows > 0) {
                    // Recorrer los registros de distribucion_presupuestaria
                    while ($sumRow = $resultSum->fetch_assoc()) {
                        $totalMontoInicial += $sumRow['monto_inicial'];

                        // Consultar la tabla partidas_presupuestarias para obtener la partida asociada
                        $sqlPartida = "SELECT id, partida FROM partidas_presupuestarias WHERE id = ?";
                        $stmtPartida = $conexion->prepare($sqlPartida);
                        $stmtPartida->bind_param("i", $sumRow['id_partida']);
                        $stmtPartida->execute();
                        $resultPartida = $stmtPartida->get_result();

                        if ($resultPartida->num_rows > 0) {
                            $partidaRow = $resultPartida->fetch_assoc();
                            $partidaNombre = $partidaRow['partida'];
                            $partidaId = $partidaRow['id'];

                            // Crear array con las 3 propiedades: partida, monto_inicial, monto_actual
                            $partidasArray[] = [
                                'id' => $partidaId,
                                'partida' => $partidaNombre,
                                'monto_inicial' => $sumRow['monto_inicial'],
                                'monto_actual' => $sumRow['monto_actual']
                            ];
                        }

                        $stmtPartida->close();
                    }
                }

                // Calcular el restante
                $restante = $situado - $totalMontoInicial;

                // Añadir el restante, distribuido y las partidas al array del ejercicio
                $row['restante'] = $restante;
                $row['distribuido'] = $totalMontoInicial;
                $row['partidas'] = $partidasArray;

                // Añadir al array final de ejercicios
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
            $sqlDistribucion = "SELECT id_partida, monto_inicial, monto_actual 
                                FROM distribucion_presupuestaria 
                                WHERE id_ejercicio = ?";
            $stmtDistribucion = $conexion->prepare($sqlDistribucion);
            $stmtDistribucion->bind_param("i", $id);
            $stmtDistribucion->execute();
            $resultDistribucion = $stmtDistribucion->get_result();

            $totalMontoInicial = 0;
            $partidasArray = [];

            if ($resultDistribucion->num_rows > 0) {
                while ($rowDistribucion = $resultDistribucion->fetch_assoc()) {
                    $totalMontoInicial += $rowDistribucion['monto_inicial'];

                    // Obtener el valor de partida de la tabla partidas_presupuestarias
                    $sqlPartida = "SELECT id, partida FROM partidas_presupuestarias WHERE id = ?";
                    $stmtPartida = $conexion->prepare($sqlPartida);
                    $stmtPartida->bind_param("i", $rowDistribucion['id_partida']);
                    $stmtPartida->execute();
                    $resultPartida = $stmtPartida->get_result();

                    if ($resultPartida->num_rows > 0) {
                        $partidaRow = $resultPartida->fetch_assoc();
                        $partidaNombre = $partidaRow['partida'];
                        $partidaId = $partidaRow['id'];

                        // Crear array con las 3 propiedades: partida, monto_inicial, monto_actual
                        $partidasArray[] = [
                            'id' => $partidaId,
                            'partida' => $partidaNombre,
                            'monto_inicial' => $rowDistribucion['monto_inicial'],
                            'monto_actual' => $rowDistribucion['monto_actual']
                        ];
                    }

                    $stmtPartida->close();
                }
            }

            // Calcular el restante
            $restante = $ejercicio['situado'] - $totalMontoInicial;

            // Añadir el restante, distribuido y las partidas al array de respuesta
            $ejercicio['restante'] = $restante;
            $ejercicio['distribuido'] = $totalMontoInicial;
            $ejercicio['partidas'] = $partidasArray;

            return json_encode(["success" => $ejercicio]);
        } else {
            return json_encode(["error" => "No se encontró un registro con el ID proporcionado."]);
        }
    } catch (Exception $e) {
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
    } else {
        echo json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    echo json_encode(['error' => "No se especificó ninguna acción"]);
}
