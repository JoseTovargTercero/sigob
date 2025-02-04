<?php
set_time_limit(10000000); // 5 minutos

// Configurar conexiones a las bases de datos
$local_db = new mysqli('localhost', 'root', '', 'sigob');
$remote_db = new mysqli('sigob.net', 'sigobnet_userroot', ']n^VmqjqCD1k', 'sigobnet_sigob_entes');

if ($local_db->connect_error || $remote_db->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener nombres de tablas
function obtenerTablas($db) {
    $result = $db->query("SHOW TABLES");
    return array_column($result->fetch_all(), 0);
}

// Obtener datos de una tabla en lotes
function obtenerDatosTabla($db, $tabla, $limit = 5000) {
    $datos = [];
    $offset = 0;

    while (true) {
        $query = "SELECT * FROM `$tabla` LIMIT $limit OFFSET $offset";
        $result = $db->query($query);
        if ($result->num_rows === 0) break;
        
        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
        $offset += $limit;
    }
    return $datos;
}

// Vaciar tabla en el remote_db
function vaciarTabla($db, $tabla) {
    $db->query("DELETE FROM `$tabla`");
}

// Insertar datos en el remote_db en lotes
function insertarDatos($db, $tabla, $datos) {
    if (empty($datos)) return;

    $columnas = "`" . implode("`, `", array_keys($datos[0])) . "`";

    foreach (array_chunk($datos, 1000) as $lote) { // Inserta en lotes de 1000 registros
        $valores = [];
        foreach ($lote as $fila) {
            $valores[] = "('" . implode("', '", array_map([$db, 'real_escape_string'], array_values($fila))) . "')";
        }
        $query = "INSERT INTO `$tabla` ($columnas) VALUES " . implode(", ", $valores);
        $db->query($query);
    }
}

// Sincronizar bases de datos vaciando y copiando datos
function sincronizarBasesDeDatos($local_db, $remote_db) {
    $tablas_local = obtenerTablas($local_db);
    $tablas_remoto = obtenerTablas($remote_db);
    $tablas_comunes = array_intersect($tablas_local, $tablas_remoto);
    $errores = [];

    $remote_db->begin_transaction();
    
    try {
        // Vaciar tablas del remote_db
        foreach ($tablas_comunes as $tabla) {
            vaciarTabla($remote_db, $tabla);
        }
        $remote_db->commit();
    } catch (Exception $e) {
        $remote_db->rollback();
        return ["error" => "Error al vaciar tablas: " . $e->getMessage()];
    }

    // Insertar datos desde el local_db
    foreach ($tablas_comunes as $tabla) {
        $datos = obtenerDatosTabla($local_db, $tabla);
        if (!empty($datos)) {
            $remote_db->begin_transaction();
            try {
                insertarDatos($remote_db, $tabla, $datos);
                $remote_db->commit();
            } catch (Exception $e) {
                $remote_db->rollback();
                $errores[] = "Error en la tabla `$tabla`: " . $e->getMessage();
            }
        }
    }

    return empty($errores) ? ["success" => "Las bases de datos fueron sincronizadas correctamente"] : ["error" => "Errores en la sincronización", "detalles" => $errores];
}

$resultado = sincronizarBasesDeDatos($local_db, $remote_db);
header('Content-Type: application/json');
echo json_encode($resultado);
?>
