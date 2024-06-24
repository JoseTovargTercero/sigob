<?php
require_once '../sistema_global/conexion.php';

// Verificar si se recibió un JSON válido
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(array('error' => 'No se recibió un JSON válido.'));
    exit();
}

// Verificar si el array contiene los datos necesarios
if (!isset($data['nombre_nomina']) || !isset($data['empleados']) || !isset($data['total_pagar']) || !isset($data['suma_asignaciones']) || !isset($data['suma_deducciones']) || !isset($data['suma_aportes']) || !isset($data['identificador'])) {
    echo json_encode(array('error' => 'Faltan datos en el JSON recibido.'));
    exit();
}

// Obtener la frecuencia de la nómina desde la tabla nominas
$nombre_nomina = $data['nombre_nomina'];
$identificador = $data['identificador'];
$query_frecuencia = "SELECT frecuencia FROM nominas WHERE nombre = ?";
$stmt_frecuencia = $conexion->prepare($query_frecuencia);

if (!$stmt_frecuencia) {
    echo json_encode(array('error' => 'Error al preparar la consulta SQL: ' . $conexion->error));
    exit();
}

$stmt_frecuencia->bind_param("s", $nombre_nomina);
$stmt_frecuencia->execute();

if ($stmt_frecuencia->errno) {
    echo json_encode(array('error' => 'Error al ejecutar la consulta de la frecuencia: ' . $stmt_frecuencia->error));
    exit();
}

$result_frecuencia = $stmt_frecuencia->get_result();
$row_frecuencia = $result_frecuencia->fetch_assoc();

$frecuencia = $row_frecuencia['frecuencia'];

// Obtener el mes y el año actual
$mes_anio_actual = date('m-Y');

// Obtener el último valor del correlativo desde la tabla 'txt'
$query_correlativo = "SELECT MAX(CAST(correlativo AS UNSIGNED)) AS ultimo_valor_correlativo FROM txt";
$result_correlativo = $conexion->query($query_correlativo);

if (!$result_correlativo) {
    echo json_encode(array('error' => 'Error al obtener el último valor del correlativo: ' . $conexion->error));
    exit();
}

$row_correlativo = $result_correlativo->fetch_assoc();
$ultimo_valor_correlativo = $row_correlativo['ultimo_valor_correlativo'];

// Incrementar el correlativo
$nuevo_correlativo = $ultimo_valor_correlativo + 1;
$correlativo_formateado = str_pad($nuevo_correlativo, 5, '0', STR_PAD_LEFT);

// Recorrer los arrays de empleados y total_pagar y registrar los datos en la tabla 'txt'
for ($i = 0; $i < count($data['empleados']); $i++) {
    $id_empleado = $data['empleados'][$i];
    $total_a_pagar = $data['total_pagar'][$i];

    // Verificar la frecuencia y dividir el total a pagar si es necesario
    switch ($frecuencia) {
        case 1:
                $pago_individual = $total_a_pagar; // Dividir en 4 pagos
                registrarPago($conexion, $id_empleado, round($pago_individual, 2), $data['nombre_nomina'], $identificador, $mes_anio_actual, $correlativo_formateado);
            break;
        case 2:
                $pago_individual = $total_a_pagar; // Dividir en 2 pagos
                registrarPago($conexion, $id_empleado, round($pago_individual, 2), $data['nombre_nomina'], $identificador, $mes_anio_actual, $correlativo_formateado);
            break;
        case 3:
        case 4:
            // Mantener el total a pagar sin cambios
            registrarPago($conexion, $id_empleado, round($total_a_pagar, 2), $data['nombre_nomina'], "unico", $mes_anio_actual, $correlativo_formateado);
            break;
        default:
            echo json_encode(array('error' => 'Frecuencia de pago no válida.'));
            exit();
    }
}

// Registrar los datos en la tabla 'peticiones'
$empleados_json = json_encode($data['empleados']);
$suma_asignaciones_json = json_encode($data['suma_asignaciones']);
$suma_deducciones_json = json_encode($data['suma_deducciones']);
$suma_aportes_json = json_encode($data['suma_aportes']);
$total_pagar_json = json_encode($data['total_pagar']);
$status = "0";
$creacion = Date('Y-m-d');

$sql_peticiones = "INSERT INTO peticiones (empleados, asignaciones, deducciones, aportes, total_pagar, correlativo, status, nombre_nomina, creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_peticiones = $conexion->prepare($sql_peticiones);

if (!$stmt_peticiones) {
    echo json_encode(array('error' => 'Error al preparar la consulta SQL para peticiones: ' . $conexion->error));
    exit();
}

$stmt_peticiones->bind_param("sssssssss", $empleados_json, $suma_asignaciones_json, $suma_deducciones_json, $suma_aportes_json, $total_pagar_json, $correlativo_formateado, $status, $nombre_nomina, $creacion);
$stmt_peticiones->execute();

if ($stmt_peticiones->affected_rows === 0) {
    echo json_encode(array('error' => 'Error al insertar datos en la tabla peticiones.'));
    exit();
}

// Cerrar la conexión
$conexion->close();

// Enviar una respuesta exitosa
echo json_encode(array('success' => 'Datos registrados correctamente en las tablas txt y peticiones.'));

// Función para registrar un pago en la tabla 'txt'
function registrarPago($conexion, $id_empleado, $total_a_pagar, $nombre_nomina, $identificador, $mes_anio_actual, $correlativo) {
    // Preparar la consulta SQL
    $sql = "INSERT INTO txt (id_empleado, total_a_pagar, nombre_nomina, identificador, fecha_pagar, correlativo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        echo json_encode(array('error' => 'Error al preparar la consulta SQL: ' . $conexion->error));
        exit();
    }

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("idssss", $id_empleado, $total_a_pagar, $nombre_nomina, $identificador, $mes_anio_actual, $correlativo);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo json_encode(array('error' => 'Error al insertar datos en la tabla txt.'));
        exit();
    }

    // Cerrar la consulta
    $stmt->close();
}
?>
