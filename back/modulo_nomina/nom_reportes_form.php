<?php
require_once '../sistema_global/conexion.php';
require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración

use Mpdf\Mpdf;

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

$pdfUrl = "";

if ($formato == "pdf") {
    // Consulta a la base de datos
    $query = "SELECT " . implode(", ", array_map(function($col) use ($conexion) {
        return mysqli_real_escape_string($conexion, $col);
    }, $columnas)) . " FROM empleados WHERE $condicion";

    $result = $conexion->query($query);

    if ($result) {
        if ($result->num_rows > 0) {
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

            // Nombre del archivo PDF
            $pdfFilename = 'reporte_empleados_' . date('YmdHis') . '.pdf';

            // Guardar el PDF en el servidor
            $pdfPath = __DIR__ . '/../../reportes/' . $pdfFilename;
            $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

            // Generar URL del PDF
            $pdfUrl = '../../reportes/' . $pdfFilename;
        } else {
            echo json_encode(['error' => 'No se encontraron registros que cumplan con la condición']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Error en la consulta SQL: ' . $conexion->error]);
        exit;
    }
}

// Inserción en la base de datos
if ($almacenar == 'Si') {
    $stmt = $conexion->prepare("INSERT INTO reportes (furmulacion, columnas, formato, nombre, user, creacion) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $condicion, $columnas_serializadas, $formato, $nombre, $id_usuario, $creacion);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'pdfUrl' => $pdfUrl]);
    } else {
        echo json_encode(['error' => 'Error al guardar el reporte en la base de datos']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => true, 'pdfUrl' => $pdfUrl, 'message' => 'El reporte no se almacenó en la base de datos']);
}

$conexion->close();
?>
