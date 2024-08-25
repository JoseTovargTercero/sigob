<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

if ($_SESSION["u_oficina_id"] == '1' && isset($_GET['tabla']) || true) {
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

    $ids = array(
        'empleados' => 'id',
        'cargos_grados' => 'id',
        'empleados_por_grupo' => 'id',
        'nominas_grupos' => 'id',
        'dependencias' => 'id_dependencia'
    );
    $tabla = $_GET['tabla'];
    $condicion = $_GET["condicion"];



    backups($tabla, $ids[$tabla]);


    if ($condicion == 'nuevo') {

        // update 'backups' campos: user, fecha
        $stmt = mysqli_prepare($conexion, "INSERT INTO `backups` (user, fecha, tablas) VALUES (?, ?, ?)");
        $user_id = $_SESSION['u_id'];
        $fecha_actual = date('d-m-Y');
        $stmt->bind_param('sss', $user_id, $fecha_actual, $tabla);
        $stmt->execute();
       
        

    } else {
        // obtiene el valor de tabla del ultimo registro y le concatena ', $tqbla' mediante un UPDATE

        $stmt = mysqli_prepare($conexion, "SELECT tablas, id FROM `backups` ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
                $tablasActualizadas = $row['tablas'];
            }
        }
        $stmt->close();

        $tablasActualizadas = $tablasActualizadas . ', ' . $tabla;



        $stmt2 = $conexion->prepare("UPDATE `backups` SET `tablas`='$tablasActualizadas' WHERE id=?");
        $stmt2->bind_param("s", $id);
        $stmt2->execute();
        $stmt2->close();
    }

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
