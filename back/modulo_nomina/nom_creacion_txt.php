<?php
require_once '../sistema_global/conexion.php';

// Obtener el contenido del cuerpo de la solicitud
$input = file_get_contents('php://input');

// Decodificar el JSON recibido
$data = json_decode($input, true);

// Validar que se hayan recibido los valores necesarios
if (isset($data['correlativo']) && isset($data['identificador'])) {
    // Consulta SQL para obtener los datos de la tabla txt
    $sql_txt = "SELECT id_empleado, total_a_pagar, identificador, correlativo
                FROM txt
                WHERE correlativo = ? AND identificador = ?";

    // Preparar la declaración SQL
    $stmt_txt = $conexion->prepare($sql_txt);

    // Comprobar si la preparación de la declaración fue exitosa
    if (!$stmt_txt) {
        die("Error en la preparación de la declaración: " . $conexion->error);
    }

    // Vincular los parámetros y ejecutar la consulta
    $stmt_txt->bind_param('ss', $data['correlativo'], $data['identificador']);
    $stmt_txt->execute();
    $result_txt = $stmt_txt->get_result();

    // Crear un array para almacenar los datos
    $empleados_ids = array();
    $txt_data = array();

    if ($result_txt->num_rows > 0) {
        // Llenar el array con los datos obtenidos de la consulta
        while ($row_txt = $result_txt->fetch_assoc()) {
            $empleados_ids[] = $row_txt["id_empleado"];
            $txt_data[$row_txt["id_empleado"]] = array(
                "total_a_pagar" => $row_txt["total_a_pagar"],
                "identificador" => $row_txt["identificador"],
                "correlativo" => $row_txt["correlativo"]
            );
        }
    } else {
        echo json_encode(["mensaje" => "No se encontraron resultados en la tabla txt."]);
        exit();
    }

    // Cerrar la declaración de txt
    $stmt_txt->close();

    // Si hay empleados encontrados, realizar la consulta a la tabla empleados
    if (!empty($empleados_ids)) {
        // Crear una lista de IDs separados por comas para la consulta IN
        $ids_list = implode(',', array_fill(0, count($empleados_ids), '?'));

        // Consulta SQL para obtener los datos de la tabla empleados
        $sql_empleados = "SELECT id, cedula,nacionalidad, nombres, banco, cuenta_bancaria, tipo_cuenta
                          FROM empleados
                          WHERE id IN ($ids_list)";

        // Preparar la declaración SQL
        $stmt_empleados = $conexion->prepare($sql_empleados);

        // Comprobar si la preparación de la declaración fue exitosa
        if (!$stmt_empleados) {
            die("Error en la preparación de la declaración: " . $conexion->error);
        }

        // Vincular los parámetros y ejecutar la consulta
        $stmt_empleados->bind_param(str_repeat('i', count($empleados_ids)), ...$empleados_ids);
        $stmt_empleados->execute();
        $result_empleados = $stmt_empleados->get_result();

        // Crear arrays para almacenar los datos agrupados por banco y las sumatorias
        $venezuela = array();
        $tesoro = array();
        $bicentenario = array();
        $caroni = array();
        $correlativo_venezuela = 0;
        $correlativo_tesoro = 0;
        $correlativo_caroni = 0;
        $sum_venezuela = 0;
        $sum_tesoro = 0;
        $sum_bicentenario = 0;
        $sum_caroni = 0;

        if ($result_empleados->num_rows > 0) {
            // Llenar los arrays con los datos obtenidos de la consulta
            while ($row_empleados = $result_empleados->fetch_assoc()) {
                $id_empleado = $row_empleados["id"];
                $empleado_data = array(
                    "cedula" => $row_empleados["cedula"],
                    "nacionalidad" => $row_empleados["nacionalidad"],
                    "nombres" => $row_empleados["nombres"],
                    "banco" => $row_empleados["banco"],
                    "cuenta_bancaria" => $row_empleados["cuenta_bancaria"],
                    "tipo_cuenta" => $row_empleados["tipo_cuenta"],
                    "total_a_pagar" => $txt_data[$id_empleado]["total_a_pagar"],
                    "identificador" => $txt_data[$id_empleado]["identificador"],
                    "correlativo" => $txt_data[$id_empleado]["correlativo"]
                );

                // Agrupar según el banco y sumar total_a_pagar
                switch ($row_empleados["banco"]) {
                    case "Venezuela":
                        $venezuela[] = $empleado_data;
                        $sum_venezuela += $txt_data[$id_empleado]["total_a_pagar"];
                        $correlativo_venezuela = $txt_data[$id_empleado]["correlativo"];

                        break;
                    case "Tesoro":
                        $tesoro[] = $empleado_data;
                        $sum_tesoro += $txt_data[$id_empleado]["total_a_pagar"];
                        $correlativo_tesoro = $txt_data[$id_empleado]["correlativo"];
                        break;
                    case "Bicentenario":
                        $bicentenario[] = $empleado_data;
                        $sum_bicentenario += $txt_data[$id_empleado]["total_a_pagar"];
                        break;
                    case "Caroni":
                        $caroni[] = $empleado_data;
                        $sum_caroni += $txt_data[$id_empleado]["total_a_pagar"];
                        $correlativo_caroni = $txt_data[$id_empleado]["correlativo"];
                        break;
                }
            }
        } else {
            echo json_encode(["mensaje" => "No se encontraron resultados en la tabla empleados."]);
            exit();
        }

        // Cerrar la declaración de empleados
        $stmt_empleados->close();

        // Cerrar la conexión a la base de datos
        $conexion->close();
        // Datos a enviar al archivo receptor
        $data_to_send = [
            "Venezuela" => [
                "empleados" => $venezuela,
                "total_a_pagar" => $sum_venezuela,
                "correlativo" => $correlativo_venezuela,
            ],
            "Tesoro" => [
                "empleados" => $tesoro,
                "total_a_pagar" => $sum_tesoro,
                "correlativo" => $correlativo_tesoro,
            ],
            "Bicentenario" => [
                "empleados" => $bicentenario,
                "total_a_pagar" => $sum_bicentenario
            ],
            "Caroni" => [
                "empleados" => $caroni,
                "total_a_pagar" => $sum_caroni,
                "correlativo" => $correlativo_caroni,
            ]
        ];

        // URL del archivo receptor
        $url = 'http://localhost/sigob/back/modulo_nomina/nom_creacion2_txt.php';

        // Convertir el array a formato JSON
        $json = json_encode($data_to_send);

        // Configurar las opciones de la solicitud
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $json
            )
        );

        // Crear el contexto de la solicitud
        $context = stream_context_create($options);

        // Realizar la solicitud HTTP POST
        $result = file_get_contents($url, false, $context);

        // Imprimir la respuesta del servidor receptor
        echo $result;

    } else {
        echo json_encode(["mensaje" => "No se encontraron empleados con los IDs proporcionados."]);
    }
} else {
    echo json_encode(['mensaje' => 'Datos insuficientes.']);
}
?>
