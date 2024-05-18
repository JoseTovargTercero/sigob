<?php
require_once '../sistema_global/conexion.php';

// Verificar si el método de solicitud es DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Verificar si el parámetro 'id' está presente en la URL
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Iniciar la transacción
        $conexion->begin_transaction();

        try {
            // Preparar la declaración SQL para eliminar de la tabla tabuladores
            $sql_tabuladores = "DELETE FROM tabuladores WHERE id = ?";
            $stmt_tabuladores = $conexion->prepare($sql_tabuladores);

            if (!$stmt_tabuladores) {
                throw new Exception("Error en la preparación de la declaración para tabuladores: " . $conexion->error);
            }

            // Vincular el parámetro y ejecutar la consulta para tabuladores
            $stmt_tabuladores->bind_param("i", $id);
            if (!$stmt_tabuladores->execute()) {
                throw new Exception("Error al eliminar el registro de tabuladores: " . $stmt_tabuladores->error);
            }

            // Preparar la declaración SQL para eliminar de la tabla tabuladores_estr
            $sql_tabuladores_estr = "DELETE FROM tabuladores_estr WHERE tabulador_id = ?";
            $stmt_tabuladores_estr = $conexion->prepare($sql_tabuladores_estr);

            if (!$stmt_tabuladores_estr) {
                throw new Exception("Error en la preparación de la declaración para tabuladores_estr: " . $conexion->error);
            }

            // Vincular el parámetro y ejecutar la consulta para tabuladores_estr
            $stmt_tabuladores_estr->bind_param("i", $id);
            if (!$stmt_tabuladores_estr->execute()) {
                throw new Exception("Error al eliminar el registro de tabuladores_estr: " . $stmt_tabuladores_estr->error);
            }

            // Si todo fue exitoso, se confirma la transacción
            $conexion->commit();
            echo "Registros eliminados correctamente.";

        } catch (Exception $e) {
            // En caso de error, se revierte la transacción
            $conexion->rollback();
            echo "Error al eliminar los registros: " . $e->getMessage();
        }

        // Cerrar las declaraciones
        $stmt_tabuladores->close();
        $stmt_tabuladores_estr->close();

    } else {
        echo "No se ha proporcionado un ID.";
    }
} else {
    echo "Método de solicitud no permitido.";
}

// Cerrar la conexión
$conexion->close();
?>
