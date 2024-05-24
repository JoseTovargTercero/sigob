<?php
// Incluir la conexión a la base de datos y la sesión
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

$api_key = "4bfc66a740d312008475dded";
    $url = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    $precio_dolar = $data['conversion_rate'];            
// Función para obtener datos de los empleados
function datosEmpleados($conexion,$precio_dolar) {
    $sql = "SELECT id, cedula, nombres, tipo_nomina, id_dependencia, nacionalidad, cod_empleado, fecha_ingreso, otros_años, status, observacion, cod_cargo, banco, cuenta_bancaria, hijos, instruccion_academica, discapacidades, tipo_cuenta FROM empleados";
    
    $result = $conexion->query($sql);

    if ($result === false) {
        echo "Error en la consulta: " . $conexion->error . "\n";
        return;
    }

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $empleado = array(
                "id_empleado" => $row["id"],
                "cedula" => $row["cedula"],
                "nombres" => $row["nombres"],
                "tipo_nomina" => $row["tipo_nomina"],
                "id_dependencia" => $row["id_dependencia"],
                "nacionalidad" => $row["nacionalidad"],
                "cod_empleado" => $row["cod_empleado"],
                "fecha_ingreso" => $row["fecha_ingreso"],
                "otros_años" => $row["otros_años"],
                "status" => $row["status"],
                "observacion" => $row["observacion"],
                "cod_cargo" => $row["cod_cargo"],
                "banco" => $row["banco"],
                "cuenta_bancaria" => $row["cuenta_bancaria"],
                "hijos" => $row["hijos"],
                "instruccion_academica" => $row["instruccion_academica"],
                "discapacidades" => $row["discapacidades"],
                "tipo_cuenta" => $row["tipo_cuenta"]
            );
            // Llamada a la función calcularNomina
            calcularNomina($conexion, $empleado,$precio_dolar);
        }
    } else {
        echo "0 resultados";
    }
}

// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto, $salarioBase, $precio_dolar, $salarioIntegral) {
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
                return $valor / 2;
            case 6:
                return $valor * 2;
            default:
                echo "Tipo de cálculo no reconocido.";
                return 0;
        }
    } else {
        echo "No se encontró el concepto.";
        return 0;
    }
}

