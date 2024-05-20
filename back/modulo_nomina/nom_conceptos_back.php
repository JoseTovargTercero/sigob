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
} elseif (isset($_POST["registro"])) {

    $nombre = clear($_POST["nombre"]);
    $tipo = $_POST["tipo"];
    $partida = clear($_POST["partida"]);
    $tipo_calculo = clear($_POST["tipo_calculo"]);
    $valor = clear($_POST["valor"]);

    //Comprobar que no exist
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `conceptos` WHERE nom_concepto = ? LIMIT 1");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo 'ye';
    } else {
function evaluar_expresion($expresion) {
    // Definir arrays de variaciones de "si" y "no"
    $si_variations = ['si', 'sí', 'yes', 'affirmative', 'Si', 'SI'];
    $no_variations = ['no', 'not', 'negative', 'No', 'NO', 'N0'];

    // Reemplazar las variaciones de 'Si' y 'No' en la expresión
    $expresion = str_ireplace($si_variations, '1', $expresion);
    $expresion = str_ireplace($no_variations, '0', $expresion);

    return $expresion;
}

$stmt->close();
$stmt = mysqli_prepare($conexion, "INSERT INTO `conceptos` (nom_concepto, tipo_concepto, cod_partida, tipo_calculo, valor) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nombre, $tipo, $partida, $tipo_calculo, $valor);

if ($stmt->execute()) {
    echo 'ok';
    $concepto_id = $stmt->insert_id;

    if ($tipo_calculo == '6') {
        $tipo_calculo_aplicado = clear($_POST["tipo_calculo_aplicado"]);
        $condiciones = $_POST["condiciones"];
        $valores = $_POST["valores"];

        // Recorrer $condiciones y convertir "si" a 1 y "no" a 0
        for ($i = 0; $i < count($condiciones); $i++) {
            // Analizar la expresión y evaluarla
            $resultado_evaluacion = evaluar_expresion($condiciones[$i]);

            // Preparar e insertar en `conceptos_formulacion`
            $stmt = mysqli_prepare($conexion, "INSERT INTO `conceptos_formulacion` (tipo_calculo, condicion, valor, concepto_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $tipo_calculo_aplicado, $resultado_evaluacion, $valores[$i], $concepto_id);
            $stmt->execute();
        }
    }
} else {
    print_r($stmt);
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

}
$conexion->close();
