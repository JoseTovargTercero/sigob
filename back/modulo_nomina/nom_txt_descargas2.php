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

$data = json_decode(file_get_contents('php://input'), true);

    $cedula = $data['cedula'];
    $fecha_inicio = $data['fecha_inicio'];
    $fecha_final = $data['fecha_final'];
    $nombre_nomina = $data['nombre_nomina'];


// URL para el PDF único
$url = "{$base_url}nom_recibos_pagos_unico.php?cedula=$cedula&fecha_inicio=$fecha_inicio&fecha_final=$fecha_final&nombre_nomina=$nombre_nomina";
$pdf_filename = "Recibo_de_pago_{$cedula}.pdf";

// Crear una instancia de la clase ZipArchive
$zip_filename = "Recibo_de_pago_{$cedula}.zip";
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}

// Generar el PDF con mPDF en orientación horizontal
$mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
$html = file_get_contents($url);
$mpdf->WriteHTML($html);
$mpdf->Output($pdf_filename, 'F');

// Agregar el PDF generado al archivo ZIP
$zip->addFile($pdf_filename);



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

// Eliminar los archivos temporales (ZIP y PDF) del servidor después de la descarga
unlink($zip_filename);
unlink($pdf_filename);

// Salir del script
exit;

?>
