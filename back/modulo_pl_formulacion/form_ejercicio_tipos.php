<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para obtener sumatoria según el tipo
function obtenerSumatoriaPorTipo($ejercicio, $tipo) {
    global $conexion;

    try {
        // Validar que ejercicio y tipo no estén vacíos
        if (empty($ejercicio) || empty($tipo)) {
            throw new Exception("Debe proporcionar un ejercicio y un tipo válido");
        }

        // Crear un array para almacenar los resultados finales
        $resultados = [];

        // Consultar la tabla distribucion_presupuestaria para obtener los registros con el id_ejercicio dado
        $sql = "SELECT id_partida, monto_inicial, monto_actual FROM distribucion_presupuestaria WHERE id_ejercicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ejercicio);
        $stmt->execute();
        $resultadoDistribucion = $stmt->get_result();

        // Array para almacenar los datos de las partidas
        $partidasDatos = [];

        // Iterar sobre los registros obtenidos de distribucion_presupuestaria
        while ($filaDistribucion = $resultadoDistribucion->fetch_assoc()) {
            $idPartida = $filaDistribucion['id_partida'];
            $montoInicial = isset($filaDistribucion['monto_inicial']) ? (float)$filaDistribucion['monto_inicial'] : 0; // Asegurar que sea numérico
            $montoActual = isset($filaDistribucion['monto_actual']) ? (float)$filaDistribucion['monto_actual'] : 0;   // Asegurar que sea numérico

            // Consultar en la tabla partidas_presupuestarias para obtener el valor de "partida" según el id_partida
            $sqlPartida = "SELECT partida FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $conexion->prepare($sqlPartida);
            $stmtPartida->bind_param("i", $idPartida);
            $stmtPartida->execute();
            $resultadoPartida = $stmtPartida->get_result();
            $filaPartida = $resultadoPartida->fetch_assoc();

            if ($filaPartida) {
                $partida = $filaPartida['partida']; // Ejemplo de estructura xx.xx.xx.xxx.xx.xx.xxxx

                // Dividir la partida en sus partes según los puntos (.)
                $partidaPartes = explode('.', $partida);

                // Dependiendo del tipo, tomamos la parte correcta de la partida
                $indice = obtenerIndiceTipo($tipo);

                if ($indice !== null && isset($partidaPartes[$indice])) {
                    $valor = $partidaPartes[$indice];

                    // Si el valor ya existe en el array, sumamos los montos
                    if (isset($partidasDatos[$valor])) {
                        $partidasDatos[$valor]['total_inicial'] += $montoInicial;
                        $partidasDatos[$valor]['total_restante'] += $montoActual;
                    } else {
                        // Si no existe, lo agregamos
                        $partidasDatos[$valor] = [
                            'value' => $valor,
                            'total_inicial' => $montoInicial,
                            'total_restante' => $montoActual
                        ];
                    }
                }
            }
        }

        // Preparar el array final de resultados
        foreach ($partidasDatos as $datos) {
            $resultados[] = [
                'value' => $datos['value'],
                'total_inicial' => $datos['total_inicial'],
                'total_restante' => $datos['total_restante']
            ];
        }

        // Devolver los resultados como JSON
        return json_encode($resultados);

    } catch (Exception $e) {
        // Registrar el error en la tabla error_log
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función auxiliar para obtener el índice del tipo en la partida
function obtenerIndiceTipo($tipo) {
    $tipos = [
        "sector" => 0,
        "programa" => 1,
        "actividad" => 2,
        "proyecto" => 3,
        "generica" => 4,
        "especifica" => 5,
        "subespecifica" => 6
    ];

    return isset($tipos[$tipo]) ? $tipos[$tipo] : null;
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["ejercicio"]) && isset($data["tipo"])) {
    $ejercicio = $data["ejercicio"];
    $tipo = $data["tipo"]; // Recibimos solo un valor de tipo

    // Llamar a la función para obtener las sumatorias por tipo
    echo obtenerSumatoriaPorTipo($ejercicio, $tipo);
} else {
    echo json_encode(['error' => "No se proporcionaron los datos necesarios (ejercicio y tipo)"]);
}
