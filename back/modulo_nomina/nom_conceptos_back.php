<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';

if (isset($_POST["tabla"])) {

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `conceptos` ORDER BY nom_concepto");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();

    echo json_encode($data);
}if (isset($_POST["registro"])) {

    $nombre = clear($_POST["nombre"]);
    $tipo = $_POST["tipo"];
    $partida = clear($_POST["partida"]);
    $tipo_calculo = clear($_POST["tipo_calculo"]);
    $valor = clear($_POST["valor"]);
    $maxValue = clear($_POST["maxValue"]);
    $codigo_concepto = clear($_POST["codigo_concepto"]);
    $tipo_calculo_origen = '0';
    
    if ($tipo_calculo == '7') {
        $tipo_calculo = '6';
        $tipo_calculo_origen = '7';
    }

    
    // Comprobar que no exista el concepto
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `conceptos` WHERE nom_concepto = ? LIMIT 1");
    if (!$stmt) {
        die('Error en la preparación del statement: ' . mysqli_error($conexion));
    }
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo 'ye';
        $stmt->close();
    } else {
        $stmt->close();

        // Función para evaluar la expresión
        function evaluar_expresion($expresion) {
            $si_variations = ['si', 'sí', 'yes', 'affirmative', 'Si', 'SI'];
            $no_variations = ['no', 'not', 'negative', 'No', 'NO', 'N0'];
            $expresion = str_ireplace($si_variations, '1', $expresion);
            $expresion = str_ireplace($no_variations, '0', $expresion);
            return $expresion;
        }

        // Insertar en `conceptos`
        $stmt = mysqli_prepare($conexion, "INSERT INTO `conceptos` (nom_concepto, tipo_concepto, cod_partida, tipo_calculo, valor, maxval, tipo_calculo_origen, codigo_concepto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die('Error en la preparación del statement: ' . mysqli_error($conexion));
        }
        $stmt->bind_param("ssssssss", $nombre, $tipo, $partida, $tipo_calculo, $valor, $maxValue, $tipo_calculo_origen, $codigo_concepto);

        if ($stmt->execute()) {
            echo 'ok';

            
            $concepto_id = $stmt->insert_id;

            if ($tipo_calculo == '6') {
                $tipo_calculo_aplicado = clear($_POST["tipo_calculo_aplicado"]);
                $condiciones = $_POST["condiciones"];
                $valores = $_POST["valores"];

                // Recorrer $condiciones y convertir "si" a 1 y "no" a 0
                for ($i = 0; $i < count($condiciones); $i++) {
                    $resultado_evaluacion = evaluar_expresion($condiciones[$i]);

                    // Preparar e insertar en `conceptos_formulacion`
                    $stmt_formulacion = mysqli_prepare($conexion, "INSERT INTO `conceptos_formulacion` (tipo_calculo, condicion, valor, concepto_id) VALUES (?, ?, ?, ?)");
                    if (!$stmt_formulacion) {
                        die('Error en la preparación del statement: ' . mysqli_error($conexion));
                    }
                    $stmt_formulacion->bind_param("ssii", $tipo_calculo_aplicado, $resultado_evaluacion, $valores[$i], $concepto_id);
                    $stmt_formulacion->execute();
                    $stmt_formulacion->close();

                    // Actualizar el valor en `conceptos`
                    $stmt_update = mysqli_prepare($conexion, "UPDATE `conceptos` SET valor = ? WHERE id = ?");
                    if (!$stmt_update) {
                        die('Error en la preparación del statement: ' . mysqli_error($conexion));
                    }
                    $stmt_update->bind_param("si", $valores[$i], $concepto_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                }
            }
        } else {
            echo 'Error en la ejecución del statement: ' . mysqli_error($conexion);
        }

        $stmt->close();
    }




} elseif (isset($_POST["eliminar"])) {
   $id = $_POST["id"];

// Verificar si el registro existe en la tabla concepto_aplicados
$stmt = mysqli_prepare($conexion, "SELECT COUNT(*) FROM `conceptos_aplicados` WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    // Si el registro existe en concepto_aplicados, no realizar ninguna eliminación
    echo json_encode(["status" => "error", "mensaje" => "No se puede borrar el registro porque existe en la tabla concepto_aplicados."]);
} else {
    // Si el registro no existe en concepto_aplicados, proceder con la eliminación
    $stmt = mysqli_prepare($conexion, "DELETE FROM `conceptos` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = mysqli_prepare($conexion, "DELETE FROM `conceptos_formulacion` WHERE concepto_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["status" => "sucess", "mensaje" => "Registro borrado con exito."]);
}



} elseif (isset($_POST["validarConceptoFormulado"])) {

    // Obtener datos POST
    $condicion = $_POST["condicion"];

    // Verificar si se proporcionó la condición
    if (empty($condicion)) {
        echo "error";
        $conexion->close();
        exit();
    }
    // Palabras clave prohibidas
    $palabras_prohibidas = array('UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'INSERT', 'ALTER', 'GRANT', 'REVOKE');

    // Verificar si la condición contiene palabras clave prohibidas
    foreach ($palabras_prohibidas as $palabra) {
        if (stripos($condicion, $palabra) !== false) {
            echo "prohibido";
            $conexion->close();
            exit();
        }
    }

    // Construir y ejecutar la consulta
    $sql = "SELECT COUNT(*) as cantidad FROM empleados WHERE $condicion";
    $result = $conexion->query($sql);

    if ($result === FALSE) {
        echo "error";
    } else {
        $row = $result->fetch_assoc();
        echo $row['cantidad'];
    }
} elseif (isset($_POST["consulta_nombre"])) {
    $nombre = clear($_POST["nombre"]);
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `conceptos` WHERE nom_concepto = ? LIMIT 1");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo 'ye';
    }else {
        echo 'ok';
    }

} elseif (isset($_POST['valorMultiplicado'])) {
   
    

    $campo = $_POST["campo"];

    // Validar y sanitizar el valor del campo para prevenir inyecciones SQL
    if (preg_match('/^[a-zA-Z0-9_]+$/', $campo)) {
        // Preparar la consulta SQL utilizando sentencias preparadas
        $sql = "SELECT DISTINCT `$campo` FROM empleados ORDER BY `$campo` DESC LIMIT 1";
        
        // Ejecutar la consulta
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->execute();
            $result = $stmt->get_result();

            // Verificar si hay resultados
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo json_encode(intval($row[$campo]));
            } else {
                echo json_encode('error');
            }

            // Cerrar el statement
            $stmt->close();
        } else {
            echo json_encode('error');
        }
    } else {
        echo json_encode('error');
    }



} elseif (isset($_POST['editar_getData'])) {
    $id = $_POST["id"];
    function getFormulas($id){
        global $conexion;
        $data = [];
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `conceptos_formulacion` WHERE concepto_id = ? ORDER BY id ASC");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;

    }
    $stmt = mysqli_prepare($conexion, "SELECT *  FROM `conceptos` AS c WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'id' => $row['id'],
            'nombre' => $row['nom_concepto'],
            'tipo' => $row['tipo_concepto'],
            'partida' => $row['cod_partida'],
            'tipo_calculo' => $row['tipo_calculo'],
            'valor' => $row['valor'],
            'maxValue' => $row['maxval'],
            'tipo_calculo_origen' => $row['tipo_calculo_origen'],
            'formulacion' => getFormulas($row['id'])
        ]);
    }

} elseif (isset($_POST["editar_setData"])) {
    $id = $_POST["id"];
    $valor = $_POST["valor"];

    // obtener el tipo de calculo del concepto y tipo_calculo_origen
    $stmt = mysqli_prepare($conexion, "SELECT tipo_calculo, tipo_calculo_origen, valor FROM `conceptos` WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $tipo_calculo = $row['tipo_calculo'];
    $tipo_calculo_origen = $row['tipo_calculo_origen'];
    $valor_previo = $row['valor'];
    $stmt->close();


    if ($tipo_calculo != 6 && $tipo_calculo_origen != 7) {
        // actualiza el valor del concepto y 
        $stmt = mysqli_prepare($conexion, "UPDATE `conceptos` SET valor = ? WHERE id = ?");
        $stmt->bind_param("si", $valor, $id);
        if ($stmt->execute()) {
            //guarda en 'historico_conceptos' (id_concepto, valor) el valor previo
            $stmt = mysqli_prepare($conexion, "INSERT INTO `historico_conceptos` (identificador, valor) VALUES (?, ?)");
            $stmt->bind_param("is", $id, $valor_previo);
            $stmt->execute();
            echo json_encode(['status' => 'ok', 'mensaje' => 'Actualizado correctamente']);
        }
        $stmt->close();
        
    }elseif ($tipo_calculo == 6) {

        $stmt_q = mysqli_prepare($conexion, "SELECT * FROM `conceptos_formulacion` WHERE id = ? ORDER BY id ASC");
        $error = false;

        foreach ($valor as $value) {
            $id_item = $value['id'];
            $valor_item = $value['valor'];

            $stmt_q->bind_param('s', $id_item);
            $stmt_q->execute();
            $result = $stmt_q->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $valor_previo_item = $row['valor'];
                    $stmt_update = mysqli_prepare($conexion, "UPDATE `conceptos_formulacion` SET valor = ? WHERE id = ?");
                    $stmt_update->bind_param("si", $valor_item, $id_item);
                    if ($stmt_update->execute()) {
                        $stmt = mysqli_prepare($conexion, "INSERT INTO `historico_conceptos` (identificador, valor) VALUES (?, ?)");
                        $stmt->bind_param("is", $id_item, $valor_previo_item);
                        $stmt->execute();
                    }else {
                        $error = true;
                    }
                }
            }
        }
        if (!$error) {
            echo json_encode(['status' => 'ok', 'mensaje' => 'Actualizado correctamente']);
        }
        $stmt_q->close();
    }

}
$conexion->close();
