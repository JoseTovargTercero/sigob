<?php
// require_once '../sistema_global/session.php';
require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración
require '../modulo_pl_formulacion/lib/FPDI-2.6.0/src/autoload.php';
require_once '../sistema_global/conexion.php'; // Archivo de conexión con $conexion

use Mpdf\Mpdf;

$id_compromiso = $_GET['id_compromiso'];

// Consultar la base de datos para obtener numero_compromiso y tabla_registro
$query = "SELECT numero_compromiso, tabla_registro FROM compromisos WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_compromiso);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $numero_compromiso = $row['numero_compromiso'];
    $tabla_registro = $row['tabla_registro'];
} else {
    exit("No se encontró el compromiso.");
}
$stmt->close();

// Determinar el prefijo según la tabla_registro
$prefijo = '';
switch ($tabla_registro) {
    case 'gastos':
        $prefijo = 'G';
        break;
    case 'solicitud_dozavos':
        $prefijo = 'D';
        break;
    case 'proyecto':
        $prefijo = 'P';
        break;
    default:
        exit("Tipo de compromiso desconocido.");
}

// Nombre del archivo ZIP
$zip_filename = "{$prefijo}-{$numero_compromiso}.zip";

$pdf_files = [];
$url_pdf = "{$base_url}pre_pdf_compromiso.php?id=" . $id_compromiso;
$pdf_files["{$url_pdf}"] = "Compromiso.pdf";

$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}

foreach ($pdf_files as $url => $pdf_filename) {
    $html = file_get_contents($url);

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'tempDir' => __DIR__ . '/temp/mpdf',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16
    ]);
    $mpdf->SetHTMLHeader('<div style="text-align: right;">Página {PAGENO} de {nb}</div>');
    $mpdf->WriteHTML($html);
    $mpdf->Output($pdf_filename, 'F');
    $zip->addFile($pdf_filename);
}

$zip->close();

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
