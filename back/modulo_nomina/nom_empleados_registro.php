<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Construir la consulta SQL para insertar datos
$sql = "INSERT INTO empleados (nacionalidad, Cedula, cod_empleado, nombres, fecha_ingreso, otros_años, status, observacion, cod_cargo, cargo, banco, cuenta_bancaria, hijos, instruccion_academica, discapacidades, becas)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros y ejecutar la consulta
$stmt->bind_param("iisssisssssisisi", $data["nacionalidad"], $data["Cedula"], $data["cod_empleado"], $data["nombres"], $data["fecha_ingreso"], $data["otros_años"], $data["status"], $data["observacion"], $data["cod_cargo"], $data["cargo"], $data["banco"], $data["cuenta_bancaria"], $data["hijos"], $data["instruccion_academica"], $data["discapacidades"], $data["becas"]);

// Ejecutar la consulta preparada
if ($stmt->execute()) {
    echo "Datos insertados correctamente.";
} else {
    echo "Error al insertar datos: " . $conexion->error;
}

// Cerrar la declaración y la conexión
$stmt->close();
$conexion->close();
?>
