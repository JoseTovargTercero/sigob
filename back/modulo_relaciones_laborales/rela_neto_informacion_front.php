<?php
function generarNetoInformacion($id_empleado, $conn){
    $query = "SELECT
                e.cedula AS cedula, 
                e.nombres AS nombres, 
                cg.cargo AS cargo, 
                e.fecha_ingreso AS fecha_de_ingreso, 
                '' AS fecha_de_egreso, 
                rp.asignaciones AS asignacion, 
                rp.deducciones AS deduccion, 
                rp.aportes AS aporte, 
                rp.total_pagar AS total_pagar, 
                rp.sueldo_base AS sueldo_base,
                rp.fecha_pagar AS fecha_pagar2,
                rp.nombre_nomina AS nombre_nomina,
                n.id AS id_nomina, 
                n.frecuencia AS frecuencia_nomina,
                e.banco AS centro_de_pago, 
                e.cod_cargo AS co_cargo, 
                e.cuenta_bancaria AS cuenta_bancaria 
            FROM 
                recibo_pago rp 
            JOIN 
                empleados e ON rp.id_empleado = e.id 
            JOIN 
                cargos_grados cg ON e.cod_cargo = cg.cod_cargo 
            LEFT JOIN 
                nominas n ON rp.nombre_nomina = n.nombre 
            WHERE 
                rp.id_empleado = :id_empleado";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $datosRetornados = [];

    $conceptoQuery = "SELECT codigo_concepto FROM conceptos WHERE nom_concepto = :nom_concepto";
    $conceptoStmt = $conn->prepare($conceptoQuery);

    function obtenerCodigoConcepto($conceptoStmt, $nom_concepto) {
        $conceptoStmt->bindValue(':nom_concepto', $nom_concepto, PDO::PARAM_STR);
        $conceptoStmt->execute();
        $result = $conceptoStmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['codigo_concepto'] : null;
    }

    $datosPorFecha = [];
    $datosPorTrimestre = [
        'Q1' => ['asignaciones' => [], 'deducciones' => [], 'aportes' => [], 'sueldo_total' => 0],
        'Q2' => ['asignaciones' => [], 'deducciones' => [], 'aportes' => [], 'sueldo_total' => 0],
        'Q3' => ['asignaciones' => [], 'deducciones' => [], 'aportes' => [], 'sueldo_total' => 0],
        'Q4' => ['asignaciones' => [], 'deducciones' => [], 'aportes' => [], 'sueldo_total' => 0],
    ];

    foreach ($results as $row) {
        $sueldoTotal = $row['sueldo_base'];

        $asignaciones = json_decode($row['asignacion'], true);
        $deducciones = json_decode($row['deduccion'], true);
        $aportes = json_decode($row['aporte'], true);

        $fecha_pagar2 = $row['fecha_pagar2'];
        if (!isset($datosPorFecha[$fecha_pagar2])) {
            $datosPorFecha[$fecha_pagar2] = [
                'asignaciones' => [],
                'deducciones' => [],
                'aportes' => [],
                'sueldo_total' => 0
            ];
        }

        foreach ($asignaciones as $nom_concepto => $valor) {
            $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
            if ($codigo_concepto !== null) {
                if (!isset($datosPorFecha[$fecha_pagar2]['asignaciones'][$codigo_concepto])) {
                    $datosPorFecha[$fecha_pagar2]['asignaciones'][$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
                }
                $datosPorFecha[$fecha_pagar2]['asignaciones'][$codigo_concepto]['valor'] += $valor;
            }
        }

        foreach ($deducciones as $nom_concepto => $valor) {
            $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
            if ($codigo_concepto !== null) {
                if (!isset($datosPorFecha[$fecha_pagar2]['deducciones'][$codigo_concepto])) {
                    $datosPorFecha[$fecha_pagar2]['deducciones'][$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
                }
                $datosPorFecha[$fecha_pagar2]['deducciones'][$codigo_concepto]['valor'] += $valor;
            }
        }

        foreach ($aportes as $nom_concepto => $valor) {
            $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
            if ($codigo_concepto !== null) {
                if (!isset($datosPorFecha[$fecha_pagar2]['aportes'][$codigo_concepto])) {
                    $datosPorFecha[$fecha_pagar2]['aportes'][$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
                }
                $datosPorFecha[$fecha_pagar2]['aportes'][$codigo_concepto]['valor'] += $valor;
            }
        }

        $datosPorFecha[$fecha_pagar2]['sueldo_total'] += $sueldoTotal;

        // Ajustar el análisis de DateTime para 'mm-yyyy'
        $fecha = DateTime::createFromFormat('m-Y', $fecha_pagar2);
        if ($fecha === false) {
            echo "Error al crear objeto DateTime para la fecha: $fecha_pagar2\n";
            continue;
        }

        $mes = (int) $fecha->format('m');
        $año = $fecha->format('Y');
        $trimestre = 'Q' . ceil($mes / 3);

        if (!isset($datosPorTrimestre[$trimestre])) {
            echo "Trimestre $trimestre no encontrado en datosPorTrimestre\n";
            continue;
        }

        foreach ($asignaciones as $nom_concepto => $valor) {
            $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
            if ($codigo_concepto !== null) {
                if (!isset($datosPorTrimestre[$trimestre]['asignaciones'][$codigo_concepto])) {
                    $datosPorTrimestre[$trimestre]['asignaciones'][$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
                }
                $datosPorTrimestre[$trimestre]['asignaciones'][$codigo_concepto]['valor'] += $valor;
            }
        }

        foreach ($deducciones as $nom_concepto => $valor) {
            $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
            if ($codigo_concepto !== null) {
                if (!isset($datosPorTrimestre[$trimestre]['deducciones'][$codigo_concepto])) {
                    $datosPorTrimestre[$trimestre]['deducciones'][$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
                }
                $datosPorTrimestre[$trimestre]['deducciones'][$codigo_concepto]['valor'] += $valor;
            }
        }

        foreach ($aportes as $nom_concepto => $valor) {
            $codigo_concepto = obtenerCodigoConcepto($conceptoStmt, $nom_concepto);
            if ($codigo_concepto !== null) {
                if (!isset($datosPorTrimestre[$trimestre]['aportes'][$codigo_concepto])) {
                    $datosPorTrimestre[$trimestre]['aportes'][$codigo_concepto] = ['nom_concepto' => $nom_concepto, 'valor' => 0];
                }
                $datosPorTrimestre[$trimestre]['aportes'][$codigo_concepto]['valor'] += $valor;
            }
        }

        $datosPorTrimestre[$trimestre]['sueldo_total'] += $sueldoTotal;
    }

    $datosRetornados['datos_por_fecha'] = $datosPorFecha;
    $datosRetornados['datos_por_trimestre'] = $datosPorTrimestre;

    return $datosRetornados;
}


// Captura el cuerpo de la solicitud
$json = file_get_contents('php://input');

// Decodifica el JSON a un array asociativo
$data = json_decode($json, true);



// El código de conexión y consulta
if (isset($data['cedula'])) {
    $cedula = $data['cedula'];


    $conn = new PDO('mysql:host=localhost;dbname=sigob;charset=utf8', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT id FROM empleados WHERE cedula = :cedula";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':cedula', $cedula, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $id_empleado = $result['id'];
        $netoDatos = generarNetoInformacion($id_empleado, $conn);

        header('Content-Type: application/json');
        echo json_encode($netoDatos, JSON_PRETTY_PRINT);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Empleado no encontrado.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => "Parametro 'cedula' es requerido."]);
}

$conn = null;
?>
