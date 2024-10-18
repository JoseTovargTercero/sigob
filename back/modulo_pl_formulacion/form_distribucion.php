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
            if (count($registro) !== 4) { // Actualizado para incluir id_sector
                throw new Exception("El formato del array no es válido");
            }

            $id_partida = $registro[0];
            $monto_inicial = $registro[1];
            $id_ejercicio = $registro[2];
            $id_sector = $registro[3]; // Nuevo campo

            // Validar que no existan duplicados con el mismo id_partida e id_ejercicio
            $verificarSql = "SELECT * FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
            $stmtVerificar = $conexion->prepare($verificarSql);
            $stmtVerificar->bind_param("ii", $id_partida, $id_ejercicio);
            $stmtVerificar->execute();
            $resultadoVerificar = $stmtVerificar->get_result();

            if ($resultadoVerificar->num_rows > 0) {
                throw new Exception("Una partida ya está registrada en este ejercicio fiscal");
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

            if (empty($id_partida) || empty($monto_inicial) || empty($id_ejercicio) || empty($id_sector)) {
                throw new Exception("Faltan datos en uno de los registros (id_partida, monto_inicial, id_ejercicio, id_sector)");
            }

            // Insertar los datos en la tabla
            $sql = "INSERT INTO distribucion_presupuestaria (id_partida, monto_inicial, id_ejercicio, monto_actual, id_sector, status) VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("isisi", $id_partida, $monto_inicial, $id_ejercicio, $monto_actual, $id_sector);
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

    $sql = "SELECT dp.*, s.sector, s.programa, s.proyecto, s.nombre FROM distribucion_presupuestaria dp 
            JOIN pl_sectores_presupuestarios s ON dp.id_sector = s.id";
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

    $sql = "SELECT dp.*, s.sector, s.programa, s.proyecto, s.nombre FROM distribucion_presupuestaria dp 
            JOIN pl_sectores_presupuestarios s ON dp.id_sector = s.id WHERE dp.id = ?";
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

// Función para actualizar un registro, incluyendo id_sector
function actualizarDistribucion($id, $id_partida, $monto_inicial, $id_ejercicio, $id_sector)
{
    global $conexion;

    try {
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

        $sqlEjercicio = "SELECT status FROM ejercicio_fiscal WHERE id = ?";
        $stmtEjercicio = $conexion->prepare($sqlEjercicio);
        $stmtEjercicio->bind_param("i", $id_ejercicio);
        $stmtEjercicio->execute();
        $resultadoEjercicio = $stmtEjercicio->get_result();
        $filaEjercicio = $resultadoEjercicio->fetch_assoc();

        if ($filaEjercicio['status'] == 0) {
            throw new Exception("El ejercicio fiscal seleccionado ya fue cerrado.");
        }

        $sql = "UPDATE distribucion_presupuestaria SET id_partida = ?, monto_inicial = ?, id_ejercicio = ?, id_sector = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isiii", $id_partida, $monto_inicial, $id_ejercicio, $id_sector, $id);
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
            echo actualizarDistribucion($data["id"], $data["id_partida"], $data["monto_inicial"], $data["id_ejercicio"], $data["id_sector"]);
            break;

        case 'eliminar':
            echo eliminarDistribucion($data["id"]);
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    echo json_encode(['error' => 'No se especificó ninguna acción']);
}
