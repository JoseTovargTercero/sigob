<?php
include('../sistema_global/session.php');

require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración

use Mpdf\Mpdf;

// Leer el cuerpo de la solicitud HTTP
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Obtener 'fecha_pagar' y 'cedula' desde el JSON recibido
$fecha_pagar = $data['fecha_pagar'];
$cedula = $data['cedula'];

if ($fecha_pagar == '' || $cedula == '') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Cedula y fecha son requeridos']);
    exit;
}



$pdf_files = [
    "{$base_url}neto.php?fecha_pagar=$fecha_pagar&cedula=$cedula" => "Neto {$cedula}.pdf",
];

// Nombre del archivo ZIP que se generará
$zip_filename = "neto{$cedula}.zip";

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
unlink($zip_filename);


// Salir del script
exit;

?>