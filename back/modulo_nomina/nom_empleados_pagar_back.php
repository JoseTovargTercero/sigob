<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

if (isset($_POST["select"])) {
    $data = array();
    $grupo = $_POST["grupo"];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas` WHERE grupo_nomina = ?");
    $stmt->bind_param('s', $grupo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $nombre_nomina = $row['nombre'];
            $frecuencia = $row['frecuencia'];
            $tipo = $row['tipo'];

            $concepto_valor_max = null;

            if ($frecuencia == 5 && $tipo == 2) {
                // Consulta para obtener las fechas de aplicar
                $stmt_conceptos = mysqli_prepare($conexion, "SELECT fecha_aplicar FROM `conceptos_aplicados` WHERE nombre_nomina = ?");
                $stmt_conceptos->bind_param('s', $nombre_nomina);
                $stmt_conceptos->execute();
                $result_conceptos = $stmt_conceptos->get_result();
                if ($result_conceptos->num_rows > 0) {
                    while ($row_conceptos = $result_conceptos->fetch_assoc()) {
                        // Decodificar el array de fecha_aplicar
                        $fechas = json_decode($row_conceptos['fecha_aplicar'], true);

                        if ($fechas && is_array($fechas)) {
                            // Tomar el valor más alto de las fechas
                            $valor_max_actual = max($fechas);
                            if ($concepto_valor_max === null || $valor_max_actual > $concepto_valor_max) {
                                $concepto_valor_max = $valor_max_actual;
                            }
                        }
                    }
                }
                $stmt_conceptos->close();
            }

            $data[] = array(
                'nombre' => $nombre_nomina,
                'frecuencia' => $frecuencia,
                'tipo' => $tipo,
                'concepto_valor_max' => $concepto_valor_max // Guardar el valor máximo
            );
        }
    }
    $stmt->close();

    echo json_encode($data);
}
?>
