<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

// Recibir el array de valores desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$empleado_id = 0;
$movimiento = "Se han modificado los campos: ";
$valor_anterior = '';
$valor_nuevo = '';
$campo = '';
$tabla = "empleados";
$errores = array();
$cedula = ''; // Variable para guardar la cédula

// Iterar sobre el array recibido e insertar cada conjunto de valores
foreach ($data as $item) {
    $empleado_id = $item[0];
    $campo = $item[1];
    $valor_nuevo = $item[2];
    $valor_anterior = $item[3];
    
    // Verificar si el campo es 'cedula'
    if ($campo === 'cedula') {
        $cedula = $valor_nuevo; // Guardar la nueva cédula
    }

    // Verificar si el campo es 'foto'
    if ($campo === 'foto') {
        // Crear la ruta para almacenar la imagen
        $target_dir = "img" . DIRECTORY_SEPARATOR . $cedula . DIRECTORY_SEPARATOR;

        // Crear la carpeta si no existe
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true) && !is_dir($target_dir)) {
                array_push($errores, "Error al crear la carpeta: $target_dir");
                continue; // Salir del ciclo en caso de error
            }
        }

        // Manejar el archivo subido
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_name = basename($_FILES['foto']['name']);
            $target_file = $target_dir . $file_name;

            // Eliminar la imagen anterior si existe
            if ($valor_anterior) {
                $archivo_anterior = $target_dir . basename($valor_anterior);
                if (file_exists($archivo_anterior)) {
                    unlink($archivo_anterior); // Eliminar el archivo anterior
                }
            }

            // Mover el archivo subido a la carpeta
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                array_push($errores, "Error al mover el archivo subido para el empleado ID: $empleado_id.");
                continue; // Salir del ciclo en caso de error
            }

            // Actualizar el campo foto con el nuevo nombre
            $valor_nuevo = $file_name;
        } else {
            array_push($errores, "No se recibió ninguna imagen o hubo un error en la carga.");
            continue; // Salir del ciclo en caso de error
        }
    }

    // Actualizar el campo correspondiente en la base de datos
    $movimiento .= "$campo: $valor_nuevo. ";

    $stmt2 = mysqli_prepare($conexion, "UPDATE empleados SET $campo = ? WHERE id = ?");
    $stmt2->bind_param('si', $valor_nuevo, $empleado_id);
    
    if (!$stmt2->execute()) {
        array_push($errores, $campo);
    }
    
    $stmt2->close();
}


echo $response;


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