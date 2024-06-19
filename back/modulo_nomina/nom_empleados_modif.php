<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Verificar si algún campo está vacío
foreach ($data as $key => $value) {
    if ($value == '') {
        echo "Error: el campo $key no puede estar vacío.";
        exit;
    }
}

// Verificar si el ID está presente y no está vacío
if (empty($data['id'])) {
    echo "Error: el campo id no puede estar vacío.";
    exit;
}



// verificar el status del empleado
$stmt_emp = $conexion->prepare("SELECT * FROM `empleados` WHERE id = ?");
$stmt_emp->bind_param('s', $data["id"]);
$stmt_emp->execute();
$result = $stmt_emp->get_result();
if ($row = $result->fetch_assoc()) {
    $verificado = ($row["verificado"] == '2') ? '0' : $row["verificado"];
}
$stmt_emp->close();



// Construir la consulta SQL para actualizar datos
$sql = "UPDATE empleados SET nacionalidad = ?, cedula = ?, nombres = ?, fecha_ingreso = ?, otros_años = ?, status = ?, observacion = ?, cod_cargo = ?, banco = ?, cuenta_bancaria = ?, hijos = ?, instruccion_academica = ?, discapacidades = ?, tipo_cuenta = ?, tipo_nomina = ?, id_dependencia = ?, verificado='$verificado' WHERE id = ?";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros y ejecutar la consulta
$stmt->bind_param(
    "ssssissssssiiiiis", 
    $data["nacionalidad"], 
    $data["cedula"], 
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
