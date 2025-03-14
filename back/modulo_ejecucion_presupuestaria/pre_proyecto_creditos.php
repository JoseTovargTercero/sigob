<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';

require_once '../sistema_global/notificaciones.php';
header('Content-Type: application/json');
require_once '../sistema_global/errores.php';
require_once '../modulo_ejecucion_presupuestaria/pre_compromisos.php';

// Función para gestionar la solicitud y compromisos
function gestionarCreditosAdicionales($data)
{
    global $conexion;

    try {
        if (!isset($data['accion'])) {
            return json_encode(["error" => "No se ha especificado acción."]);
        }

        $accion = $data['accion'];

        // Acción: Consultar todos los registros
        if ($accion === 'consulta') {
            return obtenerTodosLosCreditos($data);
        }

        // Acción: Consultar un registro por ID
        if ($accion === 'consulta_id') {
            return obtenerCreditoPorId($data);
        }

        // Acción: Registrar una nueva solicitud
        if ($accion === 'registrar') {
            return registrarCreditoAdicional($data);
        }

        if ($accion === 'subir_decreto') {
            return procesarCreditoAdicional($data);
        }

        // Acción: Actualizar un registro
        if ($accion === 'update') {
            return actualizarCredito($data);
        }

        // Acción: Eliminar un registro (rechazar)
        if ($accion === 'delete') {
            return eliminarCredito($data);
        }


        // Otras acciones...

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


function registrarCreditoAdicional($data)
{
    global $conexion;
    global $remote_db;

    try {
        // Validar que no haya campos vacíos
        if (!isset($data['id_ente']) || !isset($data['id_ejercicio']) || !isset($data['monto']) || !isset($data['fecha']) || !isset($data['tipo_credito']) || !isset($data['tipo_proyecto']) || !isset($data['descripcion_proyecto']) || !isset($data['distribuciones'])) {
            return json_encode(["error" => "Faltan datos obligatorios para registrar el crédito adicional."]);
        }

        // Iniciar transacción en ambas bases de datos
        $conexion->begin_transaction();
        $remote_db->begin_transaction();

        // Datos recibidos
        $id_ente = $data['id_ente'];
        $id_ejercicio = $data['id_ejercicio'];
        $monto = $data['monto'];
        $fecha = $data['fecha'];
        $tipo_credito = $data['tipo_credito'];
        $tipo_proyecto = $data['tipo_proyecto'];
        $descripcion_proyecto = $data['descripcion_proyecto'];
        $distribuciones = $data['distribuciones'];

        // Validación de montos en distribucion_entes en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            foreach ($distribuciones as $partida) {
                $id_distribucion = $partida['id_distribucion'];
                $monto_solicitado = $partida['monto'];

                // Consultar el monto de distribución desde distribucion_entes
                $sqlMontoDistribucion = "SELECT distribucion 
                                         FROM distribucion_entes 
                                         WHERE id_ente = ? AND id_ejercicio = ? AND distribucion LIKE '%\"id_distribucion\":\"$id_distribucion\"%'";
                $stmtMontoDistribucion = $db->prepare($sqlMontoDistribucion);
                $stmtMontoDistribucion->bind_param("ii", $id_ente, $id_ejercicio);
                $stmtMontoDistribucion->execute();
                $resultadoMontoDistribucion = $stmtMontoDistribucion->get_result();

                if ($resultadoMontoDistribucion->num_rows === 0) {
                    throw new Exception("El ID de distribución no se encuentra en el campo 'distribucion' de distribucion_entes");
                }

                $filaMontoDistribucion = $resultadoMontoDistribucion->fetch_assoc();
                $distribucionesData = json_decode($filaMontoDistribucion['distribucion'], true);

                // Obtener monto disponible en la distribución
                $montoDistribucion = null;
                foreach ($distribucionesData as &$distribucion) {
                    if ($distribucion['id_distribucion'] == $id_distribucion) {
                        $montoDistribucion = (float) $distribucion['monto'];
                        break;
                    }
                }

                if ($montoDistribucion === null) {
                    throw new Exception("No se encontró el monto para el ID de distribución $id_distribucion.");
                }

                // Validar si el monto disponible es suficiente
                if ($montoDistribucion < $monto_solicitado) {
                    throw new Exception("El presupuesto en distribucion_entes es insuficiente para el ID de distribución $id_distribucion.");
                }
            }
        }

        // Generar el número de orden automáticamente
        $numero_orden = generarNumeroOrden();
        $fecha = date("Y-m-d");
        $distribuciones_json = json_encode($distribuciones);

        // Insertar en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            // Insertar en credito_adicional
            $sqlCredito = "INSERT INTO credito_adicional (id_ente, id_ejercicio, monto, fecha, tipo_credito, status) 
                           VALUES (?, ?, ?, ?, ?, 0)";
            $stmtCredito = $db->prepare($sqlCredito);
            $stmtCredito->bind_param("iidsi", $id_ente, $id_ejercicio, $monto, $fecha, $tipo_credito);
            if (!$stmtCredito->execute()) {
                $conexion->rollback();
                $remote_db->rollback();
                return json_encode(["error" => "Error en credito_adicional: " . $stmtCredito->error]);
            }

            if ($stmtCredito->affected_rows === 0) {
                $conexion->rollback();
                $remote_db->rollback();
                return json_encode(["error" => "No se pudo registrar el crédito adicional."]);
            }

            $id_credito = $db->insert_id; // Obtén el ID del crédito insertado
            $decreto = "";

            // Insertar en proyecto_credito
            $sqlProyecto = "INSERT INTO proyecto_credito (id_credito, tipo_proyecto, descripcion_proyecto, distribuciones, decreto, status) 
                            VALUES (?, ?, ?, ?, ?, 0)";
            $stmtProyecto = $db->prepare($sqlProyecto);
            $distribuciones_json = json_encode($distribuciones);
            $stmtProyecto->bind_param("issss", $id_credito, $tipo_proyecto, $descripcion_proyecto, $distribuciones_json, $decreto);
            if (!$stmtProyecto->execute()) {
                $conexion->rollback();
                $remote_db->rollback();
                return json_encode(["error" => "Error en proyecto_credito: " . $stmtProyecto->error]);
            }

            if ($stmtProyecto->affected_rows === 0) {
                $conexion->rollback();
                $remote_db->rollback();
                return json_encode(["error" => "No se pudo registrar el proyecto de crédito."]);
            }
        }

        // Confirmar la transacción en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();
        return json_encode(["success" => "El crédito adicional y su proyecto se registraron correctamente."]);
    } catch (Exception $e) {
        // Revertir transacciones en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        registrarError($e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}






function obtenerCreditoPorId($data)
{
    global $conexion;
    $id_credito = $data['id_credito'];
    if ($data['id_credito'] == "") {
        return json_encode(["error" => "No se ha enviado el credito a consultar"]);
    }

    $sql = "SELECT 
                ca.*, 
                pc.*, 
                e.ente_nombre AS nombre_ente, 
                s.denominacion AS nombre_sector, 
                p.denominacion AS nombre_programa, 
                pr.denominacion AS nombre_proyecto
            FROM credito_adicional ca
            JOIN proyecto_credito pc ON ca.id = pc.id_credito
            JOIN entes e ON ca.id_ente = e.id
            LEFT JOIN pl_sectores s ON e.sector = s.id
            LEFT JOIN pl_programas p ON e.programa = p.id
            LEFT JOIN pl_proyectos pr ON e.proyecto = pr.id
            WHERE ca.id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_credito);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $informacion = $resultado->fetch_assoc();

    $sqlCompromiso = "SELECT id as id_compromiso, correlativo FROM compromisos WHERE id_registro = ? AND tabla_registro = 'proyecto_credito'";
    $stmtCompromiso = $conexion->prepare($sqlCompromiso);
    $stmtCompromiso->bind_param("i", $informacion['id_credito']);
    $stmtCompromiso->execute();
    $resultadoCompromiso = $stmtCompromiso->get_result();
    $compromiso = $resultadoCompromiso->fetch_assoc();

    if ($resultado->num_rows === 0) {
        return json_encode(["error" => "No se encontró el crédito adicional con ID " . $id_credito]);
    }


    $informacion['id_compromiso'] = $compromiso['id_compromiso'] ?? null;
    $informacion['correlativo_compromiso'] = $compromiso['correlativo'] ?? null;

    return json_encode(['success' => $informacion]);
}

