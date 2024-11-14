<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/errores.php';

function registrarCompromiso($idRegistro, $nombreTabla, $descripcion, $tipo_beneficiario, $id_beneficiario, $id_ejercicio)
{
    global $conexion;

    try {
        if (!isset($idRegistro) || !isset($nombreTabla) || !isset($descripcion) || !isset($tipo_beneficiario) || !isset($id_beneficiario) || !isset($id_ejercicio)) {
            return ["error" => "Faltan datos obligatorios para registrar el compromiso."];
        }

        $yearActual = date("Y");

        $sql = "SELECT correlativo FROM compromisos WHERE correlativo LIKE ? ORDER BY correlativo DESC LIMIT 1";
        $correlativoLike = "C%-$yearActual";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $correlativoLike);
        $stmt->execute();
        $stmt->bind_result($ultimoCorrelativo);
        $stmt->fetch();
        $stmt->close();

        if ($ultimoCorrelativo) {
            $numeroSeguimiento = (int)substr($ultimoCorrelativo, 1, 5) + 1;
        } else {
            $numeroSeguimiento = 1;
        }

        $nuevoCorrelativo = 'C' . str_pad($numeroSeguimiento, 5, '0', STR_PAD_LEFT) . '-' . $yearActual;

        $sqlInsert = "INSERT INTO compromisos (correlativo, descripcion, id_registro, tipo_beneficiario, id_beneficiario, id_ejercicio, tabla_registro) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        $stmtInsert->bind_param("ssisiis", $nuevoCorrelativo, $descripcion, $idRegistro, $tipo_beneficiario, $id_beneficiario, $id_ejercicio, $nombreTabla);
        $stmtInsert->execute();

        if ($stmtInsert->affected_rows > 0) {
            $idCompromiso = $conexion->insert_id;

            if ($nombreTabla === 'solicitud_dozavos') {
                $sqlUpdate = "UPDATE $nombreTabla SET numero_compromiso = ? WHERE id = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $nuevoCorrelativo, $idRegistro);
                $stmtUpdate->execute();

                if ($stmtUpdate->affected_rows > 0) {
                    return ["success" => true, "correlativo" => $nuevoCorrelativo, "id_compromiso" => $idCompromiso];
                } else {
                    return ["error" => "No se pudo actualizar el número de compromiso en la tabla $nombreTabla."];
                }
            } else {
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





