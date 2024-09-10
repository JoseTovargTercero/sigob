<?php
require_once '../sistema_global/conexion.php';
// Consulta a la tabla 'tasa' para obtener el valor del dólar
$query = "SELECT valor FROM tasa ORDER BY id DESC LIMIT 1"; // Selecciona el último valor registrado
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $precio_dolar = $row['valor']; // Asigna el valor del campo 'valor'
    echo $precio_dolar;
} else {
    // Manejo de error en caso de que no se encuentre ningún registro en la tabla 'tasa'
    echo json_encode(array('error' => 'Error al preparar la consulta del Precio del dolar: ' . $conexion->error));
    exit();
}
// Obtener el contenido JSON enviado en la solicitud POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar si el array contiene el nombre
if (!isset($data['nombre'])) {
    echo json_encode(array('error' => 'No se recibió el nombre en el array.'));
    exit();
}

$identificador = $data['identificador'];
$nombre = $data['nombre'];
$tipo = $data['tipo'];
$frecuencia = $data['frecuencia'];
$concepto_valor_max = $data['concepto_valor_max'];
$palabrasClave = ['diferencia', 'Diferencia', 'DIFERENCIA', 'diferencias', 'DIFERENCIAS', 'Diferencias'];

$contienePalabraClave = false;

foreach ($palabrasClave as $palabra) {
    if (strpos($nombre, $palabra) !== false) {
        $contienePalabraClave = true;
        break;
    }
}

if ($contienePalabraClave) {
function calculoSalarioBase($conexion, $empleado, $nombre, $identificador) {
    $busqueda = "%Diferencia de sueldo%";

    // Preparar y ejecutar la consulta
    $sql = $conexion->prepare("SELECT * FROM conceptos_aplicados WHERE nombre_nomina = ? AND nom_concepto LIKE ? LIMIT 1");
    $sql->bind_param("ss", $nombre, $busqueda);
    $sql->execute();

    $result = $sql->get_result();

    // Verificar si se encontraron registros
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nomina_restar = str_replace(['[', ']', '"'], '', $row["nomina_restar"]); // Eliminar [ ] y "
        $nomina_restar = explode(',', $nomina_restar); // Convertir a array
    } else {
        echo "No se encontraron registros.";
        return null;
    }

    // Datos de entrada

    $fecha_pagar = date('m-Y');

    // Obtener los últimos registros de cada nómina
    $totals = [];
    foreach ($nomina_restar as $nomina) {
        $nomina = trim($nomina); // Eliminar espacios en blanco
        $sql2 = "SELECT total_a_pagar FROM txt 
                WHERE nombre_nomina = ? AND identificador = ? AND fecha_pagar = ?
                ORDER BY id DESC LIMIT 1";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bind_param("sss", $nomina, $identificador, $fecha_pagar);
        $stmt2->execute();
        $stmt2->bind_result($total_a_pagar);
        if ($stmt2->fetch()) {
            $totals[] = $total_a_pagar;
        }
        $stmt2->close();
    }

    // Calcular la diferencia
    if (count($totals) == 2) {
        $difference = abs($totals[0] - $totals[1]);
        return round($difference,2);
    } else {
        echo "No se encontraron suficientes registros para calcular la diferencia.";
        return null;
    }
}



// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto, $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $frecuencia) {
    $sql = "SELECT c.tipo_calculo, c.valor
            FROM conceptos c
            JOIN conceptos_aplicados ca ON c.nom_concepto = ca.nom_concepto
            WHERE c.nom_concepto = ?
            AND JSON_CONTAINS(ca.fecha_aplicar, JSON_QUOTE(?))";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $nom_concepto, $identificador); // Agregamos el identificador como segundo parámetro
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_calculo = $row["tipo_calculo"];
        $valor2 = $row["valor"];
        
        if ($frecuencia == 1) {
        // Obtener el número de la semana del año del identificador
$semana_ano = intval(substr($identificador, 1));

// Calcular la semana del mes
$semana_mes = ceil($semana_ano / 4);

// Ajustar la semana del mes si el mes tiene más de 4 semanas
if ($semana_mes > 5) {
    $semana_mes = 5; // Considerar solo hasta la quinta semana
}
     if ($semana_mes >= 1 && $semana_mes <= 5) {
        $valor = round(($valor2 * 0.25), 2);
    }
}elseif ($frecuencia == 2) {
    if ($identificador == "q1" || $identificador == "q2") {
            $valor = round($valor2 * 0.50, 2);
        }
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
                            $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $frecuencia);
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
                                                    $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $frecuencia);
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