function obtenerTodosLosCreditos()
{
    global $conexion;

    $sql = "SELECT 
                ca.*, 
                pc.*, 
                e.ente_nombre AS nombre_ente, 
                s.denominacion AS nombre_sector, 
                p.denominacion AS nombre_programa, 
                pr.denominacion AS nombre_proyecto
            FROM credito_adicional ca
            JOIN proyecto_credito pc ON ca.id = pc.id_credito
            JOIN entes e ON ca.id_ente = e.id
            LEFT JOIN pl_sectores s ON e.sector = s.id
            LEFT JOIN pl_programas p ON e.programa = p.id
            LEFT JOIN pl_proyectos pr ON e.proyecto = pr.id";

    $resultado = $conexion->query($sql);



    if ($resultado->num_rows === 0) {
        return json_encode(["error" => "No se encontraron créditos adicionales registrados."]);
    }

    return json_encode($resultado->fetch_all(MYSQLI_ASSOC));
}


function eliminarCredito($data)
{
    global $conexion;
    global $remote_db;

    // Iniciar transacción en ambas bases de datos
    $conexion->begin_transaction();
    $remote_db->begin_transaction();

    try {

        if ($data['id_credito'] == "") {
            throw new Exception("No se han enviado todos los valores para la eliminacion.");
        }

        $id_credito = $data['id_credito'];

        // Eliminar primero el registro en proyecto_credito en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlProyecto = "DELETE FROM proyecto_credito WHERE id_credito = ?";
            $stmtProyecto = $db->prepare($sqlProyecto);
            $stmtProyecto->bind_param("i", $id_credito);

            if (!$stmtProyecto->execute()) {
                throw new Exception("Error al eliminar el proyecto asociado: " . $stmtProyecto->error);
            }
        }

        // Luego eliminar el registro en credito_adicional en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlCredito = "DELETE FROM credito_adicional WHERE id = ?";
            $stmtCredito = $db->prepare($sqlCredito);
            $stmtCredito->bind_param("i", $id_credito);

            if (!$stmtCredito->execute()) {
                throw new Exception("Error al eliminar el crédito adicional: " . $stmtCredito->error);
            }
        }

        // Confirmar la transacción en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();

        return json_encode(["success" => "Crédito adicional eliminado correctamente"]);

    } catch (Exception $e) {
        // Revertir la transacción en ambas bases de datos en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }
}



