<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
if (isset($_POST["tabla"])) {
    // necesito regresar el nombre de las columnas y el tipo (int, varchar, etc) y su longitud de la tabla empleados
    $sql = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'empleados'";
    $result = $conexion->query($sql);
    $columnas = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $columnas[] = $row;
        }
    }
    // borrar repetidos de $columnas
    $columnas = array_map("unserialize", array_unique(array_map("serialize", $columnas)));

    echo json_encode($columnas);
} elseif (isset($_POST["registro"])) {

    $nombre = $_POST["nombre"];
    $tipo = $_POST["tipo"];
    $longitud = '';

    if ($tipo == 'varchar') {
        $longitud = '255';
    } elseif ($tipo == 'int') {
        $longitud = '11';
    }

    // Añadir los tipos de datos que no requieren longitud
    $tipos_sin_longitud = ['date', 'datetime', 'timestamp', 'time', 'year'];

    $palabras_ban = [
        'DROP',
        'INSERT',
        'DELETE',
        'UPDATE',
        'SELECT',
        'CREATE',
        'ALTER',
        'TRUNCATE',
        'RENAME',
        'REVOKE',
        'GRANT',
        'COMMIT',
        'ROLLBACK',
        'SAVEPOINT',
        'MERGE',
        'REPLACE',
        'SET',
        'SHOW',
        'USE',
        'DESCRIBE',
        'DESC',
        'EXPLAIN',
        'LOCK',
        'UNLOCK',
        'KILL',
        'FLUSH',
        'ANALYZE',
        'OPTIMIZE',
        'REPAIR',
        'CHECK',
        'ANALYSE',
        'BACKUP',
        'RESTORE',
        'RELOAD',
        'PURGE',
        'RESET',
        'SHUTDOWN',
        'START',
        'STOP',
        'RESTART',
        'STATUS',
        'STATS',
        'VERSION',
        'VARIABLES',
        'WARNINGS',
        'ERRORS',
        'LOGS',
        'BINARY',
        'MASTER',
        'SLAVE',
        "'",
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        '/',
        '(',
        ')',
        '=',
        '?',
        '¡',
        '¿',
        '´',
        '+',
        '*',
        '¨',
        '^',
        '`',
        '}',
        '{',
        ']',
        '[',
        ';',
        ':',
        ',',
        '.',
        '-',
        '|',
        '@',
        '~',
        '°',
        '¬',
        '·',
        'ç',
        '€',
        '£',
        '§',
        'Ñ',
        'ñ',
        ' '
    ];

    function validarSql($nombre)
    {
        global $palabras_ban;
        // Verificar nombre no contenga nada de palabras_ban
        foreach ($palabras_ban as $palabra) {
            if (stripos($nombre, $palabra) !== false) {
                return false;
            }
        }
        return true;
    }

    if (validarSql($nombre)) {
        // verifica si en la tabla empleados existe una columna con el mismo $nombre
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'empleados' AND COLUMN_NAME = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Crear la consulta SQL para agregar la columna
            if (in_array($tipo, $tipos_sin_longitud)) {
                $sql = "ALTER TABLE empleados ADD $nombre $tipo";
            } else {
                $sql = "ALTER TABLE empleados ADD $nombre $tipo($longitud)";
            }

            if ($conexion->query($sql) === TRUE) {
                echo json_encode(['status' => 'success', 'mensaje' => 'Campo agregado correctamente']);
            } else {
                echo json_encode(['status' => 'error', 'mensaje' => 'Error al agregar el campo.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'mensaje' => 'Ya existe un campo con ese nombre']);
        }
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'El nombre del campo no cumple con los requisitos mínimos']);
    }
}

$conexion->close();
