<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Objeto recibido
$objeto = json_decode(file_get_contents('php://input'), true);

// Datos del objeto
$nombre = $objeto["nombre"];
$grados = $objeto["grados"];
$pasos = $objeto["pasos"];
$aniosPasos = $objeto["aniosPasos"];
$tabulador = $objeto["tabulador"];

// Insertar en la tabla tabuladores
$timestamp = date("Y-m-d H:i:s"); // Timestamp actual
$sql_tabuladores = "INSERT INTO tabuladores (nombre, grados, pasos, aniosPasos, timestamp) VALUES ('$nombre', '$grados', $pasos, $aniosPasos, '$timestamp')";

if ($conexion->query($sql_tabuladores) !== TRUE) {
    echo "Error al insertar datos en tabuladores: " . $conexion->error;
} else {
    // Obtener el ID insertado en tabuladores
    $tabuladores_id = $conexion->insert_id;

    // Insertar en la tabla tabuladores_estr
    foreach ($tabulador as $data) {
        $grado = $data[0];
        $paso = $data[1];
        $monto = $data[2];

        $sql_estr = "INSERT INTO tabuladores_estr (grado, paso, monto, tabulador_id) VALUES ('$grado', '$paso', $monto, $tabuladores_id)";

        if ($conexion->query($sql_estr) !== TRUE) {
            echo "Error al insertar datos en tabuladores_estr: " . $conexion->error;
        }
    }

    echo "Datos insertados correctamente.";
}

// Cerrar conexión
$conexion->close();
?>
