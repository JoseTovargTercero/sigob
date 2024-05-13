<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Objeto recibido
$objeto = json_decode(file_get_contents('php://input'), true);

// Datos del objeto
$id = $objeto["id"];
$nombre = $objeto["nombre"];
$grados = $objeto["grados"];
$pasos = $objeto["pasos"];
$aniosPasos = $objeto["aniosPasos"];
$tabulador = $objeto["tabulador"];

// Modificar en la tabla tabuladores
$sql_tabuladores = "UPDATE tabuladores SET nombre = '$nombre', grados = '$grados', pasos = $pasos, aniosPasos = $aniosPasos WHERE id = $id";

if ($conexion->query($sql_tabuladores) !== TRUE) {
    echo "Error al modificar datos en tabuladores: " . $conexion->error;
} else {
    // Eliminar los registros existentes en tabuladores_estr para este tabulador
    $sql_delete = "DELETE FROM tabuladores_estr WHERE tabulador_id = $id";
    if ($conexion->query($sql_delete) === FALSE) {
        echo "Error al eliminar registros existentes en tabuladores_estr: " . $conexion->error;
    } else {
        // Insertar los nuevos registros en tabuladores_estr
        foreach ($tabulador as $data) {
            $grado = $data["grado"];
            $paso = $data["paso"];
            $monto = $data["monto"];

            $sql_estr = "INSERT INTO tabuladores_estr (grado, paso, monto, tabulador_id) VALUES ('$grado', '$paso', $monto, $id)";

            if ($conexion->query($sql_estr) !== TRUE) {
                echo "Error al insertar datos en tabuladores_estr: " . $conexion->error;
            }
        }

        echo "Datos modificados correctamente.";
    }
}

// Cerrar conexión
$conexion->close();
?>