function actualizarCredito($data)
{
    global $conexion;
    global $remote_db;

    // Iniciar una transacción para ambas bases de datos
    $conexion->begin_transaction();
    $remote_db->begin_transaction();

    try {

        if ($data['id_ente'] == "" or $data['id_ejercicio'] == "" or $data['monto'] == "" or $data['fecha'] == "" or $data['tipo_credito'] == "" or $data['tipo_proyecto'] == "" or $data['descripcion_proyecto'] == "" or $data['distribuciones'] == "") {
            throw new Exception("No se han enviado todos los valores para el registro.");
        }

        $distribuciones = $data['distribuciones'];
        // Validación de montos en distribucion_entes en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            foreach ($distribuciones as $partida) {
                $id_distribucion = $partida['id_distribucion'];
                $monto_solicitado = $partida['monto'];

                // Consultar monto de distribución en distribucion_entes
                $sqlMontoDistribucion = "SELECT distribucion 
                                         FROM distribucion_entes 
                                         WHERE id_ente = ? 
                                         AND id_ejercicio = ? 
                                         AND distribucion LIKE ?";
                $likePattern = '%"id_distribucion":"' . $id_distribucion . '"%';
                $stmtMontoDistribucion = $db->prepare($sqlMontoDistribucion);
                $stmtMontoDistribucion->bind_param("iis", $data['id_ente'], $data['id_ejercicio'], $likePattern);
                $stmtMontoDistribucion->execute();
                $resultadoMontoDistribucion = $stmtMontoDistribucion->get_result();

                if ($resultadoMontoDistribucion->num_rows === 0) {
                    throw new Exception("El ID de distribución $id_distribucion no se encuentra en distribucion_entes.");
                }

                $filaMontoDistribucion = $resultadoMontoDistribucion->fetch_assoc();
                $distribucionesData = json_decode($filaMontoDistribucion['distribucion'], true);

                // Obtener monto disponible en la distribución
                $montoDistribucion = null;
                foreach ($distribucionesData as &$distribucion) {
                    if ($distribucion['id_distribucion'] == $id_distribucion) {
                        $montoDistribucion = (float) $distribucion['monto'];
                        break;
                    }
                }

                if ($montoDistribucion === null) {
                    throw new Exception("No se encontró el monto para el ID de distribución $id_distribucion.");
                }

                // Validar si el monto disponible es suficiente
                if ($montoDistribucion < $monto_solicitado) {
                    // Obtener id_partida desde distribucion_presupuestaria
                    $sqlDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
                    $stmtDistribucion = $conexion->prepare($sqlDistribucion);
                    $stmtDistribucion->bind_param("i", $id_distribucion);
                    $stmtDistribucion->execute();
                    $resultadoDistribucion = $stmtDistribucion->get_result();

                    if ($resultadoDistribucion->num_rows === 0) {
                        throw new Exception("No se encontró la distribución presupuestaria con ID " . $id_distribucion);
                    }

                    $filaDistribucion = $resultadoDistribucion->fetch_assoc();
                    $idPartida = $filaDistribucion['id_partida'];

                    // Obtener la clave de partida
                    $sqlPartida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
                    $stmtPartida = $conexion->prepare($sqlPartida);
                    $stmtPartida->bind_param("i", $idPartida);
                    $stmtPartida->execute();
                    $resultadoPartida = $stmtPartida->get_result();

                    if ($resultadoPartida->num_rows === 0) {
                        throw new Exception("No se encontró la partida presupuestaria con ID " . $idPartida);
                    }

                    $partida = $resultadoPartida->fetch_assoc()['partida'];

                    throw new Exception("El presupuesto en distribucion_entes es insuficiente para la partida " . $partida);
                }
            }
        }

        // Actualizar los datos en la tabla credito_adicional en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlCredito = "UPDATE credito_adicional SET 
                            id_ente = ?, id_ejercicio = ?, monto = ?, fecha = ?, tipo_credito = ? 
                            WHERE id = ?";

            $stmtCredito = $db->prepare($sqlCredito);
            $stmtCredito->bind_param(
                "iidssi",
                $data['id_ente'],
                $data['id_ejercicio'],
                $data['monto'],
                $data['fecha'],
                $data['tipo_credito'],
                $data['id_credito']
            );

            if (!$stmtCredito->execute()) {
                throw new Exception("Error al actualizar crédito adicional: " . $stmtCredito->error);
            }
        }

        // Actualizar los datos en la tabla proyecto_credito en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlProyecto = "UPDATE proyecto_credito SET 
                            tipo_proyecto = ?, descripcion_proyecto = ?, distribuciones = ? 
                            WHERE id_credito = ?";

            $stmtProyecto = $db->prepare($sqlProyecto);
            $stmtProyecto->bind_param(
                "sssi",
                $data['tipo_proyecto'],
                $data['descripcion_proyecto'],
                $data['distribuciones'],
                $data['id_credito']
            );

            if (!$stmtProyecto->execute()) {
                throw new Exception("Error al actualizar proyecto crédito: " . $stmtProyecto->error);
            }
        }

        // Confirmar la transacción en ambas bases de datos
        $conexion->commit();
        $remote_db->commit();

        return json_encode(["success" => "Crédito adicional actualizado correctamente"]);

    } catch (Exception $e) {
        // Revertir la transacción en ambas bases de datos en caso de error
        $conexion->rollback();
        $remote_db->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }
}


