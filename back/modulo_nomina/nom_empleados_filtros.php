<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
// Obtener precio del dólar
$api_key = "4bfc66a740d312008475dded";
$url = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
$response = file_get_contents($url);
$data = json_decode($response, true);
$precio_dolar = $data['conversion_rate'];

// Verificar que los datos se han enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método no permitido');
}

// Leer los datos JSON del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Verificar si los datos se han decodificado correctamente
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error al decodificar los datos JSON');
}

// Obtener los parámetros
$empleados = isset($data['empleados']) ? $data['empleados'] : null;
$concepto = isset($data['concepto']) ? $data['concepto'] : null;

// Verificar que los parámetros no sean nulos
if (is_null($empleados) || is_null($concepto) || !is_array($empleados) || empty($concepto)) {
    die("Faltan parámetros necesarios. empleados: " . json_encode($empleados) . ", concepto: " . json_encode($concepto));
}

// Consulta para obtener todas las condiciones del concepto
$sql = "SELECT condicion, tipo_calculo, valor FROM conceptos_formulacion WHERE concepto_id = ?";
$stmt = $conexion->prepare($sql);
if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conexion->error);
}
$stmt->bind_param("i", $concepto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Concepto no encontrado.");
}

// Inicializar contadores
$cantidad_empleados_cumplen = 0;
$cantidad_a_pagar = 0;

// Almacenar todas las condiciones y tipos de cálculo
$conceptos = [];
while ($row = $result->fetch_assoc()) {
    $conceptos[] = $row;
}

// Evaluar condiciones para cada empleado
foreach ($empleados as $empleado_id) {
    // Consulta para obtener datos del empleado
    $sql = "SELECT * FROM empleados WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $conexion->error);
    }
    $stmt->bind_param("i", $empleado_id);
    $stmt->execute();
    $empleado = $stmt->get_result()->fetch_assoc();

    if (!$empleado) {
        continue;
    }

    // Evaluar todas las condiciones para el empleado
    $cumple_alguna_condicion = false;
    foreach ($conceptos as $concepto) {
        $condiciones = $concepto['condicion'];
        $tipo_calculo = $concepto['tipo_calculo'];
        $valor = $concepto['valor'];

        // Evaluar condiciones utilizando la función evalua_condiciones
        if (evalua_condiciones($conexion, $empleado, $condiciones)) {
            $cumple_alguna_condicion = true;

            if ($tipo_calculo == '1') {
                $cantidad_a_pagar += $valor; // Aquí puedes añadir la lógica específica de cálculo si es necesario
            } elseif ($tipo_calculo == '2') {
                $cantidad_a_pagar += round($valor * $precio_dolar, 2);
            } elseif ($tipo_calculo == '3') {
                $salarioBase = calculoSalarioBase($conexion, $empleado_id);
                if ($valor < 100) {
                    $cantidad_a_pagar += round($salarioBase * ($valor / 100), 2);
                } else {
                    echo "El valor del porcentaje no es válido.";
                }
            }
        }
    }

    if ($cumple_alguna_condicion) {
        $cantidad_empleados_cumplen++;
    }
}

// Función para evaluar condiciones
function evalua_condiciones($conexion, $empleado, $condiciones)
{
    // Ejecutar consulta SQL dinámica con las condiciones proporcionadas
    $sql = "SELECT COUNT(*) AS count FROM empleados WHERE id = ? AND {$condiciones}";
    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $conexion->error);
    }
    $stmt->bind_param("i", $empleado['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['count'] > 0;
}

// Función para calcular el salario base
function calculoSalarioBase($conexion, $empleado_id)
{
    // Consulta SQL con LEFT JOIN
    $sql = "SELECT empleados.*, cargos_grados.grado,
            TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_anios AS paso
            FROM empleados
            LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
            WHERE empleados.id = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $conexion->error);
    }
    $stmt->bind_param("i", $empleado_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        die('Error en la consulta: ' . $conexion->error);
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $grado = $row["grado"];
        $paso = $row["paso"];

        // Obtener el monto correspondiente a este empleado
        $monto = obtenerMonto($conexion, $grado, $paso);

        return $monto;
    } else {
        return "No disponible";
    }
}

function obtenerMonto($conexion, $grado, $paso)
{
    // Agregar el prefijo 'G' al grado
    $grado = "G" . $grado;
    // Agregar el prefijo 'P' al paso
    $paso = "P" . $paso;
    // Consulta SQL para obtener el monto
    $sql = "SELECT monto FROM tabuladores_estr WHERE grado = ? AND paso = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $conexion->error);
    }
    $stmt->bind_param("ss", $grado, $paso);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        die('Error en la consulta: ' . $conexion->error);
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["monto"];
    } else {
        return "No disponible";
    }
}

// Respuesta JSON
$response = array(
    "cantidad_empleados_cumplen" => $cantidad_empleados_cumplen,
    "cantidad_a_pagar" => $cantidad_a_pagar,
    "tipo_calculo" => isset($tipo_calculo) ? $tipo_calculo : null
);

header('Content-Type: application/json');
echo json_encode($response);

$conexion->close();
