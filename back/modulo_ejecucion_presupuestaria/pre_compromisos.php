<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/errores.php';

function registrarCompromiso($idRegistro, $nombreTabla, $descripcion, $id_ejercicio)
{
    global $conexion;

    try {
        // Validar que los campos obligatorios no estén vacíos
        if (!isset($idRegistro) || !isset($nombreTabla) || !isset($descripcion) || !isset($id_ejercicio)) {
            return ["error" => "Faltan datos obligatorios para registrar el compromiso."];
        }

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
        $sqlInsert = "INSERT INTO compromisos (correlativo, descripcion, id_registro, id_ejercicio, tabla_registro) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        $stmtInsert->bind_param("ssisi", $nuevoCorrelativo, $descripcion, $idRegistro, $id_ejercicio, $nombreTabla);
        $stmtInsert->execute();

        // Verificar si la inserción fue exitosa
        if ($stmtInsert->affected_rows > 0) {
            $idCompromiso = $conexion->insert_id;

            // Si la tabla es 'solicitud_dozavos', actualizar el número de compromiso en la tabla correspondiente
            if ($nombreTabla === 'solicitud_dozavos') {
                $sqlUpdate = "UPDATE $nombreTabla SET numero_compromiso = ? WHERE id = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $nuevoCorrelativo, $idRegistro);
                $stmtUpdate->execute();

                // Verificar si la actualización fue exitosa
                if ($stmtUpdate->affected_rows > 0) {
                    return ["success" => true, "correlativo" => $nuevoCorrelativo, "id_compromiso" => $idCompromiso];
                } else {
                    return ["error" => "No se pudo actualizar el número de compromiso en la tabla $nombreTabla."];
                }
            } else {
                // Si no es 'solicitud_dozavos', retornar el éxito
                return ["success" => true, "correlativo" => $nuevoCorrelativo, "id_compromiso" => $idCompromiso];
            }
        } else {
            return ["error" => "No se pudo registrar el compromiso."];
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return ["error" => $e->getMessage()];
    }
}






