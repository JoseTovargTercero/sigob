<?php
require_once '../sistema_global/session.php';
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


$data = json_decode(file_get_contents('php://input'), true)['data'];
$id_ejercicio = $data['ejercicio_fiscal'];
$tipo = $data['tipo'];
$fecha = date('d-m-y');

$reportes = [
    '2002' => [
        'nombre' => 'FORMULARIO 2002 RESUMEN DE LOS CRED. PRESP. SECTORES'
    ],
    '2004' => [
        'nombre' => 'FORMULARIO 2004 RESUMEN A NIVEL DE SECTORES. Y PROGRAMA'
    ],
    '2005' => [
        'nombre' => 'FORMULARIO 2005 RESM CRED A NIVEL DE PARTIDAS Y PROGRAMAS ' . $fecha
    ],
    '2006' => [
        'nombre' => 'FORMULARIO 2006 RESUM. CRED. PRES. A NIVEL  PARTIDAS DE SECTORES ' . $fecha
    ],
    '2009' => [
        'nombre' => 'FORMULARIO 2009 GASTOS DE INVERSION ESTIMADO ' . $fecha
    ],
    '2010' => [
        'nombre' => 'FORMULARIO 2010 TRASFERENCIAS Y DONACIONES'
    ],
];

$pdf_files = [];
$url_pdf = "{$base_url}form_pdf_$tipo.php?id_ejercicio=" . $id_ejercicio;


if ($tipo == '2015') { // Se generean

    // Consulta para obtener todos los registros de la tabla pl_sectores_presupuestarios
    $query = "SELECT id, sector, programa FROM pl_sectores_presupuestarios";
    $result = $conexion->query($query);

    if ($result === false) {
        die("Error en la consulta: " . $conexion->error);
    }

    // Generar el array de archivos PDF con los sectores y programas
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $sector = str_pad($row['sector'], 2, '0', STR_PAD_LEFT); // Asegurar formato de 2 dígitos
        $programa = str_pad($row['programa'], 2, '0', STR_PAD_LEFT); // Asegurar formato de 2 dígitos

        $pdf_files["{$url_pdf}&id=$id"] = "{$sector}-{$programa}_CREDITOS.pdf";
    }
} else {
    $pdf_files["{$url_pdf}"] = $reportes[$tipo]['nombre'] . ".pdf";
}



// Nombre del archivo ZIP que se generará
$zip_filename = "Reportes.zip";

// Crear una instancia de la clase ZipArchive
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}

$merger = new Merger(); // Crear instancia de Merger para combinar los PDFs

// Agregar archivos PDF de sectores al ZIP
foreach ($pdf_files as $url => $pdf_filename) {
    // Obtener el contenido HTML
    $html = file_get_contents($url);
    // Generar el PDF con mPDF en orientación horizontal
    $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
    $mpdf->WriteHTML($html);
    // Guardar el PDF generado temporalmente en el servidor
    $mpdf->Output($pdf_filename, 'F');
    // Agregar el archivo PDF al archivo ZIP
    $zip->addFile($pdf_filename);
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
foreach ($pdf_files as $pdf_filename) {
    unlink($pdf_filename);
}

// Salir del script
exit;
