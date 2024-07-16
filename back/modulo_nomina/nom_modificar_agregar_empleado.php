<?php
require_once '../sistema_global/conexion.php';

$precio_dolar = 36;


header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

/*
{
    "accion":"agregar_empleado",  # La acción a realizar
    "empleado":875, # el ID del empleado en la tabla 'empleados'
    "grupo_nomina":"3", # El grupo de nomina al que se esta haciendo el cambio
    "nominas":[
        {"nomina":"5","conceptos":["sueldo_base","21","24","25","26","27"]}, # se exceptuando los tipo 6
        {"nomina":"7","conceptos":["sueldo_base"]}
        ], # Las nominas alteradas. 
          # [nomina] = ID nomina en la tabla nominas.
          # [conceptos] = Todos los conceptos que se le aplicaran al empleado
    "info_reintegro":{ # En caso de que se requiera reintegro
        "reintegro":{
            "reintegro":"1", # 1 =  Si, 0 = No
            "datos":{
                "pagarDesde":"1", # 1 = pagar desde la fecha de ingreso, 2 = pagar desde la fechaEspecifica
                "fechaIngreso":"2022-11-21",
                "fechaEspecifica":""}
                }
                }
            }

            * Al recorrer cada nomina, se debe buscar los conceptos formulados (tipo_calculo == 6) y verificar si corresponde aplicarlos al empleado en cuestión

*/
if ($data['accion'] == "agregar_empleado") {
    $identificador = "Unico";
    $empleado = $data['empleado'];
    $empleado_id = $empleado; // Suponiendo que el ID del empleado está en esta clave
    $grupo_nomina = $data['grupo_nomina'];
    $nominas = $data['nominas'];
    $info_reintegro = $data['info_reintegro'];
    $info_reintegro2 = $info_reintegro['reintegro']['reintegro'];
    $info_reintegro3 = $info_reintegro['reintegro']['datos']['pagarDesde'];

    // Verificar que $nominas es un array
    if (is_array($nominas)) {
        // Array para almacenar los conceptos con sus nóminas
        $conceptos_ids = [];

        // Recorrer cada entrada en el array $nominas
        foreach ($nominas as $nomina) {
            if (isset($nomina['nomina']) && isset($nomina['conceptos'])) {
                // Obtener el nombre de la nómina
                $query = "SELECT nombre FROM nominas WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $nomina['nomina']);
                $stmt->execute();
                $stmt->bind_result($nombre_nomina);
                if ($stmt->fetch()) {
                    $conceptos_ids[] = array(
                        "nombre_nomina" => $nombre_nomina,
                        "conceptos" => $nomina['conceptos']
                    );
                }
                $stmt->close();
            }
        }

        // Aquí puedes continuar con las acciones que necesitas realizar con los nombres de las nóminas
        foreach ($conceptos_ids as $concepto) {
            $nombre_nomina = $concepto['nombre_nomina'];
            // Reiniciar el contador de meses
            $i = 0;
            // Buscar en la tabla conceptos_aplicados
            foreach ($concepto['conceptos'] as $concepto_id) {
                $query = "SELECT id, empleados FROM conceptos_aplicados WHERE concepto_id = ? AND nombre_nomina = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("is", $concepto_id, $nombre_nomina);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    // Procesar cada registro encontrado
                    $empleados_array = json_decode($row['empleados'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($empleados_array)) {
                        // Verificar si el empleado ya está en el array y que no sea null
                        if (!in_array((string)$empleado_id, $empleados_array) && $empleado_id !== null) {
                            $empleados_array[] = (string)$empleado_id; // Convertir a cadena
                            // Eliminar posibles valores null
                            $empleados_array = array_filter($empleados_array, function($value) {
                                return $value !== null;
                            });
                            // Codificar el array nuevamente en formato JSON con comillas dobles
                            $nuevo_empleados = json_encode(array_values($empleados_array), JSON_HEX_QUOT);
                            // Actualizar el campo empleados en la tabla conceptos_aplicados
                            $update_query = "UPDATE conceptos_aplicados SET empleados = ? WHERE id = ?";
                            $update_stmt = $conexion->prepare($update_query);
                            $update_stmt->bind_param("si", $nuevo_empleados, $row['id']);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error al decodificar el campo empleados para el registro con ID " . $row['id']]);
                    }
                }
                $stmt->close();
                
            }
        }

        // Verificar si el empleado ya existe en empleados_por_grupo para este grupo
        $exists_query = "SELECT id FROM empleados_por_grupo WHERE id_empleado = ? AND id_grupo = ?";
        $exists_stmt = $conexion->prepare($exists_query);
        $exists_stmt->bind_param("ii", $empleado_id, $grupo_nomina);
        $exists_stmt->execute();
        $exists_stmt->store_result();

        if ($exists_stmt->num_rows > 0) {
            echo json_encode([$info_reintegro2]);
            echo json_encode(["status" => "error", "mensaje" => "El empleado ya está registrado en este grupo"]);
        } else {
            // Insertar registro en la tabla empleados_por_grupo
            $status = 1; // Definir el valor de status
            $insert_query = "INSERT INTO empleados_por_grupo (id_empleado, id_grupo, status) VALUES (?, ?, ?)";
            $insert_stmt = $conexion->prepare($insert_query);
            $insert_stmt->bind_param("iii", $empleado_id, $grupo_nomina, $status);
            if ($insert_stmt->execute()) {
                if ($info_reintegro2 == 0) {
                    echo json_encode(["status" => "success", "mensaje" => "Registro insertado correctamente en empleados_por_grupo."]);
                } else {
                    if ($info_reintegro3 == 1) {
                        $info_reintegro4 = $info_reintegro['reintegro']['datos']['fechaIngreso'];
                    } else {
                        $info_reintegro4 = $info_reintegro['reintegro']['datos']['fechaEspecifica'];
                    }

                    $start_date = new DateTime($info_reintegro4);
                    $end_date = new DateTime(); // Fecha actual

                    $interval = $start_date->diff($end_date);
                    $total_months = ($interval->y * 12) + $interval->m;

                    foreach ($conceptos_ids as $concepto) {
                        $nombre_nomina = $concepto['nombre_nomina'];
                        $conceptos_aplicados = $concepto['conceptos'];

                        // Reiniciar el contador de meses
                        $i = 0;

                        for ($i = 0; $i <= $total_months; $i++) {
                            $start_date = new DateTime($info_reintegro4);
                            $start_date->modify('+' . $i . ' months');
                            $month = $start_date->format('m-Y');

                            // Datos a enviar a otro archivo
                            $data_to_send = [
                                'empleado_id' => $empleado_id,
                                'nombre' => $nombre_nomina,
                                'meses' => $month,
                                'conceptos_aplicados' => $conceptos_aplicados,
                                'info_reintegro' => $info_reintegro,
                                'precio_dolar' => $precio_dolar,
                                'conceptos_ids' => $concepto['conceptos'],
                                'identificador' => $identificador,
                            ];

                            // Usar cURL para enviar los datos a procesar_datos.php
                            $url = 'http://localhost/sigob/back/modulo_nomina/nom_calculo_reintegro.php';
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
                                echo $response;
                            }
                        }
                    }
                }
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al insertar el registro en empleados_por_grupo"]);
            }
        }
        $exists_stmt->close();
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Las nóminas no están en un formato válido"]);
    }
}











