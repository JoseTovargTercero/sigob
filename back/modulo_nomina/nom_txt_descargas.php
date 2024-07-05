<?php

require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf

use Mpdf\Mpdf;

$correlativo = $_POST['correlativo'];
$identificador = $_POST['identificador'];


    $txt_files = [
        "tesoro_{$correlativo}_{$identificador}.txt",
        "venezuela_{$correlativo}_{$identificador}.txt",
        "bicentenario_{$correlativo}_{$identificador}.txt",
        "caroni_{$correlativo}_{$identificador}.txt",
    ];

    $pdf_files = [
        "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_venezuela_{$identificador}.pdf",
        "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_tesoro_{$identificador}.pdf",
        "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_bicentenario_{$identificador}.pdf",
        "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_caroni_{$identificador}.pdf",
        
    ];

// Array con los URLs de los archivos PDF que quieres generar


// Nombre del archivo ZIP que se generará
$zip_filename = "archivos__{$correlativo}.zip";

// Crear una instancia de la clase ZipArchive
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}

// Agregar archivos de texto al ZIP
foreach ($txt_files as $txt_file) {
    $file_path = "../../txt/" . $txt_file;
    if (file_exists($file_path)) {
        $zip->addFile($file_path, $txt_file);
    }
}

// Generar y agregar los PDFs al ZIP
foreach ($pdf_files as $url => $pdf_filename) {
    // Obtener el contenido HTML
    $html = file_get_contents($url);

    // Generar el PDF con mpdf
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