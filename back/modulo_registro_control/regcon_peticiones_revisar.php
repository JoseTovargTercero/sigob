<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

// Inicializar el valor del contador
$contador = 0;

try {
    // Consulta para contar los registros con status 0
    $sql = "SELECT COUNT(*) as contador FROM peticiones WHERE status = 0";
    $result = $conexion->query($sql);

    if ($result === false) {
        // Si hay un error en la consulta, mostrarlo y salir del script
        throw new Exception("Error en la consulta: " . $conexion->error);
    } else {
        $row = $result->fetch_assoc();
        $contador = (int) $row['contador'];
    }
} catch (\Exception $e) {
    // En caso de error, revertir la transacción
    $conexion->rollback();
    // Devolver una respuesta de error al cliente
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}

// Devolver el contador
echo json_encode($contador);
?>
