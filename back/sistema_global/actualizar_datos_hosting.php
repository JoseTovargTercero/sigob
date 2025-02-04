<?php
set_time_limit(10000000); // 5 minutos

// Configurar conexiones a las bases de datos
$local_db = new mysqli('localhost', 'root', '', 'sigob');
$remote_db = new mysqli('sigob.net', 'sigobnet_userroot', ']n^VmqjqCD1k', 'sigobnet_sigob_entes');

if ($local_db->connect_error || $remote_db->connect_error) {
    die("Error de conexión a la base de datos");
}

// Obtener nombres de tablas
function obtenerTablas($db) {
    $result = $db->query("SHOW TABLES");
    return array_column($result->fetch_all(), 0);
}

// Obtener datos de la tabla en lotes para evitar consumir demasiada memoria
function obtenerDatosTabla($db, $tabla, $limit = 5000, $offset = 0) {
    $datos = [];
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

// Obtener la clave primaria de una tabla
function obtenerClavePrimaria($db, $tabla) {
    $result = $db->query("SHOW KEYS FROM `$tabla` WHERE Key_name = 'PRIMARY'");
    return $result->fetch_assoc()['Column_name'] ?? null;
}

// Insertar o actualizar en una sola consulta
function insertarActualizarRegistro($db, $tabla, $datos, $clave_primaria) {
    $columnas = implode("`, `", array_keys($datos));
    $valores = implode("', '", array_values($datos));
    $actualizaciones = implode(", ", array_map(fn($c) => "`$c` = VALUES(`$c`)", array_keys($datos)));
    
    $query = "INSERT INTO `$tabla` (`$columnas`) VALUES ('$valores') 
              ON DUPLICATE KEY UPDATE $actualizaciones";
    $db->query($query);
}

// Eliminar registros en lote para mayor eficiencia
function eliminarRegistros($db, $tabla, $ids, $clave_primaria) {
    if (empty($ids)) return;
    $id_list = implode("', '", $ids);
    $db->query("DELETE FROM `$tabla` WHERE `$clave_primaria` IN ('$id_list')");
}

// Sincronizar bases de datos optimizando los procesos
function sincronizarBasesDeDatos($local_db, $remote_db) {
    $tablas_local = obtenerTablas($local_db);
    $tablas_remoto = obtenerTablas($remote_db);
    $sincronizacion = [];

    $tablas_comunes = array_intersect($tablas_local, $tablas_remoto);

    foreach ($tablas_comunes as $tabla) {
        $clave_primaria = obtenerClavePrimaria($local_db, $tabla);
        if (!$clave_primaria) continue;

        $datos_local = obtenerDatosTabla($local_db, $tabla);
        $datos_remoto = obtenerDatosTabla($remote_db, $tabla);

        $mapa_local = array_column($datos_local, null, $clave_primaria);
        $mapa_remoto = array_column($datos_remoto, null, $clave_primaria);

        $insertar_actualizar = [];
        $eliminar_ids = [];

        foreach ($mapa_local as $id => $fila) {
            if (!isset($mapa_remoto[$id]) || $fila != $mapa_remoto[$id]) {
                $insertar_actualizar[] = $fila;
            }
        }

        foreach ($mapa_remoto as $id => $fila) {
            if (!isset($mapa_local[$id])) {
                $eliminar_ids[] = $id;
            }
        }

        $remote_db->begin_transaction();

        try {
            foreach ($insertar_actualizar as $fila) {
                insertarActualizarRegistro($remote_db, $tabla, $fila, $clave_primaria);
            }

            eliminarRegistros($remote_db, $tabla, $eliminar_ids, $clave_primaria);

            $remote_db->commit();
        } catch (Exception $e) {
            $remote_db->rollback();
            $sincronizacion['errores'][] = $e->getMessage();
        }
    }

    return $sincronizacion;
}

$resultado = sincronizarBasesDeDatos($local_db, $remote_db);
header('Content-Type: application/json');
if (!empty($resultado)) {
    echo json_encode(["sucess" => "Las Base de datos fueron sincronizadas correctamente"]);
} else {
    echo json_encode(["error" => "Hubo un error al sincronizar las base de datos"]);
}
?>
