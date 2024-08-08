<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($_SESSION["u_oficina_id"] == '1' && isset($data['accion'])) {
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




    function backups($tabla, $id_table){
        globaL $conexion;
        globaL $remoteConn;
    // Obtener datos de la tabla local
    $localResult = $conexion->query("SELECT * FROM $tabla");
    $localData = [];
    while ($row = $localResult->fetch_assoc()) {
        $localData[$row[$id_table]] = $row;
    }

    // Obtener datos de la tabla del hosting
    $remoteResult = $remoteConn->query("SELECT * FROM $tabla");
    $remoteData = [];
    while ($row = $remoteResult->fetch_assoc()) {
        $remoteData[$row[$id_table]] = $row;
    }

    // Contadores de operaciones
    $agregados = 0;
    $eliminados = 0;
    $actualizados = 0;

    // Comparar y sincronizar
    foreach ($localData as $id => $localRow) {
        if (isset($remoteData[$id])) {
            // Registro existe en ambas tablas, verificar si está modificado
            if ($localRow != $remoteData[$id]) {
                // Registro modificado, actualizar en el hosting
                $set = [];
                foreach ($localRow as $key => $value) {
                    $set[] = "$key='" . $remoteConn->real_escape_string($value) . "'";
                }
                $set = implode(", ", $set);
                $remoteConn->query("UPDATE $tabla SET $set WHERE $id_table='$id'");
                $actualizados++;
            }
        } else {
            // Registro nuevo, insertar en el hosting
            $columns = implode(", ", array_keys($localRow));
            $values = implode("', '", array_map([$remoteConn, 'real_escape_string'], array_values($localRow)));
            $remoteConn->query("INSERT INTO $tabla ($columns) VALUES ('$values')");
            $agregados++;
        }
    }

        // Eliminar registros que están en el hosting pero no en la tabla local
        foreach ($remoteData as $id => $remoteRow) {
            if (!isset($localData[$id])) {
                $remoteConn->query("DELETE FROM $tabla WHERE $id_table='$id'");
                $eliminados++;
            }
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
        'status' => 'ok', 'acciones' => [
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
}else {
    echo json_encode(['status' => 'error', 'mensaje'=>'Permiso denegado']);
}
