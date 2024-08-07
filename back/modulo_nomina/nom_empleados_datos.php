<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Consulta SQL para obtener los datos del empleado y su dependencia
$sql = "SELECT e.id, e.cedula, e.nombres, e.tipo_nomina, e.status, d.id_dependencia, d.dependencia, e.verificado
        FROM empleados AS e
        INNER JOIN dependencias AS d ON e.id_dependencia = d.id_dependencia";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

// Comprobar si la preparación de la declaración fue exitosa
if (!$stmt) {
    die("Error en la preparación de la declaración: " . $conexion->error);
}

// Vincular el parámetro y ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Crear un array para almacenar los datos
$datos = array();

if ($result->num_rows > 0) {
    // Llenar el array con los datos obtenidos de la consulta
    while ($row = $result->fetch_assoc()) {
        $empleado = array(
            "id_empleado" => $row["id"],
            "cedula" => $row["cedula"],
            "nombres" => $row["nombres"],
            "tipo_nomina" => $row["tipo_nomina"],
            "id_dependencia" => $row["id_dependencia"],
            "dependencia" => $row["dependencia"],
            "status" => $row['status'],
            "verificado" => $row["verificado"]
        );
        $datos[] = $empleado;
    }
} else {
    echo json_encode(["mensaje" => "No se encontraron resultados."]);
    exit();
}

// Cerrar la declaración
$stmt->close();

// Cerrar la conexión a la base de datos
$conexion->close();

// Pasar el array a la vista (puedes utilizar un archivo de vista o imprimir los datos aquí mismo)
header('Content-Type: application/json');
echo json_encode($datos);
?>