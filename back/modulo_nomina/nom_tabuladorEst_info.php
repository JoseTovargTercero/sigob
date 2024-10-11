<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Obtener el ID de la URL
$id = $_GET['id'];

// Consulta SQL para obtener los datos de las tablas
$sql = "SELECT t.id, t.nombre, t.grados, t.pasos, t.aniosPasos, te.grado, te.paso, te.monto
        FROM tabuladores t
        INNER JOIN tabuladores_estr te ON t.id = te.tabulador_id
        WHERE t.id = $id";

$result = $conexion->query($sql);

// Verificar si la consulta fue exitosa
if ($result === false) {
    // Si hay un error en la consulta, mostrarlo y salir del script
    die("Error en la consulta: " . $conexion->error);
}

// Crear un array para almacenar los datos
$datos = array();

if ($result->num_rows > 0) {
    // Llenar el array con los datos obtenidos de la consulta
    while ($row = $result->fetch_assoc()) {
        $tabulador = array(
            "id" => $row["id"],
            "nombre" => $row["nombre"],
            "grados" => $row["grados"],
            "pasos" => $row["pasos"],
            "aniosPasos" => $row["aniosPasos"],
            "tabulador" => array(
                "grado" => $row["grado"],
                "paso" => $row["paso"],
                "monto" => $row["monto"]
            )
        );
        $datos[] = $tabulador;
    }
} else {
    echo "No se encontraron resultados.";
}

// Cerrar la conexión a la base de datos
$conexion->close();

// Pasar el array a la vista (puedes utilizar un archivo de vista o imprimir los datos aquí mismo)
header('Content-Type: application/json');
echo json_encode($datos);
?>
