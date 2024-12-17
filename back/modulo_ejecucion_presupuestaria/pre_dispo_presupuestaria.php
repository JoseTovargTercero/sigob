<?php
function consultarDisponibilidad($id_partida, $id_ejercicio, $monto)
{
    global $conexion;

    // Paso 1: Consultar la tabla distribucion_presupuestaria para validar id_partida e id_ejercicio
    $sqlDistribucion = "SELECT id, monto_actual FROM distribucion_presupuestaria WHERE id_partida = ? AND id_ejercicio = ?";
    $stmtDistribucion = $conexion->prepare($sqlDistribucion);
    $stmtDistribucion->bind_param("ii", $id_partida, $id_ejercicio);
    $stmtDistribucion->execute();
    $resultadoDistribucion = $stmtDistribucion->get_result();

    // Validar si se encontró un registro
    if ($resultadoDistribucion->num_rows === 0) {
        throw new Exception("No se encontró una distribución presupuestaria con el id_partida y id_ejercicio proporcionados");
    }

    // Obtener el registro de distribucion_presupuestaria
    $filaDistribucion = $resultadoDistribucion->fetch_assoc();
    $idDistribucion = $filaDistribucion['id'];

    // Paso 2: Consultar la tabla distribucion_entes usando el id_distribucion
    $sqlEntes = "SELECT distribucion FROM distribucion_entes WHERE id_ejercicio = ?";
    $stmtEntes = $conexion->prepare($sqlEntes);
    $stmtEntes->bind_param("i", $id_ejercicio);
    $stmtEntes->execute();
    $resultadoEntes = $stmtEntes->get_result();

    $montoItem = null;

    while ($filaEntes = $resultadoEntes->fetch_assoc()) {
        $distribuciones = json_decode($filaEntes['distribucion'], true);

        // Verificar si alguna distribución coincide con el id_distribucion
        foreach ($distribuciones as $distribucion) {
            if ($distribucion['id_distribucion'] == $idDistribucion) {
                $montoItem = $distribucion['monto'];
                break 2; // Salir del bucle si se encuentra
            }
        }
    }

    // Validar si se encontró el monto asociado
    if ($montoItem === null) {
        throw new Exception("No se encontró un registro en distribucion_entes que coincida con el id_distribucion y id_ejercicio proporcionados");
    }

    // Paso 3: Verificar que el monto asociado sea mayor o igual que el monto solicitado
    if ($montoItem < $monto) {
        return [
            'exito' => false,
            'monto_actual' => $montoItem
        ];
    } else {
        return [
            'exito' => true,
            'monto_actual' => $montoItem
        ];
    }
}
?>
