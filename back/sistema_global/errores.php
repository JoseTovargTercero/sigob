<?php
// Función para registrar errores en la tabla error_log
function registrarError($descripcion) {
    global $conexion;

    try {
        $fechaHora = date('Y-m-d H:i:s');
        $sql = "INSERT INTO error_log (descripcion, fecha) VALUES (?, ?)";
        
        // Verificar si la consulta SQL se prepara correctamente
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("ss", $descripcion, $fechaHora);
            $stmt->execute();
            $stmt->close();
        } else {
            // Mostrar el error si la preparación falla
            echo "Error en la consulta SQL: " . $conexion->error;
        }
    } catch (Exception $e) {
        // Manejo de error si el registro de errores falla
        echo "Error al registrar el error: " . $e->getMessage();
    }
}

 ?>