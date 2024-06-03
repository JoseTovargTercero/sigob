<?php
require_once '../sistema_global/conexion.php';
$api_key = "4bfc66a740d312008475dded";
$url = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
$response = file_get_contents($url);
$data = json_decode($response, true);
$precio_dolar = $data['conversion_rate'];

// Función para calcular el salario base de un empleado
function calculoSalarioBase($conexion, $empleado) {
    // Consulta SQL con LEFT JOIN
    $sql = "SELECT empleados.*, cargos_grados.grado,
            TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_años AS paso
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

        // Obtener el monto correspondiente a este empleado
        $monto = obtenerMonto($conexion, $row["grado"], $row["paso"]);

        return $monto;
    } else {
        return "No disponible";
    }
}

// Función para obtener el monto del salario base
function obtenerMonto($conexion, $grado, $paso) {
    // Consulta SQL para obtener el monto
    $grado = "G" . $grado; // Agregar el prefijo 'G' al grado
    $paso = "P" . $paso;   // Agregar el prefijo 'P' al paso

    // Encerrar los valores entre comillas
    $grado = $conexion->real_escape_string($grado);
    $paso = $conexion->real_escape_string($paso);

    $sql = "SELECT monto FROM tabuladores_estr WHERE grado = ? AND paso = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $grado, $paso);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        echo "Error en la consulta: " . $conexion->error . "\n";
        return "No disponible";
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["monto"];
    } else {
        return "No disponible";
    }
}

// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto, $salarioBase, $precio_dolar, $salarioIntegral, $ids_empleados) {



    $sql = "SELECT tipo_calculo, valor FROM conceptos WHERE nom_concepto = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $nom_concepto);
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

// Variable para almacenar el total a pagar
$total_a_pagar = 0;

// Función para obtener los datos de un empleado por su ID
function obtenerEmpleadoPorID($conexion, $id_empleado) {
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
    }

    // Consultar la tabla 'empleados' para cada ID de empleado
    foreach ($ids_empleados as $id_empleado) {
        // Verificar si este empleado ya ha sido agregado
        if (!isset($empleados_unicos[$id_empleado])) {
            // Obtener los datos del empleado por su ID
            $empleado = obtenerEmpleadoPorID($conexion, $id_empleado);

            if ($empleado) {
                // Calcular el salario base del empleado
                $empleado['salario_base'] = calculoSalarioBase($conexion, $empleado);

                // Inicializar el salario integral con el salario base
                $empleado['salario_integral'] = $empleado['salario_base'];

                // Agregar el empleado al array de empleados únicos
                $empleados_unicos[$id_empleado] = $empleado;
            }
        }

        // Obtener el tipo de concepto
        $tipo_concepto = $concepto['tipo_concepto'];

        // Calcular el valor del concepto para este empleado
        $valor_concepto = obtenerValorConcepto($conexion, $concepto['nom_concepto'], $empleados_unicos[$id_empleado]['salario_base'], $precio_dolar, $empleados_unicos[$id_empleado]['salario_integral'], array($id_empleado));

        // Agregar el valor del concepto al array del empleado
        $empleados_unicos[$id_empleado][$concepto['nom_concepto']] = $valor_concepto;

        // Si el tipo de concepto es "A" y no es el salario base, sumarlo al salario integral
        if ($tipo_concepto === "A" && $concepto['nom_concepto'] !== "salario_base") {
            $empleados_unicos[$id_empleado]['salario_integral'] += $valor_concepto;
        }
    }
}

// Calcular el total a pagar para cada empleado
$id_empleados_detalles = array();
$total_a_pagar_empleados = array();

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
            $valorAsignacion = obtenerValorConcepto($conexion, $nom_concepto, $empleado['salario_base'], $precio_dolar, $empleado['salario_integral'], array($empleado['id']));
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
            $valorDeduccion = obtenerValorConcepto($conexion, $nom_concepto, $empleado['salario_base'], $precio_dolar, $empleado['salario_integral'], array($empleado['id']));
            $total_a_pagar_empleado -= $valorDeduccion;
        }
    }

    // Almacenar el total a pagar para este empleado en el array del empleado
    $empleado['total_a_pagar'] = $total_a_pagar_empleado;

    // Almacenar el ID del empleado y el total a pagar en los arrays correspondientes
    $id_empleados_detalles[] = $empleado['id'];
    $total_a_pagar_empleados[] = $total_a_pagar_empleado;
}

// Cerrar la conexión
$stmtConceptos->close();
$conexion->close();

$nombre_nomina = $data['nombre'];

// Preparar la respuesta con los resultados
$response = array(
    'empleados' => $id_empleados_detalles,
    'total_pagar' => $total_a_pagar_empleados,
    'nombre_nomina' => $nombre_nomina,
);



// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);

// Enviar los datos al archivo nom_calculonomina_registro.php usando cURL
$url = 'http://localhost/sigob/back/modulo_nomina/nom_calculonomina_registro.php';
$data_string = json_encode($response);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))
);

$result = curl_exec($ch);
curl_close($ch);

// Verificar el resultado de la solicitud cURL
if ($result === false) {
    echo json_encode(array('error' => 'Error al enviar los datos a nom_calculonomina_registro.php'));
} else {
    echo $result;
}

?>