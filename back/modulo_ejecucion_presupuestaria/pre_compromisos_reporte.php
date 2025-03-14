<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/conexion_remota.php';
global $ano_ejercicio, $situado_ejercicio;
// Función para manejar errores
function manejarError($mensaje, $db = null)
{
    if ($db && $db->error) {
        $mensaje .= " Error en la base de datos: " . $db->error;
    }
    error_log($mensaje);
    echo json_encode(["error" => $mensaje]);
    exit;
}

function procesarDatos($tipo, $tipo_fecha, $fecha, $local_db, $remote_db, $id_ejercicio)
{
    try {
        $db = $local_db; // Solo se usa $local_db
        global $ano_ejercicio, $situado_ejercicio;

        // Consultar el ejercicio fiscal
        $stmt_ejercicio = $db->prepare("SELECT * FROM ejercicio_fiscal WHERE id = ?");
        if (!$stmt_ejercicio) {
            manejarError("Fallo al preparar la consulta del ejercicio fiscal.", $db);
        }
        $stmt_ejercicio->bind_param('i', $id_ejercicio);
        $stmt_ejercicio->execute();
        $result_ejercicio = $stmt_ejercicio->get_result();
        $ejercicio = $result_ejercicio->fetch_assoc();
        $stmt_ejercicio->close();

        if (!$ejercicio) {
            manejarError("No se encontró el ejercicio fiscal para el ID proporcionado.");
        }

        // Variables del ejercicio fiscal
        $ano_ejercicio = $ejercicio['ano'];
        $situado_ejercicio = $ejercicio['situado'];

        // Consultar la tabla compromisos
        $stmt = $db->prepare("SELECT * FROM compromisos WHERE tabla_registro = ?");
        if (!$stmt) {
            manejarError("Fallo al preparar la consulta de compromisos.", $db);
        }
        $stmt->bind_param('s', $tipo);
        $stmt->execute();
        $result_compromisos = $stmt->get_result();
        $compromisos = $result_compromisos->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $data = [];

        foreach ($compromisos as $compromiso) {
            $id_registro = $compromiso['id_registro'];
            $mes = null;

            if ($tipo === 'solicitud_dozavos') {
                $stmt_solicitud = $db->prepare("SELECT mes FROM solicitud_dozavos WHERE id = ?");
                if (!$stmt_solicitud) {
                    manejarError("Fallo al preparar la consulta de solicitud_dozavos.", $db);
                }
                $stmt_solicitud->bind_param('i', $id_registro);
                $stmt_solicitud->execute();
                $result_solicitud = $stmt_solicitud->get_result();
                $solicitud = $result_solicitud->fetch_assoc();
                $stmt_solicitud->close();
                if (!$solicitud) continue;

                $mes = $solicitud['mes'] + 1; // Si es 0, lo cambia a 1 automáticamente

            } elseif ($tipo === 'gastos') {
                $stmt_gasto = $db->prepare("SELECT fecha FROM gastos WHERE id = ?");
                if (!$stmt_gasto) {
                    manejarError("Fallo al preparar la consulta de gastos.", $db);
                }
                $stmt_gasto->bind_param('i', $id_registro);
                $stmt_gasto->execute();
                $result_gasto = $stmt_gasto->get_result();
                $gasto = $result_gasto->fetch_assoc();
                $stmt_gasto->close();
                if (!$gasto) continue;

                $mes = (int)date('n', strtotime($gasto['fecha']));
            } elseif ($tipo === 'proyecto_credito') {
                $stmt_gasto = $db->prepare("
                    SELECT ca.fecha 
                    FROM proyecto_credito pc
                    JOIN credito_adicional ca ON pc.id_credito = ca.id
                    WHERE pc.id = ?
                ");
                if (!$stmt_gasto) {
                    manejarError("Fallo al preparar la consulta de crédito adicional.", $db);
                }
                $stmt_gasto->bind_param('i', $id_registro);
                $stmt_gasto->execute();
                $result_gasto = $stmt_gasto->get_result();
                $gasto = $result_gasto->fetch_assoc();
                $stmt_gasto->close();
                if (!$gasto) continue;

                $mes = (int)date('n', strtotime($gasto['fecha']));
            }

            // Validar si el mes pertenece al trimestre o al mes exacto
            if ($tipo_fecha === 'trimestre') {
                $trimestre = (int)$fecha;
                if ($trimestre == 1) {
                    $inicio_trimestre = 1;
                    $fin_trimestre = 3;
                } elseif ($trimestre == 2) {
                    $inicio_trimestre = 4;
                    $fin_trimestre = 6;
                } elseif ($trimestre == 3) {
                    $inicio_trimestre = 7;
                    $fin_trimestre = 9;
                } elseif ($trimestre == 4) {
                    $inicio_trimestre = 10;
                    $fin_trimestre = 12;
                }


                if ($mes < $inicio_trimestre || $mes > $fin_trimestre) {
                    continue;
                }
            } else {
                if ($mes != $fecha) {
                    continue;
                }
            }

            $data[] = [
                'id' => $compromiso['id'],
                'correlativo' => $compromiso['correlativo'],
                'descripcion' => $compromiso['descripcion'],
                'id_registro' => $compromiso['id_registro'],
                'id_ejercicio' => $compromiso['id_ejercicio'],
                'tabla_registro' => $compromiso['tabla_registro'],
                'numero_compromiso' => $compromiso['numero_compromiso'],
            ];
        }

        return $data;
    } catch (Exception $e) {
        manejarError("Ocurrió un error inesperado: " . $e->getMessage());
    }
}

// Variables de entrada
$tipo = $_GET['tipo'] ?? '';
$tipo_fecha = $_GET['tipo_fecha'] ?? '';
$fecha = $_GET['fecha'] ?? '';
$id_ejercicio = $_GET['id_ejercicio'] ?? null;

if (!$id_ejercicio) {
    manejarError("El parámetro 'id_ejercicio' es obligatorio.");
}

$data = procesarDatos($tipo, $tipo_fecha, $fecha, $local_db, $remote_db, $id_ejercicio);



$stmt = mysqli_prepare($conexion, "SELECT * FROM `ejercicio_fiscal`  WHERE id = ?");
$stmt->bind_param('s', $id_ejercicio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ano = $row['ano']; // formato: dd-mm-YY
    }
}
$stmt->close();


