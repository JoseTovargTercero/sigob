<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
require_once '../sistema_global/errores.php';


// Recibir el array de valores desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$empleado_id = 0;
$movimiento = "Se han modificado los campos: ";
$valor_nuevo = '';
$campo = '';
$errores = array();
$cedula = ''; // Variable para guardar la cédula

// Iterar sobre el array recibido e insertar cada conjunto de valores
foreach ($data as $item) {
    $empleado_id = $item[0]; // Asumimos que el primer elemento es el ID del empleado
    $campo = $item[1]; // El segundo elemento es el campo a actualizar
    $valor_nuevo = $item[2]; // El tercer elemento es el nuevo valor

    // Solo buscamos la cédula una vez al inicio
    if (empty($cedula)) {
        // Consulta para obtener la cédula del empleado
        $stmtCedula = $conexion->prepare("SELECT cedula FROM empleados WHERE id = ?");
        $stmtCedula->bind_param("i", $empleado_id);
        $stmtCedula->execute();
        $stmtCedula->bind_result($cedula);
        $stmtCedula->fetch();
        $stmtCedula->close();

        // Verificar si se obtuvo la cédula
        if (empty($cedula)) {
            $error_message = "No se encontró la cédula para el empleado ID: $empleado_id.";
            registrarError($error_message);
            array_push($errores, $error_message);
            continue; // Salir del ciclo en caso de error
        }
    }

    // Verificar si el campo es 'foto'
    if ($campo === 'foto') {
        // Ruta de la imagen
        $target_dir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "empleados" . DIRECTORY_SEPARATOR;
        $nombreArchivo = "$cedula.jpg"; // Usar la cédula para el nombre del archivo
        $target_file = $target_dir . $nombreArchivo;

        // Crear la carpeta si no existe
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true) && !is_dir($target_dir)) {
                $error_message = "Error al crear la carpeta: $target_dir";
                registrarError($error_message);
                array_push($errores, $error_message);
                continue; // Salir del ciclo en caso de error
            }
        }

        // Eliminar la imagen anterior si existe
        if (file_exists($target_file)) {
            unlink($target_file);
        }

        // Decodificar la imagen Base64
        $fotoBase64 = $valor_nuevo;
        $fotoDecodificada = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64));

        if (!$fotoDecodificada) {
            $error_message = "Error al decodificar la imagen para el empleado ID: $empleado_id.";
            registrarError($error_message);
            array_push($errores, $error_message);
            continue; // Salir del ciclo en caso de error
        }

        // Crear la imagen en memoria
        $imagen = imagecreatefromstring($fotoDecodificada);
        if (!$imagen) {
            $error_message = "Error al crear la imagen desde los datos Base64.";
            registrarError($error_message);
            array_push($errores, $error_message);
            continue; // Salir del ciclo en caso de error
        }

        // Guardar la imagen como JPG con calidad de 75
        if (!imagejpeg($imagen, $target_file, 75)) {
            $error_message = "Error al guardar la imagen en formato JPG para el empleado ID: $empleado_id.";
            registrarError($error_message);
            array_push($errores, $error_message);
            imagedestroy($imagen); // Liberar memoria
            continue; // Salir del ciclo en caso de error
        }

        // Liberar la memoria de la imagen
        imagedestroy($imagen);
    } else {
        // Actualizar el campo correspondiente en la base de datos si no es 'foto'
        $movimiento .= "$campo: $valor_nuevo. ";
        $stmt2 = mysqli_prepare($conexion, "UPDATE empleados SET $campo = ? WHERE id = ?");
        $stmt2->bind_param('si', $valor_nuevo, $empleado_id);

        if (!$stmt2->execute()) {
            $error_message = "Error al actualizar el campo $campo para el empleado ID: $empleado_id.";
            registrarError($error_message);
            array_push($errores, $campo);
        }

        $stmt2->close();
    }
}



// echo $response;






// Array para almacenar los ids de nómina únicos
$tipo_nomina = array();

// Consultar la tabla conceptos_aplicados
$stmt = $conexion->prepare("SELECT id, nombre_nomina, empleados FROM conceptos_aplicados");
if (!$stmt) {
    die("Error en la preparación de la declaración SELECT: " . $conexion->error);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $empleados_array = json_decode($row['empleados'], true);

    // Verificar si el empleado_id está en el array de empleados
    if (in_array($empleado_id, $empleados_array)) {
        // Obtener el id de la tabla nominas basado en el nombre_nomina
        $nomina_stmt = $conexion->prepare("SELECT id FROM nominas WHERE nombre = ?");
        if (!$nomina_stmt) {
            die("Error en la preparación de la declaración SELECT nominas: " . $conexion->error);
        }
        $nomina_stmt->bind_param('s', $row['nombre_nomina']);
        $nomina_stmt->execute();
        $nomina_result = $nomina_stmt->get_result();

        if ($nomina_result->num_rows > 0) {
            $nomina_row = $nomina_result->fetch_assoc();
            $nomina_id = $nomina_row['id'];

            // Agregar el id de nomina al array tipo_nomina si no está ya presente
            if (!in_array($nomina_id, $tipo_nomina)) {
                $tipo_nomina[] = $nomina_id;
            }
        }
        $nomina_stmt->close();
    }
}
$stmt->close();

// Preparar la información para insertar en la tabla movimientos
$id_nomina = json_encode($tipo_nomina);
$accion = 'UPDATE';
$fecha_movimiento = date('Y-m-d H:i:s');

// Insertar en la tabla movimientos
$stmt_o = $conexion->prepare("INSERT INTO movimientos (id_empleado, id_nomina, fecha_movimiento, accion, tabla, campo, descripcion, valor_anterior, valor_nuevo, usuario_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
if (!$stmt_o) {
    die("Error en la preparación de la declaración INSERT movimientos: " . $conexion->error);
}
$stmt_o->bind_param("issssssssi", $empleado_id, $id_nomina, $fecha_movimiento, $accion, $tabla, $campo, $movimiento, $valor_anterior, $valor_nuevo, $_SESSION['u_id']);

// $stmt_o->bind_param("isssssi", $empleado_id, $id_nomina, $fecha_movimiento, $accion, $tabla, $campo, $movimiento, $valor_anterior, $valor_nuevo, $_SESSION['u_id']);
$stmt_o->execute();
$stmt_o->close();

// Cerrar la conexión
$conexion->close();

// Devolver una respuesta en JSON
echo json_encode(["errores" => $errores]);

?>