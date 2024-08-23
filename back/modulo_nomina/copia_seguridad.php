<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($_SESSION["u_oficina_id"] == '1' && isset($data['accion']) || true) {
    //192.99.18.84

    // Configuración de conexión a la base de datos remota
    $remoteHost = '167.114.86.159';
    $remoteDb = 'sigob_web';
    $remoteUser = 'sigob_user';
    $remotePass = 'JH6$.GnJA6eL';

    // Conexión a la base de datos del hosting
    $remoteConn = new mysqli($remoteHost, $remoteUser, $remotePass, $remoteDb);
    $remoteConn->set_charset('latin1_spanish_ci'); // Establecer charset


    if ($remoteConn->connect_error) { // Verificar la conexión
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

        // Obtener columnas de la tabla local
        $localColsResult = $conexion->query("SHOW COLUMNS FROM $tabla");
        $localColumns = [];
        while ($col = $localColsResult->fetch_assoc()) {
            $localColumns[] = $col['Field'];
        }

        // Obtener columnas de la tabla remota
        $remoteColsResult = $remoteConn->query("SHOW COLUMNS FROM $tabla");
        $remoteColumns = [];
        while ($col = $remoteColsResult->fetch_assoc()) {
            $remoteColumns[] = $col['Field'];
        }

        // Comparar columnas y crear las faltantes en la tabla remota
        foreach ($localColumns as $column) {
            if (!in_array($column, $remoteColumns)) {
                $colDefinition = $conexion->query("SHOW COLUMNS FROM $tabla WHERE Field = '$column'")->fetch_assoc();
                $remoteConn->query("ALTER TABLE $tabla ADD COLUMN {$colDefinition['Field']} {$colDefinition['Type']}");
            }
        }
    }



    // Función para sincronizar datos entre las tablas
    function backups($tabla, $id_table)
    {
        global $agregados, $eliminados, $actualizados, $conexion, $remoteConn;

        // Verificar columnas antes de sincronizar
        verificarColumnas($tabla);

        // Obtener datos de la tabla local
        $localResult = $conexion->query("SELECT * FROM $tabla");
        $localData = [];
        while ($row = $localResult->fetch_assoc()) {
            $localData[$row[$id_table]] = $row;
        }

        // Obtener datos de la tabla remota
        $remoteResult = $remoteConn->query("SELECT * FROM $tabla");
        $remoteData = [];
        while ($row = $remoteResult->fetch_assoc()) {
            $remoteData[$row[$id_table]] = $row;
        }

        // Iniciar transacción para asegurar consistencia
        $remoteConn->begin_transaction();

        try {
            // Comparar y sincronizar
            foreach ($localData as $id => $localRow) {
                if (isset($remoteData[$id])) {
                    // Registro existe en ambas tablas, verificar si está modificado
                    if (isset($remoteData[$id])) {
                        if ($localRow != $remoteData[$id]) {
                            // Registro modificado, actualizar en el hosting
                            $set = [];
                            foreach ($localRow as $key => $value) {
                                $set[] = "$key=?";
                            }
                            $setStr = implode(", ", $set);

                            // Preparar y ejecutar la consulta de actualización
                            $stmt = $remoteConn->prepare("UPDATE $tabla SET $setStr WHERE $id_table=?");

                            $types = str_repeat('s', count($localRow)) . 's';

                            // Unir los valores del localRow con el ID en un solo array
                            $values = array_values($localRow);
                            $values[] = $id; // Añadir el ID al final

                            // Desempaquetar los valores en bind_param
                            $stmt->bind_param($types, ...$values);
                            $stmt->execute();
                            $stmt->close();

                            $actualizados++;
                        }
                    }
                } else {
                    // Registro nuevo, insertar en el hosting
                    $columns = implode(", ", array_keys($localRow));
                    $placeholders = implode(", ", array_fill(0, count($localRow), '?'));

                    // Preparar y ejecutar la consulta de inserción
                    $stmt = $remoteConn->prepare("INSERT INTO $tabla ($columns) VALUES ($placeholders)");
                    $types = str_repeat('s', count($localRow));
                    $stmt->bind_param($types, ...array_values($localRow));
                    $stmt->execute();
                    $stmt->close();

                    $agregados++;
                }
            }

            // Eliminar registros que están en el hosting pero no en la tabla local
            foreach ($remoteData as $id => $remoteRow) {
                if (!isset($localData[$id])) {
                    $stmt = $remoteConn->prepare("DELETE FROM $tabla WHERE $id_table=?");
                    $stmt->bind_param('s', $id);
                    $stmt->execute();
                    $stmt->close();

                    $eliminados++;
                }
            }

            // Confirmar transacción
            $remoteConn->commit();
        } catch (Exception $e) {
            // Si hay un error, revertir los cambios
            $remoteConn->rollback();
            throw $e;
        }
    }


    backups('empleados', 'id');
    backups('cargos_grados', 'id');
    backups('empleados_por_grupo', 'id');
    backups('nominas_grupos', 'id');
    backups('dependencias', 'id_dependencia');



    // update 'backups' campos: user, fecha
    $stmt = mysqli_prepare($conexion, "INSERT INTO `backups` (user, fecha) VALUES (?, ?)");
    $user_id = $_SESSION['u_id'];
    $fecha_actual = date('d-m-Y');
    $stmt->bind_param('ss', $user_id, $fecha_actual);
    $stmt->execute();

    // Crear arreglo con los resultados de la sincronización
    $resultado = [
        'status' => 'ok',
        'acciones' => [
            "agregados" => $agregados,
            "eliminados" => $eliminados,
            "actualizados" => $actualizados
        ]
    ];

    // Retornar el resultado en formato JSON
    echo json_encode($resultado);

    // Cerrar conexiones
    $conexion->close();
    $remoteConn->close();
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Permiso denegado']);
}