// Modificar las funciones de cálculo de primas y deducciones para aceptar salario integral
function calcularPrimaPorProfesionales($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PRIMA POR PROFESIONALES", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaPorAntiguedad($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PRIMA POR ANTIGUEDAD EMPLEADOS", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaPorTransporte($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PRIMA POR TRANSPORTE", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaPorEscalafon($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PRIMA POR ESCALAFON", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaPorFrontera($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PRIMA POR FRONTERA", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaPorHijos($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PRIMA POR HIJO EMPLEADOS", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaDiscapacidad($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "CONTRIBUCION POR DISCAPACIDAD", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaBeca($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PAGO DE BECA", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaSalud($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PRIMA P/DED AL S/PUBLICO UNICO DE SALUD", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularPrimaAntiguedadEspecial($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "PRIMA POR ANTIGUEDAD (ESPECIAL)", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularDeduccionSSO($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "S. S. O", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularDeduccionRPE($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "RPE", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularDeduccionAPSSO($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "A/P S.S.O", $salarioBase, $precio_dolar, $salarioIntegral);
}

function calcularDeduccionAPRPE($conexion, $salarioBase, $precio_dolar, $salarioIntegral) {
    return obtenerValorConcepto($conexion, "A/P RPE", $salarioBase, $precio_dolar, $salarioIntegral);
}

// Función para calcular la nómina
function calcularNomina($conexion, $empleado, $precio_dolar) {


    // Calculo del salario base
    $salarioBase = calculoSalarioBase($conexion, $empleado);

    // Calcular el salario integral inicialmente sin las deducciones
    $primaProfesionales = calcularPrimaPorProfesionales($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaAntiguedad = calcularPrimaPorAntiguedad($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaTransporte = calcularPrimaPorTransporte($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaEscalafon = calcularPrimaPorEscalafon($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaFrontera = calcularPrimaPorFrontera($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaDiscapacidad = calcularPrimaDiscapacidad($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaBeca = calcularPrimaBeca($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaSalud = calcularPrimaSalud($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaAntiguedadEspecial = calcularPrimaAntiguedadEspecial($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $primaPorHijos = calcularPrimaPorHijos($conexion, $salarioBase, $precio_dolar, $salarioIntegral) * $empleado['hijos'];

    // Calcular el salario integral provisionalmente
    $salarioIntegral = ($salarioBase + $primaProfesionales + $primaAntiguedad + $primaTransporte +
        $primaEscalafon + $primaFrontera + $primaDiscapacidad + $primaBeca +
        $primaSalud + $primaAntiguedadEspecial + $primaPorHijos);

    // Ahora calcular las deducciones con el salario integral provisional
    $deduccionSSO = calcularDeduccionSSO($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $deduccionRPE = calcularDeduccionRPE($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $deduccionAPSSO = calcularDeduccionAPSSO($conexion, $salarioBase, $precio_dolar, $salarioIntegral);
    $deduccionAPRPE = calcularDeduccionAPRPE($conexion, $salarioBase, $precio_dolar, $salarioIntegral);

    // Calcular el salario integral definitivo
    $salarioTotal = $salarioBase + $primaProfesionales + $primaAntiguedad + $primaTransporte +
        $primaEscalafon + $primaFrontera + $primaDiscapacidad + $primaBeca +
        $primaSalud + $primaAntiguedadEspecial + $primaPorHijos - ($deduccionSSO + $deduccionRPE + $deduccionAPSSO + $deduccionAPRPE);
    $salarioQuincena = ($salarioTotal/30) * 15;
    $salarioSemanal = ($salarioTotal/30) * 7;
    $salarioDiario = ($salarioTotal/30) * 1;

    // Mostrar los detalles de la nómina
    echo "Empleado: " . $empleado['nombres'] . " (ID: " . $empleado['id_empleado'] . ")\n<br>";
    echo "Salario Base: $salarioBase Bs\n<br>";
    echo "Prima Profesionales: $primaProfesionales Bs\n<br>";
    echo "Prima Antigüedad: $primaAntiguedad Bs\n<br>";
    echo "Prima de Transporte: $primaTransporte Bs\n<br>";
    echo "Prima de Escalafón: $primaEscalafon Bs\n<br>";
    echo "Prima de Frontera: $primaFrontera Bs\n<br>";
    echo "Prima de Discapacidad: $primaDiscapacidad Bs\n<br>";
    echo "Prima de Beca: $primaBeca Bs\n<br>";
    echo "Prima de Salud: $primaSalud Bs\n<br>";
    echo "Prima de Antiguedad Especial: $primaAntiguedadEspecial Bs\n<br>";
    echo "S.S.O: $deduccionSSO Bs\n<br>";
    echo "RPE: $deduccionRPE Bs\n<br>";
    echo "AP S.S.O: $deduccionAPSSO Bs\n<br>";
    echo "AP RPE: $deduccionAPRPE Bs\n<br>";
    echo "Prima por Hijos: $primaPorHijos Bs\n<br>";
    echo "Salario Total: $salarioTotal Bs\n<br>";
    echo "Salario Quincena: $salarioQuincena Bs\n<br>";
    echo "Salario Semanal: $salarioSemanal Bs\n<br>";
    echo "Salario Diario: $salarioDiario Bs\n<br>";
    echo "Salario Integral: $salarioIntegral Bs\n<br><br><br>";


    // Retornar el salario integral
    return $salarioIntegral;
}





// Función para calcular el salario base
function calculoSalarioBase($conexion, $empleado) {
    // Consulta SQL con LEFT JOIN
    $sql = "SELECT empleados.*, cargos_grados.grado,
            TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_años AS paso
            FROM empleados
            LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo
            WHERE empleados.id = " . $empleado['id_empleado'];

    $result = $conexion->query($sql);

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

function obtenerMonto($conexion, $grado, $paso) {
    // Consulta SQL para obtener el monto
    $grado = "G".$grado; // Agregar el prefijo 'G' al grado
    $paso = "P".$paso;   // Agregar el prefijo 'P' al paso
    
    // Encerrar los valores entre comillas
    $grado = $conexion->real_escape_string($grado);
    $paso = $conexion->real_escape_string($paso);

    $sql = "SELECT monto FROM tabuladores_estr WHERE grado = '$grado' AND paso = '$paso'";
    $result = $conexion->query($sql);
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






// Llamada a la función principal
datosEmpleados($conexion,$precio_dolar);
?>
