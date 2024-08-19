<?php

require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once '../sistema_global/config.php'; // Ajusta la ruta según la ubicación de mpdf

$base_url = constant('URL')."back/modulo_registro_control/";
use Mpdf\Mpdf;

$id_empleado = $_GET['id_empleado'];
$fecha = $_GET['fecha'];

$fecha = str_replace(' ', '_', $fecha);

$pdf_files = [
    "{$base_url}regcon_reintegro_pdf2.php?id_empleado=$id_empleado&fecha=$fecha" => "reintegro_{$id_empleado}_1.pdf",
    "{$base_url}regcon_reintegro_pdf3.php?id_empleado=$id_empleado&fecha=$fecha" => "reintegro_{$id_empleado}_2.pdf",
];

// Nombre del archivo ZIP que se generará
$zip_filename = "reintegro_{$id_empleado}.zip";

// Crear una instancia de la clase ZipArchive
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}

// Agregar PDFs relacionados con bancos al ZIP
foreach ($pdf_files as $url => $pdf_filename) {
    // Obtener el contenido HTML
    $html = file_get_contents($url);

    // Generar el PDF con mPDF
    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);

    // Obtener el contenido del PDF generado
    $pdf_content = $mpdf->Output('', 'S');

    // Agregar el PDF al archivo ZIP
    $zip->addFromString($pdf_filename, $pdf_content);
}

// Cerrar el archivo ZIP
$zip->close();

// Configurar las cabeceras para la descarga del archivo ZIP
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename=' . basename($zip_filename));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zip_filename));

// Limpiar el búfer de salida y desactivar la salida en búfer
ob_clean();
flush();

// Leer el archivo ZIP y enviarlo al navegador para su descarga
readfile($zip_filename);

// Eliminar el archivo ZIP del servidor después de la descarga
//unlink($zip_filename);


?>
