<?php

require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf

use Mpdf\Mpdf;

$correlativo = $_GET['correlativo'];
$frecuencia = $_GET['frecuencia'];

// Lista de archivos de texto a incluir en el ZIP
if ($frecuencia == 1) {
    $txt_files = [
        "tesoro_{$correlativo}_s1.txt",
        "tesoro_{$correlativo}_s2.txt",
        "tesoro_{$correlativo}_s3.txt",
        "tesoro_{$correlativo}_s4.txt",
        "venezuela_{$correlativo}_s1.txt",
        "venezuela_{$correlativo}_s2.txt",
        "venezuela_{$correlativo}_s3.txt",
        "venezuela_{$correlativo}_s4.txt",
        "bicentenario_{$correlativo}_s1.txt",
        "bicentenario_{$correlativo}_s2.txt",
        "bicentenario_{$correlativo}_s3.txt",
        "bicentenario_{$correlativo}_s4.txt",
        "caroni_{$correlativo}_s1.txt",
        "caroni_{$correlativo}_s2.txt",
        "caroni_{$correlativo}_s3.txt",
        "caroni_{$correlativo}_s4.txt",
    ];
} elseif ($frecuencia == 2) {
    $txt_files = [
        "tesoro_{$correlativo}_q1.txt",
        "tesoro_{$correlativo}_q2.txt",
        "venezuela_{$correlativo}_q1.txt",
        "venezuela_{$correlativo}_q2.txt",
        "bicentenario_{$correlativo}_q1.txt",
        "bicentenario_{$correlativo}_q2.txt",
        "caroni_{$correlativo}_q1.txt",
        "caroni_{$correlativo}_q2.txt",
    ];
} elseif ($frecuencia == 3 || $frecuencia == 4) {
    $txt_files = [
        "tesoro_{$correlativo}_unico.txt",
        "venezuela_{$correlativo}_unico.txt",
        "bicentenario_{$correlativo}_unico.txt",
        "caroni_{$correlativo}_unico.txt",
    ];
}

if ($frecuencia == 1) {
    $pdf_files = [
    "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=s1" => "relacion_de_banco_venezuela_s1.pdf",
    "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=s2" => "relacion_de_banco_venezuela_s2.pdf",
    "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=s3" => "relacion_de_banco_venezuela_s3.pdf",
    "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=s4" => "relacion_de_banco_venezuela_s4.pdf",
    "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=s1" => "relacion_de_banco_tesoro_s1.pdf",
    "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=s2" => "relacion_de_banco_tesoro_s2.pdf",
    "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=s3" => "relacion_de_banco_tesoro_s3.pdf",
    "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=s4" => "relacion_de_banco_tesoro_s4.pdf",
    "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=s1" => "relacion_de_banco_bicentenario_s1.pdf",
    "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=s2" => "relacion_de_banco_bicentenario_s2.pdf",
    "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=s3" => "relacion_de_banco_bicentenario_s3.pdf",
    "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=s4" => "relacion_de_banco_bicentenario_s4.pdf",
    "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=s1" => "relacion_de_banco_caroni_s1.pdf",
    "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=s2" => "relacion_de_banco_caroni_s2.pdf",
    "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=s3" => "relacion_de_banco_caroni_s3.pdf",
    "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=s4" => "relacion_de_banco_caroni_s4.pdf",
];
} elseif ($frecuencia == 2) {
    $pdf_files = [
    "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=q1" => "relacion_de_banco_venezuela_q1.pdf",
    "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=q2" => "relacion_de_banco_venezuela_q2.pdf",
    "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=q1" => "relacion_de_banco_tesoro_q1.pdf",
    "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=q2" => "relacion_de_banco_tesoro_q2.pdf",
    "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=q1" => "relacion_de_banco_bicentenario_q1.pdf",
    "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=q2" => "relacion_de_banco_bicentenario_q2.pdf",
    "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=q1" => "relacion_de_banco_caroni_q1.pdf",
    "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=q2" => "relacion_de_banco_caroni_q2.pdf",
];
} elseif ($frecuencia == 3 || $frecuencia == 4) {
   $pdf_files = [
    "http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=unico" => "relacion_de_banco_venezuela_unico.pdf",
    "http://localhost/sigob/back/modulo_nomina/tesoro_pdf.php?correlativo=$correlativo&identificador=unico" => "relacion_de_banco_tesoro_unico.pdf",
    "http://localhost/sigob/back/modulo_nomina/bicentenario_pdf.php?correlativo=$correlativo&identificador=unico" => "relacion_de_banco_bicentenario_unico.pdf",
    "http://localhost/sigob/back/modulo_nomina/caroni_pdf.php?correlativo=$correlativo&identificador=unico" => "relacion_de_banco_caroni_unico.pdf",
];
}
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
