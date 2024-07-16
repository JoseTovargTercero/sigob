<?php

require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración

use Mpdf\Mpdf;

$correlativo = $_POST['correlativo'];
$identificador = $_POST['identificador'];

// Definir el número de registros por página
$registrosPorPagina = 350;

// Obtener el número total de registros para la paginación
$conn = new PDO('mysql:host=localhost;dbname=sigob', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM recibo_pago
    WHERE correlativo = :correlativo
");
$stmt->bindValue(':correlativo', $correlativo, PDO::PARAM_STR);
$stmt->execute();
$totalRegistros = $stmt->fetchColumn();

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

// Agregar archivos TXT al ZIP
foreach ($txt_files as $txt_file) {
    $file_path = "../../txt/" . $txt_file;
    if (file_exists($file_path)) {
        $zip->addFile($file_path, $txt_file);
    }
}

// Generar PDFs por cada página y agregarlos al ZIP
for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
    $pdf_filename = "Recibos_de_pago_{$correlativo}_Pagina_{$pagina}.pdf";
    $url = "{$base_url}nom_recibos_pagos.php?correlativo=$correlativo&pagina=$pagina";

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
