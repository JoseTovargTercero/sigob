<?php
function consultarDisponibilidad($id_partida, $id_ejercicio, $monto)
{
    global $conexion;

    // Paso 2: Consultar la tabla distribucion_presupuestaria para validar el monto actual
    $sqlDistribucion = "SELECT monto_actual FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
    $stmtDistribucion = $conexion->prepare($sqlDistribucion);
    $stmtDistribucion->bind_param("ii", $id_partida, $id_ejercicio);
    $stmtDistribucion->execute();
    $resultadoDistribucion = $stmtDistribucion->get_result();

    // Validar si se encontró un registro
    if ($resultadoDistribucion->num_rows === 0) {
        throw new Exception("No se encontró una distribución presupuestaria con el id_partida y id_ejercicio proporcionados");
    }

    // Obtener el monto actual
    $filaDistribucion = $resultadoDistribucion->fetch_assoc();
    $monto_actual = $filaDistribucion['monto_actual'];

    // Paso 3: Verificar que el monto_actual sea mayor o igual que el monto solicitado
    if ($monto_actual < $monto) {
        return false;
    } else {
        return true;
    }
}
?>