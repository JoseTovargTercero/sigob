<?php
require_once '../sistema_global/conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empleado_id = $_POST['empleado_id'];
    $nombre = $_POST['nombre'];
    $meses = $_POST['meses'];
    $conceptos_aplicados = $_POST['conceptos_aplicados'];
    $info_reintegro = $_POST['info_reintegro'];
    $conceptos_ids = $_POST['conceptos_ids'];
    $precio_dolar = $_POST['precio_dolar'];
    $identificador = "Unico";
    // Convertir el array de conceptos en una cadena de parámetros para la consulta
        $placeholders = implode(',', array_fill(0, count($conceptos_aplicados), '?'));

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
            AND 
                ca.concepto_id IN ($placeholders)
        ";

        $stmtConceptos = $conexion->prepare($queryConceptos);

        // Verificar si la preparación de la consulta fue exitosa
        if (!$stmtConceptos) {
            echo json_encode(array('error' => 'Error al preparar la consulta de conceptos_aplicados: ' . $conexion->error));
            exit();
        }

        // Crear un array con los tipos de parámetros para bind_param
        $params = array_merge([$nombre], $conceptos_ids);

        // Crear los tipos de datos para bind_param
        $types = str_repeat('i', count($conceptos_ids)); // Suponiendo que los ids de los conceptos son enteros
        $types = 's' . $types; // Agregar 's' para el primer parámetro (nombre_nomina)

        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }

        // Vincular los parámetros
        call_user_func_array([$stmtConceptos, 'bind_param'], $bind_names);

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

    function calculoSalarioBase($conexion, $empleado, $nombre, $identificador) {
 
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_años AS antiguedad
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

        // Calcular el paso basándose en la antigüedad
        $antiguedad = $row['antiguedad'];

        if ($antiguedad > 15) {
            $paso = 15;
        } elseif ($antiguedad < 1) {
            $paso = 1;
        } else {
            $paso = $antiguedad;
        }
            
          
       
        

        // Consulta SQL para obtener el tabulador correspondiente al nombre_nomina
        $sqlTabulador = "SELECT tabulador FROM conceptos_aplicados WHERE nombre_nomina = ?";
        $stmtTabulador = $conexion->prepare($sqlTabulador);
        $stmtTabulador->bind_param("s", $nombre);
        $stmtTabulador->execute();
        $resultTabulador = $stmtTabulador->get_result();

        if ($resultTabulador === false) {
            echo "Error en la consulta: " . $conexion->error . "\n";
            return "No disponible";
        }

        if ($resultTabulador->num_rows > 0) {
            $rowTabulador = $resultTabulador->fetch_assoc();
            $tabulador = $rowTabulador["tabulador"];

            // Obtener el monto correspondiente a este empleado usando el tabulador
            $monto = obtenerMonto($conexion, $row["grado"], $paso, $tabulador, $identificador);

            return $monto;
        } else {
            return "No disponible";
        }
    } else {
        return "No disponible";
    }
}

// Función para obtener el monto del salario base
function obtenerMonto($conexion, $grado, $paso, $tabulador, $identificador) {
    // Consulta SQL para obtener el monto
    $grado = "G" . $grado; // Agregar el prefijo 'G' al grado
    $paso = "P" . $paso;   // Agregar el prefijo 'P' al paso

    // Encerrar los valores entre comillas
    $grado = $conexion->real_escape_string($grado);
    $paso = $conexion->real_escape_string($paso);
    $tabulador = $conexion->real_escape_string($tabulador);

    $sql = "SELECT monto FROM tabuladores_estr WHERE grado = ? AND paso = ? AND tabulador_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $grado, $paso, $tabulador);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        echo "Error en la consulta: " . $conexion->error . "\n";
        return "No disponible";
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
            $monto = $row["monto"];
        
        return $monto;
    } else {
        return "No disponible";
    }
}

// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto, $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador) {
    $sql = "SELECT c.tipo_calculo, c.valor
            FROM conceptos c
            JOIN conceptos_aplicados ca ON c.nom_concepto = ca.nom_concepto
            WHERE c.nom_concepto = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $nom_concepto); // Agregamos el identificador como segundo parámetro
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
                // Verificar conceptos adicionales en n_conceptos
                $sql_conceptos = "SELECT n_conceptos FROM conceptos_aplicados WHERE nom_concepto = ?";
                $stmt_conceptos = $conexion->prepare($sql_conceptos);
                $stmt_conceptos->bind_param("s", $nom_concepto);
                $stmt_conceptos->execute();
                $result_conceptos = $stmt_conceptos->get_result();

                if ($result_conceptos->num_rows > 0) {
                    $row_conceptos = $result_conceptos->fetch_assoc();
                    $n_conceptos = json_decode($row_conceptos['n_conceptos'], true);
                    $total_valor = 0;

                    foreach ($n_conceptos as $concepto_id) {
                        $sql_concepto = "SELECT nom_concepto, tipo_calculo, valor FROM conceptos WHERE id = ?";
                        $stmt_concepto = $conexion->prepare($sql_concepto);
                        $stmt_concepto->bind_param("i", $concepto_id);
                        $stmt_concepto->execute();
                        $result_concepto = $stmt_concepto->get_result();

                        if ($result_concepto->num_rows > 0) {
                            $row_concepto = $result_concepto->fetch_assoc();
                            $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $conceptos_aplicados);
                            $total_valor += $valor_concepto;
                        }
                    }

                    // Calcular el porcentaje del valor total
                    if ($valor < 100) {
                        return round($total_valor * ($valor / 100), 2);
                    } else {
                        echo "El valor del porcentaje no es válido.";
                        return 0;
                    }
                } else {
                    echo "No se encontraron conceptos adicionales.";
                    return 0;
                }
            case 6:
                // Obtener el ID del concepto
                $sql_conceptos = "SELECT id FROM conceptos WHERE nom_concepto = ?";
                $stmt_conceptos = $conexion->prepare($sql_conceptos);
                $stmt_conceptos->bind_param("s", $nom_concepto);
                $stmt_conceptos->execute();
                $result_conceptos = $stmt_conceptos->get_result();

                if ($result_conceptos->num_rows > 0) {
                    $row_conceptos = $result_conceptos->fetch_assoc();
                    $concepto_id = $row_conceptos['id'];

                    // Consultar en conceptos_formulacion usando el concepto_id
                    $sql_concepto_formulacion = "SELECT condicion, tipo_calculo, valor FROM conceptos_formulacion WHERE concepto_id = ?";
                    $stmt_concepto_formulacion = $conexion->prepare($sql_concepto_formulacion);
                    $stmt_concepto_formulacion->bind_param("i", $concepto_id);
                    $stmt_concepto_formulacion->execute();
                    $result_concepto_formulacion = $stmt_concepto_formulacion->get_result();

                    if ($result_concepto_formulacion->num_rows > 0) {
                        $row_concepto_formulacion = $result_concepto_formulacion->fetch_assoc();
                        $condicion = $row_concepto_formulacion['condicion']; // Se define aquí la variable $condicion
                        $tipo_calculo = $row_concepto_formulacion['tipo_calculo'];
                        $valor = $row_concepto_formulacion['valor'];

                        // Consultar en la tabla empleados con la condición proporcionada
                        foreach ($ids_empleados as $id_empleado) { // Iterar sobre cada ID de empleado
                            $sql_empleado = "SELECT id FROM empleados WHERE id = ? AND $condicion"; // Modificar la consulta para incluir la condición
                            $stmt_empleado = $conexion->prepare($sql_empleado);
                            $stmt_empleado->bind_param("i", $id_empleado);
                            $stmt_empleado->execute();
                            $result_empleado = $stmt_empleado->get_result();

                            if ($result_empleado->num_rows > 0) {
                                // Si el empleado cumple con la condición, proceder con el cálculo
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
                                        // Verificar conceptos adicionales en n_conceptos
                                        $sql_conceptos = "SELECT n_conceptos FROM conceptos_aplicados WHERE nom_concepto = ?";
                                        $stmt_conceptos = $conexion->prepare($sql_conceptos);
                                        $stmt_conceptos->bind_param("s", $nom_concepto);
                                        $stmt_conceptos->execute();
                                        $result_conceptos = $stmt_conceptos->get_result();

                                        if ($result_conceptos->num_rows > 0) {
                                            $row_conceptos = $result_conceptos->fetch_assoc();
                                            $n_conceptos = json_decode($row_conceptos['n_conceptos'], true);
                                            $total_valor = 0;

                                            foreach ($n_conceptos as $concepto_id) {
                                                $sql_concepto = "SELECT nom_concepto, tipo_calculo, valor FROM conceptos WHERE id = ?";
                                                $stmt_concepto = $conexion->prepare($sql_concepto);
                                                $stmt_concepto->bind_param("i", $concepto_id);
                                                $stmt_concepto->execute();
                                                $result_concepto = $stmt_concepto->get_result();

                                                if ($result_concepto->num_rows > 0) {
                                                    $row_concepto = $result_concepto->fetch_assoc();
                                                    $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador);
                                                    $total_valor += $valor_concepto;
                                                }
                                            }

                                            // Calcular el porcentaje del valor total
                                            if ($valor < 100) {
                                                return round($total_valor * ($valor / 100), 2);
                                            } else {
                                                echo "El valor del porcentaje no es válido.";
                                                return 0;
                                            }
                                        } else {
                                            echo "No se encontraron conceptos adicionales.";
                                            return 0;
                                        }
                                    default:
                                        echo "Tipo de cálculo no reconocido.";
                                        return 0;
                                }
                            }
                        }
                        return 0;
                    } else {
                        echo "No se encontraron datos en conceptos_formulacion.";
                        return 0;
                    }
                } else {
                    echo "No se encontró el concepto.";
                    return 0;
                }
            default:
                echo "Tipo de cálculo no reconocido.";
                return 0;
        }
    } else {
        // No hacer nada si no se encontró el identificador en fecha_aplicar
        return 0;
    }
}

