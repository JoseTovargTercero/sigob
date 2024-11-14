<?php
require_once '../sistema_global/session.php';
require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración
require '../modulo_pl_formulacion/lib/FPDI-2.6.0/src/autoload.php';
require_once '../sistema_global/conexion.php'; // Archivo de conexión con $conexion

use Mpdf\Mpdf;


$data = json_decode(file_get_contents('php://input'), true)['data'];
$id_compromiso = $data['id_compromiso'];



$pdf_files = [];
$url_pdf = "{$base_url}pre_pdf_compromiso.php?id=" . $id_compromiso;
$pdf_files["{$url_pdf}"] =  "Compromiso.pdf";


$zip_filename = "Compromisos.zip";
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}




foreach ($pdf_files as $url => $pdf_filename) {
    $html = file_get_contents($url);

   $mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'tempDir' => __DIR__ . '/temp/mpdf',
    'margin_left' => 15,  // margen izquierdo estándar (en mm)
    'margin_right' => 15, // margen derecho estándar (en mm)
    'margin_top' => 16,   // margen superior estándar (en mm)
    'margin_bottom' => 16 // margen inferior estándar (en mm)
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
