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
$fecha = date('d-m-y');

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
        'formato' => [430, 216]
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
        'formato' => 'A4'
    ]
];

$pdf_files = [];
$url_pdf = "{$base_url}form_pdf_$tipo.php?id_ejercicio=" . $id_ejercicio;

if ($tipo == '2015') {
    // Datos de sector y programa
    $sector = $data['sector'];
    $programa = $data['programa'];

    // Consulta base
    $query = "SELECT id, sector, programa FROM pl_sectores_presupuestarios WHERE 1=1";
    $params = [];
    $types = "";

    // Condiciones dinámicas para sector y programa
    if ($sector != '') {
        $query .= " AND sector = ?";
        $params[] = $sector;
        $types .= "s";
    }

    if ($programa != '') {
        $query .= " AND programa = ?";
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

    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $sector = str_pad($row['sector'], 2, '0', STR_PAD_LEFT);
        $programa = str_pad($row['programa'], 2, '0', STR_PAD_LEFT);

        $pdf_files["{$url_pdf}&id=$id"] = "{$sector}-{$programa}_CREDITOS.pdf";
    }

    $result->free();
    $stmt->close();
} elseif ($tipo == 'descripcion') {
    $queryDescripcionProgramas = "SELECT id_sector, id_programa FROM descripcion_programas";
    $resultDescripcionProgramas = $conexion->query($queryDescripcionProgramas);

    if ($resultDescripcionProgramas && $resultDescripcionProgramas->num_rows > 0) {
        while ($rowDescripcion = $resultDescripcionProgramas->fetch_assoc()) {
            $sector_descripcion = $rowDescripcion['id_sector'];
            $programa_descripcion = $rowDescripcion['id_programa'];
            $pdf_files["{$url_pdf}&id_sector=$sector_descripcion&id_programa=$programa_descripcion"] = "{$sector_descripcion}-{$programa_descripcion}_descripcion.pdf";
        }
    }
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