// El resto del código permanece igual...



// Array asociativo para mantener un registro de empleados únicos
$empleados_unicos = array();
$status = 0;

// Array para almacenar asignaciones y deducciones
$asignaciones = array();
$deducciones = array();
$aportes = array();
// Arrays para almacenar las sumas de cada asignación, deducción y aporte
$suma_asignaciones = array();
$suma_deducciones = array();
$suma_aportes = array();

// Función para obtener los datos de un empleado por su ID
function obtenerEmpleadoPorID($conexion, $id_empleado) {
    $queryEmpleado = "SELECT * FROM empleados WHERE id = ?";
    $stmtEmpleado = $conexion->prepare($queryEmpleado);

    if (!$stmtEmpleado) {
        error_log("Error al preparar la consulta para obtener empleado: " . $conexion->error);
        return false;
    }

    $stmtEmpleado->bind_param("i", $id_empleado);
    $stmtEmpleado->execute();

    $resultEmpleado = $stmtEmpleado->get_result();

    if ($resultEmpleado->num_rows > 0) {
        return $resultEmpleado->fetch_assoc();
    } else {
        error_log("No se encontró empleado con ID: " . $id_empleado);
        return false;
    }
}


// Iterar sobre cada registro de conceptos_aplicados
foreach ($conceptos_aplicados as &$concepto) {
    // Obtener los IDs de empleados de este concepto
    $id_empleado = $empleado_id;

    // Clasificar los conceptos en asignaciones, deducciones o aportes
    if ($concepto['tipo_concepto'] === "A") {
        $asignaciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "D") {
        $deducciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "P") {
        $aportes[] = $concepto;
    }

    // Consultar la tabla 'empleados' para cada ID de empleado
        // Verificar si este empleado ya ha sido agregado
        if (!isset($empleados_unicos[$id_empleado])) {
            // Obtener los datos del empleado por su ID
            $empleado = obtenerEmpleadoPorID($conexion, $id_empleado);

            if ($empleado) {
                // Calcular el salario base del empleado
                $empleado['salario_base'] = calculoSalarioBase($conexion, $empleado, $nombre, $identificador);

                // Inicializar el salario integral con el salario base
                $empleado['salario_integral'] = $empleado['salario_base'];

                // Inicializar arrays para asignaciones, deducciones y aportes
                $empleado['asignaciones'] = array();
                $empleado['deducciones'] = array();
                $empleado['aportes'] = array();

                // Agregar el empleado al array de empleados únicos
                $empleados_unicos[$id_empleado] = $empleado;
            }
        }

        // Obtener el tipo de concepto
        $tipo_concepto = $concepto['tipo_concepto'];

        // Calcular el valor del concepto para este empleado
        $valor_concepto = obtenerValorConcepto($conexion, $concepto['nom_concepto'], $empleados_unicos[$id_empleado]['salario_base'], $precio_dolar, $empleados_unicos[$id_empleado]['salario_integral'], array($id_empleado), $identificador);

        // Agregar el valor del concepto al array correspondiente del empleado
        if ($tipo_concepto === "A") {
            $empleados_unicos[$id_empleado]['asignaciones'][] = array($concepto['nom_concepto'] => $valor_concepto);
            // Sumar al salario integral si no es el salario base
            if ($concepto['nom_concepto'] !== "salario_base") {
                $empleados_unicos[$id_empleado]['salario_integral'] += $valor_concepto;
            }

            // Sumar al array de sumas de asignaciones
            if (isset($suma_asignaciones[$concepto['nom_concepto']])) {
                $suma_asignaciones[$concepto['nom_concepto']] += $valor_concepto;
            } else {
                $suma_asignaciones[$concepto['nom_concepto']] = $valor_concepto;
            }
        } elseif ($tipo_concepto === "D") {
            $empleados_unicos[$id_empleado]['deducciones'][] = array($concepto['nom_concepto'] => $valor_concepto);

            // Sumar al array de sumas de deducciones
            if (isset($suma_deducciones[$concepto['nom_concepto']])) {
                $suma_deducciones[$concepto['nom_concepto']] += $valor_concepto;
            } else {
                $suma_deducciones[$concepto['nom_concepto']] = $valor_concepto;
            }
        } elseif ($tipo_concepto === "P") {
            $empleados_unicos[$id_empleado]['aportes'][] = array($concepto['nom_concepto'] => $valor_concepto);

            // Sumar al array de sumas de aportes
            if (isset($suma_aportes[$concepto['nom_concepto']])) {
                $suma_aportes[$concepto['nom_concepto']] += $valor_concepto;
            } else {
                $suma_aportes[$concepto['nom_concepto']] = $valor_concepto;
            }
        }
    
}
$id_empleados_detalles = array();
$total_a_pagar_empleados = array();
$informacion_empleados = array();
// Calcular el total a pagar para cada empleado y guardar en el array de recibos de pago
foreach ($empleados_unicos as &$empleado) {
    // Inicializar el total a pagar para este empleado con el salario base
    $total_a_pagar_empleado = $empleado['salario_base'];

    // Sumar las asignaciones
    foreach ($empleado['asignaciones'] as $asignacion) {
        foreach ($asignacion as $valor) {
            $total_a_pagar_empleado += $valor;
        }
    }

    // Restar las deducciones
    foreach ($empleado['deducciones'] as $deduccion) {
        foreach ($deduccion as $valor) {
            $total_a_pagar_empleado -= $valor;
        }
    }

    // Restar los aportes
    foreach ($empleado['aportes'] as $aporte) {
        foreach ($aporte as $valor) {
            $total_a_pagar_empleado -= $valor;
        }
    }

    // Almacenar el total a pagar para este empleado en el array del empleado
    $empleado['total_a_pagar'] = $total_a_pagar_empleado;



// Almacenar el ID del empleado y el total a pagar en los arrays correspondientes
 // Almacenar el total a pagar para este empleado en el array del empleado
    $empleado['total_a_pagar'] = $total_a_pagar_empleado;
    $informacion_empleados[] = $empleado;
    // Almacenar el ID del empleado y el total a pagar en los arrays correspondientes
    $id_empleados_detalles[] = $empleado['id'];
    $total_a_pagar_empleados[] = $total_a_pagar_empleado;
}

