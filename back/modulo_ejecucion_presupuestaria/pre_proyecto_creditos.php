<?php

require_once '../sistema_global/conexion.php';

require_once '../sistema_global/notificaciones.php';
header('Content-Type: application/json');
require_once '../sistema_global/errores.php';
require_once 'pre_compromisos.php';

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
        // Iniciar la transacción
        $conexion->begin_transaction();

        // Datos recibidos
        $id_ente = $data['id_ente'];
        $id_ejercicio = $data['id_ejercicio'];
        $monto = $data['monto'];
        $fecha = $data['fecha'];
        $descripcion_credito = $data['descripcion_credito'];
        $tipo_credito = $data['tipo_credito'];
        $tipo_proyecto = $data['tipo_proyecto'];
        $descripcion_proyecto = $data['descripcion_proyecto'];
        $distribuciones = $data['distribuciones']; 

        // Validación de montos en distribucion_entes
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
            $stmtMontoDistribucion = $remote_db->prepare($sqlMontoDistribucion);
            $stmtMontoDistribucion->bind_param("iis", $id_ente, $id_ejercicio, $likePattern);
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

        // Insertar en credito_adicional
        $sqlCredito = "INSERT INTO credito_adicional (id_ente, id_ejercicio, monto, fecha, descripcion_credito, tipo_credito) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmtCredito = $conexion->prepare($sqlCredito);
        $stmtCredito->bind_param("iidssi", $id_ente, $id_ejercicio, $monto, $fecha, $descripcion_credito, $tipo_credito);
        $stmtCredito->execute();

        if ($stmtCredito->affected_rows === 0) {
            throw new Exception("No se pudo registrar el crédito adicional.");
        }

        $id_credito = $conexion->insert_id;

        // Insertar en proyecto_credito
        $sqlProyecto = "INSERT INTO proyecto_credito (id_credito, tipo_proyecto, descripcion_proyecto, distribuciones, decreto) 
                        VALUES (?, ?, ?, ?, NULL)";
        $stmtProyecto = $conexion->prepare($sqlProyecto);
        $distribuciones_json = json_encode($distribuciones);
        $stmtProyecto->bind_param("isss", $id_credito, $tipo_proyecto, $descripcion_proyecto, $distribuciones_json);
        $stmtProyecto->execute();

        if ($stmtProyecto->affected_rows === 0) {
            throw new Exception("No se pudo registrar el proyecto de crédito.");
        }

        // Confirmar la transacción
        $conexion->commit();

        return json_encode(["success" => "El crédito adicional y su proyecto se registraron correctamente."]);

    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}



function obtenerCreditoPorId($data)
{
    global $conexion;
    $id_credito = $data['id_credito'];
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

    if ($resultado->num_rows === 0) {
        return json_encode(["error" => "No se encontró el crédito adicional con ID " . $id_credito]);
    }

    return json_encode($resultado->fetch_assoc());
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

    // Iniciar una transacción para asegurar la integridad de los datos
    $conexion->begin_transaction();

    try {
        $id_credito = $data['id_credito'];
        // Eliminar primero el registro en proyecto_credito
        $sqlProyecto = "DELETE FROM proyecto_credito WHERE id_credito = ?";
        $stmtProyecto = $conexion->prepare($sqlProyecto);
        $stmtProyecto->bind_param("i", $id_credito);

        if (!$stmtProyecto->execute()) {
            throw new Exception("Error al eliminar el proyecto asociado: " . $stmtProyecto->error);
        }

        // Luego eliminar el registro en credito_adicional
        $sqlCredito = "DELETE FROM credito_adicional WHERE id = ?";
        $stmtCredito = $conexion->prepare($sqlCredito);
        $stmtCredito->bind_param("i", $id_credito);

        if (!$stmtCredito->execute()) {
            throw new Exception("Error al eliminar el crédito adicional: " . $stmtCredito->error);
        }

        // Confirmar la transacción
        $conexion->commit();

        return json_encode(["success" => "Crédito adicional eliminado correctamente"]);

    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }
}


function actualizarCredito($data)
{
    global $conexion;
    global $remote_db;

    // Iniciar una transacción para asegurar la integridad de los datos
    $conexion->begin_transaction();

    try {

        $distribuciones = $data['distribuciones'];
        // Validación de montos en distribucion_entes
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
            $stmtMontoDistribucion = $remote_db->prepare($sqlMontoDistribucion);
            $stmtMontoDistribucion->bind_param("iis", $id_ente, $id_ejercicio, $likePattern);
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

        // Actualizar los datos en la tabla credito_adicional
        $sqlCredito = "UPDATE credito_adicional SET 
                        id_ente = ?, id_ejercicio = ?, monto = ?, fecha = ?, 
                        descripcion_credito = ?, tipo_credito = ? 
                       WHERE id = ?";

        $stmtCredito = $conexion->prepare($sqlCredito);
        $stmtCredito->bind_param("iidsssi", 
            $data['id_ente'], 
            $data['id_ejercicio'], 
            $data['monto'], 
            $data['fecha'], 
            $data['descripcion_credito'], 
            $data['tipo_credito'], 
            $data['id_credito']
        );

        if (!$stmtCredito->execute()) {
            throw new Exception("Error al actualizar crédito adicional: " . $stmtCredito->error);
        }

        // Actualizar los datos en la tabla proyecto_credito
        $sqlProyecto = "UPDATE proyecto_credito SET 
                            tipo_proyecto = ?, descripcion_proyecto = ?, distribuciones = ? 
                        WHERE id_credito = ?";

        $stmtProyecto = $conexion->prepare($sqlProyecto);
        $stmtProyecto->bind_param("sssi", 
            $data['tipo_proyecto'], 
            $data['descripcion_proyecto'], 
            $data['distribuciones'], 
            $data['id_credito']
        );

        if (!$stmtProyecto->execute()) {
            throw new Exception("Error al actualizar proyecto crédito: " . $stmtProyecto->error);
        }

        // Confirmar la transacción
        $conexion->commit();

        return json_encode(["success" => "Crédito adicional actualizado correctamente"]);

    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        return json_encode(["error" => $e->getMessage()]);
    }
}








// Ejecutar la función principal
$data = json_decode(file_get_contents("php://input"), true);
echo gestionarCreditosAdicionales($data);









 ?>