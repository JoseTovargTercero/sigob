<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
if (isset($_POST["tabla"])) {


    $sql = "SELECT dependencias.*, c.categoria_nombre, 
    (SELECT COUNT(*) FROM empleados WHERE empleados.id_dependencia = dependencias.id_dependencia) AS total_empleados
    FROM dependencias 
    LEFT JOIN categorias AS c ON c.id = dependencias.id_categoria
    ORDER BY id_categoria";

    $result = $conexion->query($sql);
    $valores = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $valores[] = $row;
        }
    }
    echo json_encode($valores, JSON_PRETTY_PRINT);
} elseif (isset($_POST["updates"])) {
    // Recibir y limpiar los datos de entrada
    $id_dependencia = $_POST["id_dependencia"];
    $empleados = $_POST["empleados"]; // Ya es un array, no hace falta usar json_decode




    $stmt = mysqli_prepare($conexion, "SELECT * FROM `dependencias` WHERE id_dependencia = ?");
    $stmt->bind_param('s', $id_dependencia);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id_categoria = $row['id_categoria'];
        }
    }
    $stmt->close();

    if ($id_categoria == '') {
        echo json_encode(['status' => 'error', 'mensaje' => 'La unidad no cuenta con una dependencia asociada']);
        exit;
    }





    // Verificar que $empleados no esté vacío
    if (!empty($empleados) && is_array($empleados)) {
        // Crear una lista separada por comas de los IDs de empleados
        $empleados_lista = implode(',', array_map('intval', $empleados)); // Convertir cada elemento a entero para mayor seguridad

        // Construir la consulta SQL con marcadores de posición
        $sql = "UPDATE empleados SET id_dependencia = ?, id_categoria = ? WHERE id IN ($empleados_lista)";

        // Preparar la sentencia
        $stmt = mysqli_prepare($conexion, $sql);

        if ($stmt) {
            // Ligar el parámetro (tipo de dato string, si es otro tipo ajustarlo a 'i', 'd', etc.)
            mysqli_stmt_bind_param($stmt, 'ss', $id_dependencia, $id_categoria);

            // Ejecutar la sentencia
            if (mysqli_stmt_execute($stmt)) {
                // Respuesta exitosa
                echo json_encode(['status' => 'success', 'mensaje' => 'Actualización exitosa.']);
            } else {
                // Error en la ejecución de la sentencia
                echo json_encode(['status' => 'error', 'mensaje' => 'Error al ejecutar la actualización: ' . mysqli_stmt_error($stmt)]);
            }

            // Cerrar la sentencia
            mysqli_stmt_close($stmt);
        } else {
            // Error en la preparación de la sentencia
            echo json_encode(['status' => 'error', 'mensaje' => 'Error en la preparación de la consulta: ' . mysqli_error($conexion)]);
        }
    } else {
        // No se proporcionaron empleados
        echo json_encode(['status' => 'error', 'mensaje' => 'No se proporcionaron empleados para actualizar.']);
    }
} elseif (isset($_POST["tabla_filtrada"])) {
    $id_dependencia = $_POST["id_dependencia"];


    $valores = [];
    $sql = "SELECT nombres, cedula, id FROM empleados WHERE id_dependencia='$id_dependencia'";
    $result = $conexion->query($sql);
    if ($result->num_rows > 0) {
        $valores = array();
        while ($row = $result->fetch_assoc()) {
            $valores[$row['id']] = $row;
        }
    }


    echo json_encode($valores, JSON_PRETTY_PRINT);
}

$conexion->close();
