<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../../vendor/autoload.php'; // Ajusta la ruta según la ubicación de mpdf y SimpleXLSXGen
require_once 'pdf_files_config.php'; // Incluir el archivo de configuración

use Mpdf\Mpdf;
use Shuchkin\SimpleXLSXGen;

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
$id_usuario = $_SESSION['u_id']; // Asegúrate de que la sesión contiene el id del usuario
$columnas_serializadas = json_encode($columnas);
$creacion = date('Y-m-d H:i:s');

// Crear archivo ZIP
$zip = new ZipArchive();
$zipFilename = 'reportes_' . date('YmdHis') . '.zip';
if ($zip->open($zipFilename, ZipArchive::CREATE) !== TRUE) {
    echo json_encode(['error' => 'No se pudo crear el archivo ZIP']);
    exit;
}

// Función para agregar archivos al ZIP
function addToZip($zip, $filename, $content) {
    if (!$zip->addFromString($filename, $content)) {
        echo json_encode(['error' => 'No se pudo agregar el archivo al ZIP']);
        exit;
    }
}

$nominas_string = implode(", ", array_map('intval', $nominas));


// Determinar la consulta principal según el valor de $tipoFiltro
$query = "";  // Inicializamos $query para evitar el error de variable no definida

if ($tipoFiltro === "Ninguno") {
    $query = "SELECT " . implode(", ", array_map(function($col) use ($conexion) {
        return mysqli_real_escape_string($conexion, $col);
    }, $columnas)) . " FROM empleados WHERE $condicion";
} elseif ($tipoFiltro === "nominas") {
    // Convertir los valores de $nominas en una cadena para la consulta

    // Consulta para obtener los nombres de las nóminas
    $query_nominas = "SELECT nombre FROM nominas WHERE id IN ($nominas_string)";
    $result_nominas = $conexion->query($query_nominas);

    if ($result_nominas && $result_nominas->num_rows > 0) {
        $nombres_nominas = [];
        while ($row = $result_nominas->fetch_assoc()) {
            $nombres_nominas[] = $row['nombre'];
        }

        // Unir los nombres de nóminas para usarlos más adelante
        $nombres_nominas_string = implode("', '", $nombres_nominas);
    } else {
        echo json_encode(['error' => 'No se encontraron nombres de nóminas para los IDs proporcionados']);
        exit;
    }

    // Consulta a conceptos_aplicados para obtener los empleados con Sueldo Base
    $query_conceptos = "SELECT empleados FROM conceptos_aplicados WHERE nom_concepto = 'Sueldo Base' AND nombre_nomina IN ('$nombres_nominas_string')";
    $result_conceptos = $conexion->query($query_conceptos);

    if ($result_conceptos && $result_conceptos->num_rows > 0) {
        $empleados = [];
        while ($row = $result_conceptos->fetch_assoc()) {
            $empleados = array_merge($empleados, json_decode($row['empleados'], true));
        }

        $empleados_string = implode(", ", array_map('intval', $empleados));

        $query = "SELECT " . implode(", ", array_map(function($col) use ($conexion) {
            return mysqli_real_escape_string($conexion, $col);
        }, $columnas)) . " FROM empleados WHERE $condicion AND id IN ($empleados_string)";
    } else {
        echo json_encode(['error' => 'No se encontraron empleados para las nóminas seleccionadas']);
        exit;
    }
} elseif ($tipoFiltro === "nominas_g") {
    // Convertir los valores de $nominas en una cadena para la consulta
    $nominas_string = implode(", ", array_map('intval', $nominas));

    // Consulta a empleados_por_grupo para obtener los empleados del grupo
    $query_empleados_grupo = "SELECT id_empleado FROM empleados_por_grupo WHERE id_grupo IN ($nominas_string)";
    $result_empleados_grupo = $conexion->query($query_empleados_grupo);

    if ($result_empleados_grupo && $result_empleados_grupo->num_rows > 0) {
        $empleados = [];
        while ($row = $result_empleados_grupo->fetch_assoc()) {
            $empleados[] = $row['id_empleado'];
        }

        $empleados_string = implode(", ", array_map('intval', $empleados));

        $query = "SELECT " . implode(", ", array_map(function($col) use ($conexion) {
            return mysqli_real_escape_string($conexion, $col);
        }, $columnas)) . " FROM empleados WHERE $condicion AND id IN ($empleados_string)";
    } else {
        echo json_encode(['error' => 'No se encontraron empleados para los grupos seleccionados']);
        exit;
    }
}

if ($formato == "pdf" || $formato == "xlsx") {
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
                // Crear array de datos para SimpleXLSXGen
                $rows = [];
                $rows[] = $columnas; // Añadir encabezados de columnas

                // Añadir filas de datos
                while ($row = $result->fetch_assoc()) {
                    $rowData = [];
                    foreach ($columnas as $columna) {
                        $rowData[] = $row[$columna];
                    }
                    $rows[] = $rowData;
                }

                // Crear instancia de SimpleXLSXGen
                $xlsx = SimpleXLSXGen::fromArray($rows);
                $xlsxFilename = 'reporte_empleados_' . date('YmdHis') . '.xlsx';
                $xlsx->saveAs($xlsxFilename);

                // Agregar archivo Excel al ZIP
                if (!file_exists($xlsxFilename)) {
                    echo json_encode(['error' => 'No se pudo generar el archivo Excel']);
                    exit;
                }
                $zip->addFile($xlsxFilename, $xlsxFilename);
            }

            // Cerrar y enviar el ZIP
            $zip->close();

            // Asegúrate de que no hay salida previa
            if (headers_sent()) {
                echo json_encode(['error' => 'Headers already sent']);
                exit;
            }

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
            readfile($zipFilename);

            // Borrar archivos temporales
            unlink($zipFilename);
            if (isset($xlsxFilename) && file_exists($xlsxFilename)) {
                unlink($xlsxFilename);
            }
            if ($almacenar == "Si") {
                // Inserción en la tabla reportes
            $columnas_serializadas = json_encode($columnas);
             $sql = "INSERT INTO reportes (furmulacion, nominas, columnas, formato, nombre, user, creacion, tipoFiltro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
                    // Preparar la declaración SQL
                    $stmt = $conexion->prepare($sql);
            
                    // Vincular parámetros y ejecutar la consulta
                    $stmt->bind_param("ssssssss", $condicion, $nominas_string, $columnas_serializadas, $formato, $nombre, $id_usuario, $creacion, $tipoFiltro );
            
                    // Ejecutar la consulta preparada
                    if ($stmt->execute()) {
                         json_encode(['success' => 'Datos Insertados correctamente']);
                    } else {
                         json_encode([
                            'error' => 'Error al insertar datos:',
                            'details' => $stmt->error // Muestra el mensaje de error
                        ]);
                    }
                    
            }
            

        } else {
            echo json_encode(['error' => 'No se encontraron registros']);
        }
    } else {
        echo json_encode(['error' => 'Error en la consulta']);
    }
} else {
    echo json_encode(['error' => 'Formato no válido']);
}

$conexion->close();
?>
