<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
function generarNetoInformacion($id_empleado, $conexion)
{
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
                rp.id_empleado = ?";

    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_empleado);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = $result->fetch_all(MYSQLI_ASSOC);

    $datosPorAnoMes = [];
    $datosPorTrimestre = [];

    $mesesEspañol = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
        '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
        '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
        '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
    ];

    $codigoSueldoBase = "sueldo_base";

    foreach ($results as $row) {
        $sueldoBase = $row['sueldo_base'];
        $total_pagar = $row['total_pagar'];

        $asignaciones = json_decode($row['asignacion'], true);
        $deducciones = json_decode($row['deduccion'], true);
        $aportes = json_decode($row['aporte'], true);

        $fecha_pagar2 = $row['fecha_pagar2'];

        $fechaParts = explode('-', $fecha_pagar2);
        if (count($fechaParts) == 2) {
            $mes = str_pad($fechaParts[0], 2, '0', STR_PAD_LEFT);
            $año = $fechaParts[1];
            $fechaFormateada = "$año-$mes-01";
        } else {
            echo "Formato de fecha no válido: $fecha_pagar2\n";
            continue;
        }

        $fecha = DateTime::createFromFormat('Y-m-d', $fechaFormateada);
        if ($fecha === false) {
            echo "Error al crear objeto DateTime para la fecha: $fechaFormateada\n";
            continue;
        }

        $mes = $fecha->format('m');
        $año = $fecha->format('Y');
        $mesTexto = $mesesEspañol[$mes];
        $anoMes = $mesTexto;  // Solo el nombre del mes
        $trimestre = 'Q' . ceil((int)$fecha->format('m') / 3);

        if (!isset($datosPorAnoMes[$año])) {
            $datosPorAnoMes[$año] = [];
        }

        if (!isset($datosPorAnoMes[$año][$anoMes])) {
            $datosPorAnoMes[$año][$anoMes] = [
                'asignaciones' => [],
                'deducciones' => [],
                'aportes' => [],
                'sueldo_total' => 0
            ];
        }

        foreach ($asignaciones as $nom_concepto => $valor) {
            if (!isset($datosPorAnoMes[$año][$anoMes]['asignaciones'][$nom_concepto])) {
                $datosPorAnoMes[$año][$anoMes]['asignaciones'][$nom_concepto] = [
                    'nom_concepto' => $nom_concepto,
                    'valor' => 0
                ];
            }
            $datosPorAnoMes[$año][$anoMes]['asignaciones'][$nom_concepto]['valor'] += $valor;
        }

        if ($codigoSueldoBase !== null) {
            if (!isset($datosPorAnoMes[$año][$anoMes]['asignaciones'][$codigoSueldoBase])) {
                $datosPorAnoMes[$año][$anoMes]['asignaciones'][$codigoSueldoBase] = [
                    'nom_concepto' => 'Sueldo Base',
                    'valor' => 0
                ];
            }
            $datosPorAnoMes[$año][$anoMes]['asignaciones'][$codigoSueldoBase]['valor'] += $sueldoBase;
        }

        foreach ($deducciones as $nom_concepto => $valor) {
            if (!isset($datosPorAnoMes[$año][$anoMes]['deducciones'][$nom_concepto])) {
                $datosPorAnoMes[$año][$anoMes]['deducciones'][$nom_concepto] = [
                    'nom_concepto' => $nom_concepto,
                    'valor' => 0
                ];
            }
            $datosPorAnoMes[$año][$anoMes]['deducciones'][$nom_concepto]['valor'] += $valor;
        }

        foreach ($aportes as $nom_concepto => $valor) {
            if (!isset($datosPorAnoMes[$año][$anoMes]['aportes'][$nom_concepto])) {
                $datosPorAnoMes[$año][$anoMes]['aportes'][$nom_concepto] = [
                    'nom_concepto' => $nom_concepto,
                    'valor' => 0
                ];
            }
            $datosPorAnoMes[$año][$anoMes]['aportes'][$nom_concepto]['valor'] += $valor;
        }

        $datosPorAnoMes[$año][$anoMes]['sueldo_total'] += $total_pagar;

        if (!isset($datosPorTrimestre[$año])) {
            $datosPorTrimestre[$año] = [
                'Q1' => ['asignaciones' => [], 'deducciones' => [], 'aportes' => [], 'sueldo_total' => 0, 'ultimo_mes' => ''],
                'Q2' => ['asignaciones' => [], 'deducciones' => [], 'aportes' => [], 'sueldo_total' => 0, 'ultimo_mes' => ''],
                'Q3' => ['asignaciones' => [], 'deducciones' => [], 'aportes' => [], 'sueldo_total' => 0, 'ultimo_mes' => ''],
                'Q4' => ['asignaciones' => [], 'deducciones' => [], 'aportes' => [], 'sueldo_total' => 0, 'ultimo_mes' => ''],
            ];
        }

        $trimestreDatos = &$datosPorTrimestre[$año][$trimestre];

        $trimestreDatos['asignaciones'] = $asignaciones;
        $trimestreDatos['deducciones'] = $deducciones;
        $trimestreDatos['aportes'] = $aportes;
        $trimestreDatos['sueldo_total'] = $total_pagar;
        $trimestreDatos['ultimo_mes'] = $anoMes;
    }

    foreach ($datosPorTrimestre as $anio => &$trimestres) {
        foreach ($trimestres as $trimestre => &$datos) {
            if ($datos['ultimo_mes'] !== '') {
                $mesTrimestre = $datos['ultimo_mes'];
                $anioTrimestre = $anio;

                if (isset($datosPorAnoMes[$anioTrimestre][$mesTrimestre])) {
                    $datos['asignaciones'] = array_values($datosPorAnoMes[$anioTrimestre][$mesTrimestre]['asignaciones']);
                    $datos['deducciones'] = array_values($datosPorAnoMes[$anioTrimestre][$mesTrimestre]['deducciones']);
                    $datos['aportes'] = array_values($datosPorAnoMes[$anioTrimestre][$mesTrimestre]['aportes']);
                    $datos['sueldo_total'] = $datosPorAnoMes[$anioTrimestre][$mesTrimestre]['sueldo_total'];
                }
            }
        }
    }

    return [
        'datos_por_ano_mes' => $datosPorAnoMes,
        'datos_por_trimestre' => $datosPorTrimestre
    ];
}

// Captura el cuerpo de la solicitud
$json = file_get_contents('php://input');

// Decodifica el JSON a un array asociativo
$data = json_decode($json, true);

if (isset($data['cedula'])) {
    $cedula = $data['cedula'];
    $query_id = "SELECT id FROM empleados WHERE cedula = ?";
    $stmt = $conexion->prepare($query_id);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $id_empleado = $row['id'];
        $datosNeto = generarNetoInformacion($id_empleado, $conexion);
        header('Content-Type: application/json');
        echo json_encode($datosNeto);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Empleado no encontrado']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Cédula no proporcionada']);
}

?>
