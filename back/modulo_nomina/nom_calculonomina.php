<?php
require_once '../sistema_global/conexion.php';
$api_key = "4bfc66a740d312008475dded";
$url = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
$response = file_get_contents($url);
$data = json_decode($response, true);
$precio_dolar = $data['conversion_rate'];

// Función para calcular el salario base de un empleado
function calculoSalarioBase($conexion, $empleado) {
    // Consulta SQL con LEFT JOIN
    $sql = "SELECT empleados.*, cargos_grados.grado,
            TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_años AS paso
            FROM empleados
            LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
            WHERE empleados.id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $empleado['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        echo "Error en la consulta: " . $conexion->error . "\n";
        return "No disponible";
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Obtener el monto correspondiente a este empleado
        $monto = obtenerMonto($conexion, $row["grado"], $row["paso"]);

        return $monto;
    } else {
        return "No disponible";
    }
}

// Función para obtener el monto del salario base
function obtenerMonto($conexion, $grado, $paso) {
    // Consulta SQL para obtener el monto
    $grado = "G" . $grado; // Agregar el prefijo 'G' al grado
    $paso = "P" . $paso;   // Agregar el prefijo 'P' al paso

    // Encerrar los valores entre comillas
    $grado = $conexion->real_escape_string($grado);
    $paso = $conexion->real_escape_string($paso);

    $sql = "SELECT monto FROM tabuladores_estr WHERE grado = ? AND paso = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $grado, $paso);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        echo "Error en la consulta: " . $conexion->error . "\n";
        return "No disponible";
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["monto"];
    } else {
        return "No disponible";
    }
}

// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto, $salarioBase, $precio_dolar, $salarioIntegral) {
    $sql = "SELECT tipo_calculo, valor FROM conceptos WHERE nom_concepto = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $nom_concepto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_calculo = $row["tipo_calculo"];
        $valor = $row["valor"];

        // Calcular valor según el tipo de cálculo
        switch ($tipo_calculo) {
            case 1:
                return $valor;
            case 2:
                return round($precio_dolar * $valor, 2);
            case 3:
                if ($valor < 100) {
                    return round($salarioBase * ($valor / 100), 2);
                } else {
                    echo "El valor del porcentaje no es válido.";
                    return 0;
                }
            case 4:
                if ($valor < 100) {
                    return round($salarioIntegral * ($valor / 100), 2);
                } else {
                    echo "El valor del porcentaje no es válido.";
                    return 0;
                }
            case 5:
                return $valor / 2;
            case 6:
                return $valor * 2;
            default:
                echo "Tipo de cálculo no reconocido.";
                return 0;
        }
    } else {
        echo "No se encontró el concepto.";
        return 0;
    }
}

// Obtener el contenido JSON enviado en la solicitud POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar si el array contiene el nombre
if (!isset($data['nombre'])) {
    echo json_encode(array('error' => 'No se recibió el nombre en el array.'));
    exit();
}

$nombre = $data['nombre'];

// Consultar la tabla 'conceptos_aplicados' para obtener los registros con el mismo nombre_nomina
$queryConceptos = "
    SELECT 
        ca.*,
        c.tipo_concepto
    FROM 
        conceptos_aplicados ca
    JOIN
        conceptos c ON ca.concepto_id = c.id
    WHERE 
        ca.nombre_nomina = ?
";
$stmtConceptos = $conexion->prepare($queryConceptos);

// Verificar si la preparación de la consulta fue exitosa
if (!$stmtConceptos) {
    echo json_encode(array('error' => 'Error al preparar la consulta de conceptos_aplicados: ' . $conexion->error));
    exit();
}

$stmtConceptos->bind_param("s", $nombre);

// Verificar si ocurrió un error al vincular los parámetros
if ($stmtConceptos->errno) {
    echo json_encode(array('error' => 'Error al vincular parámetros de la consulta de conceptos_aplicados: ' . $stmtConceptos->error));
    exit();
}

$stmtConceptos->execute();

// Verificar si ocurrió un error al ejecutar la consulta
if ($stmtConceptos->errno) {
    echo json_encode(array('error' => 'Error al ejecutar la consulta de conceptos_aplicados: ' . $stmtConceptos->error));
    exit();
}

$resultConceptos = $stmtConceptos->get_result();
$conceptos_aplicados = $resultConceptos->fetch_all(MYSQLI_ASSOC);

// Array asociativo para mantener un registro de empleados únicos
$empleados_unicos = array();

// Array para almacenar asignaciones y deducciones
$asignaciones = array();
$deducciones = array();

// Variable para almacenar el total a pagar
$total_a_pagar = 0;

