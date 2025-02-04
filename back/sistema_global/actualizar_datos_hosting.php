<?php
set_time_limit(10000000); // 5 minutos
require_once 'conexion.php';
if ($local_db->connect_error || $remote_db->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener nombres de tablas
function obtenerTablas($db) {
    $result = $db->query("SHOW TABLES");
    return array_column($result->fetch_all(), 0);
}

function obtenerHashTabla($db, $tabla) {
    $columnas = [];
    $resultado = $db->query("SHOW COLUMNS FROM `$tabla`");

    if (!$resultado) {
        die(json_encode(["error" => "No se pudieron obtener las columnas de la tabla `$tabla`: " . $db->error]));
    }

    while ($fila = $resultado->fetch_assoc()) {
        $columnas[] = "`" . $fila['Field'] . "`";
    }

    if (empty($columnas)) {
        die(json_encode(["error" => "La tabla `$tabla` no tiene columnas definidas"]));
    }

    $columnasConcat = implode(", '|', ", $columnas);
    $query = "SELECT MD5(GROUP_CONCAT(CONCAT_WS('|', $columnasConcat) ORDER BY " . $columnas[0] . ")) as hash FROM `$tabla`";
    $result = $db->query($query);

    if (!$result) {
        die(json_encode(["error" => "Error en la consulta SQL de la tabla `$tabla`: " . $db->error]));
    }

    $row = $result->fetch_assoc();
    return $row ? $row['hash'] : null;
}

function basesDeDatosIguales($local_db, $remote_db) {
    $tablas_local = obtenerTablas($local_db);
    $tablas_remoto = obtenerTablas($remote_db);
    $tablas_comunes = array_intersect($tablas_local, $tablas_remoto);

    foreach ($tablas_comunes as $tabla) {
        $hash_local = obtenerHashTabla($local_db, $tabla);
        $hash_remoto = obtenerHashTabla($remote_db, $tabla);

        if ($hash_local !== $hash_remoto) {
            return false; // Las bases de datos NO están sincronizadas
        }
    }

    return true; // Las bases de datos están sincronizadas
}

// Obtener datos de una tabla en lotes
function obtenerDatosTabla($db, $tabla, $limit = 5000) {
    $datos = [];
    $offset = 0;

    while (true) {
        $query = "SELECT * FROM `$tabla` LIMIT $limit OFFSET $offset";
        $result = $db->query($query);

        if (!$result) {
            die(json_encode(["error" => "Error al consultar la tabla `$tabla`: " . $db->error]));
        }

        if ($result->num_rows === 0) break;

        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
        $offset += $limit;
    }
    return $datos;
}


function vaciarTabla($db, $tabla) {
    $db->query("DELETE FROM `$tabla`");
}

function insertarDatos($db, $tabla, $datos) {
    if (empty($datos)) return;

    $columnas = "`" . implode("`, `", array_keys($datos[0])) . "`";

    foreach (array_chunk($datos, 1000) as $lote) {
        $valores = [];
        foreach ($lote as $fila) {
            $valores[] = "('" . implode("', '", array_map([$db, 'real_escape_string'], array_values($fila))) . "')";
        }
        $query = "INSERT INTO `$tabla` ($columnas) VALUES " . implode(", ", $valores);
        $db->query($query);
    }
}

function sincronizarDistribucionEnte($local_db, $remote_db) {
    $tabla = "distribucion_entes";

    // Obtener datos de la tabla en ambas bases de datos
    $datos_local = obtenerDatosTabla($local_db, $tabla);
    $datos_remoto = obtenerDatosTabla($remote_db, $tabla);

    // Crear un índice por ID para facilitar la comparación
    $index_remoto = [];
    foreach ($datos_remoto as $fila) {
        $index_remoto[$fila['id']] = $fila;
    }

    $actualizaciones = [];
    $nuevos = [];

    foreach ($datos_local as $fila_local) {
        $id = $fila_local['id'];
        if (isset($index_remoto[$id])) {
            // Comparar solo `id_ente` y `actividad_id`
            $diferencias = [];
            if ($fila_local['id_ente'] !== $index_remoto[$id]['id_ente']) {
                $diferencias['id_ente'] = $fila_local['id_ente'];
            }
            if ($fila_local['actividad_id'] !== $index_remoto[$id]['actividad_id']) {
                $diferencias['actividad_id'] = $fila_local['actividad_id'];
            }

            if (!empty($diferencias)) {
                $actualizaciones[$id] = $diferencias;
            }
        } else {
            // Si no existe en la base de datos remota, agregar como nuevo
            $nuevos[] = $fila_local;
        }
    }

    // Realizar actualizaciones (solo campos `id_ente` y `actividad_id`)
    foreach ($actualizaciones as $id => $campos) {
        $set = [];
        foreach ($campos as $columna => $valor) {
            $set[] = "`$columna` = '" . $remote_db->real_escape_string($valor) . "'";
        }
        $query = "UPDATE `$tabla` SET " . implode(", ", $set) . " WHERE `id` = '" . $id . "'";
        if (!$remote_db->query($query)) {
            echo "Error al actualizar el registro con ID $id: " . $remote_db->error . "\n";
        }
    }

    // Insertar nuevos registros
    insertarDatos($remote_db, $tabla, $nuevos);
}




function sincronizarBasesDeDatos($local_db, $remote_db) {
    if (basesDeDatosIguales($local_db, $remote_db)) {
        return ["success" => "Las bases de datos ya están sincronizadas, no se realizaron cambios"];
    }

    $tablas_local = obtenerTablas($local_db);
    $tablas_remoto = obtenerTablas($remote_db);
    $tablas_comunes = array_intersect($tablas_local, $tablas_remoto);
    $errores = [];

    $remote_db->begin_transaction();

    try {
        // Vaciar tablas excepto distribucion_ente
        foreach ($tablas_comunes as $tabla) {
            if ($tabla !== 'distribucion_entes') {
                vaciarTabla($remote_db, $tabla);
            }
        }
        $remote_db->commit();
    } catch (Exception $e) {
        $remote_db->rollback();
        return ["error" => "Error al vaciar tablas: " . $e->getMessage()];
    }

    // Insertar datos desde el local_db
    foreach ($tablas_comunes as $tabla) {
        if ($tabla !== 'distribucion_entes') {
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
    }

    // Sincronizar distribucion_ente con lógica especial
    sincronizarDistribucionEnte($local_db, $remote_db);

    return empty($errores) ? ["success" => "Las bases de datos fueron sincronizadas correctamente"] : ["error" => "Errores en la sincronización", "detalles" => $errores];
}

$resultado = sincronizarBasesDeDatos($local_db, $remote_db);
header('Content-Type: application/json');
echo json_encode($resultado);
?>
