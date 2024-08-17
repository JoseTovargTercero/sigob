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
if (!isset($data['nombre_nomina']) || !isset($data['empleados']) || !isset($data['total_pagar']) || !isset($data['suma_asignaciones']) || !isset($data['suma_deducciones']) || !isset($data['suma_aportes']) || !isset($data['identificador']) || !isset($data['recibos_pagos'])) {
    echo json_encode(array('error' => 'Faltan datos en el JSON recibido.'));
    exit();
}

// Obtener la frecuencia de la nómina desde la tabla nominas
$recibos_de_pago = $data['recibos_pagos'];
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
$status_archivos = "0";
$creacion = Date('m-Y');

$sql_peticiones = "INSERT INTO peticiones (empleados, asignaciones, deducciones, aportes, total_pagar, correlativo, status, nombre_nomina, creacion, identificador, status_archivos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_peticiones = $conexion->prepare($sql_peticiones);

if (!$stmt_peticiones) {
    echo json_encode(array('error' => 'Error al preparar la consulta SQL para peticiones: ' . $conexion->error));
    exit();
}

$stmt_peticiones->bind_param("sssssssssss", $empleados_json, $suma_asignaciones_json, $suma_deducciones_json, $suma_aportes_json, $total_pagar_json, $correlativo_formateado, $status, $nombre_nomina, $creacion, $identificador, $status_archivos);
$stmt_peticiones->execute();

if ($stmt_peticiones->affected_rows === 0) {
    echo json_encode(array('error' => 'Error al insertar datos en la tabla peticiones.'));
    exit();
}



// Registrar cada recibo de pago en la tabla 'recibo_pago'
foreach ($recibos_de_pago as $recibo) {
    $id_empleado = $recibo['id_empleado'];
    $sueldo_base = $recibo['sueldo_base'];
    $sueldo_integral = $recibo['sueldo_integral'];
    // Ejemplo con las variables $asignaciones, $deducciones y $aportes
$asignaciones =  substr(json_encode($recibo['asignaciones'], JSON_UNESCAPED_UNICODE), 1, -1);
$deducciones = substr(json_encode($recibo['deducciones'], JSON_UNESCAPED_UNICODE), 1, -1);
$aportes = substr(json_encode($recibo['aportes'], JSON_UNESCAPED_UNICODE), 1, -1);
// Función para combinar las claves y sumar los valores


// Aplicar la función a cada variable
$resultado_asignaciones = combinarDatos($asignaciones);
$resultado_deducciones = combinarDatos($deducciones);
$resultado_aportes = combinarDatos($aportes);

// Codificar los resultados combinados en formato JSON
$asignaciones_final = json_encode($resultado_asignaciones, JSON_UNESCAPED_UNICODE);
$deducciones_final = json_encode($resultado_deducciones, JSON_UNESCAPED_UNICODE);
$aportes_final = json_encode($resultado_aportes, JSON_UNESCAPED_UNICODE);



    $total_pagar = $recibo['total_a_pagar'];
    
    // Preparar la consulta SQL
    $sql_recibo_pago = "INSERT INTO recibo_pago (id_empleado, sueldo_base, sueldo_integral, asignaciones, deducciones, aportes, total_pagar, identificador, fecha_pagar, correlativo, nombre_nomina) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_recibo_pago = $conexion->prepare($sql_recibo_pago);

    if (!$stmt_recibo_pago) {
        echo json_encode(array('error' => 'Error al preparar la consulta SQL para recibo_pago: ' . $conexion->error));
        exit();
    }

    // Vincular parámetros y ejecutar la consulta
    $stmt_recibo_pago->bind_param("issssssssss", $id_empleado, $sueldo_base, $sueldo_integral, $asignaciones_final, $deducciones_final, $aportes_final, $total_pagar, $identificador, $mes_anio_actual, $correlativo_formateado, $nombre_nomina);
    $stmt_recibo_pago->execute();

    if ($stmt_recibo_pago->affected_rows === 0) {
        echo json_encode(array('error' => 'Error al insertar datos en la tabla recibo_pago.'));
        exit();
    }

    // Cerrar la consulta
    $stmt_recibo_pago->close();

}
function combinarDatos($datos) {
    $resultado = [];
    // Dividir la cadena en objetos individuales
    $objetos = explode('},{', $datos);

    foreach ($objetos as $objeto) {
        // Limpiar los corchetes al inicio y final de cada objeto
        $objeto = trim($objeto, '{}');
        // Decodificar el objeto JSON en un array asociativo
        $datos_array = json_decode('{' . $objeto . '}', true);

        // Combinar las claves y sumar los valores
        foreach ($datos_array as $concepto => $valor) {
            if (array_key_exists($concepto, $resultado)) {
                $resultado[$concepto] += $valor;
            } else {
                $resultado[$concepto] = $valor;
            }
        }
    }

    return $resultado;
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


































