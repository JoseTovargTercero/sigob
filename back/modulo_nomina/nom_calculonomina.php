<?php
require_once '../sistema_global/conexion.php';
$api_key = "4bfc66a740d312008475dded";
$url = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
$response = file_get_contents($url);
$data = json_decode($response, true);
$precio_dolar = $data['conversion_rate'];

function calculoSalarioBase($conexion, $empleado, $nombre, $identificador) {
    // Consulta SQL con LEFT JOIN
    if ($identificador == "s1") {
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
} elseif ($identificador == "s2") {
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 14 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
} elseif ($identificador == "s3") {
   $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 21 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
} elseif ($identificador == "s4") {
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 28 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
}elseif ($identificador == "q1") {
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 15 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
}elseif ($identificador == "q2") {
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 30 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
}else{
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
}

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
        if ($identificador == "s1" OR $identificador == "s2" OR $identificador == "s3" OR $identificador == "s4") {
            $monto2 = $row["monto"];
            $monto = round($monto2*0.25,2);
        }elseif ($identificador == "q1" OR $identificador == "q2") {
            $monto2 = $row["monto"];
            $monto = round($monto2*0.50,2);
        }else{
            $monto = $row["monto"];
        }
        
        return $monto;
    } else {
        return "No disponible";
    }
}

// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto, $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador) {



    $sql = "SELECT tipo_calculo, valor FROM conceptos WHERE nom_concepto = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $nom_concepto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_calculo = $row["tipo_calculo"];
        if ($identificador == "s1" OR $identificador == "s2" OR $identificador == "s3" OR $identificador == "s4") {
            $valor2 = $row["valor"];
            $valor = round($valor2*0.25,2);
        }elseif ($identificador == "q1" OR $identificador == "q2") {
            $valor2 = $row["valor"];
            $valor = round($valor2*0.50,2);
        }else{
            $valor = $row["valor"];
        }

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
                            $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, 0);
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
                            $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, 0, $identificador);
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
$identificador = $data['identificador'];


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
$aportes = array();

// Arrays para almacenar las sumas de cada asignación y deducción
$suma_asignaciones = array();
$suma_deducciones = array();
$suma_aportes = array();

// Variable para almacenar el total a pagar
$total_a_pagar = 0;

// Función para obtener los datos de un empleado por su ID
function obtenerEmpleadoPorID($conexion, $id_empleado, $identificador) {
    $queryEmpleado = "SELECT * FROM empleados WHERE id = ?";
    $stmtEmpleado = $conexion->prepare($queryEmpleado);

    if (!$stmtEmpleado) {
        return false;
    }

    $stmtEmpleado->bind_param("i", $id_empleado);
    $stmtEmpleado->execute();

    $resultEmpleado = $stmtEmpleado->get_result();

    if ($resultEmpleado->num_rows > 0) {
        return $resultEmpleado->fetch_assoc();
    } else {
        return false;
    }
}

// Iterar sobre cada registro de conceptos_aplicados
foreach ($conceptos_aplicados as &$concepto) {
    // Obtener los IDs de empleados de este concepto
    $ids_empleados = json_decode($concepto['empleados'], true);

    // Clasificar los conceptos en asignaciones o deducciones
    if ($concepto['tipo_concepto'] === "A") {
        $asignaciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "D") {
        $deducciones[] = $concepto;
    }elseif($concepto['tipo_concepto'] === "P") {
        $aportes[] = $concepto;
    }

    // Consultar la tabla 'empleados' para cada ID de empleado
    foreach ($ids_empleados as $id_empleado) {
        // Verificar si este empleado ya ha sido agregado
        if (!isset($empleados_unicos[$id_empleado])) {
            // Obtener los datos del empleado por su ID
            $empleado = obtenerEmpleadoPorID($conexion, $id_empleado,$identificador);

            if ($empleado) {
                // Calcular el salario base del empleado
                $empleado['salario_base'] = calculoSalarioBase($conexion, $empleado,$nombre, $identificador);

                // Inicializar el salario integral con el salario base
                $empleado['salario_integral'] = $empleado['salario_base'];

                // Agregar el empleado al array de empleados únicos
                $empleados_unicos[$id_empleado] = $empleado;
            }
        }

        // Obtener el tipo de concepto
        $tipo_concepto = $concepto['tipo_concepto'];

        // Calcular el valor del concepto para este empleado
        $valor_concepto = obtenerValorConcepto($conexion, $concepto['nom_concepto'], $empleados_unicos[$id_empleado]['salario_base'], $precio_dolar, $empleados_unicos[$id_empleado]['salario_integral'], array($id_empleado), $identificador);

        // Agregar el valor del concepto al array del empleado
        $empleados_unicos[$id_empleado][$concepto['nom_concepto']] = $valor_concepto;

        // Sumar el valor del concepto al array de sumatorias correspondientes
        if ($tipo_concepto === "A") {
            if (isset($suma_asignaciones[$concepto['nom_concepto']])) {
                $suma_asignaciones[$concepto['nom_concepto']] += $valor_concepto;
            } else {
                $suma_asignaciones[$concepto['nom_concepto']] = $valor_concepto;
            }
        } elseif ($tipo_concepto === "D") {
            if (isset($suma_deducciones[$concepto['nom_concepto']])) {
                $suma_deducciones[$concepto['nom_concepto']] += $valor_concepto;
            } else {
                $suma_deducciones[$concepto['nom_concepto']] = $valor_concepto;
            }
        }elseif ($tipo_concepto === "P"){
            if (isset($suma_aportes[$concepto['nom_concepto']])) {
                $suma_aportes[$concepto['nom_concepto']] += $valor_concepto;
            } else {
                $suma_aportes[$concepto['nom_concepto']] = $valor_concepto;
            }
        }

        // Si el tipo de concepto es "A" y no es el salario base, sumarlo al salario integral
        if ($tipo_concepto === "A" && $concepto['nom_concepto'] !== "salario_base") {
            $empleados_unicos[$id_empleado]['salario_integral'] += $valor_concepto;
        }
    }
}

