<?php

require_once '../sistema_global/conexion.php';

// Verificar si el parámetro 'id' está presente en la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // sacar de las nominas al que pertenece 
    $stmt = mysqli_prepare($conexion, "SELECT *, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_años AS antiguedad_total FROM empleados WHERE id = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $empleado = $result->fetch_assoc();
    }
    $stmt->close();
    
    // insertar en empleados_pasados
    $stmt = $conexion->prepare("INSERT INTO empleados_pasados (nacionalidad, cedula, nombres, otros_años, status, observacion, cod_cargo, banco, cuenta_bancaria, hijos, instruccion_academica, discapacidades, tipo_nomina, id_dependencia, verificado, correcion, beca, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssssssss", 
        $empleado['nacionalidad'], 
        $empleado['cedula'], 
        $empleado['nombres'], 
        $empleado['antiguedad_total'], 
        $empleado['status'], 
        $empleado['observacion'], 
        $empleado['cod_cargo'], 
        $empleado['banco'], 
        $empleado['cuenta_bancaria'], 
        $empleado['hijos'], 
        $empleado['instruccion_academica'], 
        $empleado['discapacidades'], 
        $empleado['tipo_nomina'], 
        $empleado['id_dependencia'], 
        $empleado['verificado'], 
        $empleado['correcion'], 
        $empleado['beca'], 
        $empleado['fecha_ingreso']
    );
    $stmt->execute();
    $stmt->close();
    
    exit(); // TODO: QUITAR AL FINALIZAR


    // Preparar la declaración SQL para eliminar el registro
    $sql = "DELETE FROM empleados WHERE id = ?";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Comprobar si la preparación de la declaración fue exitosa
    if (!$stmt) {
        die("Error en la preparación de la declaración: " . $conexion->error);
    }

    // Vincular el parámetro y ejecutar la consulta
    $stmt->bind_param("i", $id);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        echo "Registro eliminado correctamente.";
    } else {
        echo "Error al eliminar el registro: " . $stmt->error;
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    echo "No se ha proporcionado un ID.";
}

// Cerrar la conexión
$conexion->close();
?>