function procesarCreditoAdicional($data)
{
    global $conexion;
    global $remote_db;

    try {
        $id_credito = $data['id_credito'];
        $archivoBase64 = $data['archivoBase64'];
        $nombreArchivo = $data['nombreArchivo'] ?? 'decreto.pdf'; // Usar nombre proporcionado o uno por defecto
        $tipoArchivo = $data['tipoArchivo'] ?? 'application/pdf'; // Usar tipo proporcionado o uno por defecto

        if ($data['archivoBase64'] == "" or $data['id_credito'] == "") {
            throw new Exception("No se han enviado todos los valores para el registro del decreto");
        }

        // Validar que el contenido base64 corresponde a un PDF (opcional)
        if ($tipoArchivo !== 'application/pdf') {
            throw new Exception("El archivo debe ser un PDF.");
        }

        // Decodificar el base64
        $archivoDecodificado = base64_decode($archivoBase64);

        if ($archivoDecodificado === false) {
            throw new Exception("Error al decodificar el archivo base64.");
        }

        // Crear un nombre de archivo único
        $nombreArchivoUnico = uniqid('decreto_', true) . '.pdf';
        $rutaDestino = __DIR__ . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . 'decretos' . DIRECTORY_SEPARATOR . $nombreArchivoUnico;

        // Verificar si la carpeta 'decretos' existe, si no, crearla
        $rutaCarpeta = dirname($rutaDestino);
        if (!file_exists($rutaCarpeta)) {
            if (!mkdir($rutaCarpeta, 0755, true)) {
                throw new Exception("Error al crear la carpeta '$rutaCarpeta'.");
            }
        }

        // Guardar el archivo
        if (file_put_contents($rutaDestino, $archivoDecodificado) === false) {
            throw new Exception("Error al guardar el archivo PDF.");
        }

        // Actualizar la tabla proyecto_credito con el nombre del archivo en ambas bases de datos
        foreach ([$conexion, $remote_db] as $db) {
            $sqlUpdateProyecto = "UPDATE proyecto_credito SET decreto = ? WHERE id_credito = ?";
            $stmtUpdateProyecto = $db->prepare($sqlUpdateProyecto);
            $stmtUpdateProyecto->bind_param("si", $nombreArchivoUnico, $id_credito);
            $stmtUpdateProyecto->execute();

            if ($stmtUpdateProyecto->affected_rows === 0) {
                throw new Exception("No se pudo actualizar el decreto en proyecto_credito.");
            }
        }

        // Consultar distribuciones de proyecto_credito en ambas bases de datos
        $distribuciones = [];
        foreach ([$conexion, $remote_db] as $db) {
            $sqlDistribuciones = "SELECT distribuciones, tipo_proyecto, descripcion_proyecto FROM proyecto_credito WHERE id_credito = ?";
            $stmtDistribuciones = $db->prepare($sqlDistribuciones);
            $stmtDistribuciones->bind_param("i", $id_credito);
            $stmtDistribuciones->execute();
            $resultadoDistribuciones = $stmtDistribuciones->get_result();

            if ($resultadoDistribuciones->num_rows === 0) {
                throw new Exception("No se encontró el registro en proyecto_credito.");
            }

            $filaDistribuciones = $resultadoDistribuciones->fetch_assoc();
            $distribuciones = json_decode($filaDistribuciones['distribuciones'], true);
        }

        // Consultar distribuciones de credito_adicional en ambas bases de datos
        $id_ejercicio = null;
        foreach ([$conexion, $remote_db] as $db) {
            $sqlDistribuciones2 = "SELECT id_ejercicio FROM credito_adicional WHERE id = ?";
            $stmtDistribuciones2 = $db->prepare($sqlDistribuciones2);
            $stmtDistribuciones2->bind_param("i", $id_credito);
            $stmtDistribuciones2->execute();
            $resultadoDistribuciones2 = $stmtDistribuciones2->get_result();

            if ($resultadoDistribuciones2->num_rows === 0) {
                throw new Exception("No se encontró el registro en credito_adicional.");
            }

            $filaDistribuciones2 = $resultadoDistribuciones2->fetch_assoc();
            $id_ejercicio = $filaDistribuciones2['id_ejercicio'];
        }

        // Validar montos de distribuciones en ambas bases de datos
        foreach ($distribuciones as $partida) {
            $id_distribucion = $partida['id_distribucion'];
            $monto_solicitado = $partida['monto'];

            $montoDistribucion = null;
            foreach ([$conexion, $remote_db] as $db) {
                $sqlMontoDistribucion = "SELECT distribucion FROM distribucion_entes WHERE distribucion LIKE ?";
                $likePattern = '%"id_distribucion":"' . $id_distribucion . '"%';
                $stmtMontoDistribucion = $db->prepare($sqlMontoDistribucion);
                $stmtMontoDistribucion->bind_param("s", $likePattern);
                $stmtMontoDistribucion->execute();
                $resultadoMontoDistribucion = $stmtMontoDistribucion->get_result();

                if ($resultadoMontoDistribucion->num_rows === 0) {
                    throw new Exception("El ID de distribución $id_distribucion no se encuentra en distribucion_entes.");
                }

                $filaMontoDistribucion = $resultadoMontoDistribucion->fetch_assoc();
                $distribucionesData = json_decode($filaMontoDistribucion['distribucion'], true);

                foreach ($distribucionesData as &$distribucion) {
                    if ($distribucion['id_distribucion'] == $id_distribucion) {
                        $montoDistribucion = (float) $distribucion['monto'];
                        break;
                    }
                }
            }

            if ($montoDistribucion === null) {
                throw new Exception("No se encontró el monto para el ID de distribución $id_distribucion.");
            }

            if ($montoDistribucion < $monto_solicitado) {
                throw new Exception("El presupuesto en distribucion_entes es insuficiente para el ID de distribución $id_distribucion.");
            }
        }

        // Si el monto es suficiente, registrar el compromiso en ambas bases de datos
        $resultadoCompromiso = registrarCompromiso($conexion, $remote_db, $id_credito, 'proyecto_credito', $descripcion_proyecto, $id_ejercicio, '');
        if (isset($resultadoCompromiso['success']) && $resultadoCompromiso['success']) {
            $conexion->commit();
            $remote_db->commit();

            // Si tipo_proyecto es 1, actualizar monto en distribucion_entes
            if ($tipo_proyecto == 1) {
                foreach ($distribuciones as $partida) {
                    $idDistribucion = $partida['id_distribucion'];
                    $montoSolicitado = $partida['monto'];

                    foreach ($distribucionesData as &$dist) {
                        if ($dist['id_distribucion'] == $idDistribucion) {
                            $dist['monto'] -= $montoSolicitado;
                        }
                    }

                    $nuevoDistribucionJSON = json_encode($distribucionesData);
                    foreach ([$conexion, $remote_db] as $db) {
                        $sqlUpdateDistribucionEnte = "UPDATE distribucion_entes SET distribucion = ? WHERE distribucion LIKE ?";
                        $stmtUpdateDistribucionEnte = $db->prepare($sqlUpdateDistribucionEnte);
                        $stmtUpdateDistribucionEnte->bind_param("ss", $nuevoDistribucionJSON, $likePattern);
                        $stmtUpdateDistribucionEnte->execute();
                    }
                }
            }

            // Actualizar el campo "status" en proyecto_credito en ambas bases de datos
            foreach ([$conexion, $remote_db] as $db) {
                $sqlUpdateProyectoStatus = "UPDATE proyecto_credito SET status = 1 WHERE id_credito = ?";
                $stmtUpdateProyectoStatus = $db->prepare($sqlUpdateProyectoStatus);
                $stmtUpdateProyectoStatus->bind_param("i", $id_credito);
                $stmtUpdateProyectoStatus->execute();
            }

            // Actualizar el campo "status" en credito_adicional en ambas bases de datos
            foreach ([$conexion, $remote_db] as $db) {
                $sqlUpdateCreditoAdicionalStatus = "UPDATE credito_adicional SET status = 1 WHERE id = ?";
                $stmtUpdateCreditoAdicionalStatus = $db->prepare($sqlUpdateCreditoAdicionalStatus);
                $stmtUpdateCreditoAdicionalStatus->bind_param("i", $id_credito);
                $stmtUpdateCreditoAdicionalStatus->execute();
            }

            return json_encode([
                "success" => "El proyecto ha sido aceptado, el compromiso se ha registrado, el presupuesto actualizado, el estado actualizado y el decreto ha sido subido.",
                "compromiso" => [
                    "correlativo" => $resultadoCompromiso['correlativo'],
                    "id_compromiso" => $resultadoCompromiso['id_compromiso']
                ]
            ]);
        }
    } catch (Exception $e) {
        $conexion->rollback();
        $remote_db->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }
}








// Ejecutar la función principal
$data = json_decode(file_get_contents("php://input"), true);
echo gestionarCreditosAdicionales($data);
