<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';


function guardarSector($sector_array) // todo: EN USO
{
    global $conexion;

    try {
        // Verificar que el array de proyectos no esté vacío
        if (empty($sector_array)) {
            throw new Exception("El array de proyectos está vacío");
        }
        $nombre = $sector_array['nombre'];
        $sector = $sector_array['sector'];
        $programa = $sector_array['programa'];
        $proyecto = $sector_array['proyecto'];

        // verificar que no exista el sector

        $stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_sectores_presupuestarios` WHERE sector = ? AND programa  = ? AND proyecto = ?");
        $stmt->bind_param('sss', $sector, $programa, $proyecto);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("El sector ya existe");
        }
        $stmt->close();


        $sql = "INSERT INTO pl_sectores_presupuestarios (sector, programa, proyecto, nombre) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $sector, $programa, $proyecto, $nombre);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            $error = $stmt->error;
            throw new Exception("Error al insertar en la tabla proyecto_inversion. $error");
        }

        $stmt->close();

        return json_encode(["success" => "Datos guardados correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Función para actualizar datos del sector
function actualizarSector($sector_array) // todo: EN USO
{
    global $conexion;

    try {

        $sector = $sector_array['sector'];
        $programa = $sector_array['programa'];
        $proyecto = $sector_array['proyecto'];
        $nombre = $sector_array['nombre'];
        $id = $sector_array['id'];

        $error = false;
        // Actualizar los datos del proyecto
        $sql = "UPDATE pl_sectores_presupuestarios SET sector = ?, programa = ?, proyecto = ?, nombre = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssss", $sector, $programa, $proyecto, $nombre, $id);
        if (!$stmt->execute()) {
            $error = true;
        }
        $stmt->close();

        if ($error) {
            throw new Exception("Error al actualizar el sector.");
        }

        return json_encode(["success" => "Sector actualizado correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Obtener lista de sectores
function getSectores()  // todo: EN USO
{
    global $conexion;
    $data = [];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `pl_sectores_presupuestarios`");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($data, $row);
        }
    }
    $stmt->close();
    return json_encode(['success' => $data]);
}



function eliminarSector($id) // todo: EN USO
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `distribucion_presupuestaria` WHERE id_sector = ? ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception("No se puede eliminar, el sector esta en uso.");
    }
    $stmt->close();



    $stmt = $conexion->prepare("DELETE FROM `pl_sectores_presupuestarios` WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Sector eliminado']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar el sector']);
    }
    $stmt->close();
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    // Nuevos proyectos
    if ($accion === "registrar_proyecto" && isset($data["data"])) {
        echo guardarSector($data["data"]); // TODO: EN USO
        // Actualizar plan de inversión
    } elseif ($accion === "update_sector" && isset($data["data"])) {
        echo actualizarSector($data["data"]); //TODO: EN USO
        // Marcar proyecto como ejecutado
    } elseif ($accion === 'eliminar_sector' && isset($data['id'])) {
        echo eliminarSector($data['id']); // todo: EN USO
    } elseif ($accion === "get_sectores") {
        echo getSectores(); // TODO: EN USO
    } else {
        echo json_encode(["error" => "Acción inválida o datos faltantes."]);
    }
} else {
    echo json_encode(["error" => "Acción no especificada."]);
}
