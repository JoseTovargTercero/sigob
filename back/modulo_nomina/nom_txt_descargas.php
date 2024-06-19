<?php



$correlativo = $_GET['correlativo'];
$frecuencia = $_GET['frecuencia'];


// Lista de archivos a incluir en el ZIP, utilizando la variable 'correlativo'
if ($frecuencia == 1) {
    $files = [
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
}elseif ($frecuencia == 2) {
    $files = [
    "tesoro_{$correlativo}_q1.txt",
    "tesoro_{$correlativo}_q2.txt",
    "venezuela_{$correlativo}_q1.txt",
    "venezuela_{$correlativo}_q2.txt",
    "bicentenario_{$correlativo}_q1.txt",
    "bicentenario_{$correlativo}_q2.txt",
    "caroni_{$correlativo}_q1.txt",
    "caroni_{$correlativo}_q2.txt",
];
}elseif ($frecuencia == 3 OR $frecuencia == 4) {
    $files = [
    "tesoro_{$correlativo}_unico.txt",
    "venezuela_{$correlativo}_unico.txt",
    "bicentenario_{$correlativo}_unico.txt",
    "caroni_{$correlativo}_unico.txt",
];
}


// Ruta donde se encuentran los archivos
$base_dir = "../../txt/";

// Nombre del archivo ZIP que se generará
$zip_filename = "txt__{$correlativo}_{$identificador}.zip";

// Crear una instancia de la clase ZipArchive
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("No se puede abrir el archivo ZIP");
}

// Agregar cada archivo al ZIP
foreach ($files as $file) {
    $file_path = $base_dir . $file;
    if (file_exists($file_path)) {
        $zip->addFile($file_path, $file);
    } else {
        // Salir del script si falta algún archivo
        $zip->close();
        unlink($zip_filename);
        exit("El archivo $file no existe");
    }
}

// Cerrar el archivo ZIP
$zip->close();

// Verificar que el archivo ZIP se haya creado correctamente
if (!file_exists($zip_filename)) {
    exit("Error al crear el archivo ZIP");
}

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
print_r($files);
exit;

?>