<?php
header('Content-Type: application/json');

require_once '../sistema_global/conexion.php';

try {
    // Verificar si la petición es POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Obtener los datos enviados por la petición AJAX
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON');
        }

        $grupo_nomina = $data['grupo_nomina'];
        $nombre = $data['nombre'];
        $frecuencia = $data['frecuencia'];
        $tipo = $data['tipo'];
        $conceptosAplicados = isset($data['conceptosAplicados']) ? $data['conceptosAplicados'] : [];


        
        // Verificar si el nombre ya existe en la tabla nominas
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM nominas WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de la nomina ya existe']);
            exit;
        }

        // Array para almacenar los IDs de los conceptos aplicados
        $conceptosAplicadosIds = [];

        // Recorrer el array de conceptos aplicados
        foreach ($conceptosAplicados as $concepto) {
            if (isset($concepto['nom_concepto'])) {
                $nom_concepto = $concepto['nom_concepto'];

                // Preparar la consulta para obtener el ID del concepto aplicado
                $stmt = $conexion->prepare("SELECT id FROM conceptos_aplicados WHERE nom_concepto = ? AND nombre_nomina = ?");
                $stmt->bind_param("ss", $nom_concepto, $nombre);
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
            echo json_encode(['status' => 'ok']);
        } else {
            throw new Exception('Error al insertar los datos');
        }

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $conexion->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

