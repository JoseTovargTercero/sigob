<?php
require_once '../sistema_global/conexion.php';

// Recibir el array enviado desde el primer archivo
$data = json_decode(file_get_contents('php://input'), true);

// Construir la consulta SQL para insertar datos
$sql = "INSERT INTO empleados (nacionalidad, cedula, nombres, otros_años, status, observacion, cod_cargo, banco, cuenta_bancaria, hijos, instruccion_academica, discapacidades, tipo_nomina, id_dependencia, verificado, correcion, beca, fecha_ingreso)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar la declaración SQL
$stmt = $conexion->prepare($sql);

if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conexion->error);
}

// Crear variables para valores constantes
$verificado = '1';
$correcion = NULL;

// Vincular parámetros y ejecutar la consulta
$stmt->bind_param("ssssssssssssssssss", $data["nacionalidad"], $data["cedula"], $data["nombres"], $data["otros_años"], $data["status"], $data["observacion"], $data["cod_cargo"], $data["banco"], $data["cuenta_bancaria"], $data["hijos"], $data["instruccion_academica"], $data["discapacidades"], $data["tipo_nomina"], $data["id_dependencia"], $verificado, $correcion, $data["beca"], $data["fecha_ingreso"]);

// Ejecutar la consulta preparada
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Datos insertados correctamente."]);
    // Obtener el ID del empleado insertado
    $id_empleado = $conexion->insert_id;
    // Ajustar valores del empleado
    ajustarValoresEmpleado($id_empleado);
} else {
    echo "Error al insertar datos: " . $stmt->error;
}

// Cerrar la declaración y la conexión
$stmt->close();
$conexion->close();

// Función para ajustar valores del empleado
function ajustarValoresEmpleado($id_empleado)
{
    global $conexion;

    // Paso 2: Consultar los Valores del Empleado
    $sqlEmpleado = "SELECT beca, hijos FROM empleados WHERE id = ?";
    $stmtEmpleado = $conexion->prepare($sqlEmpleado);
    if ($stmtEmpleado === false) {
        die('Error en la preparación de la consulta del empleado: ' . $conexion->error);
    }
    $stmtEmpleado->bind_param("i", $id_empleado);
    $stmtEmpleado->execute();
    $resultEmpleado = $stmtEmpleado->get_result();
    $empleado = $resultEmpleado->fetch_assoc();
    $stmtEmpleado->close();

    $beca = $empleado['beca'];
    $hijos = $empleado['hijos'];

    // Paso 3: Obtener el conceptos.maxval del Concepto para beca y hijos
    $sqlConceptos = "
        SELECT id, maxval, valor, tipo_calculo
        FROM conceptos 
        WHERE nom_concepto LIKE ? 
        ORDER BY id DESC
    ";

    $stmtConceptoBeca = $conexion->prepare($sqlConceptos);
    if ($stmtConceptoBeca === false) {
        die('Error en la preparación de la consulta de conceptos: ' . $conexion->error);
    }
    $conceptoBecaLike = '%BECA%';
    $stmtConceptoBeca->bind_param("s", $conceptoBecaLike);
    $stmtConceptoBeca->execute();
    $resultConceptoBeca = $stmtConceptoBeca->get_result();
    $conceptoBeca = $resultConceptoBeca->fetch_assoc();
    $conceptoBecaId = $conceptoBeca['id'];
    $maxvalBeca = $conceptoBeca['maxval'];
    $valorBeca = $conceptoBeca['valor'];
    $tipoCalculoBeca = $conceptoBeca['tipo_calculo'];
    $stmtConceptoBeca->close();

    $stmtConceptoHijos = $conexion->prepare($sqlConceptos);
    if ($stmtConceptoHijos === false) {
        die('Error en la preparación de la consulta de conceptos: ' . $conexion->error);
    }
    $conceptoHijosLike = '%HIJO%';
    $stmtConceptoHijos->bind_param("s", $conceptoHijosLike);
    $stmtConceptoHijos->execute();
    $resultConceptoHijos = $stmtConceptoHijos->get_result();
    $conceptoHijos = $resultConceptoHijos->fetch_assoc();
    $conceptoHijosId = $conceptoHijos['id'];
    $maxvalHijos = $conceptoHijos['maxval'];
    $valorHijos = $conceptoHijos['valor'];
    $tipoCalculoHijos = $conceptoHijos['tipo_calculo'];
    $stmtConceptoHijos->close();

    // Paso 4: Verificar y Ajustar los Valores
    if ($beca > $maxvalBeca || $hijos > $maxvalHijos) {
        // Paso 5: Agregar Nuevas Formulaciones
        $sqlInsertConcepto = "
            INSERT INTO conceptos_formulacion (tipo_calculo, condicion, valor, concepto_id) 
            VALUES (?, ?, ?, ?)
        ";

        if ($hijos > $maxvalHijos) {
            $stmtInsertHijos = $conexion->prepare($sqlInsertConcepto);
            if ($stmtInsertHijos === false) {
                die('Error en la preparación de la consulta de inserción: ' . $conexion->error);
            }

            for ($i = $maxvalHijos + 1; $i <= $hijos; $i++) {
                $valorNuevoHijos = $valorHijos * $i; // Ajuste según la lógica real
                $condicionHijos = 'hijos=' . $i;
                $stmtInsertHijos->bind_param("ssii", $tipoCalculoHijos, $condicionHijos, $valorNuevoHijos, $conceptoHijosId);
                if (!$stmtInsertHijos->execute()) {
                    echo 'Error al insertar formulación de hijos: ' . $stmtInsertHijos->error;
                }
            }
            $stmtInsertHijos->close();
        }

        if ($beca > $maxvalBeca) {
            $stmtInsertBeca = $conexion->prepare($sqlInsertConcepto);
            if ($stmtInsertBeca === false) {
                die('Error en la preparación de la consulta de inserción: ' . $conexion->error);
            }

            // Asumiendo lógica similar para beca, ajustar según sea necesario
            for ($i = $maxvalBeca + 1; $i <= $beca; $i++) {
                $valorNuevoBeca = $valorBeca * $i; // Ajuste según la lógica real
                $condicionBeca = 'beca=' . $i;
                $stmtInsertBeca->bind_param("ssii", $tipoCalculoBeca, $condicionBeca, $valorNuevoBeca, $conceptoBecaId);
                if (!$stmtInsertBeca->execute()) {
                    echo 'Error al insertar formulación de beca: ' . $stmtInsertBeca->error;
                }
            }
            $stmtInsertBeca->close();
        }

        // Actualizar maxval en la tabla conceptos
        if ($hijos > $maxvalHijos || $beca > $maxvalBeca) {
            $sqlUpdateConceptos = "
                UPDATE conceptos 
                SET maxval = CASE 
                                WHEN id = ? THEN ?
                                WHEN id = ? THEN ?
                              END 
                WHERE id IN (?, ?)
            ";

            $stmtUpdateConceptos = $conexion->prepare($sqlUpdateConceptos);
            if ($stmtUpdateConceptos === false) {
                die('Error en la preparación de la consulta de actualización: ' . $conexion->error);
            }

            $stmtUpdateConceptos->bind_param("iiiiii", $conceptoBecaId, $beca, $conceptoHijosId, $hijos, $conceptoBecaId, $conceptoHijosId);
            if (!$stmtUpdateConceptos->execute()) {
                echo 'Error al actualizar maxval en conceptos: ' . $stmtUpdateConceptos->error;
            }

            $stmtUpdateConceptos->close();
        }
    }
}
?>