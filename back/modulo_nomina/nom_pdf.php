<?php

require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

$correlativo = $_GET['correlativo'];
$identificador = "unico";

// Array con los URLs de los archivos PDF que quieres generar
$pdf_urls = array(
    "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_venezuela.pdf",
    "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_tesoro.pdf",
    "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_bicentenario.pdf",
    "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_caroni.pdf"
);

// Crear una carpeta temporal para almacenar los PDFs
$tempDir = sys_get_temp_dir() . '/pdf_temp';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Generar y guardar los PDFs en la carpeta temporal
foreach ($pdf_urls as $url => $filename) {
    // Obtener el contenido HTML
    $html = file_get_contents($url);
    
    // Generar el PDF con mpdf
    $mpdfConfig = [
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font_size' => 0,
        'default_font' => '',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_header' => 0,
        'margin_footer' => 0,
        'orientation' => 'P'
    ];
    $mpdf = new Mpdf($mpdfConfig);
    
    // Escribir el HTML al PDF
    $mpdf->WriteHTML($html);
    
    // Guardar el PDF en la carpeta temporal
    $mpdf->Output($tempDir . '/' . $filename, 'F');
}

// Crear el archivo ZIP
$zipFileName = 'archivos_pdf.zip';
$zip = new ZipArchive();

if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die('Error al crear el archivo ZIP');
}

// Agregar los PDFs desde la carpeta temporal al archivo ZIP
foreach ($pdf_urls as $filename) {
    $zip->addFile($tempDir . '/' . $filename, $filename);
}

$zip->close();

// Descargar el archivo ZIP
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . $zipFileName);
header('Content-Length: ' . filesize($zipFileName));
readfile($zipFileName);

// Eliminar los PDFs de la carpeta temporal
foreach ($pdf_urls as $filename) {
    unlink($tempDir . '/' . $filename);
}

// Eliminar la carpeta temporal vacía
rmdir($tempDir);

?>
