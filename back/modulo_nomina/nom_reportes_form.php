<?php
require_once '../sistema_global/conexion.php';
require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf y PHPSpreadsheet
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración

use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

// Datos recibidos desde $data
$data = json_decode(file_get_contents('php://input'), true)['data'];

$formato = $data['formato'];
$almacenar = $data['almacenar'];
$nombre = $data['nombre'];
$columnas = $data['columnas'];
$condicion = $data['condicion'];
$tipoFiltro = $data['tipoFiltro'];
$nominas = $data['nominas'];

// Palabras clave prohibidas
$palabras_prohibidas = ['DROP', 'DELETE', 'INSERT', 'UPDATE', 'SELECT'];

// Verificación de palabras clave prohibidas
foreach ($palabras_prohibidas as $palabra) {
    if (stripos($condicion, $palabra) !== false) {
        echo json_encode(['error' => 'La condición contiene palabras clave prohibidas']);
        exit;
    }
}

// Preparación de datos para almacenar
$id_usuario = 1; // Asegúrate de que la sesión contiene el id del usuario
$columnas_serializadas = json_encode($columnas);
$creacion = date('Y-m-d H:i:s');

// Crear archivo ZIP
$zip = new ZipArchive();
$zipFilename = 'reportes_' . date('YmdHis') . '.zip';
$zip->open($zipFilename, ZipArchive::CREATE);

// Función para agregar archivos al ZIP
function addToZip($zip, $filename, $content) {
    $zip->addFromString($filename, $content);
}

if ($formato == "pdf" || $formato == "xlsx") {
    // Consulta a la base de datos
    $query = "SELECT " . implode(", ", array_map(function($col) use ($conexion) {
        return mysqli_real_escape_string($conexion, $col);
    }, $columnas)) . " FROM empleados WHERE $condicion";

    $result = $conexion->query($query);

    if ($result) {
        if ($result->num_rows > 0) {
            if ($formato == "pdf") {
                // Crear instancia de mPDF
                $mpdf = new Mpdf();

                // Comenzar el HTML del PDF
                $html = "<h1>Reporte de Empleados</h1>";
                $html .= "<table border='1' style='width: 100%; border-collapse: collapse;'>";
                $html .= "<thead><tr>";
                foreach ($columnas as $columna) {
                    $html .= "<th>$columna</th>";
                }
                $html .= "</tr></thead><tbody>";

                // Añadir filas a la tabla
                while ($row = $result->fetch_assoc()) {
                    $html .= "<tr>";
                    foreach ($columnas as $columna) {
                        $html .= "<td>" . htmlspecialchars($row[$columna]) . "</td>";
                    }
                    $html .= "</tr>";
                }

                $html .= "</tbody></table>";

                // Escribir el HTML al PDF
                $mpdf->WriteHTML($html);
                $pdfContent = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

                // Agregar PDF al ZIP
                addToZip($zip, 'reporte_empleados_' . date('YmdHis') . '.pdf', $pdfContent);
            } elseif ($formato == "xlsx") {
                // Crear instancia de Spreadsheet
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Establecer encabezados de columnas
                $colIndex = 1;
                foreach ($columnas as $columna) {
                    $sheet->setCellValueByColumnAndRow($colIndex, 1, $columna);
                    $colIndex++;
                }

                // Añadir filas de datos
                $rowIndex = 2;
                while ($row = $result->fetch_assoc()) {
                    $colIndex = 1;
                    foreach ($columnas as $columna) {
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $row[$columna]);
                        $colIndex++;
                    }
                    $rowIndex++;
                }

                // Guardar archivo Excel a una variable
                $writer = new Xlsx($spreadsheet);
                ob_start();
                $writer->save('php://output');
                $xlsxContent = ob_get_clean();

                // Agregar Excel al ZIP
                addToZip($zip, 'reporte_empleados_' . date('YmdHis') . '.xlsx', $xlsxContent);
            }

            // Cerrar y enviar el ZIP
            $zip->close();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
            readfile($zipFilename);

            // Borrar archivo ZIP temporal
            unlink($zipFilename);

            exit;
        } else {
            echo json_encode(['error' => 'No se encontraron registros que cumplan con la condición']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Error en la consulta SQL: ' . $conexion->error]);
        exit;
    }
}
?>
