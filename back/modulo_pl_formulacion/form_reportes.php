<?php
require_once '../sistema_global/session.php';
require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración
require 'lib/FPDI-2.6.0/src/autoload.php';
require_once '../sistema_global/conexion.php'; // Archivo de conexión con $conexion

use Mpdf\Mpdf;


$data = json_decode(file_get_contents('php://input'), true)['data'];
$id_ejercicio = $data['ejercicio_fiscal'];
$tipo = $data['tipo'];

if ($tipo == '' || !isset($data['tipo'])) {
    throw new Exception("No se ha recibido una solicitud valida.", 1);
    exit;
}


$fecha = date('d-m-y');

/*
$id_ejercicio = '1';

$tipo = 'distribucion';*/

$reportes = [
    '2002' => [
        'nombre' => 'FORMULARIO 2002 RESUMEN DE LOS CRED. PRESP. SECTORES',
        'formato' => 'A4-L'
    ],
    '2004' => [
        'nombre' => 'FORMULARIO 2004 RESUMEN A NIVEL DE SECTORES. Y PROGRAMA',
        'formato' => 'A4-L'
    ],
    '2005' => [
        'nombre' => 'FORMULARIO 2005 RESM CRED A NIVEL DE PARTIDAS Y PROGRAMAS ' . $fecha,
        'formato' => 'A4-L'
    ],
    '2006' => [
        'nombre' => 'FORMULARIO 2006 RESUM. CRED. PRES. A NIVEL  PARTIDAS DE SECTORES ' . $fecha,
        'formato' => 'A4-L'
    ],
    '2009' => [
        'nombre' => 'FORMULARIO 2009 GASTOS DE INVERSION ESTIMADO ' . $fecha,
        'formato' => 'A4-L'
    ],
    '2010' => [
        'nombre' => 'FORMULARIO 2010 TRASFERENCIAS Y DONACIONES',
        'formato' => 'A4-L'
    ],
    '2015' => [
        'nombre' => 'FORM. 2015 CRED. PRE. DEL SEC PRO. A NIVEL DE PAR.',
        'formato' => 'A4-L'
    ],
    'informacion' => [
        'nombre' => 'INFORMACIÓN GENERAL DE LA ENTIDAD FEDERAL',
        'formato' => 'A4-L'
    ],
    'indice' => [
        'nombre' => 'ÍNDICE DE CATEGORÍAS PROGRAMÁTICAS',
        'formato' => 'A4-L'
    ],
    'descripcion' => [
        'nombre' => 'DESCRIPCION DEL PROGRAMA,  SUB - PROGRAMA Y PROYECTO',
        'formato' => 'A4-L'
    ],
    'presupuesto' => [
        'nombre' => 'LEY DE PRESUPUESTO DE INGRESOS Y GASTOS DEL ESTADO AMAZONAS',
        'formato' => 'A4-L'
    ],
    'distribucion' => [
        'nombre' => 'DISTRIBUCIÓN INSTITUCIONAL',
        'formato' => 'A4-L'
    ],
    'metas' => [
        'nombre' => 'METAS DEL PROGRAMA, SUB-PROGRAMA Y/O PROYECTO',
        'formato' => 'A4-L'
    ],
];

$pdf_files = [];
$url_pdf = "{$base_url}form_pdf_$tipo.php?id_ejercicio=" . $id_ejercicio;

