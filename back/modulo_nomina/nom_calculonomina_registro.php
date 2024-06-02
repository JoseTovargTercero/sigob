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
if (!isset($data['nombre_nomina']) || !isset($data['empleados']) || !isset($data['total_pagar'])) {
    echo json_encode(array('error' => 'Faltan datos en el JSON recibido.'));
    exit();
}

// Obtener la frecuencia de la nómina desde la tabla nominas
$nombre_nomina = $data['nombre_nomina'];
$query_frecuencia = "SELECT frecuencia FROM nominas WHERE nombre = ?";
$stmt_frecuencia = $conexion->prepare($query_frecuencia);

// Verificar si la preparación de la consulta fue exitosa
if (!$stmt_frecuencia) {
    echo json_encode(array('error' => 'Error al preparar la consulta SQL: ' . $conexion->error));
    exit();
}

$stmt_frecuencia->bind_param("s", $nombre_nomina);
$stmt_frecuencia->execute();

// Verificar si ocurrió un error al ejecutar la consulta
if ($stmt_frecuencia->errno) {
    echo json_encode(array('error' => 'Error al ejecutar la consulta de la frecuencia: ' . $stmt_frecuencia->error));
    exit();
}

$result_frecuencia = $stmt_frecuencia->get_result();
$row_frecuencia = $result_frecuencia->fetch_assoc();

$frecuencia = $row_frecuencia['frecuencia'];

// Obtener el mes y el año actual
$mes_anio_actual = date('m-Y');

// Recorrer los arrays de empleados y total_pagar y registrar los datos en la tabla 'txt'
for ($i = 0; $i < count($data['empleados']); $i++) {
    $id_empleado = $data['empleados'][$i];
    $total_a_pagar = $data['total_pagar'][$i];

    // Verificar la frecuencia y dividir el total a pagar si es necesario
    switch ($frecuencia) {
        case 1:
            $pago_individual = $total_a_pagar / 4; // Dividir en 4 pagos
            for ($j = 1; $j <= 4; $j++) {
                $identificador = "s$j";
                registrarPago($conexion, $id_empleado, round($pago_individual, 2), $data['nombre_nomina'], $identificador, $mes_anio_actual);
            }
            break;
        case 2:
            $pago_individual = $total_a_pagar / 2; // Dividir en 2 pagos
            for ($j = 1; $j <= 2; $j++) {
                $identificador = "q$j";
                registrarPago($conexion, $id_empleado, round($pago_individual, 2), $data['nombre_nomina'], $identificador, $mes_anio_actual);
            }
            break;
        case 3:
        case 4:
            // Mantener el total a pagar sin cambios
            registrarPago($conexion, $id_empleado, round($total_a_pagar, 2), $data['nombre_nomina'], "unico", $mes_anio_actual);
            break;
        default:
            echo json_encode(array('error' => 'Frecuencia de pago no válida.'));
            exit();
    }
}

// Cerrar la conexión
$conexion->close();

// Enviar una respuesta exitosa
echo json_encode(array('success' => 'Datos registrados correctamente en la tabla txt.'));

// Función para registrar un pago en la tabla 'txt'
function registrarPago($conexion, $id_empleado, $total_a_pagar, $nombre_nomina, $identificador, $mes_anio_actual) {
    // Preparar la consulta SQL
    $sql = "INSERT INTO txt (id_empleado, total_a_pagar, nombre_nomina, identificador, fecha_pagar) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    // Verificar si la preparación de la consulta fue exitosa
    if (!$stmt) {
        echo json_encode(array('error' => 'Error al preparar la consulta SQL: ' . $conexion->error));
        exit();
    }

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("idsss", $id_empleado, $total_a_pagar, $nombre_nomina, $identificador, $mes_anio_actual);
    $stmt->execute();

    // Verificar si la consulta fue exitosa
    if ($stmt->affected_rows === 0) {
        echo json_encode(array('error' => 'Error al insertar datos en la tabla txt.'));
        exit();
    }

    // Cerrar la consulta
    $stmt->close();
}
?>
