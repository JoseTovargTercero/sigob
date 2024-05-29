<?php
header('Content-Type: application/json');

require_once '../sistema_global/conexion.php';

// Verificar si la petición es POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos enviados por la petición AJAX
    $grupo_nomina = $_POST['grupo_nomina'];
    $nombre = $_POST['nombre'];
    $frecuencia = $_POST['frecuencia'];
    $tipo = $_POST['tipo'];
    $conceptosAplicados = isset($_POST['conceptosAplicados']) ? $_POST['conceptosAplicados'] : [];

    // Array para almacenar los IDs de los conceptos aplicados
    $conceptosAplicadosIds = [];

    // Recorrer el array de conceptos aplicados
    foreach ($conceptosAplicados as $concepto) {
        if (isset($concepto['nom_concepto'])) {
            $nom_concepto = $concepto['nom_concepto'];

            // Preparar la consulta para obtener el ID del concepto aplicado
            $stmt = $conexion->prepare("SELECT id FROM conceptos_aplicados WHERE nom_concepto = ?");
            $stmt->bind_param("s", $nom_concepto);
            $stmt->execute();
            $stmt->bind_result($id);

            // Obtener el ID y almacenarlo en el array de IDs
            if ($stmt->fetch()) {
                $conceptosAplicadosIds[] = $id;
            }

            // Cerrar la declaración
            $stmt->close();
        }
    }

    // Convertir el array de IDs a JSON
    $conceptosAplicadosJson = json_encode($conceptosAplicadosIds);

    // Preparar y ejecutar la consulta de inserción en la tabla nominas
    $stmt = $conexion->prepare("INSERT INTO nominas (grupo_nomina, nombre, frecuencia, tipo, conceptos_aplicados) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $grupo_nomina, $nombre, $frecuencia, $tipo, $conceptosAplicadosJson);

    if ($stmt->execute()) {
        echo 'ok';
    } else {
        echo 'error';
    }

    // Cerrar la declaración
    $stmt->close();
}

// Cerrar la conexión
$conexion->close();
?>
