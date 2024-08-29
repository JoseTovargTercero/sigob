<?php
require_once '../sistema_global/conexion.php';

$api_key = "4bfc66a740d312008475dded";
$url2 = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
$response2 = file_get_contents($url2);
$data2 = json_decode($response2, true);
$precio_dolar = $data2['conversion_rate'];



    $identificador = "fecha_unica";

$cedula_empleado = $_POST['cedula_empleado'];
$desde_cuando_pagas = $_POST['desde_cuando_pagas'];
$pagar_desde = $_POST['pagar_desde'];


// Escapar la variable para evitar inyecciones SQL
$cedula_empleado = $conexion->real_escape_string($cedula_empleado);

// Realizar la consulta a la tabla empleados
$query = "SELECT id FROM empleados WHERE cedula = '$cedula_empleado' LIMIT 1";
$resultado = $conexion->query($query);

// Obtener el id del empleado
if ($resultado && $resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $empleado_id = $fila['id'];
} else {
    // Manejar el caso donde no se encuentre un registro
    $empleado_id = null; // o cualquier valor que consideres apropiado
}



   



    if ($desde_cuando_pagas == 1) {
    // Realizar la consulta a la tabla movimientos
    $empleado_id = $conexion->real_escape_string($empleado_id);
    $query = "SELECT fecha_movimiento FROM movimientos WHERE id_empleado = '$empleado_id' AND accion = 'SUSPENDIÓ' ORDER BY fecha_movimiento DESC LIMIT 1";
    $resultado = $conexion->query($query);

    // Convertir la fecha a formato m-Y
    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $fecha_movimiento = $fila['fecha_movimiento'];
        $fecha = date('d-m-Y', strtotime($fecha_movimiento));
    } else {
        // Manejar el caso donde no se encuentre un registro
        $fecha = 'Fecha no encontrada';
    }

 
} else {
    $fecha = $pagar_desde;
}


// Inicializar el array para almacenar las nóminas y conceptos
$nominas = [];


// Realizar la consulta para obtener todos los registros de conceptos_aplicados donde el empleado_id esté en el array de empleados
$query = "SELECT nombre_nomina, concepto_id FROM conceptos_aplicados WHERE JSON_CONTAINS(empleados, '\"$empleado_id\"')";
$resultado = $conexion->query($query);

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $nombre_nomina = $fila['nombre_nomina'];
        $concepto_id = $fila['concepto_id'];

        // Excluir conceptos de tipo 6
        if (substr($concepto_id, 0, 1) == '6') {
            continue;
        }

        // Verificar si ya existe la nómina en el array
        $existe = false;
        foreach ($nominas as &$nomina) {
            if ($nomina['nomina'] == $nombre_nomina) {
                // Si ya existe, solo agregar el concepto_id
                $nomina['conceptos'][] = $concepto_id;
                $existe = true;
                break;
            }
        }

        // Si no existe, agregar una nueva entrada a nominas
        if (!$existe) {
            $nominas[] = [
                'nomina' => $nombre_nomina,
                'conceptos' => [$concepto_id]
            ];
        }
    }
}







                    $start_date = new DateTime($fecha);
                    $end_date = new DateTime(); // Fecha actual

                    $interval = $start_date->diff($end_date);
                    $total_months = ($interval->y * 12) + $interval->m;

                    foreach ($nominas as $concepto) {
    $nombre_nomina = $concepto['nomina'];
    $conceptos_aplicados = $concepto['conceptos'];

    // Reiniciar el contador de meses
    $i = 0;

    for ($i = 0; $i <= $total_months; $i++) {
        $start_date = new DateTime($fecha);
        $start_date->modify('+' . $i . ' months');
        $month = $start_date->format('m-Y');

        // Datos a enviar a otro archivo
        $data_to_send = [
            'empleado_id' => $empleado_id,
            'nombre' => $nombre_nomina,
            'meses' => $month,
            'conceptos_aplicados' => $conceptos_aplicados,
            'precio_dolar' => $precio_dolar,
            'conceptos_ids' => $concepto['conceptos'],
            'identificador' => $identificador,
        ];


                            // Usar cURL para enviar los datos a procesar_datos.php
                            $url = 'http://localhost/sigob/back/modulo_registro_control/regcon_calculo_reintegro.php';
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data_to_send));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            curl_close($ch);

                            // Manejar la respuesta
                            if ($response === false) {
                                echo json_encode(["status" => "error", "mensaje" => "Error al enviar datos a procesar_datos.php"]);
                            } else {
                                $status_response = 1;
                                
                            }

                    

    }
}
if ($status_response == "1") {
                        echo $response;
                    }
                
                
            












