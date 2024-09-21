<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para insertar datos en la tabla distribucion_entes
function guardarDistribucionEntes($dataArray, $tipo) {
    global $conexion;

    try {
        // Verificar que el array no esté vacío
        if (empty($dataArray)) {
            throw new Exception("El array de datos está vacío");
        }

        // Si tipo es 1, verificar que solo hay un registro en el array
        if ($tipo == 1 && count($dataArray) > 1) {
            throw new Exception("Solo se permite una partida en tipo descentralizado");
        }

        // Recorrer el array y almacenar los datos en la tabla distribucion_entes
        foreach ($dataArray as $registro) {
            // Verificar que el array tenga el formato correcto
            if (count($registro) !== 4) {
                throw new Exception("El formato del array no es válido");
            }

            // Descomponer los valores del array
            $id_partida = $registro[0];
            $monto = $registro[1];
            $id_ente = $registro[2];
            $id_poa = $registro[3];

            // Validar que los campos no estén vacíos
            if (empty($id_partida) || empty($monto) || empty($id_ente) || empty($id_poa)) {
                throw new Exception("Faltan datos en uno de los registros (id_partida, monto, id_ente, id_poa)");
            }

            // Insertar los datos en la tabla distribucion_entes
            $fecha = date('d-m-Y'); // Fecha actual
            $sql = "INSERT INTO distribucion_entes (id_partida, monto, id_ente, id_poa, fecha) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("isiiss", $id_partida, $monto, $id_ente, $id_poa, $fecha);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                throw new Exception("Error al insertar en distribucion_entes");
            }

            $stmt->close();
        }

        return json_encode(["success" => "Datos de distribución guardados correctamente"]);

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar datos en la tabla distribucion_entes
function actualizarDistribucionEntes($id, $dataArray) {
    global $conexion;

    try {
        if (empty($dataArray)) {
            throw new Exception("El array de datos está vacío");
        }

        // Verificar que el array tenga el formato correcto
        if (count($dataArray) !== 4) { // No incluimos fecha ni id en el array
            throw new Exception("El formato del array no es válido");
        }

        // Descomponer los valores del array
        $id_partida = $dataArray[0];
        $monto = $dataArray[1];
        $id_ente = $dataArray[2];
        $id_poa = $dataArray[3];
        $fecha = date('d-m-Y'); // Fecha actual

        // Validar que los campos no estén vacíos
        if (empty($id_partida) || empty($monto) || empty($id_ente) || empty($id_poa)) {
            throw new Exception("Faltan datos (id_partida, monto, id_ente, id_poa)");
        }

        // Actualizar los datos en la tabla distribucion_entes
        $sql = "UPDATE distribucion_entes SET id_partida = ?, monto = ?, id_ente = ?, id_poa = ?, fecha = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isiisi", $id_partida, $monto, $id_ente, $id_poa, $fecha, $id);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al actualizar la distribución");
        }

        $stmt->close();

        return json_encode(["success" => "Datos actualizados correctamente"]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para eliminar datos en la tabla distribucion_entes
function eliminarDistribucionEntes($id) {
    global $conexion;

    try {
        // Eliminar el registro
        $sql = "DELETE FROM distribucion_entes WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al eliminar el registro");
        }

        $stmt->close();

        return json_encode(["success" => "Registro eliminado correctamente"]);

    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    // Insertar datos
    if ($accion === "insert" && isset($data["arrayDatos"]) && isset($data["tipo"])) {
        $arrayDatos = $data["arrayDatos"];
        $tipo = $data["tipo"];
        echo guardarDistribucionEntes($arrayDatos, $tipo);
    
    // Actualizar datos
    } elseif ($accion === "update" && isset($data["id"]) && isset($data["arrayDatos"])) {
        $id = $data["id"];
        $arrayDatos = $data["arrayDatos"];
        echo actualizarDistribucionEntes($id, $arrayDatos);

    // Eliminar datos
    } elseif ($accion === "delete" && isset($data["id"])) {
        $id = $data["id"];
        echo eliminarDistribucionEntes($id);

    } else {
        echo json_encode(['error' => "Acción no válida o faltan datos"]);
    }
} else {
    echo json_encode(['error' => "No se recibió ninguna acción"]);
}

?>
