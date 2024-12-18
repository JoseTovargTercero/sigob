<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/errores.php';

function registrarCompromiso($idRegistro, $nombreTabla, $descripcion, $id_ejercicio, $codigo)
{
    global $conexion;

    try {
        // Validar que los campos obligatorios no estén vacíos
        if (!isset($idRegistro) || !isset($nombreTabla) || !isset($descripcion) || !isset($id_ejercicio) || !isset($codigo)) {
            return ["error" => "Faltan datos obligatorios para registrar el compromiso."];
        }

        // Iniciar una transacción
        $conexion->begin_transaction();

        // Obtener el año actual
        $yearActual = date("Y");

        // Buscar el último correlativo con el formato 'C-%-YYYY'
        $sql = "SELECT correlativo FROM compromisos WHERE correlativo LIKE ? ORDER BY correlativo DESC LIMIT 1";
        $correlativoLike = "C%-$yearActual";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $correlativoLike);
        $stmt->execute();
        $stmt->bind_result($ultimoCorrelativo);
        $stmt->fetch();
        $stmt->close();

        // Incrementar el número de seguimiento
        if ($ultimoCorrelativo) {
            $numeroSeguimiento = (int)substr($ultimoCorrelativo, 1, 5) + 1;
        } else {
            $numeroSeguimiento = 1;
        }

        // Crear el nuevo correlativo
        $nuevoCorrelativo = 'C' . str_pad($numeroSeguimiento, 5, '0', STR_PAD_LEFT) . '-' . $yearActual;

        // Insertar el nuevo compromiso en la base de datos
        $sqlInsert = "INSERT INTO compromisos (correlativo, descripcion, id_registro, id_ejercicio, tabla_registro, numero_compromiso) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        $stmtInsert->bind_param("ssisss", $nuevoCorrelativo, $descripcion, $idRegistro, $id_ejercicio, $nombreTabla, $codigo);
        $stmtInsert->execute();

        // Verificar si la inserción fue exitosa
        if ($stmtInsert->affected_rows > 0) {
            $idCompromiso = $conexion->insert_id;

            // Si la tabla es 'solicitud_dozavos', actualizar el número de compromiso en la tabla correspondiente
            if ($nombreTabla === 'solicitud_dozavos') {
                $sqlUpdate = "UPDATE $nombreTabla SET numero_compromiso = ? WHERE id = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $codigo, $idRegistro);
                $stmtUpdate->execute();

                $sqlUpdate2 = "UPDATE compromisos SET numero_compromiso = ? WHERE id_registro = ?";
                $stmtUpdate2 = $conexion->prepare($sqlUpdate2);
                $stmtUpdate2->bind_param("si", $codigo, $idRegistro);
                $stmtUpdate2->execute();

                // Verificar si ambas actualizaciones fueron exitosas
                if ($stmtUpdate->affected_rows > 0 && $stmtUpdate2->affected_rows > 0) {
                    // Confirmar la transacción
                    $conexion->commit();
                    return ["success" => true, "correlativo" => $nuevoCorrelativo, "id_compromiso" => $idCompromiso];
                } else {
                    // Revertir en caso de error
                    $conexion->rollback();
                    return ["error" => "No se pudo actualizar el número de compromiso en la tabla $nombreTabla."];
                }
            } else {
                // Confirmar la transacción
                $conexion->commit();
                return ["success" => true, "correlativo" => $nuevoCorrelativo, "id_compromiso" => $idCompromiso];
            }
        } else {
            // Revertir la transacción si la inserción falla
            $conexion->rollback();
            return ["error" => "No se pudo registrar el compromiso."];
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        registrarError($e->getMessage());
        return ["error" => $e->getMessage()];
    }
}
