<?php

require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', TRUE);
$correlativo = $_GET['correlativo'];

// URL del script que genera el contenido del PDF
$htmlUrl = "http://localhost/sigob/back/modulo_nomina/nom_recibos_pagos.php?correlativo=$correlativo";

// Establecer límite de páginas por archivo PDF
$pagesPerFile = 5; // Por ejemplo, 5 páginas por archivo

// Obtener contenido HTML
$html = file_get_contents($htmlUrl);

// Dividir el contenido en partes
$pages = explode('<!-- PAGE_BREAK -->', $html); // Marca de separación en el HTML

// Generar y guardar cada parte como un archivo PDF
$pageCount = count($pages);
for ($i = 0; $i < $pageCount; $i += $pagesPerFile) {
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($pages[$i]);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $filename = "nominabonoespecial-".$correlativo."_part".($i/$pagesPerFile + 1).".pdf";
    $output = $dompdf->output();
    file_put_contents($filename, $output);
}

// Opcional: combinar los archivos generados si es necesario

// Descargar el primer archivo generado
$dompdf = new Dompdf($options);
$dompdf->loadHtml($pages[0]);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("nominabonoespecial-".$correlativo."_part1.pdf", array("Attachment" => true));

?>
