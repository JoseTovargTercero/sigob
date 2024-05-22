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

// Función para calcular la nómina
function calcularNomina($conexion, $empleado) {
    // Calculo del salario base
    $salarioBase = calculoSalarioBase($conexion, $empleado);

    // Obtener instrucción académica
    $instruccionAcademica2 = buscarProfesiones($conexion, $empleado['instruccion_academica']);
    $primaProfesion = round(($salarioBase * $instruccionAcademica2['porcentaje'])/100);

    // Calcular la prima de antigüedad
    $primaAntiguedad = calcularPrimaAntiguedad($conexion, $empleado);
    $primaAntiguedad2 = round(($salarioBase * $primaAntiguedad)/100);

    // Calcular cuántos lunes tiene el mes actual
    $lunesMesActual = calcularLunesMesActual();

    // Calcular la prima por hijos
    $primaPorHijos = calcularPrimaPorHijos($empleado['hijos']);

    $sueldototal2 = round($salarioBase + ($primaProfesion + $primaAntiguedad2 + $primaPorHijos),2);
    $asignaciones1 = ($primaAntiguedad2 + $primaProfesion + $primaPorHijos);



    $bonovacacional = round((145/360)*($sueldototal2/30),2);
    $aguinaldo = round((120/360)*($sueldototal2/30),2);
    $salariointegral = round((($sueldototal2/30) + $bonovacacional + $aguinaldo)*30,2);
      $PH = $salariointegral * 0.01;
      $aportePH = round($salariointegral * 0.02,2);
      $IVSS = round(((($salarioBase * 12)/52)*0.04)*$lunesMesActual,2);
      $aporteIVSS = round(((($salarioBase * 12)/52)*0.09)*$lunesMesActual,2);
      $RPE = round(((($salarioBase*12)/52)*0.005)*$lunesMesActual,2);
      $aporteRPE = round(((($salarioBase*12)/52)*0.02)*$lunesMesActual,2);
      $pensionjubilacion = round($sueldototal2 * 0.03,2);
      $aportepension = round($sueldototal2 * 0.03,2);
      $quincena = ($salarioBase / 30)*15;
      $deducciones = round(($PH + $IVSS + $RPE + $pensionjubilacion)/2,2);
      $asignaciones = ($primaAntiguedad2 + $primaProfesion + $primaPorHijos) / 2;
      $asignaciones2 = ($primaAntiguedad2 + $primaProfesion + $primaPorHijos) / 2;
      $sueldototal = round($quincena + $asignaciones2 - $deducciones,2);


    echo "Empleado: " . $empleado['nombres'] . " (ID: " . $empleado['id_empleado'] . ")\n<br>";
    echo "Salario Base: $salarioBase Bs\n<br>";
    echo "Instrucción Académica: $primaProfesion Bs\n<br>";
    echo "Prima de Antigüedad: $primaAntiguedad2 Bs\n<br>";
    echo "Lunes en el Mes Actual: $lunesMesActual\n<br>";
    echo "El aporte de bonovacacional: $bonovacacional Bs\n<br>";
    echo "El aporte de aguinaldo: $aguinaldo Bs\n<br>";
    echo "El salario integral: $salariointegral\n<br>";
    echo "El sueldo total es: $sueldototal2\n<br>";
    echo "La quincena total es: $sueldototal\n<br>";
    echo "Prima por Hijos: $primaPorHijos\n<br>";
    echo "Bonos: $asignaciones1\n<br>";
    echo "Deducciones: $deducciones\n<br>";
    echo "PH: $PH\n<br>";
    echo "aporteIVSS: $aportePH\n<br>";
    echo "IVSS: $IVSS\n<br>";
    echo "aporteIVSS: $aporteIVSS\n<br>";
    echo "RPE: $RPE\n<br>";
    echo "aporteRPE: $aporteRPE\n\n<br><br><br>";
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

// Nueva función para buscar profesiones
function buscarProfesiones($conexion, $id_profesion) {
    $sql = "SELECT profesion, porcentaje FROM profesiones WHERE id_profesion = " . $id_profesion;
    $result = $conexion->query($sql);

    if ($result === false) {
        echo "Error en la consulta: " . $conexion->error . "\n";
        return array("profesion" => "No disponible", "porcentaje" => "No disponible");
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return array("profesion" => $row["profesion"], "porcentaje" => $row["porcentaje"]);
    } else {
        return array("profesion" => "No disponible", "porcentaje" => "No disponible");
    }
}

// Nueva función para calcular la prima de antigüedad
function calcularPrimaAntiguedad($conexion, $empleado) {
    $fechaIngreso = $empleado['fecha_ingreso'];
    $otrosAños = $empleado['otros_años'];

    // Calcular los años desde la fecha de ingreso hasta la fecha actual
    $fechaIngresoDate = new DateTime($fechaIngreso);
    $fechaActualDate = new DateTime();
    $interval = $fechaActualDate->diff($fechaIngresoDate);
    $añosDesdeIngreso = $interval->y;

    // Sumar los años adicionales
    $antiguedadTotal = $añosDesdeIngreso + $otrosAños;

    // Consultar la tabla primantiguedad para obtener el porcentaje correspondiente
    $sql = "SELECT porcentaje FROM primantiguedad WHERE tiempo = " . $antiguedadTotal;
    $result = $conexion->query($sql);

    if ($result === false) {
        echo "Error en la consulta: " . $conexion->error . "\n";
        return 0;
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["porcentaje"];
    } else {
        return 0; // No se encontró un porcentaje correspondiente, devolver 0
    }
}

// Nueva función para calcular cuántos lunes tiene el mes actual
function calcularLunesMesActual() {
    $mes = date('m');
    $ano = date('Y');
    $lunes = 0;
    $dia = 1;
    $maximoDias = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

    while ($dia <= $maximoDias) {
        $diaSemana = date('N', strtotime("$ano-$mes-$dia"));
        if ($diaSemana == 1) {
            $lunes++;
        }
        $dia++;
    }
    return $lunes;
}

// Nueva función para calcular la prima por hijos
function calcularPrimaPorHijos($cantidadHijos) {
    $montoPorHijo = 12.50;
    return $cantidadHijos * $montoPorHijo;
}

// Llamada a la función principal
datosEmpleados($conexion);
?>
