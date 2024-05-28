<?php
require_once '../sistema_global/conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {


    // Recibe los datos del concepto y empleados
    $concepto = $_POST['concepto'];
    $empleados = $_POST['empleados'];

    // Consulta la tabla conceptos_formulacion
    $sql = "SELECT condicion, valor FROM conceptos_formulacion WHERE concepto_id = '$concepto'";
    $result = $conexion->query($sql);

    if ($result && $result->num_rows > 0) {
        // Obtiene las condiciones y valores del concepto
        $row = $result->fetch_assoc();
        $condicion = $row['condicion'];
        $valor = $row['valor'];

        // Consulta la tabla empleados utilizando las condiciones y el valor del concepto
        $sql_empleados = "SELECT * FROM empleados WHERE $condicion ";
        $result_empleados = $conexion->query($sql_empleados);

        if ($result_empleados->num_rows > 0) {
            // Si hay empleados que cumplen con las condiciones, devuelve los datos
            $empleados_cumplen = array();
            while ($row_empleado = $result_empleados->fetch_assoc()) {
                $empleados_cumplen[] = $row_empleado;
            }
            echo json_encode($empleados_cumplen);
        } else {
            // Si no hay empleados que cumplen con las condiciones, devuelve un mensaje de error
            echo "No se encontraron empleados que cumplan con las condiciones.";
        }
    } else {
        // Si no se encuentra el concepto en la tabla, devuelve un mensaje de error
        echo "No se encontró el concepto en la base de datos.";
    }

    // Cierra la conexión a la base de datos
    $conexion->close();
    exit(); // Termina la ejecución del script PHP después de procesar la petición AJAX
}


    // Cierra la conexión a la base de datos
    $conexion->close();

?>