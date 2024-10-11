<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/errores.php';


// Función para registrar un compromiso
function registrarCompromiso($idRegistro, $nombreTabla, $descripcion)
{
    global $conexion;

    try {
        // Verificar que los parámetros necesarios estén presentes
        if (!isset($idRegistro) || !isset($nombreTabla) || !isset($descripcion)) {
            return ["error" => "Faltan datos obligatorios para registrar el compromiso."];
        }

        // Obtener el año actual
        $yearActual = date("Y");

        // Obtener el número de seguimiento del año actual (reinicia si es un nuevo año)
        $sql = "SELECT correlativo FROM compromisos WHERE correlativo LIKE ? ORDER BY correlativo DESC LIMIT 1";
        $correlativoLike = "C%-$yearActual"; // Buscar correlativos del formato Cxxxxx-2024
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $correlativoLike);
        $stmt->execute();
        $stmt->bind_result($ultimoCorrelativo);
        $stmt->fetch();
        $stmt->close();

        // Calcular el nuevo número de seguimiento
        if ($ultimoCorrelativo) {
            $numeroSeguimiento = (int)substr($ultimoCorrelativo, 1, 5) + 1;
        } else {
            $numeroSeguimiento = 1; // Si no hay registros anteriores, iniciar en 1
        }

        // Formatear el número de seguimiento (C00001-2024)
        $nuevoCorrelativo = 'C' . str_pad($numeroSeguimiento, 5, '0', STR_PAD_LEFT) . '-' . $yearActual;

        // Obtener el primer carácter de la descripción para el campo tipo (en mayúscula)
        $tipo = strtoupper(substr($descripcion, 0, 1));

        // Insertar el nuevo compromiso en la tabla compromisos
        $sqlInsert = "INSERT INTO compromisos (correlativo, tipo, id_registro) VALUES (?, ?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        $stmtInsert->bind_param("ssi", $nuevoCorrelativo, $tipo, $idRegistro);
        $stmtInsert->execute();

        // Verificar si la inserción fue exitosa
        if ($stmtInsert->affected_rows > 0) {
            // Actualizar el campo numero_compromiso solo si nombreTabla es solicitud_dozavos
            if ($nombreTabla === 'solicitud_dozavos') {
                $sqlUpdate = "UPDATE $nombreTabla SET numero_compromiso = ? WHERE id = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $nuevoCorrelativo, $idRegistro);
                $stmtUpdate->execute();

                if ($stmtUpdate->affected_rows > 0) {
                    return ["success" => true, "correlativo" => $nuevoCorrelativo];
                } else {
                    return ["error" => "No se pudo actualizar el número de compromiso en la tabla $nombreTabla."];
                }
            } else {
                return ["success" => true, "correlativo" => $nuevoCorrelativo];
            }
        } else {
            return ["error" => "No se pudo registrar el compromiso."];
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return ["error" => $e->getMessage()];
    }
}


// Función para actualizar el compromiso relacionado
function actualizarCompromiso($idSolicitud, $descripcion)
{
    global $conexion;

    // Actualizar el tipo en la tabla compromisos
    $tipo = strtoupper(substr($descripcion, 0, 1));
    $sqlCompromiso = "UPDATE compromisos SET tipo = ? WHERE id_registro = ?";
    $stmtCompromiso = $conexion->prepare($sqlCompromiso);
    $stmtCompromiso->bind_param("si", $tipo, $idSolicitud);
    $stmtCompromiso->execute();

    if ($stmtCompromiso->affected_rows > 0) {
        return ["success" => "Compromiso actualizado correctamente."];
    } else {
        return ["error" => "No se pudo actualizar el compromiso o no hubo cambios."];
    }
}

