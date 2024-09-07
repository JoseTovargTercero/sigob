<?php

session_start();  // Inicia la sesión
$u_nombre = $_SESSION['u_nombre'];  // Asigna el nombre del usuario a la variable de sesión

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

function registrarHistorial($descripcion, $precioactual)
{
    global $conexion;

    $u_nombre = $_SESSION['u_nombre'];
    $fecha_actual = date("d-m-Y");

    $sql = "INSERT INTO tasa_historico (u_nombre, precio, descripcion, fecha) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sdss", $u_nombre, $precioactual, $descripcion, $fecha_actual);
    $stmt->execute();
    $stmt->close();
}

function obtenerTasa()
{
    global $conexion;

    try {
        $sql = "SELECT * FROM tasa ORDER BY id DESC LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("Error en la consulta: $conexion->error");
        }

        if ($result->num_rows > 0) {
            $datos = $result->fetch_assoc();
            $response = json_encode(["success" => $datos]);
        } else {
            $response = json_encode(["error" => "No se encontraron registros de tasa"]);
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

function obtenerHistorialTasa()
{
    global $conexion;

    try {
        $sql = "SELECT * FROM tasa_historico ORDER BY id DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("Error en la consulta: $conexion->error");
        }




        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }
            $response = json_encode(["success" => $datos]);
        } else {
            $response = json_encode(["error" => "No se encontraron registros de tasa"]);
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

function crearTasa()
{
    global $conexion;

    try {
        $api_key = "afa5859e067e3a9f96886ebc";
        $url = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $precioactual = $data['conversion_rate'];

        // Verificar si ya existe un registro
        $stmt_check = $conexion->prepare("SELECT * FROM tasa");
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            throw new Exception("Ya existe un registro de tasa.");
        }

        $sql = "INSERT INTO tasa (descripcion, simbolo, valor) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $descripcion = "Precio del Dólar Actual";
        $simbolo = "$";
        $stmt->bind_param("ssd", $descripcion, $simbolo, $precioactual);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            registrarHistorial("creación de tasa por el usuario", $precioactual);
            $response = obtenerTasa();
            // $response = json_encode(["success" => "Tasa creada con éxito"]);
        } else {
            throw new Exception("Error al insertar la tasa: $conexion->error");
        }

        $stmt->close();
        // $conexion->close();

        return $response;
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

function actualizarTasa()
{
    global $conexion;

    try {
        $api_key = "afa5859e067e3a9f96886ebc";
        $url = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $precioactual = $data['conversion_rate'];

        // Actualizar solo el precio actual
        $sql = "UPDATE tasa SET valor = ? ORDER BY id DESC LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("d", $precioactual);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            registrarHistorial("actualización de tasa por el usuario", $precioactual);
            $response = json_encode(["success" => "Tasa actualizada con éxito"]);
        } else {
            throw new Exception("No se ha cambiado ningún valor");
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

function eliminarTasa($informacion)
{
    global $conexion;

    try {
        if (!isset($informacion["id"])) {
            throw new Exception('No se ha indicado el ID de tasa a eliminar');
        }
        $id = $informacion["id"];

        $stmt = $conexion->prepare("DELETE FROM tasa WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $response = json_encode(["success" => "Tasa eliminada correctamente."]);
        } else {
            throw new Exception("Error al eliminar la tasa: " . $conexion->error);
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

$data = json_decode(file_get_contents("php://input"), true);
function procesarPeticion($data)
{
    if (isset($data["accion"])) {
        $accion = $data["accion"];
        if ($accion === "insertar") {
            return crearTasa();
        }
        if ($accion === "actualizar") {
            return actualizarTasa();
        }
        if ($accion === 'historial') {
            return obtenerHistorialTasa();
        }
        if ($accion === "eliminar") {
            if (!isset($data["informacion"]))
                return json_encode(['error' => "Acción no posee información"]);

            return eliminarTasa($data["informacion"]);
        }

        return json_encode(['error' => "Acción no aceptada"]);
    } else {
        return obtenerTasa();
    }
}

$response = procesarPeticion($data);

echo $response;

?>