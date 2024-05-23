<?php
// Incluir la conexión a la base de datos y la sesión
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Verificar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Función para obtener datos de los empleados
function datosEmpleados($conexion) {
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
            calcularNomina($conexion, $empleado);
        }
    } else {
        echo "0 resultados";
    }
}

/// Función para obtener el valor de un concepto según su tipo de cálculo
function obtenerValorConcepto($conexion, $nom_concepto) {
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
                return $valor + 10;
            case 3:
                return $valor * 1.2;
            case 4:
                return $valor - 5;
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

// Función para calcular la prima por profesionales
function calcularPrimaPorProfesionales($conexion) {
    return obtenerValorConcepto($conexion, "PRIMA POR PROFESIONALES");
}

// Función para calcular la prima por antigüedad
function calcularPrimaPorAntiguedad($conexion) {
    return obtenerValorConcepto($conexion, "PRIMA POR ANTIGUEDAD EMPLEADOS");
}

// Función para calcular la prima por transporte
function calcularPrimaPorTransporte($conexion) {
    return obtenerValorConcepto($conexion, "PRIMA POR TRANSPORTE");
}

// Función para calcular la prima por escalafón
function calcularPrimaPorEscalafon($conexion) {
    return obtenerValorConcepto($conexion, "PRIMA POR ESCALAFON");
}

// Función para calcular la prima por frontera
function calcularPrimaPorFrontera($conexion) {
    return obtenerValorConcepto($conexion, "PRIMA POR FRONTERA");
}

// Función para calcular la prima por hijos
function calcularPrimaPorHijos($conexion) {
    return obtenerValorConcepto($conexion, "PRIMA POR HIJO EMPLEADOS");
}
function calcularPrimaDiscapacidad($conexion) {
    return obtenerValorConcepto($conexion, "CONTRIBUCION POR DISCAPACIDAD");
}
function calcularPrimaBeca($conexion) {
    return obtenerValorConcepto($conexion, "PAGO DE BECA");
}
function calcularPrimaSalud($conexion) {
    return obtenerValorConcepto($conexion, "PRIMA P/DED AL S/PUBLICO UNICO DE SALUD");
}
function calcularPrimaAntiguedadEspecial($conexion) {
    return obtenerValorConcepto($conexion, "PRIMA POR ANTIGUEDAD (ESPECIAL)");
}
function calcularDeduccionSSO($conexion) {
    return obtenerValorConcepto($conexion, "S. S. O");
}
function calcularDeduccionRPE($conexion) {
    return obtenerValorConcepto($conexion, "RPE");
}
function calcularDeduccionAPSSO($conexion) {
    return obtenerValorConcepto($conexion, "A/P S.S.O");
}
function calcularDeduccionAPRPE($conexion) {
    return obtenerValorConcepto($conexion, "A/P RPE");
}

// Función para calcular la nómina
function calcularNomina($conexion, $empleado) {
    // Calculo del salario base
    $salarioBase = calculoSalarioBase($conexion, $empleado);

    // Obtener prima por profesionales
    $primaProfesionales = calcularPrimaPorProfesionales($conexion);

    // Calcular la prima por antigüedad
    $primaAntiguedad = calcularPrimaPorAntiguedad($conexion);

    // Calcular la prima por transporte
    $primaTransporte = calcularPrimaPorTransporte($conexion);

    // Calcular la prima por escalafón
    $primaEscalafon = calcularPrimaPorEscalafon($conexion);

    // Calcular la prima por frontera
    $primaFrontera = calcularPrimaPorFrontera($conexion);

    // Calcular la prima por frontera
    $primaDiscapacidad = calcularPrimaDiscapacidad($conexion);

    // Calcular la prima por frontera
    $primaBeca = calcularPrimaBeca($conexion);

    // Calcular la prima por frontera
    $primaSalud = calcularPrimaSalud($conexion);

    // Calcular la prima por frontera
    $primaAntiguedadEspecial = calcularPrimaAntiguedadEspecial($conexion);

    // Calcular la prima por frontera
    $deduccionSSO = calcularDeduccionSSO($conexion);
    // Calcular la prima por frontera
    $deduccionRPE = calcularDeduccionRPE($conexion);
    // Calcular la prima por frontera
    $deduccionAPSSO = calcularDeduccionAPSSO($conexion);
    // Calcular la prima por frontera
    $deduccionAPRPE = calcularDeduccionAPRPE($conexion);

    // Calcular la prima por hijos solo si se especifica la cantidad de hijos
    $primaPorHijos = calcularPrimaPorHijos($conexion) * $empleado['hijos'];

    echo "Empleado: " . $empleado['nombres'] . " (ID: " . $empleado['id_empleado'] . ")\n<br>";
    echo "Salario Base: $salarioBase Bs\n<br>";
    echo "Prima Profesionales: $primaProfesionales Bs\n<br>";
    echo "Prima Antigüedad: $primaAntiguedad Bs\n<br>";
    echo "Prima de Transporte: $primaTransporte Bs\n<br>";
    echo "Prima de Escalafón: $primaEscalafon Bs\n<br>";
    echo "Prima de Frontera: $primaFrontera Bs\n<br>";
    echo "Prima de discapacidades: $primaDiscapacidad Bs\n<br>";
    echo "Prima de Beca: $primaBeca Bs\n<br>";
    echo "Prima de Salud: $primaSalud Bs\n<br>";
    echo "Prima de Antiguedad Especial: $primaAntiguedadEspecial Bs\n<br>";
    echo "S.S.O: $deduccionSSO Bs\n<br>";
    echo "RPE: $deduccionRPE Bs\n<br>";
    echo "AP S.S.O: $deduccionAPSSO Bs\n<br>";
    echo "AP RPE: $deduccionAPRPE Bs\n<br>";
    echo "Prima por Hijos: $primaPorHijos Bs\n<br><br><br>";
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
datosEmpleados($conexion);
?>
