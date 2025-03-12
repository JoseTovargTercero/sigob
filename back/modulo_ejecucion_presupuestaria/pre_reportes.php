<?php
require_once '../sistema_global/session.php';
require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración
require '../modulo_pl_formulacion/lib/FPDI-2.6.0/src/autoload.php';
require_once '../sistema_global/conexion.php'; // Archivo de conexión con $conexion

use Mpdf\Mpdf;


$data = json_decode(file_get_contents('php://input'), true)['data'];
$id_ejercicio = $data['ejercicio_fiscal'];
$tipo = $data['tipo'];
if ($tipo == "compromiso") {
    $tipo_tabla = $data['tipo_tabla'];
    if ($tipo_tabla == "dozavos") {
        $tipo_tabla = 'solicitud_dozavos';
    }
    if ($data['tipo_fecha'] == "Mensual") {
        $tipo_fecha = "mensual";
    }else{
        $tipo_fecha = "trimestre";
    }
    $fecha = $data['fecha'];
}


if ($tipo == '' || !isset($data['tipo'])) {
    throw new Exception("No se ha recibido una solicitud valida.", 1);
    exit;
}



/*
$id_ejercicio = '1';
$tipo = 'distribucion';
*/

$reportes = [
    'sectores' => [
        'nombre' => 'SECTORES',
        'formato' => 'A4-L'
    ],
    'partidas' => [
        'nombre' => 'PARTIDAS',
        'formato' => 'A4-L'
    ],
    'secpro' => [
        'nombre' => 'SECTORES Y PROGRAMAS',
        'formato' => 'A4-L'
    ],
    'compromiso' => [
        'nombre' => 'REPORTE COMPROMISO',
        'formato' => 'A4-L'
    ],
];

$pdf_files = [];
$url_pdf = "{$base_url}pre_pdf_$tipo.php?id_ejercicio=" . $id_ejercicio;
if ($tipo == "compromiso") {
    if ($tipo_fecha == "mensual") {
        if ($tipo_tabla == "gastos") {
            $fecha += 1;
        }
        
    }
    $url_pdf = "{$base_url}pre_compromisos_reporte.php?id_ejercicio=$id_ejercicio&tipo=$tipo_tabla&tipo_fecha=$tipo_fecha&fecha=$fecha";
}

if ($tipo == 'sectores') {
    $pdf_files["{$url_pdf}&id_ejercicio=$id_ejercicio"] = "SECTORES.pdf";
} elseif ($tipo == 'partidas') {
    $pdf_files["{$url_pdf}&id_ejercicio=$id_ejercicio"] = "PARTIDAS.pdf";
}elseif($tipo == 'compromiso'){
    $pdf_files["{$url_pdf}&id_ejercicio=$id_ejercicio"] = "Compromiso.pdf";
} else {
    $pdf_files["{$url_pdf}&id_ejercicio=$id_ejercicio"] = "SECTORES Y PROGRAMAS.pdf";
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
        'margin_left' => 16,  // margen izquierdo estándar (en mm)
        'margin_right' => 15, // margen derecho estándar (en mm)
        'margin_top' => 16,   // margen superior estándar (en mm)
        'margin_bottom' => 15 // margen inferior estándar (en mm)
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
