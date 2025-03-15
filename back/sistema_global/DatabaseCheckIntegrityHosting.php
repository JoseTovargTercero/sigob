<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

if (isset($_GET['tabla']) || true) {
    // Configuración de conexión a la base de datos remota
    $remoteHost = REMOTE_HOST;
    $remoteDb = 'sigobnet_sigob_entes';
    $remoteUser = 'sigobnet_userroot';
    $remotePass = ']n^VmqjqCD1k';

    // Conexión a la base de datos del hosting
    $remoteConn = new mysqli($remoteHost, $remoteUser, $remotePass, $remoteDb);
    $remoteConn->set_charset('utf8mb4');

    if ($remoteConn->connect_error) {
        echo json_encode(['status' => 'error', 'mensaje' => "Conexión fallida a la base de datos del hosting: " . $remoteConn->connect_error]);
        exit();
    }

    // Contadores de operaciones
    $agregados = 0;
    $eliminados = 0;
    $actualizados = 0;

    // Función para verificar y crear columnas faltantes
    function verificarColumnas($tabla)
    {
        global $conexion, $remoteConn;

        $remoteColsResult = $remoteConn->query("SHOW COLUMNS FROM $tabla");
        $remoteColumns = [];
        while ($col = $remoteColsResult->fetch_assoc()) {
            $remoteColumns[] = $col['Field'];
        }

        $localColsResult = $conexion->query("SHOW COLUMNS FROM $tabla");
        $localColumns = [];
        while ($col = $localColsResult->fetch_assoc()) {
            $localColumns[] = $col['Field'];
        }

        foreach ($remoteColumns as $column) {
            if (!in_array($column, $localColumns)) {
                $colDefinition = $remoteConn->query("SHOW COLUMNS FROM $tabla WHERE Field = '$column'")->fetch_assoc();
                $conexion->query("ALTER TABLE $tabla ADD COLUMN {$colDefinition['Field']} {$colDefinition['Type']}");
            }
        }
    }

    function backups($tabla, $id_table)
    {
        global $agregados, $eliminados, $actualizados, $conexion, $remoteConn;

        verificarColumnas($tabla);

        $remoteResult = $remoteConn->query("SELECT * FROM $tabla");
        $remoteData = [];
        while ($row = $remoteResult->fetch_assoc()) {
            $remoteData[$row[$id_table]] = $row;
        }

        $localResult = $conexion->query("SELECT * FROM $tabla");
        $localData = [];
        while ($row = $localResult->fetch_assoc()) {
            $localData[$row[$id_table]] = $row;
        }

        $conexion->begin_transaction();

        try {
            foreach ($remoteData as $id => $remoteRow) {
                if (isset($localData[$id])) {
                    if ($remoteRow != $localData[$id]) {
                        $set = [];
                        foreach ($remoteRow as $key => $value) {
                            $set[] = "$key=?";
                        }
                        $setStr = implode(", ", $set);

                        $stmt = $conexion->prepare("UPDATE $tabla SET $setStr WHERE $id_table=?");
                        $types = str_repeat('s', count($remoteRow)) . 's';
                        $values = array_values($remoteRow);
                        $values[] = $id;
                        $stmt->bind_param($types, ...$values);
                        $stmt->execute();
                        $stmt->close();

                        $actualizados++;
                    }
                } else {
                    $columns = implode(", ", array_keys($remoteRow));
                    $placeholders = implode(", ", array_fill(0, count($remoteRow), '?'));

                    $stmt = $conexion->prepare("INSERT INTO $tabla ($columns) VALUES ($placeholders)");
                    $types = str_repeat('s', count($remoteRow));
                    $stmt->bind_param($types, ...array_values($remoteRow));
                    $stmt->execute();
                    $stmt->close();

                    $agregados++;
                }
            }

            foreach ($localData as $id => $localRow) {
                if (!isset($remoteData[$id])) {
                    $stmt = $conexion->prepare("DELETE FROM $tabla WHERE $id_table=?");
                    $stmt->bind_param('s', $id);
                    $stmt->execute();
                    $stmt->close();

                    $eliminados++;
                }
            }

            $conexion->commit();
        } catch (Exception $e) {
            $conexion->rollback();
            throw $e;
        }
    }

    function backupsPorId($tabla, $id_table, $id_especifico)
{
    global $agregados, $eliminados, $actualizados, $conexion, $remoteConn;

    verificarColumnas($tabla);

    // Obtener datos del registro específico en la tabla remota
    $stmt = $remoteConn->prepare("SELECT * FROM $tabla WHERE $id_table = ?");
    $stmt->bind_param('s', $id_especifico);
    $stmt->execute();
    $remoteResult = $stmt->get_result();
    $stmt->close();

    $remoteData = [];
    while ($row = $remoteResult->fetch_assoc()) {
        $remoteData[$row[$id_table]] = $row;
    }

    // Obtener datos del registro específico en la tabla local
    $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE $id_table = ?");
    $stmt->bind_param('s', $id_especifico);
    $stmt->execute();
    $localResult = $stmt->get_result();
    $stmt->close();

    $localData = [];
    while ($row = $localResult->fetch_assoc()) {
        $localData[$row[$id_table]] = $row;
    }

    $conexion->begin_transaction();

    try {
        foreach ($remoteData as $id => $remoteRow) {
            if (isset($localData[$id])) {
                if ($remoteRow != $localData[$id]) {
                    $set = [];
                    foreach ($remoteRow as $key => $value) {
                        $set[] = "$key=?";
                    }
                    $setStr = implode(", ", $set);

                    $stmt = $conexion->prepare("UPDATE $tabla SET $setStr WHERE $id_table=?");
                    $types = str_repeat('s', count($remoteRow)) . 's';
                    $values = array_values($remoteRow);
                    $values[] = $id;
                    $stmt->bind_param($types, ...$values);
                    $stmt->execute();
                    $stmt->close();

                    $actualizados++;
                }
            } else {
                $columns = implode(", ", array_keys($remoteRow));
                $placeholders = implode(", ", array_fill(0, count($remoteRow), '?'));

                $stmt = $conexion->prepare("INSERT INTO $tabla ($columns) VALUES ($placeholders)");
                $types = str_repeat('s', count($remoteRow));
                $stmt->bind_param($types, ...array_values($remoteRow));
                $stmt->execute();
                $stmt->close();

                $agregados++;
            }
        }

        foreach ($localData as $id => $localRow) {
            if (!isset($remoteData[$id])) {
                $stmt = $conexion->prepare("DELETE FROM $tabla WHERE $id_table=?");
                $stmt->bind_param('s', $id);
                $stmt->execute();
                $stmt->close();

                $eliminados++;
            }
        }

        $conexion->commit();
    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}


    $resultado = [
        'status' => 'ok',
        'acciones' => [
            "agregados" => $agregados,
            "eliminados" => $eliminados,
            "actualizados" => $actualizados
        ]
    ];

    echo json_encode($resultado);

    $conexion->close();
    $remoteConn->close();
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Permiso denegado']);
}
