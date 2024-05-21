<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Construir la consulta SQL para insertar datos
$sql = "INSERT INTO empleados (nacionalidad, cedula, cod_empleado, nombres, fecha_ingreso, otros_años, status, observacion, cod_cargo, banco, cuenta_bancaria, hijos, instruccion_academica, discapacidades, tipo_cuenta, tipo_nomina, id_dependencia)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros y ejecutar la consulta
$stmt->bind_param("sssssissssssiiiii", $data["nacionalidad"], $data["cedula"], $data["cod_empleado"], $data["nombres"], $data["fecha_ingreso"], $data["otros_años"], $data["status"], $data["observacion"], $data["cod_cargo"], $data["banco"], $data["cuenta_bancaria"], $data["hijos"], $data["instruccion_academica"], $data["discapacidades"], $data["tipo_cuenta"], $data["tipo_nomina"], $data["id_dependencia"]);

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