if ($tipo == '2015') {
    // Datos de sector y programa
    $sector = $data['sector'];
    $programa = $data['programa'];

    // Consulta base con JOIN para pl_sectores y pl_programas
    $query = "
    SELECT p.id AS id_programa, s.id AS id_sector, s.sector AS sector, p.programa AS programa 
    FROM pl_sectores s
    JOIN pl_programas p ON s.id = p.sector
    WHERE 1=1";
    $params = [];
    $types = "";

    // Condiciones dinámicas para sector y programa
    if ($sector != '') {
        $query .= " AND s.id = ?";
        $params[] = $sector;
        $types .= "s";
    }

    if ($programa != '') {
        $query .= " AND p.id = ?";
        $params[] = $programa;
        $types .= "s";
    }

    $stmt = $conexion->prepare($query);

    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        die("Error en la consulta: " . $stmt->error);
    }

    $pdf_files = [];

    while ($row = $result->fetch_assoc()) {
        $id_sector = $row['id_sector'];
        $id_programa = $row['id_programa'];
        $sector_formatted = str_pad($row['sector'], 2, '0', STR_PAD_LEFT);
        $programa_formatted = str_pad($row['programa'], 2, '0', STR_PAD_LEFT);

        $pdf_files["{$url_pdf}&id_sector=$id_sector&id_programa=$id_programa"] = "{$sector_formatted}-{$programa_formatted}_CREDITOS.pdf";
    }

    $result->free();
    $stmt->close();
} elseif ($tipo == 'descripcion') {
    $queryDescripcionProgramas = "SELECT ps.sector, pp.programa, DP.id_sector, DP.id_programa 
    FROM descripcion_programas DP
    JOIN pl_sectores ps ON DP.id_sector = ps.id
    JOIN pl_programas pp ON DP.id_programa = pp.id";

$resultDescripcionProgramas = $conexion->query($queryDescripcionProgramas);

if ($resultDescripcionProgramas && $resultDescripcionProgramas->num_rows > 0) {
    while ($rowDescripcion = $resultDescripcionProgramas->fetch_assoc()) {
        $sector_descripcion = $rowDescripcion['id_sector'];
        $programa_descripcion = $rowDescripcion['id_programa'];

        // Verificar existencia en la tabla `entes`
        $queryVerificarEnte = "SELECT 1 FROM entes 
            WHERE sector = $sector_descripcion AND programa = $programa_descripcion
            LIMIT 1";
        $resultVerificarEnte = $conexion->query($queryVerificarEnte);

        if ($resultVerificarEnte && $resultVerificarEnte->num_rows > 0) {
            $sector = $rowDescripcion['sector'];
            $programa = $rowDescripcion['programa'];
            $pdf_files["{$url_pdf}&id_sector=$sector_descripcion&id_programa=$programa_descripcion"] = "{$sector}-{$programa}.pdf";
        }
    }
}

}elseif ($tipo == 'metas') {
$pdf_files = [];

// Consulta a pl_programas para obtener id, sector y programa
$queryProgramas = "SELECT id, sector, programa FROM pl_programas";
$resultProgramas = $conexion->query($queryProgramas);

while ($rowPrograma = $resultProgramas->fetch_assoc()) {
    $id_programa = $rowPrograma['id'];
    $programa = $rowPrograma['programa'];

    // Consulta a pl_sectores para obtener el sector que coincide con el id de sector de pl_programas
    $querySector = "SELECT sector FROM pl_sectores WHERE id = ?";
    $stmtSector = $conexion->prepare($querySector);
    $stmtSector->bind_param("i", $rowPrograma['sector']);
    $stmtSector->execute();
    $resultSector = $stmtSector->get_result();

    if ($rowSector = $resultSector->fetch_assoc()) {
        $sector = $rowSector['sector'];

        // Genera la URL del PDF y lo almacena en el array con el formato especificado
        $pdf_files["{$url_pdf}&id_programa=$id_programa&id_ejercicio=$id_ejercicio"] = "{$sector}-{$programa}.pdf";
    }

    $stmtSector->close();
}

$resultProgramas->close();
} elseif ($tipo == 'distribucion') {

    function obtenerActividades($ue)
    {
        global $conexion;

        $actividades = [];

        $stmt = mysqli_prepare($conexion, "SELECT * FROM `entes_dependencias` WHERE ue = ? ORDER BY actividad ASC");
        $stmt->bind_param('i', $ue);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($actividades, $row['actividad']);
            }
        }
        $stmt->close();
        // retorna el primer y ultimo elemento del array

        return [$actividades[0], $actividades[count($actividades) - 1]];
    }


    $stmt = mysqli_prepare($conexion, "SELECT entes.id, ps.sector, pp.programa, entes.sector AS sec_id, entes.programa AS pro_id FROM `entes`
    JOIN pl_sectores ps ON entes.sector = ps.id
    JOIN pl_programas pp ON entes.programa = pp.id");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id_sector = $row['sec_id'];
            $id_programa = $row['pro_id'];
            $sector = $row['sector'];
            $programa = $row['programa'];
            $id = $row['id'];

            $actividades = obtenerActividades($row['id']);
            $actividad_1 = $actividades[0];
            $actividad_2 = $actividades[1];


            $pdf_files["{$url_pdf}&ente=$id&id_ejercicio=$id_ejercicio"] = "{$sector}-{$programa}-{$actividad_1}-{$actividad_2}.pdf";
        }
    }
    $stmt->close();
} else {
    $pdf_files["{$url_pdf}"] = $reportes[$tipo]['nombre'] . ".pdf";
}

$zip_filename = "Reportes.zip";
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}




foreach ($pdf_files as $url => $pdf_filename) {
    $html = file_get_contents($url);

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => $reportes[$tipo]['formato'],
        'tempDir' => __DIR__ . '/temp/mpdf',
        'margin_left' => 8,
        'margin_right' => 8
    ]);
    $mpdf->SetHTMLHeader('<div style="text-align: right;">Página {PAGENO} de {nb}</div>');
    $mpdf->WriteHTML($html);
    $mpdf->Output($pdf_filename, 'F');
    $zip->addFile($pdf_filename);
}

$zip->close();

header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename=' . basename($zip_filename));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zip_filename));

ob_clean();
flush();
readfile($zip_filename);

unlink($zip_filename);
foreach ($pdf_files as $pdf_filename) {
    unlink($pdf_filename);
}

exit;
