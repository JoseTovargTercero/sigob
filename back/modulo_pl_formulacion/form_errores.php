<?php
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
 ?>