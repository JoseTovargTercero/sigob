<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
header('Content-Type: application/json');
// Verificar si se envió la acción
if (!isset($_POST["accion"])) {
    echo json_encode(['error' => 'Acción no especificada.']);
    exit();
}

$accion = $_POST["accion"];

if ($accion == 'tabla') {
    // Consulta SQL para obtener los datos del empleado y su dependencia
    $sql = "SELECT DISTINCT(m.empleado), e.nombres, m.timestamp, e.id AS id_empleado, e.cedula
            FROM `modificaciones_empleados` AS m 
            LEFT JOIN empleados AS e ON e.id = m.empleado WHERE user_acepta='0'";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Comprobar si la preparación de la declaración fue exitosa
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación de la declaración: ' . $conexion->error]);
        exit();
    }

    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();

    // Crear un array para almacenar los datos
    $datos = array();

    if ($result->num_rows > 0) {
        // Llenar el array con los datos obtenidos de la consulta
        while ($row = $result->fetch_assoc()) {
            $empleado = array(
                "id" => $row["id_empleado"],
                "cedula" => $row["cedula"],
                "nombres" => $row["nombres"],
                "timestamp" => $row["timestamp"]
            );
            $datos[] = $empleado;
        }
    }

    echo json_encode($datos);

} elseif ($accion == 'revisar') {
    // Verificar si el índice 'id' está presente en $_POST
    if (!isset($_POST["id"])) {
        echo json_encode(['error' => 'ID no proporcionado.']);
        exit();
    }
    
    $id = $_POST["id"];



    // cargar dependencias
    $dependencias = array();
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `dependencias`");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dependencias[$row['id_dependencia']] = $row['dependencia'];
    }
    }
    $stmt->close();
    
    // Consulta para obtener las modificaciones
    $stmt = $conexion->prepare("SELECT campo, valor, timestamp, id FROM `modificaciones_empleados` WHERE empleado = ? AND user_acepta='0'");
    
    // Comprobar si la preparación de la declaración fue exitosa
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación de la declaración: ' . $conexion->error]);
        exit();
    }
    
    // Vincular el parámetro y ejecutar la consulta
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Crear un array para almacenar los datos
    $datos = array();
    
    if ($result->num_rows > 0) {
        // Llenar el array con los datos obtenidos de la consulta
        while ($row = $result->fetch_assoc()) {
            // Consulta para obtener el valor antiguo de la tabla empleados
            $campo = $row["campo"];
            $stmt2 = $conexion->prepare("SELECT $campo FROM empleados WHERE id = ?");
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $valor_antiguo = null;
    
            if ($result2->num_rows > 0) {
                $row2 = $result2->fetch_assoc();
                $valor_antiguo = $row2[$campo];
            }
    
            // Crear el array del empleado con el valor antiguo incluido
            $empleado = array(
                "id" => $row["id"],
                "campo" => $row["campo"],
                "valor" => ($row["campo"] == 'id_dependencia' ? $dependencias[$row["valor"]] : $row["valor"] ),
                "valor_antiguo" => ($row["campo"] == 'id_dependencia' ? $dependencias[$valor_antiguo] : $valor_antiguo )
            );
            $datos[] = $empleado;
        }
    }
    
    echo json_encode($datos);
    
} elseif ($accion == 'a') {
    // se acepta el cambio
    // Verificar si el índice 'id' está presente en $_POST
    if (!isset($_POST["id"])) {
        echo json_encode(['error' => 'ID no proporcionado.']);
        exit();
    }

    $id = $_POST["id"];
    $user_acepta = $_SESSION["u_id"];
    $fecha = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `modificaciones_empleados` WHERE id = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
       while ($row = $result->fetch_assoc()) {
            $campo = $row["campo"];
            $valor = $row["valor"];
            $empleado = $row["empleado"];
            $stmt2 = mysqli_prepare($conexion, "UPDATE empleados SET $campo = ? WHERE id = ?");
            $stmt2->bind_param('si', $valor, $empleado);
            if ($stmt2->execute()) {
                $stmt2->close();
                $stmt3 = mysqli_prepare($conexion, "UPDATE `modificaciones_empleados` SET user_acepta = ?, fecha = ? WHERE id = ?");
                $stmt3->bind_param('sss', $user_acepta, $fecha, $id);
                $stmt3->execute();
                $stmt3->close();

                notificar(['nomina'], 7);
                echo json_encode(['text' => 'ok']);
            } else {
                echo json_encode(['error' => 'Error al aceptar el cambio: ' . $conexion->error]);
            }
        }
    }else {
        echo json_encode(['error' => 'No se encontro el cambio']);
    }


} elseif ($accion == 'r') {
    // se rechaza el cambio
    // Verificar si el índice 'id' está presente en $_POST
    if (!isset($_POST["id"])) {
        echo json_encode(['error' => 'ID no proporcionado.']);
        exit();
    }

    $id = $_POST["id"];
    // eliminar la fila
    $stmt = mysqli_prepare($conexion, "DELETE FROM `modificaciones_empleados` WHERE id = ?");
    $stmt->bind_param('s', $id);
    if ($stmt->execute()) {
        echo json_encode(['text' => 'ok']);
        notificar(['nomina'], 6);
    } else {
        echo json_encode(['error' => 'Error al rechazar el cambio: ' . $conexion->error]);
    }

}

// Cerrar la declaración y la conexión
if (isset($stmt)) {
    $stmt->close();
}
$conexion->close();
?>
