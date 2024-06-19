
<?php

  require_once("../plunig/dompdf/autoload.inc.php");
  use Dompdf\Dompdf;
  use Dompdf\Options;

  $options = new Options();
  $options->set('isRemoteEnabled', TRUE);
  $dompdf = new Dompdf($options);
  $correlativo = $_GET['correlativo'];
  $identificador = "s1";
  $html=file_get_contents("http://localhost/sigob/back/modulo_nomina/venezuela_pdf.php?correlativo=$correlativo&identificador=$identificador");

  // Load HTML content 
  $dompdf->loadHtml($html); 
 
  // (Optional) Setup the paper size and orientation 
  $dompdf->setPaper('A4', 'portrait'); 
 
  // Render the HTML as PDF 
  $dompdf->render(); 
 
  // Output the generated PDF to Browser 
  $dompdf->stream("relacion_de_banco_venezuela.pdf", array("Attachment" => false));
?>