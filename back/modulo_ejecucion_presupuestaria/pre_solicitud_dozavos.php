<?php

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
require_once '../sistema_global/notificaciones.php';
header('Content-Type: application/json');

require_once '../sistema_global/errores.php';

// Función para consultar o modificar registros en solicitud_dozavos según la acción
function gestionarSolicitudDozavos($data)
{
    global $conexion;

    try {
        // Verificar que la acción esté presente en los datos
        if (!isset($data['accion'])) {
            return json_encode(["error" => "No se ha especificado acción."]);
        }

        $accion = $data['accion'];

        // Acción: Consultar todos los registros
        if ($accion === 'consulta') {
            $sql = "SELECT id, numero_orden, numero_compromiso, descripcion, tipo, monto, fecha, partidas, id_ente FROM solicitud_dozavos";
            $result = $conexion->query($sql);

            if ($result->num_rows > 0) {
                $solicitudes = [];

                while ($row = $result->fetch_assoc()) {
                    $partidasArray = json_decode($row['partidas'], true);

                    foreach ($partidasArray as &$partida) {
                        $idPartida = $partida['id'];
                        $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                        $stmtPartida = $conexion->prepare($sqlPartida);
                        $stmtPartida->bind_param("i", $idPartida);
                        $stmtPartida->execute();
                        $stmtPartida->bind_result($partidaCod, $nombre, $descripcion);
                        $stmtPartida->fetch();
                        $stmtPartida->close();

                        $partida['partida'] = $partidaCod;
                        $partida['nombre'] = $nombre;
                        $partida['descripcion'] = $descripcion;
                    }

                    $row['partidas'] = $partidasArray;
                    $solicitudes[] = $row;
                }

                return json_encode(["success" => $solicitudes]);
            } else {
                return json_encode(["success" => "No se encontraron registros en solicitud_dozavos."]);
            }
        }

        // Acción: Consultar un registro por ID
        elseif ($accion === 'consulta_id') {
            if (!isset($data['id'])) {
                return json_encode(["error" => "No se ha especificado ID para consulta."]);
            }

            $id = $data['id'];
            $sql = "SELECT id, numero_orden, numero_compromiso, descripcion, tipo, monto, fecha, partidas, id_ente FROM solicitud_dozavos WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $partidasArray = json_decode($row['partidas'], true);

                foreach ($partidasArray as &$partida) {
                    $idPartida = $partida['id'];
                    $sqlPartida = "SELECT partida, nombre, descripcion FROM partidas_presupuestarias WHERE id = ?";
                    $stmtPartida = $conexion->prepare($sqlPartida);
                    $stmtPartida->bind_param("i", $idPartida);
                    $stmtPartida->execute();
                    $stmtPartida->bind_result($partidaCod, $nombre, $descripcion);
                    $stmtPartida->fetch();
                    $stmtPartida->close();

                    $partida['partida'] = $partidaCod;
                    $partida['nombre'] = $nombre;
                    $partida['descripcion'] = $descripcion;
                }

                $row['partidas'] = $partidasArray;
                return json_encode(["success" => $row]);
            } else {
                return json_encode(["error" => "No se encontró el registro con el ID especificado."]);
            }
        }

        // Acción: Eliminar el registro (rechazar)
        elseif ($accion === 'rechazar') {
            if (!isset($data['id'])) {
                return json_encode(["error" => "No se ha especificado ID para rechazar."]);
            }

            $id = $data['id'];
            $sql = "DELETE FROM solicitud_dozavos WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                return json_encode(["success" => "La solicitud no es valida, por lo que sera eliminada."]);
                notificar(['nomina'], 11);
            } else {
                return json_encode(["error" => "No se pudo eliminar el registro o el ID no existe."]);
            }
        }

        // Si la acción no es válida
        else {
            return json_encode(["error" => "Acción no válida."]);
        }
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Recibir datos mediante POST
$data = json_decode(file_get_contents("php://input"), true);

// Ejecutar la función y mostrar los resultados según la acción
echo gestionarSolicitudDozavos($data);

?>
