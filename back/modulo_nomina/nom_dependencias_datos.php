<?php
require_once '../sistema_global/session.php';
require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');

// Inicializar el array de respuesta

// Verificar si se recibió un ID y limpiarlo

function obtenerDependencias()
{
    global $conexion;

    try {

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            $sql = "SELECT * FROM dependencias WHERE id_dependencia = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id);
        } else {
            $sql = "SELECT * FROM dependencias";
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
            $response = json_encode(["error" => "No se encontraron unidades registradas"]);
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

function crearDependencia($informacion)
{
    global $conexion;

    try {
        if (!isset($informacion["dependencia"]) || !isset($informacion["cod_dependencia"])) {
            throw new Exception('El campo código o nombre están vacíos');
        }
        if (!isset($informacion["dependencia"]) || !isset($informacion["id_categoria"])) {
            throw new Exception('La unidad debe estar asociada a una categoria');
        }
        $dependencia = $informacion["dependencia"];
        $cod_dependencia = $informacion["cod_dependencia"];
        $id_categoria = $informacion["id_categoria"];

        $stmt_dep = $conexion->prepare("SELECT * FROM `dependencias` WHERE cod_dependencia = ?");
        $stmt_dep->bind_param('s', $cod_dependencia);
        $stmt_dep->execute();
        $result = $stmt_dep->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Ya existe una unidad con el mismo código.");
        }


        $sql = "INSERT INTO dependencias (dependencia, cod_dependencia, id_categoria) VALUES (?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $dependencia, $cod_dependencia, $id_categoria);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response = json_encode(["success" => "Unidad creada con éxito"]);
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

function actualizarDependencia($informacion)
{
    global $conexion;

    try {
        if (!isset($informacion["id"])) {
            throw new Exception('No se ha indicado el ID de unidad a actualizar');
        }
        if (!isset($informacion["dependencia"]) || !isset($informacion["cod_dependencia"])) {
            throw new Exception('El campo código o nombre están vacíos');
        }
        if (!isset($informacion["dependencia"]) || !isset($informacion["id_categoria"])) {
            throw new Exception('La unidad debe estar asociada a una categoria');
        }
        $id = $informacion["id"];
        $dependencia = $informacion["dependencia"];
        $cod_dependencia = $informacion["cod_dependencia"];
        $id_categoria = $informacion["id_categoria"];

        // Verificar si el código ya está en uso por otra dependencia
        $stmt_check = $conexion->prepare("SELECT * FROM `dependencias` WHERE cod_dependencia = ? AND id_dependencia != ?");
        $stmt_check->bind_param('ss', $cod_dependencia, $id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            throw new Exception("Ya existe una unidad con el mismo código.");
        }

        $sql = "UPDATE dependencias SET dependencia = ?, cod_dependencia = ?, id_categoria = ? WHERE id_dependencia = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $dependencia, $cod_dependencia, $id_categoria, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response = json_encode(["success" => "Unidad actualizada con éxito"]);
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

function eliminarDependencia($informacion)
{

    global $conexion;

    try {
        if (!isset($informacion["id"])) {
            throw new Exception('No se ha indicado el ID de la unidad a eliminar');
        }
        $id = $informacion["id"];

        $stmt_emp = $conexion->prepare("SELECT * FROM `empleados` WHERE id_dependencia = ?");
        $stmt_emp->bind_param('s', $id);
        $stmt_emp->execute();
        $result = $stmt_emp->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Ya existe un empleado registrado con esta unidad");
        }


        $stmt_dep = $conexion->prepare("DELETE FROM `dependencias` WHERE id_dependencia = ?");
        $stmt_dep->bind_param('s', $id);


        if ($stmt_dep->execute()) {
            $response = json_encode(["success" => "Unidad eliminada correctamente."]);
        } else {
            throw new Exception("Error al eliminar la unidad: " . $conexion->error);
        }

        $stmt_dep->close();
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

            return crearDependencia($data["informacion"]);
        }
        if ($accion === "actualizar") {
            return actualizarDependencia($data["informacion"]);
        }
        if ($accion === "eliminar") {
            return eliminarDependencia($data["informacion"]);
        }

        return json_encode(['error' => "Acción no aceptada"]);
    } else {

        return obtenerDependencias();
    }


}

$response = procesarPeticion($data);

echo $response;

?>