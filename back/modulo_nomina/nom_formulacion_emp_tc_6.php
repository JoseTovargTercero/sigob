<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Recibe los datos del concepto y empleados
    $concepto = $_POST['concepto'];
    $grupo = $_POST['grupo'];




    $empleados = [];


    
    $stmt = mysqli_prepare($conexion, "SELECT id_empleado FROM `empleados_por_grupo` WHERE id_grupo = ?");
    $stmt->bind_param('s', $grupo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($empleados, $row['id_empleado']);
        }
    }
    $stmt->close();




    // Asegúrate de que $empleados sea un array y no esté vacío
    if (!is_array($empleados) || empty($empleados)) {
        echo json_encode(array('error' => 'No se proporcionaron empleados válidos.'));
        exit();
    }

    // Escapa los IDs de empleados para prevenir inyección SQL
    $empleados = array_map('intval', $empleados);
    $empleados_list = implode(',', $empleados);

    // Consulta la tabla conceptos_formulacion
    $sql = "SELECT condicion, valor, tipo_calculo FROM conceptos_formulacion WHERE concepto_id = '$concepto'";
    $result = $conexion->query($sql);

    if ($result && $result->num_rows > 0) {
        // Obtiene las condiciones y valores del concepto
        $row = $result->fetch_assoc();
        $condicion = $row['condicion'];
        $valor = $row['valor'];

        // Consulta la tabla empleados utilizando las condiciones y el valor del concepto y restringiendo a los IDs proporcionados
        $sql_empleados = "SELECT id FROM empleados WHERE $condicion AND id IN ($empleados_list)";
        $result_empleados = $conexion->query($sql_empleados);

        if ($result_empleados->num_rows > 0) {
            // Si hay empleados que cumplen con las condiciones, devuelve los IDs
            $empleados_cumplen = array();
            while ($row_empleado = $result_empleados->fetch_assoc()) {
                $empleados_cumplen[] = $row_empleado['id'];
            }
            echo json_encode($empleados_cumplen);
        } else {
            // Si no hay empleados que cumplen con las condiciones, devuelve un mensaje de error
            echo json_encode(array('error' => 'No se encontraron empleados que cumplan con las condiciones.'));
        }
    } else {
        // Si no se encuentra el concepto en la tabla, devuelve un mensaje de error
        echo json_encode(array('error' => 'No se encontró el concepto en la base de datos.'));
    }

    // Cierra la conexión a la base de datos
    $conexion->close();
    exit(); // Termina la ejecución del script PHP después de procesar la petición AJAX
}

// Cierra la conexión a la base de datos (esto es redundante aquí y puede ser eliminado)
// $conexion->close();
?>
