<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

// Inicializar el array de respuesta

// Verificar si se recibió un ID y limpiarlo

function obtenerCategorias()
{
    global $conexion;

    try {

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            $sql = "SELECT * FROM categorias WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id);
        } else {
            $sql = "SELECT * FROM categorias";
            $stmt = $conexion->prepare($sql);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("Error en la consulta: $conexion->error");
        }

        $datos = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }
            $response = json_encode(["success" => $datos]);
        } else {
            $response = json_encode(["error" => "No se encontraron categorías registradas"]);
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        // Registrar el error en un archivo de registro
        // error_log('Error: ' . $e->getMessage(), 3, '/ruta/al/archivo_de_error.log');
        return json_encode(['error' => $e->getMessage()]);
    }
}

function crearCategoria($informacion)
{
    global $conexion;

    try {
        if (!isset($informacion["categoria"]) || !isset($informacion["categoria_nombre"])) {
            throw new Exception('El campo categoría o nombre están vacíos');
        }
        $categoria = $informacion["categoria"];
        $categoria_nombre = $informacion["categoria_nombre"];

        $sql = "INSERT INTO categorias (categoria, categoria_nombre) VALUES (?,?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $categoria, $categoria_nombre);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response = json_encode(["success" => "Categoría creada con éxito"]);
        } else {
            throw new Exception("Error al insertar la categoría: $conexion->error");
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        // Registrar el error en un archivo de registro
        // error_log('Error: ' . $e->getMessage(), 3, '/ruta/al/archivo_de_error.log');
        return json_encode(['error' => $e->getMessage()]);
    }
}

function actualizarCategoria($informacion)
{
    global $conexion;

    try {
        if (!isset($informacion["id"])) {
            throw new Exception('No se ha indicado el ID de categoría a actualizar');
        }
        if (!isset($informacion["categoria"]) || !isset($informacion["categoria_nombre"])) {
            throw new Exception('El campo categoría o nombre están vacíos');
        }
        $id = $informacion["id"];
        $categoria = $informacion["categoria"];
        $categoria_nombre = $informacion["categoria_nombre"];

        $sql = "UPDATE categorias SET categoria = ?, categoria_nombre = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssi", $categoria, $categoria_nombre, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response = json_encode(["success" => "actualizada creada con éxito"]);
        } else {
            throw new Exception("No se ha cambiado ningún valor");
        }

        $stmt->close();
        $conexion->close();

        return $response;
    } catch (Exception $e) {
        // Registrar el error en un archivo de registro
        // error_log('Error: ' . $e->getMessage(), 3, '/ruta/al/archivo_de_error.log');
        return json_encode(['error' => $e->getMessage()]);
    }
}




$data = json_decode(file_get_contents("php://input"), true);
function procesarPeticion($data)
{

    if (isset($data["accion"])) {
        $accion = $data["accion"];
        if ($accion === "insertar") {
            if (!isset($data["informacion"]))
                return json_encode(['error' => "Acción no posee informacion"]);

            return crearCategoria($data["informacion"]);
        }
        if ($accion === "actualizar") {
            return actualizarCategoria($data["informacion"]);
        }
        
        return json_encode(['error' => "Acción no aceptada"]);
    } else {

        return obtenerCategorias();
    }


}

$response = procesarPeticion($data);

echo $response;

?>