if ($tipo_fecha == 'trimestre') {
    $trimestres = [
        '1' => 'primer trimestre',
        '2' => 'segundo trimestre',
        '3' => 'tercer trimestre',
        '4' => 'cuarto trimestre'
    ];
    $periodo_texto = $trimestres[$fecha];
} else {
    $meses = [
        '0' => 'enero',
        '1' => 'febrero',
        '2' => 'marzo',
        '3' => 'abril',
        '4' => 'mayo',
        '5' => 'junio',
        '6' => 'julio',
        '7' => 'agosto',
        '8' => 'septiembre',
        '9' => 'octubre',
        '10' => 'noviembre',
        '11' => 'diciembre'
    ];
    $periodo_texto = $meses[$fecha];
}

$tipos_gasto = [];

$stmt = mysqli_prepare($conexion, "SELECT * FROM `tipo_gastos`");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tipos_gasto[strtoupper($row['prefijo'])] = strtoupper($row['nombre']);
    }
}
$stmt->close();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Compromisos</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            margin: 10px;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            text-align: center;
        }

        td {
            padding: 5px;
        }

        th {
            font-weight: bold;
            text-align: center;
        }

        .py-0 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .pb-0 {
            padding-bottom: 0 !important;
        }

        .pt-0 {
            padding-top: 0 !important;
        }

        .b-1 {
            border: 1px solid;
        }

        .bc-lightgray {
            border-color: lightgray !important;
        }

        .bc-gray {
            border-color: gray;
        }

        .pt-1 {
            padding-top: 1rem !important;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        h2 {
            font-size: 16px;
            margin: 0;
        }

        .header-table {
            margin-bottom: 20px;
        }

        .small-text {
            font-size: 8px;
        }

        .w-50 {
            width: 50%;
        }

        .table-title {
            font-size: 10px;
            margin-top: 10px;
        }

        .logo {
            width: 120px;
        }

        .t-border-0>tr>td {
            border: none !important;
        }

        .fz-6 {
            font-size: 5px !important;
        }

        .fz-8 {
            font-size: 8px !important;
        }

        .fz-9 {
            font-size: 9px !important;
        }

        .fz-10 {
            font-size: 10px !important;
        }

        .bl {
            border-left: 1px solid gray;
        }

        .br {
            border-right: 1px solid gray;
        }

        .bb {
            border-bottom: 1px solid gray;
        }

        .bt {
            border-top: 1px solid gray;
        }

        .dw-nw {
            white-space: nowrap !important
        }

        @media print {
            .page-break {
                page-break-after: always;
            }
        }

        .t-content {
            page-break-inside: avoid;
        }

        .p-2 {
            padding: 10px;
        }

        .total_text {
            color: #8e1e1e;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <h2 align='center'>Total de compromisos para el <?= $periodo_texto ?> del <?= $ano ?></h2>
    <br>

    <table>
        <thead>
            <tr>
                <th class="bt bl bb p-15">#</th>
                <th class="bt bl bb p-15 text-left">Descripción</th>
                <th class="bt bl bb p-15">Fecha</th>
                <th class="bt bl bb p-15">Tipo de compromiso</th>
                <th class="bt bl bb p-15">Número de Compromiso</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($data)) {
                $count = 1;
                foreach ($data as $compromiso) {
                    $correlativo = $compromiso['correlativo'];
                    $descripcion = $compromiso['descripcion'];
                    $tabla_registro = $compromiso['tabla_registro'];
                    $numero_compromiso = $compromiso['numero_compromiso'];

                    $prefijo = strtoupper(substr($numero_compromiso, 0, 1));


                    echo "<tr>
                    <td class='fz-8 bl'>" . $count++ . "</td>
                    <td class='fz-8 bl text-left'>{$descripcion}</td>
                    <td class='fz-8 bl'>FECHA</td>
                    <td class='fz-8 bl'>{$tipos_gasto[$prefijo]}</td>
                    <td class='fz-8 bl br'>{$numero_compromiso}</td>
                </tr>";
                }
            } else {
                echo "<tr>
                <td colspan='7' class='text-center fz-8 bl br'>No se encontraron datos</td>
            </tr>";
            }
            ?>


            <tr>
                <td class='bt'></td>
                <td class='bt'></td>
                <td class='bt'></td>
                <td class='bt'></td>
                <td class='bt'></td>
                <td class='bt'></td>
            </tr>
        </tbody>
    </table>
</body>

</html>