<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/errores.php';
require_once '../sistema_global/session.php';

// Función para insertar datos en la tabla distribucion_presupuestaria
function guardarDistribucionPresupuestaria($dataArray)
{
    global $conexion;

    try {
        if (empty($dataArray)) {
            throw new Exception("El array de datos está vacío");
        }

        foreach ($dataArray as $registro) {
            if (count($registro) !== 6) { // Actualizado para incluir id_sector, id_programa, id_proyecto
                throw new Exception("El formato del array no es válido");
            }

            $id_partida = $registro[0];
            $monto_inicial = $registro[1];
            $id_ejercicio = $registro[2];
            $id_sector = $registro[3];
            $id_programa = $registro[4]; // Nuevo campo
            $id_proyecto = $registro[5]; // Nuevo campo

            // Validar que no existan duplicados con el mismo id_partida, id_ejercicio, id_sector, id_programa, id_proyecto
            $verificarSql = "SELECT PP.partida FROM distribucion_presupuestaria AS DP 
            LEFT JOIN partidas_presupuestarias AS PP ON PP.id=DP.id_partida
            WHERE id_partida = ? AND id_ejercicio = ? AND id_sector = ? AND id_programa = ? AND id_proyecto = ?";
            $stmtVerificar = $conexion->prepare($verificarSql);
            $stmtVerificar->bind_param("iiiii", $id_partida, $id_ejercicio, $id_sector, $id_programa, $id_proyecto);
            $stmtVerificar->execute();
            $resultadoVerificar = $stmtVerificar->get_result();

            if ($resultadoVerificar->num_rows > 0) {
                while ($row = $resultadoVerificar->fetch_assoc()) {
                    $partida_repetida = $row['partida'];
                }

                throw new Exception("Una partida ya está en uso en el mismo sector, programa y proyecto: " . $partida_repetida);
            }

            // Verificar que el ejercicio fiscal esté abierto (status = 1)
            $sqlEjercicio = "SELECT status FROM ejercicio_fiscal WHERE id = ?";
            $stmtEjercicio = $conexion->prepare($sqlEjercicio);
            $stmtEjercicio->bind_param("i", $id_ejercicio);
            $stmtEjercicio->execute();
            $resultadoEjercicio = $stmtEjercicio->get_result();
            $filaEjercicio = $resultadoEjercicio->fetch_assoc();

            if ($filaEjercicio['status'] == 0) {
                throw new Exception("El ejercicio fiscal seleccionado ya fue cerrado.");
            }

            // Inicializar monto_actual con el mismo valor que monto_inicial
            $monto_actual = $monto_inicial;

            if (empty($id_partida) || empty($monto_inicial) || empty($id_ejercicio) || empty($id_sector) || empty($id_programa)) {
                throw new Exception("Faltan datos en uno de los registros (id_partida, monto_inicial, id_ejercicio, id_sector, id_programa)");
            }

            // Insertar los datos en la tabla
            $sql = "INSERT INTO distribucion_presupuestaria (id_partida, monto_inicial, id_ejercicio, monto_actual, id_sector, id_programa, id_proyecto, status) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("isiiiii", $id_partida, $monto_inicial, $id_ejercicio, $monto_actual, $id_sector, $id_programa, $id_proyecto);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                throw new Exception("Error al insertar en distribucion_presupuestaria");
            }

            $stmt->close();
        }

        return json_encode(["success" => "Datos de distribución presupuestaria guardados correctamente"]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para obtener todos los registros de la tabla distribucion_presupuestaria, incluyendo el sector
function obtenerDistribuciones()
{
    global $conexion;

    $sql = "SELECT dp.*, 
                   ps.sector AS sector_nombre, 
                   ps.denominacion AS sector_nombre_completo, 
                   pp.programa AS programa_nombre, 
                   pp.denominacion AS programa_nombre_completo
            FROM distribucion_presupuestaria dp
            JOIN pl_sectores ps ON dp.id_sector = ps.id
            JOIN pl_programas pp ON dp.id_programa = pp.id";

    $result = $conexion->query($sql);

    $distribuciones = [];

    while ($row = $result->fetch_assoc()) {
        $distribuciones[] = $row;
    }

    return json_encode($distribuciones);
}


// Función para obtener un solo registro por ID, incluyendo el sector
function obtenerDistribucionPorId($id)
{
    global $conexion;

    $sql = "SELECT dp.*, 
                   ps.sector AS sector_nombre, 
                   ps.denominacion AS sector_nombre_completo, 
                   pp.programa AS programa_nombre, 
                   pp.denominacion AS programa_nombre_completo
            FROM distribucion_presupuestaria dp
            JOIN pl_sectores ps ON dp.id_sector = ps.id
            JOIN pl_programas pp ON dp.id_programa = pp.id
            WHERE dp.id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $distribucion = $result->fetch_assoc();
        return json_encode($distribucion);
    } else {
        return json_encode(['error' => 'No se encontró el registro']);
    }
}

// Función para actualizar un registro, incluyendo id_sector, id_programa e id_proyecto
function actualizarDistribucion($id, $id_partida, $monto_inicial, $id_ejercicio, $id_sector, $id_programa, $id_proyecto)
{
    global $conexion;

    try {
        // Verificar si el registro existe y su status
        $sql = "SELECT status FROM distribucion_presupuestaria WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $distribucion = $result->fetch_assoc();

        if (!$distribucion) {
            throw new Exception("El registro no existe");
        }

        if ($distribucion['status'] == 0) {
            throw new Exception("No se puede actualizar un registro cerrado.");
        }

        // Verificar si el ejercicio fiscal está abierto
        $sqlEjercicio = "SELECT status FROM ejercicio_fiscal WHERE id = ?";
        $stmtEjercicio = $conexion->prepare($sqlEjercicio);
        $stmtEjercicio->bind_param("i", $id_ejercicio);
        $stmtEjercicio->execute();
        $resultadoEjercicio = $stmtEjercicio->get_result();
        $filaEjercicio = $resultadoEjercicio->fetch_assoc();

        if ($filaEjercicio['status'] == 0) {
            throw new Exception("El ejercicio fiscal seleccionado ya fue cerrado.");
        }

        // Actualizar el registro en la tabla distribucion_presupuestaria
        $sql = "UPDATE distribucion_presupuestaria 
                SET id_partida = ?, monto_inicial = ?, id_ejercicio = ?, id_sector = ?, id_programa = ?, id_proyecto = ? 
                WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isiiiii", $id_partida, $monto_inicial, $id_ejercicio, $id_sector, $id_programa, $id_proyecto, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return json_encode(["success" => "Registro actualizado correctamente"]);
        } else {
            throw new Exception("Error al actualizar el registro");
        }
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}



function actualizarMontoDistribucion($id_sector, $id_ejercicio, $id_distribucion1, $id_distribucion2 = null, $id_partida = null, $monto)
{
    global $conexion;

    try {
        $conexion->begin_transaction();

        if ($id_sector === null) {
            // Caso: id_sector es null, se manejan id_distribucion1 y id_distribucion2
            $sqlDistribucion1 = "SELECT monto_actual FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucion1 = $conexion->prepare($sqlDistribucion1);
            $stmtDistribucion1->bind_param("i", $id_distribucion1);
            $stmtDistribucion1->execute();
            $resultadoDistribucion1 = $stmtDistribucion1->get_result();

            $sqlDistribucion2 = "SELECT monto_actual FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucion2 = $conexion->prepare($sqlDistribucion2);
            $stmtDistribucion2->bind_param("i", $id_distribucion2);
            $stmtDistribucion2->execute();
            $resultadoDistribucion2 = $stmtDistribucion2->get_result();

            if ($resultadoDistribucion1->num_rows > 0 && $resultadoDistribucion2->num_rows > 0) {
                // Resta el monto a distribucion1
                $sqlResta = "UPDATE distribucion_presupuestaria SET monto_actual = monto_actual - ? WHERE id = ?";
                $stmtResta = $conexion->prepare($sqlResta);
                $stmtResta->bind_param("di", $monto, $id_distribucion1);
                $stmtResta->execute();

                // Suma el monto a distribucion2
                $sqlSuma = "UPDATE distribucion_presupuestaria SET monto_actual = monto_actual + ? WHERE id = ?";
                $stmtSuma = $conexion->prepare($sqlSuma);
                $stmtSuma->bind_param("di", $monto, $id_distribucion2);
                $stmtSuma->execute();
            } else {
                throw new Exception("Uno o ambos registros no existen para id_distribucion1 o id_distribucion2.");
            }
        } else {
            // Caso: id_sector no es null, se usa id_partida
            $sqlInsert = "INSERT INTO distribucion_presupuestaria (id_partida, monto_inicial, id_ejercicio, monto_actual, id_sector, status) 
                          VALUES (?, ?, ?, ?, NULL, 1)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bind_param("ididi", $id_partida, $monto, $id_ejercicio, $monto, $id_sector);
            $stmtInsert->execute();

            // Actualizar monto_actual para id_distribucion1
            $sqlUpdateDistribucion1 = "UPDATE distribucion_presupuestaria SET monto_actual = monto_actual - ? WHERE id = ?";
            $stmtUpdateDistribucion1 = $conexion->prepare($sqlUpdateDistribucion1);
            $stmtUpdateDistribucion1->bind_param("di", $monto, $id_distribucion1);
            $stmtUpdateDistribucion1->execute();
        }

        $conexion->commit();
        return json_encode(["success" => "Operación completada correctamente"]);
    } catch (Exception $e) {
        $conexion->rollback();
        registrarError($e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}


// Función para eliminar un registro por ID
function eliminarDistribucion($id)
{
    global $conexion;

    try {
        // Verificar si el registro existe y su estado
        $sql = "SELECT id, status FROM distribucion_presupuestaria WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $distribucion = $result->fetch_assoc();

        if (!$distribucion) {
            throw new Exception("El registro no existe.");
        }

        // No permitir eliminar si el registro tiene status 0 (cerrado)
        if ($distribucion['status'] == 0) {
            throw new Exception("No se puede eliminar un registro cerrado.");
        }

        // Eliminar el registro
        $sqlEliminar = "DELETE FROM distribucion_presupuestaria WHERE id = ?";
        $stmtEliminar = $conexion->prepare($sqlEliminar);
        $stmtEliminar->bind_param("i", $id);
        $stmtEliminar->execute();

        if ($stmtEliminar->affected_rows > 0) {
            return json_encode(["success" => "Registro eliminado correctamente."]);
        } else {
            throw new Exception("Error al eliminar el registro.");
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    switch ($data["accion"]) {
        case 'crear':
            echo guardarDistribucionPresupuestaria($data["arrayDatos"]);
            break;

        case 'obtener':
            echo obtenerDistribuciones();
            break;

        case 'obtener_id':
            echo obtenerDistribucionPorId($data["id"]);
            break;

        case 'actualizar':
            // Adaptación para incluir id_programa e id_proyecto
            echo actualizarDistribucion(
                $data["id"],
                $data["id_partida"],
                $data["monto_inicial"],
                $data["id_ejercicio"],
                $data["id_sector"],
                $data["id_programa"],
                $data["id_proyecto"]
            );
            break;

        case 'eliminar':
            echo eliminarDistribucion($data["id"]);
            break;

        case 'actualizar_monto_distribucion':
            // Llamado a actualizarMontoDistribucion con parámetros específicos
            echo actualizarMontoDistribucion(
                $data["id_sector"] ?? null,
                $data["id_ejercicio"],
                $data["id_distribucion1"],
                $data["id_distribucion2"] ?? null,
                $data["id_partida"] ?? null,
                $data["monto"]
            );
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    echo json_encode(['error' => 'No se especificó ninguna acción']);
}
