<?php
header('Content-Type: application/json');

require_once '../sistema_global/conexion.php';

// Obtener los datos enviados desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);

$concepto_id = $data['concepto_id'];
$nom_concepto = $data['nom_concepto'];
$fecha_aplicar = json_encode($data['fecha_aplicar']);
$tipoCalculo = $data['formulacionConcepto']['TipoCalculo'];
$n_conceptos = isset($data['formulacionConcepto']['n_conceptos']) ? json_encode($data['formulacionConcepto']['n_conceptos']) : '[]';
$emp_cantidad = $data['formulacionConcepto']['emp_cantidad'];
$tabulador = isset($data['tabulador']) ? $data['tabulador'] : null;
$empleados = json_encode($data['empleados']);
$nominas_restar = json_encode($data['nominas_restar']);
$nombre_nomina = $data['nombre_nomina'];


// Verificar la existencia de la tabla en la base de datos
$table_name = 'conceptos_aplicados'; // Nombre de la tabla
$result = $conexion->query("SHOW TABLES LIKE '$table_name'");
$table_exists = $result->num_rows > 0;

// Si la tabla no existe, mostrar un mensaje de error y detener el proceso
if (!$table_exists) {
    echo json_encode(["status" => "error", "message" => "La tabla '$table_name' no existe en la base de datos"]);
    exit;
}

// Insertar los datos en la base de datos
$sql = "INSERT INTO conceptos_aplicados (concepto_id, nom_concepto, fecha_aplicar, tipo_calculo, n_conceptos, emp_cantidad, tabulador, empleados,nombre_nomina,nomina_restar)
        VALUES ('$concepto_id', '$nom_concepto', '$fecha_aplicar', '$tipoCalculo', '$n_conceptos', '$emp_cantidad', '$tabulador', '$empleados', '$nombre_nomina', '$nominas_restar')";

if ($conexion->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Registro guardado con éxito"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $sql . "<br>" . $conexion->error]);
}

$conexion->close();
?>
