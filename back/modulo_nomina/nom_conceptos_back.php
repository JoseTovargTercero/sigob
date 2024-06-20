<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

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


    if ($tipo_calculo == '7') {
        $tipo_calculo = '6';
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
        $stmt = mysqli_prepare($conexion, "INSERT INTO `conceptos` (nom_concepto, tipo_concepto, cod_partida, tipo_calculo, valor, maxval) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die('Error en la preparación del statement: ' . mysqli_error($conexion));
        }
        $stmt->bind_param("ssssss", $nombre, $tipo, $partida, $tipo_calculo, $valor, $maxValue);

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
    $stmt = mysqli_prepare($conexion, "DELETE FROM `conceptos` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = mysqli_prepare($conexion, "DELETE FROM `conceptos_formulacion` WHERE concepto_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // verifica si ejecuto e imprimir ok
    if ($stmt) {
        echo "ok";
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



}
$conexion->close();
