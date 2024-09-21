<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

require_once '../sistema_global/errores.php';
// Función para consultar los registros de solicitud_dozavos y añadir información de partidas_presupuestarias
function obtenerSolicitudDozavos()
{
    global $conexion;

    try {
        // Consulta todos los registros de la tabla solicitud_dozavos
        $sql = "SELECT id, numero_orden, numero_compromiso, descripcion, tipo, monto, fecha, partidas, id_ente FROM solicitud_dozavos";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            $solicitudes = [];

            while ($row = $result->fetch_assoc()) {
                // Decodificar el campo de partidas que está en formato JSON
                $partidasArray = json_decode($row['partidas'], true);

                // Para cada elemento del array de partidas, consultamos en partidas_presupuestarias
                foreach ($partidasArray as &$partida) {
                    $idPartida = $partida['id'];

                    // Consultar en la tabla partidas_presupuestarias usando el id de la partida
                    $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                    $stmtPartida = $conexion->prepare($sqlPartida);
                    $stmtPartida->bind_param("i", $idPartida);
                    $stmtPartida->execute();
                    $stmtPartida->bind_result($partidaCod, $nombre, $descripcion);
                    $stmtPartida->fetch();
                    $stmtPartida->close();

                    // Añadir los datos de partida, nombre y descripcion al array de partidas
                    $partida['partida'] = $partidaCod;
                    $partida['nombre'] = $nombre;
                    $partida['descripcion'] = $descripcion;
                }

                // Convertir el array de partidas actualizado a JSON nuevamente
                $row['partidas'] = $partidasArray;

                // Agregar el registro completo al array de resultados
                $solicitudes[] = $row;
            }

            // Devolver los datos en formato JSON de éxito
            return json_encode(["success" => $solicitudes]);

        } else {
            // Si no se encuentran registros, lanzar un mensaje de éxito pero indicando que no hay datos
            return json_encode(["success" => "No se encontraron registros en solicitud_dozavos."]);
        }

    } catch (Exception $e) {
        // Manejo de error
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Ejecutar la función y mostrar los resultados
echo obtenerSolicitudDozavos();

?>