// Iterar sobre cada registro de conceptos_aplicados
foreach ($conceptos_aplicados as &$concepto) { // Añadir el & para permitir modificar directamente el array original
    // Verificar el tipo de concepto
    $tipo_concepto = $concepto['tipo_concepto'];

    // Si el tipo de concepto es "A" (Asignación)
    if ($tipo_concepto === "A") {
        $asignaciones[] = $concepto;
    }
    // Si el tipo de concepto es "D" (Deducción)
    elseif ($tipo_concepto === "D") {
        $deducciones[] = $concepto;
    }

    // Obtener los IDs de empleados de este concepto
    $ids_empleados = json_decode($concepto['empleados'], true);

    // Consultar la tabla 'empleados' para cada ID de empleado
    foreach ($ids_empleados as $id_empleado) {
        // Verificar si este empleado ya ha sido agregado
        if (!isset($empleados_unicos[$id_empleado])) {
            $queryEmpleado = "SELECT * FROM empleados WHERE id = ?";
            $stmtEmpleado = $conexion->prepare($queryEmpleado);

            if (!$stmtEmpleado) {
                echo json_encode(array('error' => 'Error al preparar la consulta de empleados: ' . $conexion->error));
                exit();
            }

            $stmtEmpleado->bind_param("i", $id_empleado);

            if ($stmtEmpleado->errno) {
                echo json_encode(array('error' => 'Error al vincular parámetros de la consulta de empleados: ' . $stmtEmpleado->error));
                exit();
            }

            $stmtEmpleado->execute();

            if ($stmtEmpleado->errno) {
                echo json_encode(array('error' => 'Error al ejecutar la consulta de empleados: ' . $stmtEmpleado->error));
                exit();
            }

            $resultEmpleado = $stmtEmpleado->get_result();
            $empleado = $resultEmpleado->fetch_assoc();

            // Calcular el salario base del empleado
            $empleado['salario_base'] = calculoSalarioBase($conexion, $empleado);

            // Inicializar el salario integral con el salario base
            $empleado['salario_integral'] = $empleado['salario_base'];

            // Agregar el empleado al array de empleados únicos
            $empleados_unicos[$id_empleado] = $empleado;

            $stmtEmpleado->close();
        }

        // Calcular el valor del concepto para este empleado
        $valor_concepto = obtenerValorConcepto($conexion, $concepto['nom_concepto'], $empleados_unicos[$id_empleado]['salario_base'], $precio_dolar, $empleados_unicos[$id_empleado]['salario_integral']);

        // Agregar el valor del concepto al array del empleado
        $empleados_unicos[$id_empleado][$concepto['nom_concepto']] = $valor_concepto;

        // Si el tipo de concepto es "A" y no es el salario base, sumarlo al salario integral
        if ($tipo_concepto === "A" && $concepto['nom_concepto'] !== "salario_base") {
            $empleados_unicos[$id_empleado]['salario_integral'] += $valor_concepto;
        }
    }
}

// Calcular el total a pagar para cada empleado
foreach ($empleados_unicos as &$empleado) {
    // Sumar el salario base con las asignaciones
    $total_a_pagar = $empleado['salario_base'];
    foreach ($asignaciones as $asignacion) {
        $valorAsignacion = obtenerValorConcepto($conexion, $asignacion['nom_concepto'], $empleado['salario_base'], $precio_dolar, $empleado['salario_integral']);
        $total_a_pagar += $valorAsignacion;
    }

    // Restar las deducciones
    foreach ($deducciones as $deduccion) {
        $valorDeduccion = obtenerValorConcepto($conexion, $deduccion['nom_concepto'], $empleado['salario_base'], $precio_dolar, $empleado['salario_integral']);
        $total_a_pagar -= $valorDeduccion;
    }

    // Almacenar el total a pagar en el array del empleado
    $empleado['total_a_pagar'] = $total_a_pagar;
}

// Cerrar la conexión
$stmtConceptos->close();
$conexion->close();
// Crear un nuevo array para almacenar el ID del empleado junto con el total a pagar
$total_a_pagar_empleados = array();

// Iterar sobre los empleados únicos
foreach ($empleados_unicos as $id_empleado => $empleado) {
    // Agregar el ID del empleado junto con el total a pagar al nuevo array
    $total_a_pagar_empleados[] = array(
        'id_empleado' => $id_empleado,
        'total_a_pagar' => $empleado['total_a_pagar']
    );
}


// Preparar la respuesta con los resultados
$response = array(
    'empleados' => array_values($empleados_unicos), // Reindexar el array para eliminar claves no numéricas
    'total_pagar' => $total_a_pagar_empleados,
);

// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);
