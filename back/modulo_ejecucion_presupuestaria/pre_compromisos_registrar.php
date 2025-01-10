<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/errores.php';

// Función para registrar compromiso
function registrarCompromiso($idRegistro, $nombreTabla, $descripcion, $id_ejercicio, $codigo)
{
    global $conexion;

    try {
        // Validar que los campos obligatorios no estén vacíos
        if (empty($idRegistro) || empty($nombreTabla) || empty($descripcion) || empty($id_ejercicio) || empty($codigo)) {
            return ["error" => "Faltan datos obligatorios para registrar el compromiso."];
        }

        // Verificar si ya existe un compromiso con los mismos datos
        $sqlCheck = "SELECT id FROM compromisos WHERE id_registro = ? AND tabla_registro = ? AND descripcion = ? AND id_ejercicio = ? AND numero_compromiso = ?";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("issis", $idRegistro, $nombreTabla, $descripcion, $id_ejercicio, $codigo);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            // Si ya existe un compromiso con los mismos valores, retornar un mensaje indicando el duplicado
            $stmtCheck->close();
            return ["error" => "Ya existe un compromiso registrado con los mismos valores."];
        }
        $stmtCheck->close();

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
            $numeroSeguimiento = (int) substr($ultimoCorrelativo, 1, 5) + 1;
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
                $sqlUpdate2 = "UPDATE compromisos SET numero_compromiso = ? WHERE id = ?";
                $stmtUpdate2 = $conexion->prepare($sqlUpdate2);
                $stmtUpdate2->bind_param("si", $codigo, $idCompromiso);
                $stmtUpdate2->execute();

                // Verificar si la actualización fue exitosa
                if ($stmtUpdate2->affected_rows >= 0) {
                    return ["success" => ["correlativo" => $nuevoCorrelativo, "id_compromiso" => $idCompromiso]];
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

// Función para consultar compromiso
function consultarCompromiso($idRegistro, $tablaRegistro)
{
    global $conexion;

    try {
        // Consulta para obtener el compromiso
        $sql = "SELECT * FROM compromisos WHERE id_registro = ? AND tabla_registro = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("is", $idRegistro, $tablaRegistro);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si existen resultados
        if ($result->num_rows > 0) {
            $compromisos = [];
            while ($row = $result->fetch_assoc()) {
                $compromisos[] = $row;
            }
            return ["success" => true, "data" => $compromisos];
        } else {
            return ["error" => "No se encontraron compromisos para los parámetros especificados."];
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return ["error" => $e->getMessage()];
    }
}
// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

// Verificar que se haya especificado una acción
if (isset($data["accion"])) {
    $accion = $data["accion"];
    $idRegistro = $data["id"] ?? '';
    $nombreTabla = $data["nombre_tabla"] ?? '';
    $descripcion = $data["descripcion"] ?? '';
    $id_ejercicio = $data["id_ejercicio"] ?? '';
    $codigo = $data["codigo"] ?? '';
    $id_registro = $data["id_registro"] ?? '';
    $tabla_registro = $data["tabla_registro"] ?? '';

    if ($accion === "insert") {
        // Registrar compromiso
        $response = registrarCompromiso($idRegistro, $nombreTabla, $descripcion, $id_ejercicio, $codigo);
    } elseif ($accion === "update") {
        // Actualizar compromiso
        $response = actualizarCompromiso($idRegistro, $nombreTabla, $descripcion, $id_ejercicio, $codigo);
    } elseif ($accion === "delete") {
        // Eliminar compromiso
        $response = eliminarCompromiso($idRegistro);
    } elseif ($accion === "consultar") {
        // Consultar todos los compromisos
        $response = consultarCompromiso();
    } elseif ($accion === "consultar_id") {
        // Consultar compromiso por ID
        $response = consultarCompromisoPorId($id_registro, $tabla_registro);
    } else {
        // Acción no aceptada
        $response = json_encode(['error' => "Acción no aceptada"]);
    }
} else {
    // No se especificó ninguna acción
    $response = json_encode(['error' => "No se especificó ninguna acción"]);
}

// Devolver la respuesta
echo json_encode($response);
