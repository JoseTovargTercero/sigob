<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';
require_once '../sistema_global/errores.php';

function registrarCompromiso($conexion, $remote_db, $idRegistro, $nombreTabla, $descripcion, $id_ejercicio, $codigo)
{
    try {
        // Validar que los campos obligatorios no estén vacíos
        if (!isset($idRegistro, $nombreTabla, $descripcion, $id_ejercicio, $codigo)) {
            return ["error" => "Faltan datos obligatorios para registrar el compromiso."];
        }

        // Obtener el último correlativo registrado en la base de datos
        $sql = "SELECT MAX(correlativo) FROM compromisos";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($ultimoCorrelativo);
        $stmt->fetch();
        $stmt->close();

        // Determinar el nuevo correlativo
        $numeroSeguimiento = $ultimoCorrelativo ? (int)$ultimoCorrelativo + 1 : 1;

        // Formatear el nuevo correlativo con 8 dígitos
        $nuevoCorrelativo = str_pad($numeroSeguimiento, 8, '0', STR_PAD_LEFT);
        
        // Insertar el nuevo compromiso en ambas bases de datos
        $sqlInsert = "INSERT INTO compromisos (correlativo, descripcion, id_registro, id_ejercicio, tabla_registro, numero_compromiso) VALUES (?, ?, ?, ?, ?, ?)";
        
        foreach ([$conexion, $remote_db] as $db) {
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->bind_param("ssisss", $nuevoCorrelativo, $descripcion, $idRegistro, $id_ejercicio, $nombreTabla, $codigo);
            $stmtInsert->execute();
        }

        // Verificar si la inserción fue exitosa en la base de datos principal
        if ($stmtInsert->affected_rows > 0) {
            $idCompromiso = $conexion->insert_id;

            // Si la tabla es 'solicitud_dozavos', actualizar el número de compromiso en la tabla correspondiente
            if ($nombreTabla === 'solicitud_dozavos') {
                $sqlUpdate = "UPDATE $nombreTabla SET numero_compromiso = ? WHERE id = ?";
                
                foreach ([$conexion, $remote_db] as $db) {
                    $stmtUpdate = $db->prepare($sqlUpdate);
                    $stmtUpdate->bind_param("si", $codigo, $idRegistro);
                    $stmtUpdate->execute();
                }

                $sqlUpdate2 = "UPDATE compromisos SET numero_compromiso = ? WHERE id = ?";
                
                foreach ([$conexion, $remote_db] as $db) {
                    $stmtUpdate2 = $db->prepare($sqlUpdate2);
                    $stmtUpdate2->bind_param("si", $codigo, $idCompromiso);
                    $stmtUpdate2->execute();
                }

                // Verificar si la actualización fue exitosa
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
