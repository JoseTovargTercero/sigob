<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

if (isset($_POST["tabla_empleados"])) {

    $tipo_filtro = $_POST['tipo_filtro'];
    $filtro = $_POST['filtro'];

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

    // Verificar si se recibe 'tabla_seleccionados'
    if (isset($_POST["tabla_seleccionados"])) {
        $ids = $_POST['ids'];
        if (!is_array($ids)) {
            echo json_encode("error: ids no es un arreglo");
            $conexion->close();
            exit();
        }

        // Construir la condición IN
        $ids_str = implode(',', array_map('intval', $ids));
        $filtro .= " AND id IN ($ids_str)";
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
        $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) AS antiguedad, otros_años, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_años AS anios_totales FROM empleados LIMIT 3";
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
                    "id" => $row["id"],
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
} elseif (isset($_POST["loadData"]) && $_POST["loadData"] == 'tabulador') {
    $tabuladores = array();
    $stmt = $conexion->prepare("SELECT * FROM `tabuladores`");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tabuladores[] = array(
                "id" => $row["id"],
                "nombre" => $row["nombre"]
            );
        }
    }
    $stmt->close();
    echo json_encode($tabuladores);

}elseif (isset($_POST["loadData"]) && $_POST["loadData"] == 'conceptos') {


    $conceptos = array();
    $stmt = $conexion->prepare("SELECT * FROM `conceptos`");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $conceptos[] = array(
                "id" => $row["id"],
                "nom_concepto" => $row["nom_concepto"],
                "cod_partida" => $row["cod_partida"],
                "tipo_calculo" => $row["tipo_calculo"],
                "tipo_concepto" => $row["tipo_concepto"],
                "valor" => $row["valor"]
            );
        }
    }
    $stmt->close();

    $conceptos_formulacion = array();
    $stmt = $conexion->prepare("SELECT * FROM `conceptos_formulacion`");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $conceptos_formulacion[] = array(
                "id" => $row["id"],
                "tipo_calculo" => $row["tipo_calculo"],
                "condicion" => $row["condicion"],
                "valor" => $row["valor"],
                "concepto_id" => $row["concepto_id"]
            );
        }
    }
    $stmt->close();
    
    $response = array(
        "data1" => $conceptos,
        "data2" => $conceptos_formulacion
    );
    
    
    echo json_encode($response);
}


$conexion->close();
