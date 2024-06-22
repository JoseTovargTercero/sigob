<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Construir la consulta SQL para insertar datos
$sql = "INSERT INTO empleados (nacionalidad, cedula, nombres, otros_años, status, observacion, cod_cargo, banco, cuenta_bancaria, hijos, instruccion_academica, discapacidades, tipo_nomina, id_dependencia, verificado, correcion, beca, fecha_ingreso)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conexion->error);
}

// Crear variables para valores constantes
$verificado = '0';
$correcion = NULL;

// Vincular parámetros y ejecutar la consulta
$stmt->bind_param("ssssssssssssssssss", $data["nacionalidad"], $data["cedula"], $data["nombres"], $data["otros_años"], $data["status"], $data["observacion"], $data["cod_cargo"], $data["banco"], $data["cuenta_bancaria"], $data["hijos"], $data["instruccion_academica"], $data["discapacidades"], $data["tipo_nomina"], $data["id_dependencia"], $verificado, $correcion, $data["beca"], $data["fecha_ingreso"]);

// Ejecutar la consulta preparada
if ($stmt->execute()) {
    echo "Datos insertados correctamente.";
} else {
    echo "Error al insertar datos: " . $stmt->error;
}

// Cerrar la declaración y la conexión
$stmt->close();
$conexion->close();
?>
