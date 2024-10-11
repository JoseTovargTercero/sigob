<?php
// Obtener el contenido del cuerpo de la solicitud
$input = file_get_contents('php://input');
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Decodificar el JSON recibido
$data = json_decode($input, true);
// Validar que se hayan recibido los arrays necesarios
if (isset($data['0102']) && isset($data['0163']) && isset($data['0175']) && isset($data['0128'])) {
    
    // Función para procesar el array del banco Venezuela y generar un archivo TXT
    function txt_Venezuela($data, $conexion) {
        $empleados = $data['empleados'];
        $total_a_pagar = $data['total_a_pagar'];
        $correlativo = $data['correlativo'];
        $identificador = $data['identificador'];
        // Ruta del archivo a generar
        $file_path = '../../txt/venezuela_'.$correlativo.'_'.$identificador.'.txt';
        $direccion = "C:/xampp/htdocs/sigob/txt/".$file_path;
        $dia = date('d');
   $mes = date('m');
   $ano = date('y');
   $total = number_format($total_a_pagar,2, "", "");
   $correlativo3 = intval($correlativo);
   $cedulas = array();
        $totalescobrar = array();
        $banco = "0102";
   if (!empty($empleados)) {
   if ($correlativo3 < 10) {
      $correlativo2 = "0".$correlativo3;
   }elseif($correlativo3 > 100){
      $correlativo2 = intval(substr($correlativo3, 0, 2));
   }else{
      $correlativo2 = $correlativo3;
   }
   if (strlen($total) == 1) {
        $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."000000000000".$total."03291". "\n";
      }elseif(strlen($total) == 2) {
        $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."00000000000".$total."03291". "\n";
      }elseif(strlen($total) == 3) {
        $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."0000000000".$total."03291". "\n";
      }elseif(strlen($total) == 4) {
        $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."000000000".$total."03291". "\n";
      }elseif(strlen($total) == 5) {
         $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."00000000".$total."03291". "\n";
      }elseif(strlen($total) == 6) {
         $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."0000000".$total."03291". "\n";
      }elseif(strlen($total) == 7) {
         $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."000000".$total."03291". "\n";
      }elseif(strlen($total) == 8) {
         $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."00000".$total."03291". "\n";
      }elseif(strlen($total) == 9) {
         $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."0000".$total."03291". "\n";
      }elseif(strlen($total) == 10) {
         $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."000".$total."03291". "\n";
      }elseif(strlen($total) == 11) {
        $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."00".$total."03291". "\n";
      }elseif(strlen($total) == 12) {
         $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."0".$total."03291". "\n";
      }elseif(strlen($total) == 13) {
         $header = "HGOBERNACION DEL ESTADO AMAZONAS         F.  C.  I.".$correlativo2.$dia."/".$mes."/".$ano."".$total."03291". "\n";
      }


        
        // Cuerpo del archivo
         // Abrir el archivo para escritura
    $file = fopen($file_path, 'w');
    if ($file === false) {
        die('Error al abrir el archivo para escritura');
    }

    // Escribir el encabezado
    fwrite($file, $header);
        $content = "";
        
        foreach ($empleados as $empleado) {
            $cedulas[] = $empleado['cedula'];
            $totalescobrar[] = $empleado['total_a_pagar'];
            $cedula = $empleado['cedula'];
            $nrocuenta = $empleado['cuenta_bancaria'];
            $caracter12 = $nrocuenta[11];
    if ($caracter12 == '1') {
        $tipocuenta = 1;
    } elseif ($caracter12 == '0') {
        $tipocuenta = 0;
    } 
            $totalcobra = number_format($empleado['total_a_pagar'],2, "", "");
            $prueba = substr($empleado['nombres'], 0,30);
            $prueba = str_pad($prueba, 39, " ");
            $line = "";
            if(strlen($cedula) == 7 AND $tipocuenta == 1){
      if (strlen($totalcobra) == 1) {
       $line =  "1". $nrocuenta. "0000000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  "1". $nrocuenta. "000000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line = "1". $nrocuenta. "00000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line = "1". $nrocuenta. "0000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line = "1". $nrocuenta. "000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line = "1". $nrocuenta. "00000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line = "1". $nrocuenta. "0000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line = "1". $nrocuenta. "000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line =  "1". $nrocuenta. "00". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line =  "1". $nrocuenta. "0". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line =  "1". $nrocuenta. "". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n"; 
      }  
   }elseif(strlen($cedula) == 8 AND $tipocuenta == 1){
      if (strlen($totalcobra) == 1) {
       $line =  "1". $nrocuenta. "0000000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  "1". $nrocuenta. "000000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line =  "1". $nrocuenta. "00000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line =  "1". $nrocuenta. "0000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line =  "1". $nrocuenta. "000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line =  "1". $nrocuenta. "00000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line =  "1". $nrocuenta. "0000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line =  "1". $nrocuenta. "000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line =  "1". $nrocuenta. "00". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line =  "1". $nrocuenta. "0". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line =  "1". $nrocuenta. "". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }
   }elseif(strlen($cedula) == 7 AND $tipocuenta == 0){
      if ($cedula == 7877725) {
      if (strlen($totalcobra) == 1) {
       $line =  "0". $nrocuenta."0000000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  "0". $nrocuenta."000000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line =  "0". $nrocuenta."00000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line =  "0". $nrocuenta."0000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line =  "0". $nrocuenta."000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line =  "0". $nrocuenta."00000". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line =  "0". $nrocuenta."0000". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line =  "0". $nrocuenta."000". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line =  "0". $nrocuenta."00". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line =  "0". $nrocuenta."0". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line =  "0". $nrocuenta."". $totalcobra . $tipocuenta . "770" . $prueba."  "."000". $cedula."003291" . "\n";
      }
   }else{
      if (strlen($totalcobra) == 1) {
       $line =  "0". $nrocuenta."0000000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  "0". $nrocuenta."000000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line =  "0". $nrocuenta."00000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line =  "0". $nrocuenta."0000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line =  "0". $nrocuenta."000000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line =  "0". $nrocuenta."00000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 7) {
        $line = "0". $nrocuenta."0000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 8) {
        $line = "0". $nrocuenta."000". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 9) {
        $line = "0". $nrocuenta."00". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 10) {
        $line = "0". $nrocuenta."0". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line =  "0". $nrocuenta."". $totalcobra . $tipocuenta . "770" . $prueba." "."000". $cedula."003291" . "\n";
      }
   }
   }elseif(strlen($cedula) == 8 AND $tipocuenta == 0){
      if ($cedula == 13920259 OR $cedula == 11202458 or $cedula == 10049489 OR  $cedula == 30385423 OR $cedula == 17633481 OR $cedula == 13657269) {
         if (strlen($totalcobra) == 1) {
       $line =  "0". $nrocuenta."0000000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  "0". $nrocuenta."000000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line =  "0". $nrocuenta."00000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 4) { 
       $line =  "0". $nrocuenta."0000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line = "0". $nrocuenta."000000". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line = "0". $nrocuenta."00000". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line = "0". $nrocuenta."0000". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line = "0". $nrocuenta."000". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 9) {
      $line =  "0". $nrocuenta."00". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line = "0". $nrocuenta."0". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line = "0". $nrocuenta."". $totalcobra . $tipocuenta . "770" . $prueba."  "."00". $cedula."003291" . "\n";
      }

   }else{
      if (strlen($totalcobra) == 1) {
       $line =  "0". $nrocuenta."0000000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  "0". $nrocuenta."000000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line =  "0". $nrocuenta."00000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line =  "0". $nrocuenta."0000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line =  "0". $nrocuenta."000000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line =  "0". $nrocuenta."00000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 7) {
        $line = "0". $nrocuenta."0000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line =  "0". $nrocuenta."000". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line =  "0". $nrocuenta."00". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 10) {
        $line = "0". $nrocuenta."0". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line =  "0". $nrocuenta."". $totalcobra . $tipocuenta . "770" . $prueba." "."00". $cedula."003291" . "\n";
      }
  
        } 
        }      
            
            fwrite($file, $line);
         
        }
         fclose($file);
        // Escribir el contenido al archivo
        
        

         header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment;'.'filename=$file_path');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
        // Terminar el script para que no se envíe más salida
       readfile($file_path);
}
       $cedulas_json = json_encode($cedulas);
$totalescobrar_json = json_encode($totalescobrar);

      if (!empty($cedulas)) {
      // Construir la consulta SQL para insertar datos
    $sql = "INSERT INTO informacion_pdf (cedula, total_pagar, correlativo, identificador, banco)
            VALUES (?, ?, ?, ?, ?)";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("sssss", $cedulas_json, $totalescobrar_json, $correlativo, $identificador, $banco);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        echo "Datos insertados correctamente.";
    } else {
        echo "Error al insertar datos: " . $conexion->error;
    }

    // Cerrar la declaración y la conexión
    $stmt->close();

    }
    }
    // Función para procesar el array del banco Tesoro
    function txt_Tesoro($data, $conexion) {
        $empleados = $data['empleados'];
        $total_a_pagar = $data['total_a_pagar'];
        $correlativo = $data['correlativo'];
        $identificador = $data['identificador'];
        // Ruta del archivo a generar
        $file_path = '../../txt/tesoro_'.$correlativo.'_'.$identificador.'.txt';
        $direccion = "C:/xampp/htdocs/sigob/".$file_path;   
        // Cuerpo del archivo
         // Abrir el archivo para escritura
        $cedulas = array();
    $totalescobrar = array();
    $banco = "0163";
    if (!empty($empleados)) {
    $file = fopen($file_path, 'w');
    if ($file === false) {
        die('Error al abrir el archivo para escritura');
    }


        $content = "";
        
    
    
        foreach ($empleados as $empleado) {
          $cedulas[] = $empleado['cedula'];
        $totalescobrar[] = $empleado['total_a_pagar'];
            $cedula = $empleado['cedula'];
            $nacionalidad = $empleado['nacionalidad'];
            $nrocuenta = $empleado['cuenta_bancaria'];
            $totalcobra = $empleado['total_a_pagar'];
            $prueba = substr($empleado['nombres'], 0,30);
            $prueba = str_pad($prueba, 39, " ");
            $line = "";
            if(strlen($cedula) == 7){
                $line =  $nacionalidad.'00'.$cedula.';'.$nrocuenta.';'.$totalcobra. "\n";
            }elseif(strlen($cedula) == 8){
                $line =  $nacionalidad.'0'.$cedula.';'.$nrocuenta.';'.$totalcobra. "\n";
            }else{
                $line =  $nacionalidad.''.$cedula.';'.$nrocuenta.';'.$totalcobra. "\n";
            } 
        }      
            
            fwrite($file, $line);
         
    
         fclose($file);
        // Escribir el contenido al archivo
        
        

        
         header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment;'.'filename=$file_path');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
   readfile($file_path); 
}    
        // Terminar el script para que no se envíe más salida
   // Insertar datos en la base de datos
    $cedulas_json = json_encode($cedulas);
    $totalescobrar_json = json_encode($totalescobrar);
    if (!empty($cedulas)) {
      // Construir la consulta SQL para insertar datos
    $sql = "INSERT INTO informacion_pdf (cedula, total_pagar, correlativo, identificador, banco)
            VALUES (?, ?, ?, ?, ?)";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("sssss", $cedulas_json, $totalescobrar_json, $correlativo, $identificador, $banco);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        echo "Datos insertados correctamente.";
    } else {
        echo "Error al insertar datos: " . $conexion->error;
    }

    // Cerrar la declaración y la conexión
    $stmt->close();

    }

    
   
    }

    // Función para procesar el array del banco Bicentenario
    function txt_Bicentenario($data, $conexion) {
         $empleados = $data['empleados'];
        $total_a_pagar = $data['total_a_pagar'];
        $correlativo = $data['correlativo'];
        $cantidad_bincentenario = $data['cantidad_bincentenario'];
        $identificador = $data['identificador'];
        // Ruta del archivo a generar
        $file_path = '../../txt/bicentenario_'.$correlativo.'_'.$identificador.'.txt';
        $direccion = "C:/xampp/htdocs/sigob/".$file_path;
        $dia = date('d');
   $mes = date('m');
   $ano = date('Y');
   $total = number_format($total_a_pagar,2, "", "");
   $correlativo3 = intval($correlativo);
   $cedulas = array();
        $totalescobrar = array();
        $banco = "0175";
   if (!empty($empleados)) {
   if ($correlativo3 < 10) {
      $correlativo2 = "0".$correlativo3;
   }elseif($correlativo3 > 100){
      $correlativo2 = intval(substr($correlativo3, 0, 2));
   }else{
      $correlativo2 = $correlativo3;
   }


    if ($cantidad_bincentenario > 0 AND $cantidad_bincentenario <= 9) {
            $cantidad_bincentenario2 = "00000".$cantidad_bincentenario;
        }elseif ($cantidad_bincentenario > 10 AND $cantidad_bincentenario <= 99) {
            $cantidad_bincentenario2 = "0000".$cantidad_bincentenario;
        }elseif ($cantidad_bincentenario > 100 AND $cantidad_bincentenario <= 999) {
           $cantidad_bincentenario2 = "000".$cantidad_bincentenario;
        }elseif($cantidad_bincentenario > 1000 AND $cantidad_bincentenario <= 9999) {
            $cantidad_bincentenario2 = "00".$cantidad_bincentenario;
        }elseif($cantidad_bincentenario > 10000 AND $cantidad_bincentenario <= 99999) {
            $cantidad_bincentenario2 = "0".$cantidad_bincentenario;
        }elseif($cantidad_bincentenario > 100000 AND $cantidad_bincentenario <= 999999) {
            $cantidad_bincentenario2 = $cantidad_bincentenario;
        }
   if (strlen($total) == 1) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."00000000000000".$total. "\n";
      }elseif(strlen($total) == 2) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."0000000000000".$total. "\n";
      }elseif(strlen($total) == 3) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."000000000000".$total. "\n";
      }elseif(strlen($total) == 4) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."00000000000".$total. "\n";
      }elseif(strlen($total) == 5) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."0000000000".$total. "\n";
      }elseif(strlen($total) == 6) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."000000000".$total. "\n";
      }elseif(strlen($total) == 7) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."00000000".$total. "\n";
      }elseif(strlen($total) == 8) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."0000000".$total. "\n";
      }elseif(strlen($total) == 9) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."000000".$total. "\n";
      }elseif(strlen($total) == 10) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."00000".$total. "\n";
      }elseif(strlen($total) == 11) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."0000".$total. "\n";
      }elseif(strlen($total) == 12) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."000".$total. "\n";
      }elseif(strlen($total) == 13) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."00".$total. "\n";
      }elseif(strlen($total) == 14) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."0".$total. "\n";
      }elseif(strlen($total) == 15) {
        $header = "10000880"."01750082130000000132".$ano.$mes.$dia.$cantidad_bincentenario2."".$total. "\n";
      }
     
        // Cuerpo del archivo
         // Abrir el archivo para escritura
    $file = fopen($file_path, 'w');
    if ($file === false) {
        die('Error al abrir el archivo para escritura');
    }

    fwrite($file, $header);
        $content = "";
        
        foreach ($empleados as $empleado) {
          $cedulas[] = $empleado['cedula'];
            $totalescobrar[] = $empleado['total_a_pagar'];
            $cedula = $empleado['cedula'];
            $nrocuenta = $empleado['cuenta_bancaria'];
            $nacionalidad = $empleado['nacionalidad'];
            $totalcobra = number_format($empleado['total_a_pagar'],2, "", "");
            $prueba = substr($empleado['nombres'], 0,30);
            $prueba = str_pad($prueba, 39, " ");
            $line = "";
            if(strlen($cedula) == 6){
      if (strlen($totalcobra) == 1) {
       $line =  "20". $nacionalidad."000". $cedula. $nrocuenta. "00000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  "20".$nacionalidad."000". $cedula. $nrocuenta. "0000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line = "20".$nacionalidad. "000". $cedula.  $nrocuenta. "000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line = "20".$nacionalidad. "000". $cedula.  $nrocuenta. "00000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line = "20".$nacionalidad. "000". $cedula.  $nrocuenta. "0000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line = "20".$nacionalidad. "000". $cedula.  $nrocuenta. "000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line = "20".$nacionalidad. "000". $cedula.  $nrocuenta. "00000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line = "20".$nacionalidad. "000". $cedula.  $nrocuenta. "0000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line = "20". $nacionalidad. "000". $cedula.  $nrocuenta. "000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line = "20". $nacionalidad. "000". $cedula.  $nrocuenta. "00000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line = "20". $nacionalidad. "000". $cedula.  $nrocuenta.  "0000". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 12) {
       $line = "20". $nacionalidad. "000". $cedula.  $nrocuenta.  "000". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 13) {
       $line = "20". $nacionalidad. "000". $cedula.  $nrocuenta.  "00". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 14) {
       $line = "20". $nacionalidad. "000". $cedula.  $nrocuenta.  "0". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 15) {
       $line = "20". $nacionalidad. "000". $cedula.  $nrocuenta.  "". $totalcobra . "\n"; 
      }
    }elseif(strlen($cedula) == 7){
      if (strlen($totalcobra) == 1) {
       $line =  "20". $nacionalidad."00". $cedula. $nrocuenta. "00000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  "20".$nacionalidad."00". $cedula. $nrocuenta. "0000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line = "20".$nacionalidad. "00". $cedula.  $nrocuenta. "000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line = "20".$nacionalidad. "00". $cedula.  $nrocuenta. "00000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line = "20".$nacionalidad. "00". $cedula.  $nrocuenta. "0000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line = "20".$nacionalidad. "00". $cedula.  $nrocuenta. "000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line = "20".$nacionalidad. "00". $cedula.  $nrocuenta. "00000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line = "20".$nacionalidad. "00". $cedula.  $nrocuenta. "0000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta. "000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta. "00000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta.  "0000". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 12) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta.  "000". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 13) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta.  "00". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 14) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta.  "0". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 15) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta.  "". $totalcobra . "\n"; 
      }      
   }elseif(strlen($cedula) == 8){
      if (strlen($totalcobra) == 1) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "00000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "0000000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "000000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line = "20". $nacionalidad. "0". $cedula.   $nrocuenta. "00000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "0000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line = "20".$nacionalidad. "0". $cedula.  $nrocuenta. "000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line = "20". $nacionalidad. "0".  $cedula.  $nrocuenta. "00000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line =  "20".$nacionalidad. "0". $cedula.  $nrocuenta. "0000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line =  "20".$nacionalidad. "0". $cedula.  $nrocuenta. "000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line = "20".$nacionalidad. "0". $cedula.  $nrocuenta. "00000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "0000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 12) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 13) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta.  "00". $totalcobra . "\n"; 
      } elseif(strlen($totalcobra) == 14) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "0". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 15) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta. "". $totalcobra . "\n"; 
      } 

   }elseif(strlen($cedula) == 9){
      if (strlen($totalcobra) == 1) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."00000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."0000000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."000000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."00000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."0000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."00000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."0000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line = "20". $nacionalidad. "". $cedula.   $nrocuenta."000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."00000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line = "20". $nacionalidad. "". $cedula.  $nrocuenta."0000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 12) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 13) {
       $line = "20". $nacionalidad. "0". $cedula.  $nrocuenta. "00". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 14) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta. "0". $totalcobra . "\n"; 
      } elseif(strlen($totalcobra) == 15) {
       $line = "20". $nacionalidad. "00". $cedula.  $nrocuenta.  "". $totalcobra . "\n"; 
      } 
   }
             
            fwrite($file, $line);
         
        }
         fclose($file);
        // Escribir el contenido al archivo
     
        

       
         header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment;'.'filename=$file_path');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
 readfile($file_path);
}
 $cedulas_json = json_encode($cedulas);