// Calcular el total a pagar para cada empleado
$id_empleados_detalles = array();
$total_a_pagar_empleados = array();
$informacion_empleados = array();

foreach ($empleados_unicos as &$empleado) {
    // Inicializar el total a pagar para este empleado con el salario base
    $total_a_pagar_empleado = $empleado['salario_base'];

    // Sumar las asignaciones
    foreach ($asignaciones as $asignacion) {
        $nom_concepto = $asignacion['nom_concepto'];
        if (isset($empleado[$nom_concepto])) {
            $total_a_pagar_empleado += $empleado[$nom_concepto];
        } else {
            // Calcular el valor de la asignación si no está previamente calculado
            $valorAsignacion = obtenerValorConcepto($conexion, $nom_concepto, $empleado['salario_base'], $precio_dolar, $empleado['salario_integral'], array($empleado['id']), $identificador);
            $total_a_pagar_empleado += $valorAsignacion;
        }
    }

    // Restar las deducciones
    foreach ($deducciones as $deduccion) {
        $nom_concepto = $deduccion['nom_concepto'];
        if (isset($empleado[$nom_concepto])) {
            $total_a_pagar_empleado -= $empleado[$nom_concepto];
        } else {
            // Calcular el valor de la deducción si no está previamente calculado
            $valorDeduccion = obtenerValorConcepto($conexion, $nom_concepto, $empleado['salario_base'], $precio_dolar, $empleado['salario_integral'], array($empleado['id']), $identificador);
            $total_a_pagar_empleado -= $valorDeduccion;
        }
    }

    // Restar los aportes
    foreach ($aportes as $aporte) {
        $nom_concepto = $aporte['nom_concepto'];
        if (isset($empleado[$nom_concepto])) {
            $total_a_pagar_empleado -= $empleado[$nom_concepto];
        } else {
            // Calcular el valor de la deducción si no está previamente calculado
            $valorAporte = obtenerValorConcepto($conexion, $nom_concepto, $empleado['salario_base'], $precio_dolar, $empleado['salario_integral'], array($empleado['id']),$identificador);
            $total_a_pagar_empleado -= $valorAporte;
        }
    }

    // Almacenar el total a pagar para este empleado en el array del empleado
    $empleado['total_a_pagar'] = $total_a_pagar_empleado;
    $informacion_empleados[] = $empleado;
    // Almacenar el ID del empleado y el total a pagar en los arrays correspondientes
    $id_empleados_detalles[] = $empleado['id'];
    $total_a_pagar_empleados[] = $total_a_pagar_empleado;
}

// Cerrar la conexión
$stmtConceptos->close();
$conexion->close();

$nombre_nomina = $data['nombre'];


header('Content-Type: application/json');

// Preparar la respuesta con los resultados
$response = array(
    'informacion_empleados' => $informacion_empleados,
    'empleados' => $id_empleados_detalles,
    'total_pagar' => $total_a_pagar_empleados,
    'nombre_nomina' => $nombre_nomina,
    'suma_asignaciones' => $suma_asignaciones,
    'suma_deducciones' => $suma_deducciones,
    'suma_aportes' => $suma_aportes,
    'identificador' => $identificador,

);

 




echo json_encode($response);

?>