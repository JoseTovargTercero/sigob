<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Verificar si algún campo está vacío
foreach ($data as $key => $value) {
    if ($value !== '') {
        echo "Error: el campo $key no puede estar vacío.";
        exit;
    }
}

// Verificar si el ID está presente y no está vacío
if (empty($data['id'])) {
    echo "Error: el campo id no puede estar vacío.";
    exit;
}

// Construir la consulta SQL para actualizar datos
$sql = "UPDATE empleados SET nacionalidad = ?, cedula = ?, cod_empleado = ?, nombres = ?, fecha_ingreso = ?, otros_años = ?, status = ?, observacion = ?, cod_cargo = ?, banco = ?, cuenta_bancaria = ?, hijos = ?, instruccion_academica = ?, discapacidades = ?, tipo_cuenta = ?, tipo_nomina = ?, id_dependencia = ? WHERE id = ?";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros y ejecutar la consulta
$stmt->bind_param(
    "sssssissssisiiiiis", 
    $data["nacionalidad"], 
    $data["cedula"], 
    $data["cod_empleado"], 
    $data["nombres"], 
    $data["fecha_ingreso"], 
    $data["otros_años"], 
    $data["status"], 
    $data["observacion"], 
    $data["cod_cargo"], 
    $data["banco"], 
    $data["cuenta_bancaria"], 
    $data["hijos"], 
    $data["instruccion_academica"], 
    $data["discapacidades"], 
    $data["tipo_cuenta"], 
    $data["tipo_nomina"], 
    $data["id_dependencia"], 
    $data["id"]
);

// Ejecutar la consulta preparada
if ($stmt->execute()) {
    echo "Datos actualizados correctamente.";
} else {
    echo "Error al actualizar datos: " . $conexion->error;
}

// Cerrar la declaración y la conexión
$stmt->close();
$conexion->close();
?>
