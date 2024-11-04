<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';
require_once '../sistema_global/DatabaseHandler.php';
$db = new DatabaseHandler($conexion);


function registrar($partida, $ordinaria, $denominacion)
{
    global $db;

    // verificar que $ordinaria tenga 4 digitos
    if (strlen($ordinaria) != 4) {
        return array('error' => 'No tiene el formato correcto');
    }
    // quitar ultimos cuatro digitos de partida y adicionar $ordinaria
    $partida = substr($partida, 0, -4) . $ordinaria;

    // verificar que la partida no exista
    $tablas = [['tabla' => 'partidas_presupuestarias', 'condicion' => "partida='$partida'"]];

    $totalCoincidencias = $db->comprobar_existencia($tablas);
    // Si hay coincidencias, no se puede registrar
    if ($totalCoincidencias > 0) {
        throw new Exception('No se puede registrar la partida, ya existe.');
    }

    // Definir los valores a insertar en el array asociativo
    $campos_valores = [
        ['partida', $partida, true],
        ['descripcion', $denominacion]
    ];

    // Intentar insertar el registro
    try {
        $resultado = $db->insert('partidas_presupuestarias', $campos_valores);
        return json_encode($resultado);
    } catch (Exception $e) {
        throw new Exception("Error: " . $e->getMessage());
    }
}
//$response = registrar('401.03.97.00.0000', '0001', 'PRIMAS POR FRONTERAS');

$data = json_decode(file_get_contents("php://input"), true);

$partida = $data["partida"];
$ordinaria = $data["ordinaria"];
$denominacion = $data["denominacion"];

if (isset($data["partida"]) && isset($data["ordinaria"]) && isset($data["denominacion"])) {
    $response = registrar($partida, $ordinaria, $denominacion);
} else {
    echo json_encode(['error' => 'No se recibieron los datos necesarios.']);
}
echo $response;
