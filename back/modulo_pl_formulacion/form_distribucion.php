<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

// Función para registrar errores en la tabla error_log
function registrarError($descripcion) {
    global $conexion;

    try {
        $fechaHora = date('Y-m-d H:i:s');
        $sql = "INSERT INTO error_log (descripcion, fecha_hora) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $descripcion, $fechaHora);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // Manejo de error si el registro de errores falla
    }
}

// Función para insertar datos en la tabla distribucion_presupuestaria
function guardarDistribucionPresupuestaria($dataArray) {
    global $conexion;

    try {
        // Verificar que el array no esté vacío
        if (empty($dataArray)) {
            throw new Exception("El array de datos está vacío");
        }

        // Recorrer el array y almacenar los datos en la tabla distribucion_presupuestaria
        foreach ($dataArray as $registro) {
            // Verificar que el array tenga el formato correcto
            if (count($registro) !== 3) {
                throw new Exception("El formato del array no es válido");
            }

            // Descomponer los valores del array
            $id_partida = $registro[0];
            $monto = $registro[1];
            $id_ejercicio = $registro[2];

            // Validar que los campos no estén vacíos
            if (empty($id_partida) || empty($monto) || empty($id_ejercicio)) {
                throw new Exception("Faltan datos en uno de los registros (id_partida, monto, id_ejercicio)");
            }

            // Insertar los datos en la tabla
            $sql = "INSERT INTO distribucion_presupuestaria (id_partida, monto, id_ejercicio) VALUES (?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("isi", $id_partida, $monto, $id_ejercicio);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                throw new Exception("Error al insertar en distribucion_presupuestaria");
            }

            $stmt->close();
        }

        return json_encode(["success" => "Datos de distribución presupuestaria guardados correctamente"]);

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["arrayDatos"])) {
    $arrayDatos = $data["arrayDatos"];

    // Llamar a la función para guardar los datos en la tabla distribucion_presupuestaria
    echo guardarDistribucionPresupuestaria($arrayDatos);
} else {
    echo json_encode(['error' => "No se recibieron datos para almacenar"]);
}