// Cerrar la conexión y preparar la respuesta con los resultados
$stmtConceptos->close();


$nombre_nomina = $nombre;


// Preparar la respuesta con los resultados
$response = array(
    'informacion_empleados' => $informacion_empleados,
    'nombre_nomina' => $nombre_nomina,
    'suma_asignaciones' => $suma_asignaciones,
    'suma_deducciones' => $suma_deducciones,
    'suma_aportes' => $suma_aportes,
    'identificador' => $identificador,
    'meses' => $meses,
);
$data = json_encode($response, true);
$data2 = json_decode($data, true);

// Acceder a los datos del primer empleado (suponiendo que solo hay uno en el array informacion_empleados)
$id = $data2['informacion_empleados'][0]['id'];
$salario_base = $data2['informacion_empleados'][0]['salario_base'];
$total_a_pagar = $data2['informacion_empleados'][0]['total_a_pagar'];
$suma_asignaciones_json = json_encode($suma_asignaciones);
$suma_deducciones_json = json_encode($suma_deducciones);
$suma_aportes_json = json_encode($suma_aportes);


// Verificar si la conexión a la base de datos está establecida
if ($conexion->connect_error) {
    die("La conexión a la base de datos falló: " . $conexion->connect_error);
}

// Preparar la consulta SQL
$sql_peticiones = "INSERT INTO historico_reintegros (id_empleado, sueldo_base, asignaciones, deducciones, aportes, total_pagar, nombre_nomina, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_peticiones = $conexion->prepare($sql_peticiones);

// Verificar si la preparación de la consulta SQL fue exitosa
if (!$stmt_peticiones) {
    die("Error al preparar la consulta SQL para historico_reintegros: " . $conexion->error);
}

// Enlazar parámetros y ejecutar la consulta
$stmt_peticiones->bind_param("ssssssss", $id, $salario_base, $suma_asignaciones_json, $suma_deducciones_json, $suma_aportes_json, $total_a_pagar, $nombre_nomina, $meses);
$stmt_peticiones->execute();

// Verificar si la inserción fue exitosa
if ($stmt_peticiones->affected_rows === 0) {
    echo json_encode(array('error' => 'Error al insertar datos en la tabla historico_reintegros.'));
    exit();
}

// Cerrar la consulta preparada y la conexión
$stmt_peticiones->close();
$conexion->close();

// Si todo salió bien, puedes devolver una respuesta de éxito o realizar más acciones si es necesario




    echo json_encode(["status" => "success", "mensaje" => "Datos procesados correctamente."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Método de solicitud no permitido."]);
}



?>