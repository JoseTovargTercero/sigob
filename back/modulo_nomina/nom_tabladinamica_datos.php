<?php
require_once '../sistema_global/conexion.php';

header('Content-Type: application/json');

// Verificar si la conexión se ha realizado correctamente
if ($conexion->connect_error) {
    echo json_encode("error de conexión a la base de datos");
    exit();
}

// Obtener datos POST
$data = json_decode(file_get_contents('php://input'), true);
$tipo_filtro = isset($data['tipo_filtro']) ? $data['tipo_filtro'] : '';
$filtro = isset($data['filtro']) ? $data['filtro'] : '';

// Verificar si se proporcionó el tipo de filtro
if (empty($tipo_filtro)) {
    echo json_encode("error: tipo de filtro no especificado");
    $conexion->close();
    exit();
}

// Palabras clave prohibidas
$palabras_prohibidas = array('UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'INSERT', 'ALTER', 'GRANT', 'REVOKE');

// Verificar si el filtro contiene palabras clave prohibidas
foreach ($palabras_prohibidas as $palabra) {
    if (stripos($filtro, $palabra) !== false) {
        echo json_encode("PROHIBIDO");
        $conexion->close();
        exit();
    }
}

// Inicializar la consulta SQL
$sql = "";
$params = array();

if ($tipo_filtro == 3) {
    // Pendiente: Mostrar empleados de otras nóminas
    echo json_encode("Pendiente");
    $conexion->close();
    exit();
} elseif ($tipo_filtro == 2) {
    // Analizar el filtro para determinar las condiciones
    if (preg_match('/^antiguedad([<>]=?)(\d+)$/', $filtro, $matches)) {
        $operator = $matches[1];
        $anios_antiguedad = (int)$matches[2];
        $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) AS antiguedad, 0 AS otros_años, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) AS anios_totales FROM empleados WHERE TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) $operator ?";
        $params[] = $anios_antiguedad;
    } elseif (preg_match('/^antiguedad_total([<>]=?)(\d+)$/', $filtro, $matches)) {
        $operator = $matches[1];
        $anios_total = (int)$matches[2];
        $sql = "SELECT *, 0 AS antiguedad, otros_años, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_años AS anios_totales FROM empleados WHERE TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_años $operator ?";
        $params[] = $anios_total;
    } else {
        $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) AS antiguedad, otros_años, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_años AS anios_totales FROM empleados WHERE $filtro";
    }
} else {
    // Todos los empleados
    $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) AS antiguedad, otros_años, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_años AS anios_totales FROM empleados";
}

// Depuración: Mostrar la consulta y los parámetros
error_log("SQL: $sql");
error_log("Params: " . implode(", ", $params));

// Preparar y ejecutar la consulta
if (!empty($sql)) {
    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        error_log("Error en prepare: " . $conexion->error);
        echo json_encode("error en la preparación de la consulta");
        $conexion->close();
        exit();
    }

    // Bind parameters si existen
    if (!empty($params)) {
        $types = str_repeat('i', count($params)); // Todos los parámetros son tratados como enteros
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        echo json_encode("error en la consulta SQL");
    } else {
        $empleados = array();
        while ($row = $result->fetch_assoc()) {
            // Calcular años actuales
            if ($row["otros_años"] !== null) {
                $anios_actuales = $row["anios_totales"] - $row["otros_años"];
            } else {
                $anios_actuales = $row["antiguedad"];
            }

            $empleados[] = array(
                "nacionalidad" => $row["nacionalidad"],
                "cedula" => $row["cedula"],
                "cod_empleado" => $row["cod_empleado"],
                "nombres" => $row["nombres"],
                "fecha_ingreso" => $row["fecha_ingreso"],
                "anios_actuales" => $anios_actuales,
                "otros_anios" => $row["otros_años"],
                "anios_totales" => $row["anios_totales"],
                "status" => $row["status"],
                "observacion" => $row["observacion"],
                "cod_cargo" => $row["cod_cargo"],
                "hijos" => $row["hijos"],
                "instruccion_academica" => $row["instruccion_academica"],
                "discapacidades" => $row["discapacidades"],
                "id_dependencia" => $row["id_dependencia"]
            );
        }
        
        echo json_encode($empleados);
    }

    $stmt->close();
} else {
    echo json_encode("error: consulta SQL vacía");
}

$conexion->close();
?>