// Arrays para almacenar las sumas de cada asignación, deducción y aporte
$suma_asignaciones = array();
$suma_deducciones = array();
$suma_aportes = array();
$suma_diferencia = array();

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

// Array para almacenar la información de los empleados
$recibos_de_pago = array();
$suma_asignaciones['SALARIO BASE'] = 0;

// Iterar sobre cada registro de conceptos_aplicados
foreach ($conceptos_aplicados as &$concepto) {
    // Obtener los IDs de empleados de este concepto
    $ids_empleados = json_decode($concepto['empleados'], true);

    // Clasificar los conceptos en asignaciones, deducciones o aportes
    if ($concepto['tipo_concepto'] === "A") {
        $asignaciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "D") {
        $deducciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "P") {
        $aportes[] = $concepto;
    }

    // Consultar la tabla 'empleados' para cada ID de empleado
    foreach ($ids_empleados as $id_empleado) {
        // Verificar si este empleado ya ha sido agregado
        if (!isset($empleados_unicos[$id_empleado])) {
            // Obtener los datos del empleado por su ID
            $empleado = obtenerEmpleadoPorID($conexion, $id_empleado);

            if ($empleado) {
                // Calcular el salario base del empleado
                $empleado['salario_base'] = calculoSalarioBase($conexion, $empleado, $nombre, $identificador, $frecuencia);

                // Redondear el salario base a dos decimales
                $empleado['salario_base'] = round($empleado['salario_base'], 2);

                // Inicializar el salario integral con el salario base
                $empleado['salario_integral'] = $empleado['salario_base'];

                // Sumar el salario base redondeado al array de sumas de asignaciones
                $suma_asignaciones['SALARIO BASE'] += $empleado['salario_base'];

                // Redondear nuevamente para evitar decimales inesperados en la suma
                $suma_asignaciones['SALARIO BASE'] = round($suma_asignaciones['SALARIO BASE'], 2);

                // Inicializar arrays para asignaciones, deducciones y aportes
                $empleado['asignaciones'] = array();
                $empleado['deducciones'] = array();
                $empleado['aportes'] = array();

                // **Agregar el salario_base a las asignaciones**
                $empleado['asignaciones'][] = array('SALARIO BASE' => $empleado['salario_base']);

                // Agregar el empleado al array de empleados únicos
                $empleados_unicos[$id_empleado] = $empleado;
            }
        }

        // Obtener el tipo de concepto
        $tipo_concepto = $concepto['tipo_concepto'];

        // Calcular el valor del concepto para este empleado
        $valor_concepto = obtenerValorConcepto($conexion, $concepto['nom_concepto'], $empleados_unicos[$id_empleado]['salario_base'], $precio_dolar, $empleados_unicos[$id_empleado]['salario_integral'], array($id_empleado), $identificador, $frecuencia);

        // Agregar el valor del concepto al array correspondiente del empleado
        if ($tipo_concepto === "A") {
            $empleados_unicos[$id_empleado]['asignaciones'][] = array($concepto['nom_concepto'] => $valor_concepto);
            // Sumar al salario integral si no es el salario base
            if ($concepto['nom_concepto'] !== "SALARIO BASE") {
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

    // Restar el salario base del total a pagar
    $total_a_pagar_empleado -= $empleado['salario_base'];

    // Almacenar el total a pagar para este empleado en el array del empleado
    $empleado['total_a_pagar'] = $total_a_pagar_empleado;

  // Agregar la información del empleado al array de recibos de pago
$recibos_de_pago[] = array(
    'id_empleado' => $empleado['id'],
    'sueldo_base' => $empleado['salario_base'],
    'sueldo_integral' => $empleado['salario_integral'],
    'asignaciones' => $empleado['asignaciones'],
    'deducciones' => $empleado['deducciones'],
    'aportes' => $empleado['aportes'],
    'total_a_pagar' => $empleado['total_a_pagar']
);

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
$conexion->close();

$nombre_nomina = $data['nombre'];


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
    'recibos_pagos' => $recibos_de_pago,
);
 




echo json_encode($response);




















} elseif ($tipo == 2) {

    function calculoSalarioBase($conexion, $empleado, $nombre, $identificador) {

           
            $monto = 0;

            return $monto;
    

}



// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto, $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $empleado) {
    $sql = "SELECT c.tipo_calculo, c.valor, ca.otra_nomina
            FROM conceptos c
            JOIN conceptos_aplicados ca ON c.nom_concepto = ca.nom_concepto
            WHERE c.nom_concepto = ?
            AND JSON_CONTAINS(ca.fecha_aplicar, JSON_QUOTE(?))";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $nom_concepto, $identificador); // Agregamos el identificador como segundo parámetro
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_calculo = $row["tipo_calculo"];
        $valor = $row["valor"];
        $otra_nomina = $row["otra_nomina"];

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
                            $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador,$empleado);
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
                                                    $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $empleado);
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
            case 8:
    // Suponiendo que ya tienes una conexión a la base de datos $conexion
    $id = $otra_nomina; // Id de la otra nómina que quieres comparar
    
    // Consulta a la tabla nomina para obtener el nombre
    $consulta_nomina = $conexion->prepare("SELECT nombre, frecuencia FROM nominas WHERE id = ?");
    
    // Verificar si la consulta se preparó correctamente
    if (!$consulta_nomina) {
        die("Error en la consulta a nomina: " . $conexion->error);
    }
    
    $consulta_nomina->bind_param("i", $id);
    $consulta_nomina->execute();
    $resultado_nomina = $consulta_nomina->get_result();

    if ($resultado_nomina->num_rows > 0) {
        $fila_nomina = $resultado_nomina->fetch_assoc();
        $nombre_nomina2 = $fila_nomina['nombre']; // Cambié el nombre de la variable a $nombre_nomina2
        $frecuencia = $fila_nomina['frecuencia'];
    } else {
        $nombre_nomina2 = null; // Si no se encuentra, se asigna null
    }

    if ($nombre_nomina2) {
        // Ahora, consultamos la tabla recibo_pago para obtener el sueldo_integral
        $consulta_recibo = $conexion->prepare("SELECT sueldo_integral FROM recibo_pago WHERE id_empleado = ? AND nombre_nomina = ? ORDER BY id DESC LIMIT 1");
        
        // Verificar si la consulta se preparó correctamente
        if (!$consulta_recibo) {
            die("Error en la consulta a recibo_pago: " . $conexion->error);
        }
        
        $consulta_recibo->bind_param("is", $empleado, $nombre_nomina2);
        $consulta_recibo->execute();
        $resultado_recibo = $consulta_recibo->get_result();

        if ($resultado_recibo->num_rows > 0) {
            $fila_recibo = $resultado_recibo->fetch_assoc();
            if ($frecuencia == 1) {
                $sueldo_integral = (($fila_recibo['sueldo_integral'] * 52)/12)/30; // Esto multiplica el sueldo_integral por 2, manteniéndolo en caso de que sea necesario

                // Aplicar el porcentaje del valor al sueldo_integral
                $valor2 = round($sueldo_integral * ($valor / 100),2)*$valor;
            }elseif($frecuencia == 2){
                $sueldo_integral = ($fila_recibo['sueldo_integral'] * 2)/30; // Esto multiplica el sueldo_integral por 2, manteniéndolo en caso de que sea necesario

                // Aplicar el porcentaje del valor al sueldo_integral
                $valor2 = round($sueldo_integral * ($valor / 100),2)*$valor;
            }else{
                $sueldo_integral = ($fila_recibo['sueldo_integral'])/30; // Esto multiplica el sueldo_integral por 2, manteniéndolo en caso de que sea necesario
                 // Aplicar el porcentaje del valor al sueldo_integral
                $valor2 = round($sueldo_integral * ($valor / 100),2)*$valor;

        }

        } else {
            $sueldo_integral = "No encontrado";
        }
    } else {
        $sueldo_integral = "Nombre de nómina no encontrado";
    }

    return $valor2;




            case 9:
    // Suponiendo que ya tienes una conexión a la base de datos $conexion
    $id = $otra_nomina; // Id de la otra nómina que quieres comparar
    
    // Consulta a la tabla nomina para obtener el nombre
    $consulta_nomina = $conexion->prepare("SELECT nombre, frecuencia FROM nominas WHERE id = ?");
    
    // Verificar si la consulta se preparó correctamente
    if (!$consulta_nomina) {
        die("Error en la consulta a nomina: " . $conexion->error);
    }
    
    $consulta_nomina->bind_param("i", $id);
    $consulta_nomina->execute();
    $resultado_nomina = $consulta_nomina->get_result();

    if ($resultado_nomina->num_rows > 0) {
        $fila_nomina = $resultado_nomina->fetch_assoc();
        $nombre_nomina2 = $fila_nomina['nombre']; // Cambié el nombre de la variable a $nombre_nomina2
        $frecuencia = $fila_nomina['frecuencia'];
    } else {
        $nombre_nomina2 = null; // Si no se encuentra, se asigna null
    }

    if ($nombre_nomina2) {
    // Consultar la tabla recibo_pago para obtener el último sueldo_integral que cumpla la condición
    $consulta_recibo = $conexion->prepare("SELECT sueldo_integral FROM recibo_pago WHERE id_empleado = ? AND nombre_nomina = ? ORDER BY id DESC LIMIT 1");

    // Verificar si la consulta se preparó correctamente
    if (!$consulta_recibo) {
        die("Error en la consulta a recibo_pago: " . $conexion->error);
    }

    $consulta_recibo->bind_param("is", $empleado, $nombre_nomina2);
    $consulta_recibo->execute();
    $resultado_recibo = $consulta_recibo->get_result();

    if ($resultado_recibo->num_rows > 0) {
        $fila_recibo = $resultado_recibo->fetch_assoc();
        if ($frecuencia == 1) {
                $sueldo_integral = (($fila_recibo['sueldo_integral'] * 52)/12)/30; // Esto multiplica el sueldo_integral por 2, manteniéndolo en caso de que sea necesario
                // Calcular la fracción del valor sobre el sueldo_integral
        $valor2 = round($sueldo_integral * ($valor), 2); // Aquí se calcula la fracción multiplicando directamente
        }elseif($frecuencia == 2){
                $sueldo_integral = ($fila_recibo['sueldo_integral'] * 2)/30; // Esto multiplica el sueldo_integral por 2, manteniéndolo en caso de que sea necesario
                // Calcular la fracción del valor sobre el sueldo_integral
        $valor2 = round($sueldo_integral * ($valor), 2); // Aquí se calcula la fracción multiplicando directamente
        }else{
            $sueldo_integral = ($fila_recibo['sueldo_integral'])/30; // Esto multiplica el sueldo_integral por 2, manteniéndolo en caso de que sea necesario
                // Calcular la fracción del valor sobre el sueldo_integral
        $valor2 = round($sueldo_integral * ($valor), 2); // Aquí se calcula la fracción multiplicando directamente

        }
    } else {
        $sueldo_integral = "No encontrado";
    }
} else {
    $sueldo_integral = "Nombre de nómina no encontrado";
}

return $valor2;

           
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

// Recorrer los resultados y filtrar empleados por status 'A'
foreach ($conceptos_aplicados as &$concepto) {
    $empleados = json_decode($concepto['empleados'], true); // Convertir a array

    // Filtrar empleados por status 'A'
    $empleados_filtrados = [];
    foreach ($empleados as $id_empleado) {
        // Consultar el status del empleado
        $queryStatus = "SELECT status FROM empleados WHERE id = ?";
        $stmtStatus = $conexion->prepare($queryStatus);
        $stmtStatus->bind_param("i", $id_empleado);
        $stmtStatus->execute();
        $stmtStatus->bind_result($status);
        $stmtStatus->fetch();
        $stmtStatus->close();

        // Si el status es 'A', mantener el empleado en el array
        if ($status == 'A') {
            $empleados_filtrados[] = $id_empleado;
        }
    }

    // Actualizar el campo empleados en el concepto_aplicado
    $concepto['empleados'] = json_encode($empleados_filtrados);
}


// Array asociativo para mantener un registro de empleados únicos
$empleados_unicos = array();

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

// Array para almacenar la información de los empleados
$recibos_de_pago = array();
$suma_asignaciones['SALARIO BASE'] = 0;
// Inicializar la suma del salario base en el array de sumas de asignaciones


// Iterar sobre cada registro de conceptos_aplicados
foreach ($conceptos_aplicados as &$concepto) {
    // Obtener los IDs de empleados de este concepto
    $ids_empleados = json_decode($concepto['empleados'], true);

    // Clasificar los conceptos en asignaciones, deducciones o aportes
    if ($concepto['tipo_concepto'] === "A") {
        $asignaciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "D") {
        $deducciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "P") {
        $aportes[] = $concepto;
    }

    // Consultar la tabla 'empleados' para cada ID de empleado
    foreach ($ids_empleados as $id_empleado) {
        // Verificar si este empleado ya ha sido agregado
        if (!isset($empleados_unicos[$id_empleado])) {
            // Obtener los datos del empleado por su ID
            $empleado = obtenerEmpleadoPorID($conexion, $id_empleado);

            if ($empleado) {
                // Calcular el salario base del empleado
                $empleado['salario_base'] = calculoSalarioBase($conexion, $empleado, $nombre, $identificador);
                $empleado['id'] = $id_empleado;

                // Sumar el salario base al array de sumas de asignaciones
                // Sumar el salario base redondeado al array de sumas de asignaciones
                $suma_asignaciones['SALARIO BASE'] += $empleado['salario_base'];

                // Redondear nuevamente para evitar decimales inesperados en la suma
                $suma_asignaciones['SALARIO BASE'] = round($suma_asignaciones['SALARIO BASE'], 2);

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
        $valor_concepto = obtenerValorConcepto($conexion, $concepto['nom_concepto'], $empleados_unicos[$id_empleado]['salario_base'], $precio_dolar, $empleados_unicos[$id_empleado]['salario_integral'], array($id_empleado), $identificador, $empleados_unicos[$id_empleado]['id']);

        // Agregar el valor del concepto al array correspondiente del empleado
        if ($tipo_concepto === "A") {
            $empleados_unicos[$id_empleado]['asignaciones'][] = array($concepto['nom_concepto'] => $valor_concepto);
            // Sumar al salario integral si no es el salario base
            if ($concepto['nom_concepto'] !== "salario_base") {
                if ($frecuencia == "5" AND $tipo == "2") {
                    $empleados_unicos[$id_empleado]['salario_integral'] += $valor_concepto/$concepto_valor_max;
                }else{
                    $empleados_unicos[$id_empleado]['salario_integral'] += $valor_concepto;
                }
                 
                
            }

            // Sumar al array de sumas de asignaciones
            if (isset($suma_asignaciones[$concepto['nom_concepto']])) {
                 if ($frecuencia == "5" AND $tipo == "2") {
                    $suma_asignaciones[$concepto['nom_concepto']] += $valor_concepto/$concepto_valor_max;
                }else{
                    $suma_asignaciones[$concepto['nom_concepto']] += $valor_concepto;
                }
            } else {
                if ($frecuencia == "5" AND $tipo == "2") {
                    $suma_asignaciones[$concepto['nom_concepto']] = $valor_concepto/$concepto_valor_max;
                }else{
                    $suma_asignaciones[$concepto['nom_concepto']] = $valor_concepto;
                }
            }
        } elseif ($tipo_concepto === "D") {
            $empleados_unicos[$id_empleado]['deducciones'][] = array($concepto['nom_concepto'] => $valor_concepto);

            // Sumar al array de sumas de deducciones
            if (isset($suma_deducciones[$concepto['nom_concepto']])) {
                if ($frecuencia == "5" AND $tipo == "2") {
                     $suma_deducciones[$concepto['nom_concepto']] += $valor_concepto/$concepto_valor_max;
                }else{
                     $suma_deducciones[$concepto['nom_concepto']] += $valor_concepto;
                }

            } else {
                if ($frecuencia == "5" AND $tipo == "2") {
                     $suma_deducciones[$concepto['nom_concepto']] = $valor_concepto/$concepto_valor_max;
                }else{
                     $suma_deducciones[$concepto['nom_concepto']] = $valor_concepto;
                }
            }
        } elseif ($tipo_concepto === "P") {
            $empleados_unicos[$id_empleado]['aportes'][] = array($concepto['nom_concepto'] => $valor_concepto);

            // Sumar al array de sumas de aportes
            if (isset($suma_aportes[$concepto['nom_concepto']])) {
                if ($frecuencia == "5" AND $tipo == "2") {
                     $suma_aportes[$concepto['nom_concepto']] += $valor_concepto/$concepto_valor_max;
                }else{
                     $suma_aportes[$concepto['nom_concepto']] += $valor_concepto;
                }
            } else {
                if ($frecuencia == "5" AND $tipo == "2") {
                     $suma_aportes[$concepto['nom_concepto']] = $valor_concepto/$concepto_valor_max;
                }else{
                     $suma_aportes[$concepto['nom_concepto']] = $valor_concepto;
                }
            }
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
            if ($frecuencia == "5" AND $tipo == "2") {
                $total_a_pagar_empleado += $valor/$concepto_valor_max;
                }else{
                    $total_a_pagar_empleado += $valor;
                }
            
        }
    }

    // Restar las deducciones
    foreach ($empleado['deducciones'] as $deduccion) {
        foreach ($deduccion as $valor) {
            if ($frecuencia == "5" AND $tipo == "2") {
                $total_a_pagar_empleado -= $valor/$concepto_valor_max;
                }else{
                    $total_a_pagar_empleado -= $valor;
                }
           
        }
    }

    // Restar los aportes
    foreach ($empleado['aportes'] as $aporte) {
        foreach ($aporte as $valor) {
            if ($frecuencia == "5" AND $tipo == "2") {
                $total_a_pagar_empleado -= $valor/$concepto_valor_max;
                }else{
                    $total_a_pagar_empleado -= $valor;
                }
        }
    }

    // Almacenar el total a pagar para este empleado en el array del empleado
    $empleado['total_a_pagar'] = $total_a_pagar_empleado;

  // Agregar la información del empleado al array de recibos de pago
$recibos_de_pago[] = array(
    'id_empleado' => $empleado['id'],
    'sueldo_base' => $empleado['salario_base'],
    'sueldo_integral' => $empleado['salario_integral'],
    'asignaciones' => $empleado['asignaciones'],
    'deducciones' => $empleado['deducciones'],
    'aportes' => $empleado['aportes'],
    'total_a_pagar' => $empleado['total_a_pagar']
);

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
$conexion->close();

$nombre_nomina = $data['nombre'];


// Preparar la respuesta con los resultados
// Redondear los valores en suma_asignaciones
foreach ($suma_asignaciones as $key => $value) {
    $suma_asignaciones[$key] = round($value, 2);
}

// Redondear los valores en suma_deducciones
foreach ($suma_deducciones as $key => $value) {
    $suma_deducciones[$key] = round($value, 2);
}

// Redondear los valores en suma_aportes
foreach ($suma_aportes as $key => $value) {
    $suma_aportes[$key] = round($value, 2);
}

$response = array(
    'informacion_empleados' => $informacion_empleados,
    'empleados' => $id_empleados_detalles,
    'total_pagar' => $total_a_pagar_empleados,
    'nombre_nomina' => $nombre_nomina,
    'suma_asignaciones' => $suma_asignaciones,
    'suma_deducciones' => $suma_deducciones,
    'suma_aportes' => $suma_aportes,
    'identificador' => $identificador,
    'recibos_pagos' => $recibos_de_pago,
);

// Devolver la respuesta como JSON
echo json_encode($response);






















    
}else{
   function calculoSalarioBase($conexion, $empleado, $nombre, $identificador, $frecuencia) {
    // Consulta SQL con LEFT JOIN
   // Obtener la semana del año actual

if ($frecuencia == 1) {
    $semana_ano = intval(substr($identificador, 1));

// Calcular la semana del mes
$semana_mes = $semana_ano % 4;
if ($semana_mes == 0) {
    $semana_mes = 4; // Para que la semana 4, 8, 12, ... se considere la cuarta semana del mes
}

// Verificar el identificador con la semana correspondiente
// Obtener el número de la semana del año del identificador
$semana_ano = intval(substr($identificador, 1));

// Calcular la semana del mes
$semana_mes = ceil($semana_ano / 4);

// Ajustar la semana del mes si el mes tiene más de 4 semanas
if ($semana_mes > 5) {
    $semana_mes = 5; // Considerar solo hasta la quinta semana
}

// Verificar el identificador con la semana correspondiente
if ($semana_mes == 1) {
    // Primera semana de cada mes
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
} elseif ($semana_mes == 2) {
    // Segunda semana de cada mes
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 7 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
} elseif ($semana_mes == 3) {
    // Tercera semana de cada mes
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 14 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
} elseif ($semana_mes == 4) {
    // Cuarta semana de cada mes
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 21 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
}else{
    // Quinta semana de cada mes (si es aplicable)
    $sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, DATE_ADD(CURDATE(), INTERVAL 28 DAY)) + empleados.otros_años AS antiguedad
    FROM empleados
    LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
    WHERE empleados.id = ?";
}
}elseif ($frecuencia == 2) {
    if ($identificador == "q1") {
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
}

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
            $monto = obtenerMonto($conexion, $row["grado"], $paso, $tabulador, $identificador, $frecuencia);

            return $monto;
        } else {
            return "0";
        }
    } else {
        return "0";
    }
}

// Función para obtener el monto del salario base
function obtenerMonto($conexion, $grado, $paso, $tabulador, $identificador, $frecuencia) {
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
        if ($frecuencia == 1) {
             // Obtener el número de la semana del año del identificador
$semana_ano = intval(substr($identificador, 1));

// Calcular la semana del mes
$semana_mes = ceil($semana_ano / 4);

// Ajustar la semana del mes si el mes tiene más de 4 semanas
if ($semana_mes > 5) {
    $semana_mes = 5; // Considerar solo hasta la quinta semana
    if ($semana_mes == 1 || $semana_mes == 2 || $semana_mes == 3 || $semana_mes == 4 || $semana_mes == 5) {
            $monto2 = $row["monto"];
            $monto = round(($monto2 * 0.25), 2);
        }
}else{
    if ($semana_mes == 1 || $semana_mes == 2 || $semana_mes == 3 || $semana_mes == 4) {
            $monto2 = $row["monto"];
            $monto = round(($monto2 * 0.25), 2);

}
        }
       
}elseif ($frecuencia == 2) {
   if ($identificador == "q1" OR $identificador == "q2") {
            $monto2 = $row["monto"];
            $monto = round($monto2*0.50,2);
}
}else{
            $monto = $row["monto"];  
}
        
        return $monto;
    } else {
        return "0";
    }
}

// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto, $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $frecuencia) {
    $sql = "SELECT c.tipo_calculo, c.valor
            FROM conceptos c
            JOIN conceptos_aplicados ca ON c.nom_concepto = ca.nom_concepto
            WHERE c.nom_concepto = ?
            AND JSON_CONTAINS(ca.fecha_aplicar, JSON_QUOTE(?))";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $nom_concepto, $identificador); // Agregamos el identificador como segundo parámetro
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_calculo = $row["tipo_calculo"];
        $valor2 = $row["valor"];
        
if ($frecuencia == 1) {
        // Obtener el número de la semana del año del identificador
$semana_ano = intval(substr($identificador, 1));

// Calcular la semana del mes
$semana_mes = ceil($semana_ano / 4);

// Ajustar la semana del mes si el mes tiene más de 4 semanas
if ($semana_mes > 5) {
    $semana_mes = 5; // Considerar solo hasta la quinta semana
}
     if ($semana_mes >= 1 && $semana_mes <= 5) {
        $valor = round(($valor2 * 0.25), 2);
    }
}elseif ($frecuencia == 2) {
    if ($identificador == "q1" || $identificador == "q2") {
            $valor = round($valor2 * 0.50, 2);
        }
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
                            $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $frecuencia);
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
                                                    $valor_concepto = obtenerValorConcepto($conexion, $row_concepto['nom_concepto'], $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados, $identificador, $frecuencia);
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

// Recorrer los resultados y filtrar empleados por status 'A'
foreach ($conceptos_aplicados as &$concepto) {
    $empleados = json_decode($concepto['empleados'], true); // Convertir a array

    // Filtrar empleados por status 'A'
    $empleados_filtrados = [];
    foreach ($empleados as $id_empleado) {
        // Consultar el status del empleado
        $queryStatus = "SELECT status FROM empleados WHERE id = ?";
        $stmtStatus = $conexion->prepare($queryStatus);
        $stmtStatus->bind_param("i", $id_empleado);
        $stmtStatus->execute();
        $stmtStatus->bind_result($status);
        $stmtStatus->fetch();
        $stmtStatus->close();

        // Si el status es 'A', mantener el empleado en el array
        if ($status == 'A') {
            $empleados_filtrados[] = $id_empleado;
        }
    }

    // Actualizar el campo empleados en el concepto_aplicado
    $concepto['empleados'] = json_encode($empleados_filtrados);
}


// Array asociativo para mantener un registro de empleados únicos
$empleados_unicos = array();

// Array para almacenar asignaciones, deducciones y aportes
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

// Array para almacenar la información de los empleados
$recibos_de_pago = array();
$suma_asignaciones['SALARIO BASE'] = 0;

// Iterar sobre cada registro de conceptos_aplicados
foreach ($conceptos_aplicados as &$concepto) {
    // Obtener los IDs de empleados de este concepto
    $ids_empleados = json_decode($concepto['empleados'], true);

    // Clasificar los conceptos en asignaciones, deducciones o aportes
    if ($concepto['tipo_concepto'] === "A") {
        $asignaciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "D") {
        $deducciones[] = $concepto;
    } elseif ($concepto['tipo_concepto'] === "P") {
        $aportes[] = $concepto;
    }

    // Consultar la tabla 'empleados' para cada ID de empleado
    foreach ($ids_empleados as $id_empleado) {
        // Verificar si este empleado ya ha sido agregado
        if (!isset($empleados_unicos[$id_empleado])) {
            // Obtener los datos del empleado por su ID
            $empleado = obtenerEmpleadoPorID($conexion, $id_empleado);

            if ($empleado) {
                // Calcular el salario base del empleado
                $empleado['salario_base'] = calculoSalarioBase($conexion, $empleado, $nombre, $identificador, $frecuencia);

                // Redondear el salario base a dos decimales
                $empleado['salario_base'] = round($empleado['salario_base'], 2);

                // Inicializar el salario integral con el salario base
                $empleado['salario_integral'] = $empleado['salario_base'];

                // Sumar el salario base redondeado al array de sumas de asignaciones
                $suma_asignaciones['SALARIO BASE'] += $empleado['salario_base'];

                // Redondear nuevamente para evitar decimales inesperados en la suma
                $suma_asignaciones['SALARIO BASE'] = round($suma_asignaciones['SALARIO BASE'], 2);

                // Inicializar arrays para asignaciones, deducciones y aportes
                $empleado['asignaciones'] = array();
                $empleado['deducciones'] = array();
                $empleado['aportes'] = array();

                // **Agregar el salario_base a las asignaciones**
                $empleado['asignaciones'][] = array('SALARIO BASE' => $empleado['salario_base']);

                // Agregar el empleado al array de empleados únicos
                $empleados_unicos[$id_empleado] = $empleado;
            }
        }

        // Obtener el tipo de concepto
        $tipo_concepto = $concepto['tipo_concepto'];

        // Calcular el valor del concepto para este empleado
        $valor_concepto = obtenerValorConcepto($conexion, $concepto['nom_concepto'], $empleados_unicos[$id_empleado]['salario_base'], $precio_dolar, $empleados_unicos[$id_empleado]['salario_integral'], array($id_empleado), $identificador, $frecuencia);

        // Agregar el valor del concepto al array correspondiente del empleado
        if ($tipo_concepto === "A") {
            $empleados_unicos[$id_empleado]['asignaciones'][] = array($concepto['nom_concepto'] => $valor_concepto);
            // Sumar al salario integral si no es el salario base
            if ($concepto['nom_concepto'] !== "SALARIO BASE") {
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

    // Restar el salario base del total a pagar
    $total_a_pagar_empleado -= $empleado['salario_base'];

    // Almacenar el total a pagar para este empleado en el array del empleado
    $empleado['total_a_pagar'] = $total_a_pagar_empleado;

    // Agregar la información del empleado al array de recibos de pago
    $recibos_de_pago[] = array(
        'id_empleado' => $empleado['id'],
        'sueldo_base' => $empleado['salario_base'],
        'sueldo_integral' => $empleado['salario_integral'],
        'asignaciones' => $empleado['asignaciones'],
        'deducciones' => $empleado['deducciones'],
        'aportes' => $empleado['aportes'],
        'total_a_pagar' => $empleado['total_a_pagar']
    );

    // Almacenar el ID del empleado y el total a pagar en los arrays correspondientes
    $informacion_empleados[] = $empleado;
    $id_empleados_detalles[] = $empleado['id'];
    $total_a_pagar_empleados[] = $total_a_pagar_empleado;
}

// Cerrar la conexión y preparar la respuesta con los resultados
$stmtConceptos->close();
$conexion->close();

$nombre_nomina = $data['nombre'];

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
    'recibos_pagos' => $recibos_de_pago,
);
echo json_encode($response);
}



?>