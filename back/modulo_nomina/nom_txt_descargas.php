<?php

require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración
require_once 'lib/TCPDF/tcpdf.php';
require 'lib/FPDI-2.6.0/src/autoload.php';
require_once 'lib/libmergepdf-master/src/Merger.php';
require_once '../sistema_global/conexion.php'; // Archivo de conexión con $conexion

use Mpdf\Mpdf;

// Autoload manual de clases
spl_autoload_register(function ($class) {
    $prefix = 'iio\\libmergepdf\\';
    $base_dir = __DIR__ . '/lib/libmergepdf-master/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use iio\libmergepdf\Merger;

$correlativo = $_POST['correlativo'];
$identificador = $_POST['identificador'];

// Definir el número de registros por página
$registrosPorPagina = 350;

// Obtener el número total de registros para la paginación
$sql = "SELECT COUNT(*) AS total FROM recibo_pago WHERE correlativo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $correlativo);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalRegistros = $row['total'];

$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Archivos TXT y PDFs a incluir en el ZIP
$txt_files = [
    "tesoro_{$correlativo}_{$identificador}.txt",
    "venezuela_{$correlativo}_{$identificador}.txt",
    "bicentenario_{$correlativo}_{$identificador}.txt",
    "caroni_{$correlativo}_{$identificador}.txt",
];

$pdf_files = [
    "{$base_url}venezuela_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_venezuela_{$identificador}.pdf",
    "{$base_url}tesoro_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_tesoro_{$identificador}.pdf",
    "{$base_url}bicentenario_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_bicentenario_{$identificador}.pdf",
    "{$base_url}caroni_pdf.php?correlativo=$correlativo&identificador=$identificador" => "relacion_de_banco_caroni_{$identificador}.pdf",
    "{$base_url}nom_resumen_nomina.php?correlativo=$correlativo" => "Resumen_de_nomina_{$correlativo}.pdf",
];

// Nombre del archivo ZIP que se generará
$zip_filename = "archivos__{$correlativo}_Paginado.zip";

// Crear una instancia de la clase ZipArchive
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}

$merger = new Merger(); // Crear instancia de Merger para combinar los PDFs

// Generar PDFs por cada página y agregar al merger (no se agregan al ZIP individualmente)
for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
    $pdf_filename = "Recibos_de_pago_{$correlativo}_Pagina_{$pagina}.pdf";
    $url = "{$base_url}nom_recibos_pagos.php?correlativo=$correlativo&pagina=$pagina";

    // Obtener el contenido HTML
    $html = file_get_contents($url);

    // Generar el PDF con mPDF en orientación horizontal
    $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
    $mpdf->WriteHTML($html);

    // Guardar el PDF generado temporalmente en el servidor
    $mpdf->Output($pdf_filename, 'F');

    // Agregar el archivo PDF a la lista de PDFs a unificar
    $merger->addFile($pdf_filename);
}

// Unificar los PDFs generados
$combinedPdf = $merger->merge();

// Guardar el PDF unificado en el servidor
$combinedPdfFilename = "NOMINA_.pdf";
file_put_contents($combinedPdfFilename, $combinedPdf);

// Agregar el PDF unificado al archivo ZIP
$zip->addFile($combinedPdfFilename);

// Agregar archivos TXT al ZIP
foreach ($txt_files as $txt_file) {
    $file_path = "../../txt/" . $txt_file;
    if (file_exists($file_path)) {
        $zip->addFile($file_path, $txt_file);
    }
}

// Agregar PDFs relacionados con bancos al ZIP
foreach ($pdf_files as $url => $pdf_filename) {
    // Obtener el contenido HTML
    $html = file_get_contents($url);

    // Generar el PDF con mPDF en orientación horizontal
    $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
    $mpdf->WriteHTML($html);

    // Guardar el PDF generado temporalmente en el servidor
    $mpdf->Output($pdf_filename, 'F');

    // Agregar el PDF al archivo ZIP
    $zip->addFile($pdf_filename);
}

// Construir la consulta SQL para actualizar datos
$sql2 = "UPDATE peticiones SET status_archivos = ? WHERE correlativo = ?";
$stmt2 = $conexion->prepare($sql2);

if ($stmt2 === false) {
    die("Error al preparar la consulta.");
}

// Vincular los parámetros
$status_archivos = 1;
$stmt2->bind_param('ii', $status_archivos, $correlativo);

// Ejecutar la consulta
if ($stmt2->execute() === false) {
    die("Error al ejecutar la consulta: " . $conexion->error);
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

// Eliminar los archivos temporales (ZIP y PDFs) del servidor después de la descarga
unlink($zip_filename);
unlink($combinedPdfFilename);
for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
    unlink("Recibos_de_pago_{$correlativo}_Pagina_{$pagina}.pdf");
}
foreach ($pdf_files as $pdf_filename) {
    unlink($pdf_filename);
}

// Salir del script
exit;

?>
