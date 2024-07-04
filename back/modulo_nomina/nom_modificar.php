<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

// Obtener los datos JSON enviados por AJAX
$data = json_decode(file_get_contents('php://input'), true);
$accion = $data['accion'];



if ($accion == 'registro_masivo') {
    $grupo_nomina = $data['grupo_nomina'];
    $empleados = $data['empleados'];

    $response = array();
    $error = false;
    $query = "INSERT INTO empleados_por_grupo (id_empleado, id_grupo) VALUES (?, ?)";
    

    if (!empty($empleados)) {

        $stmt_d = $conexion->prepare("DELETE FROM `empleados_por_grupo` WHERE id_grupo = ?");
        $stmt_d->bind_param("i", $grupo_nomina);
        $stmt_d->execute();
        $stmt_d->close();


        foreach ($empleados as $item) {
            
            if ($stmt = $conexion->prepare($query)) {
                $stmt->bind_param('ii', $item[0], $grupo_nomina);
                if (!$stmt->execute()) {
                    $error = true;
                    $response['message'] = 'Error al insertar el empleado con id ' . $item;
                    break;
                }
                $stmt->close();
            } else {
                $error = true;
                $response['message'] = 'Error en la preparación de la consulta.';
                break;
            }
        }
    } else {
        $error = true;
        $response['message'] = 'No se proporcionaron empleados.';
    }


    if ($error) {
        $response['status'] = 'error';
    } else {
        $response['status'] = 'ok';
        $response['message'] = 'Registrados con éxito';
    }

    // Enviar respuesta en formato JSON
    echo json_encode($response);
} elseif ($accion == 'cargar_lista') {
    $grupo_nomina = $data['grupo_nomina'];
    $sql = "SELECT empleados.nombres, empleados.id, empleados.cedula, empleados.status
            FROM empleados_por_grupo
            INNER JOIN empleados ON empleados.id = empleados_por_grupo.id_empleado WHERE id_grupo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $grupo_nomina);
    
    if (!$stmt) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error en la preparación de la declaración: " . $conexion->error
        ]);
        exit();
    }
    
    // Vincular el parámetro y ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Crear un array para almacenar los datos
    $datos = array();
    
    if ($result->num_rows > 0) {
        // Llenar el array con los datos obtenidos de la consulta
        while ($row = $result->fetch_assoc()) {
            $empleado = array(
                "nombres" => $row["nombres"],
                "id" => $row["id"],
                "cedula" => $row["cedula"],
                "status" => $row["status"]
            );
            $datos[] = $empleado;
        }
    } else {
        echo json_encode([
            "status" => "ok",
            "mensaje" => "No se encontraron resultados."
        ]);
        exit();
    }
    
    // Cerrar la declaración
    $stmt->close();
 
    
    // Pasar el array a la vista (puedes utilizar un archivo de vista o imprimir los datos aquí mismo)
    echo json_encode([
        "status" => "ok",
        "datos" => $datos
    ]);
} elseif ($accion == 'verificar_grupo') {
    $grupo_nomina = $data['grupo_nomina'];


    $stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas` WHERE grupo_nomina = ?");
    $stmt->bind_param('s', $grupo_nomina);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $statusGrupo = 'ok';
    } else {
        $statusGrupo = 'error';
    }
    $stmt->close();

    echo json_encode(['status' => $statusGrupo]);

}



$conexion->close();