$totalescobrar_json = json_encode($totalescobrar);

       if (!empty($cedulas)) {
      // Construir la consulta SQL para insertar datos
    $sql = "INSERT INTO informacion_pdf (cedula, total_pagar, correlativo, identificador, banco)
            VALUES (?, ?, ?, ?, ?)";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("sssss", $cedulas_json, $totalescobrar_json, $correlativo, $identificador, $banco);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        echo "Datos insertados correctamente.";
    } else {
        echo "Error al insertar datos: " . $conexion->error;
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    }
    }

    // Función para procesar el array del banco Caroni
    function txt_Caroni($data, $conexion) {

        $empleados = $data['empleados'];
        $total_a_pagar = $data['total_a_pagar'];
        $correlativo = $data['correlativo'];
        $identificador = $data['identificador'];
        // Ruta del archivo a generar
        $file_path = '../../txt/caroni_'.$correlativo.'_'.$identificador.'.txt';
        $direccion = "C:/xampp/htdocs/sigob/".$file_path;
        $cedulas = array();
        $totalescobrar = array();
        $banco = "0128";
         if (!empty($empleados)) {
        // Cuerpo del archivo
         // Abrir el archivo para escritura
    $file = fopen($file_path, 'w');
    if ($file === false) {
        die('Error al abrir el archivo para escritura');
    }


        $content = "";
         
        foreach ($empleados as $empleado) {
          $cedulas[] = $empleado['cedula'];
            $totalescobrar[] = $empleado['total_a_pagar'];
            $cedula = $empleado['cedula'];
            $nrocuenta = $empleado['cuenta_bancaria'];
            $nacionalidad = $empleado['nacionalidad'];
            $totalcobra = number_format($empleado['total_a_pagar'],2, "", "");
            $prueba = substr($empleado['nombres'], 0,30);
            $prueba = str_pad($prueba, 39, " ");
            $line = "";
            if(strlen($cedula) == 7){
      if (strlen($totalcobra) == 1) {
       $line =  $nacionalidad."00". $cedula. $prueba. $nrocuenta. "000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  $nacionalidad."00". $cedula. $prueba. $nrocuenta. "00000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line = $nacionalidad. "00". $cedula. $prueba. $nrocuenta. "0000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line = $nacionalidad. "00". $cedula. $prueba. $nrocuenta. "000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line = $nacionalidad. "00". $cedula. $prueba. $nrocuenta. "00000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line = $nacionalidad. "00". $cedula. $prueba. $nrocuenta. "0000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line = $nacionalidad. "00". $cedula. $prueba. $nrocuenta. "000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line = $nacionalidad. "00". $cedula. $prueba. $nrocuenta. "00000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line =  $nacionalidad. "00". $cedula. $prueba. $nrocuenta. "0000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line =  $nacionalidad. "00". $cedula. $prueba. $nrocuenta. "000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line =  $nacionalidad. "00". $cedula. $prueba. $nrocuenta.  "00". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 12) {
       $line =  $nacionalidad. "00". $cedula. $prueba. $nrocuenta.  "0". $totalcobra . "\n"; 
      }elseif(strlen($totalcobra) == 13) {
       $line =  $nacionalidad. "00". $cedula. $prueba. $nrocuenta.  "". $totalcobra . "\n"; 
      }    
   }elseif(strlen($cedula) == 8){
      if (strlen($totalcobra) == 1) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "00000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "0000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line =  $nacionalidad. "0". $cedula.  $prueba. $nrocuenta. "000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "00000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line = $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "0000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line =  $nacionalidad. "0".  $cedula. $prueba. $nrocuenta. "000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "00000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "0000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line = $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "00". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 12) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "0". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 13) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "". $totalcobra . "\n";
      }

   }elseif(strlen($cedula) == 9){
      if (strlen($totalcobra) == 1) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."000000000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 2) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."00000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 3) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."0000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 4) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."000000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 5) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."00000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 6) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."0000000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 7) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."000000". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 8) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."00000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 9) {
       $line =  $nacionalidad. "". $cedula.  $prueba. $nrocuenta."0000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 10) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."000". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 11) {
       $line =  $nacionalidad. "". $cedula. $prueba. $nrocuenta."00". $totalcobra .  "\n";
      }elseif(strlen($totalcobra) == 12) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "0". $totalcobra . "\n";
      }elseif(strlen($totalcobra) == 13) {
       $line =  $nacionalidad. "0". $cedula. $prueba. $nrocuenta. "". $totalcobra . "\n";
      }
   }
             
            fwrite($file, $line);
         
        }
         fclose($file);
        // Escribir el contenido al archivo
        
        // Forzar la descarga del archivo
      
        header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment;'.'filename=$file_path');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
}
        // Terminar el script para que no se envíe más salida
$cedulas_json = json_encode($cedulas);
$totalescobrar_json = json_encode($totalescobrar);

  if (!empty($cedulas)) {
      // Construir la consulta SQL para insertar datos
    $sql = "INSERT INTO informacion_pdf (cedula, total_pagar, correlativo, identificador, banco)
            VALUES (?, ?, ?, ?, ?)";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("sssss", $cedulas_json, $totalescobrar_json, $correlativo, $identificador, $banco);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        echo "Datos insertados correctamente.";
    } else {
        echo "Error al insertar datos: " . $conexion->error;
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    }else{

    }
    }

    // Llamar a las funciones con los datos correspondientes
    $result_venezuela = txt_Venezuela($data['0102'], $conexion);
    $result_tesoro = txt_Tesoro($data['0163'], $conexion);
    $result_caroni = txt_Caroni($data['0128'], $conexion);
    $result_bicentenario = txt_Bicentenario($data['0175'], $conexion);

    // Devolver los resultados en formato JSON (excepto Venezuela)
    
    
} else {
    echo json_encode(['mensaje' => 'Datos insuficientes en la solicitud.']);
}




        
?>


   