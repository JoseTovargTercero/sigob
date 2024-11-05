<?php

require_once '../sistema_global/conexion.php';
header('Content-Type: application/json');
require_once '../sistema_global/session.php';
require_once '../sistema_global/errores.php';

// Función para insertar datos en plan_inversion y proyecto_inversion
function guardarEnte($proyectosArray)
{
    global $conexion;

    try {
        if (empty($proyectosArray)) {
            throw new Exception("El array de proyectos está vacío");
        }


        $sector = $proyectosArray['sector'];
        $programa = $proyectosArray['programa'];
        $proyecto = $proyectosArray['proyecto'];
        $actividad = $proyectosArray['actividad'];
        $nombre = $proyectosArray['nombre'];
        $tipo_ente = $proyectosArray['tipo_ente'];
        /*
        // verificar nombre
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `entes` WHERE sector = ? AND programa = ? AND proyecto = ?");
        $stmt->bind_param('sss', $sector, $programa, $proyecto);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Ya existe un ente con el mismo nombre");
        }
        $stmt->close();
        // Verificar que no exista una actividad para el sector y programa seleccionado

*/

        $sql = "INSERT INTO entes (
        sector,
        programa,
        proyecto,
        actividad,
        ente_nombre,
        tipo_ente
        ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssss", $sector, $programa, $proyecto, $actividad, $nombre, $tipo_ente);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            $error = $stmt->error;
            throw new Exception("Error al insertar en la tabla proyecto_inversion. $error");
        }
        $id_ente = $conexion->insert_id;

        // Registrar en actividades (entes_dependencias)
        $info = [
            'id_ente' => $id_ente,
            'sector' => $sector,
            'programa' => $programa,
            'proyecto' => $proyecto,
            'actividad_suu' => '51',
            'denominacion_suu' => $nombre
        ];
        $stmt->close();

        guardar_suu($info, $tipo_ente);


        return json_encode(["success" => "Datos guardados correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}
// Función para insertar datos en plan_inversion y proyecto_inversion
function guardar_suu($info, $tipo_ente = 'J')
{
    global $conexion;

    try {
        if (empty($info)) {
            throw new Exception("El array de proyectos está vacío");
        }


        $id_ente = $info['id_ente'];
        $sector = $info['sector'];
        $programa = $info['programa'];
        $proyecto = $info['proyecto'];
        $actividad_suu = $info['actividad_suu'];
        $denominacion_suu = $info['denominacion_suu'];

        // verificar nombre
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `entes_dependencias` WHERE ente_nombre = ?");
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Ya existe una dependencia con el mismo nombre");
        }
        $stmt->close();

        $sql = "INSERT INTO entes_dependencias (
        ue,
        sector,
        programa,
        proyecto,
        actividad,
        ente_nombre,
        tipo_ente
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssss", $id_ente, $sector, $programa, $proyecto, $actividad_suu, $denominacion_suu, $tipo_ente);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            $error = $stmt->error;
            throw new Exception("Error al registrar la dependencia. $error");
        }

        $stmt->close();

        return json_encode(["success" => "Datos guardados correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar datos en plan_inversion
function actualizarPlanInversion($id_plan, $monto_total)
{
    global $conexion;

    try {
        $fecha = date('Y-m-d');
        $sql = "UPDATE plan_inversion SET monto_total = ?, fecha = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("dsi", $monto_total, $fecha, $id_plan);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            throw new Exception("Error al actualizar el plan de inversión.");
        }

        $stmt->close();
        return json_encode(["success" => "Plan de inversión actualizado correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Función para actualizar datos en proyecto_inversion
function actualizarEnte($ente)
{
    global $conexion;

    try {
        $nombre = $ente['nombre'];
        $sector = $ente['sector'];
        $programa = $ente['programa'];
        $proyecto = $ente['proyecto'];
        $id_ente = $ente['id_ente'];

        $error = false;
        // Actualizar los datos del proyecto
        $sql = "UPDATE entes SET sector = ?, programa=?, proyecto = ?, ente_nombre= ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssss", $sector, $programa, $proyecto, $nombre, $id_ente);
        if (!$stmt->execute()) {
            $error = true;
        }
        $stmt->close();

        if ($error) {
            throw new Exception("Error al actualizar el proyecto de inversión.");
        }
        return json_encode(["success" => "Proyecto actualizado correctamente."]);
    } catch (Exception $e) {
        registrarError($e->getMessage());
        return json_encode(['error' => $e->getMessage()]);
    }
}


// Obtener lista de entes
function get_unidades()
{
    global $conexion;
    $data = [];

    $stmt = mysqli_prepare($conexion, "SELECT entes.*, pl_sectores.sector AS sector_n, pl_programas.programa AS programa_n, pl_proyectos.proyecto_id AS proyecto_n FROM `entes`
    LEFT JOIN pl_sectores ON entes.sector = pl_sectores.id
    LEFT JOIN pl_programas ON entes.programa = pl_programas.id
    LEFT JOIN pl_proyectos ON entes.proyecto = pl_proyectos.id
     ORDER BY tipo_ente DESC, ente_nombre ASC ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push(
                $data,
                $row
            );
        }
    }
    $stmt->close();
    return json_encode(['success' => $data]);
}

// Obtener lista de subEntes
function get_sub_unidades()
{
    global $conexion;
    $data = [];





    $stmt = mysqli_prepare($conexion, "SELECT entes_dependencias.*, entes.ente_nombre AS nombre_ente_p, pl_sectores.sector AS sector_n, pl_programas.programa AS programa_n, pl_proyectos.proyecto_id AS proyecto_n FROM `entes_dependencias`
    LEFT JOIN entes ON entes.id = entes_dependencias.ue
    LEFT JOIN pl_sectores ON entes_dependencias.sector = pl_sectores.id
    LEFT JOIN pl_programas ON entes_dependencias.programa = pl_programas.id
    LEFT JOIN pl_proyectos ON entes_dependencias.proyecto = pl_proyectos.id
    WHERE entes_dependencias.actividad != '51'
     ORDER BY entes_dependencias.ue ASC, entes_dependencias.ente_nombre ASC ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push(
                $data,
                $row
            );
        }
    }
    $stmt->close();
    return json_encode(['success' => $data]);
}



function eliminarEnte($id)
{
    global $conexion;

    $stmt_2 = mysqli_prepare($conexion, "SELECT * FROM `distribucion_entes` WHERE id_ente = ? ");
    $stmt_2->bind_param('s', $id);
    $stmt_2->execute();
    $result_2 = $stmt_2->get_result();
    if ($result_2->num_rows > 0) {
        echo json_encode(['error' => 'No se puede eliminar, el ente tiene una asignación']);
        exit;
    }
    $stmt_2->close();


    $sub_entes = [];
    $stmt = mysqli_prepare($conexion, "SELECT actividad FROM `entes_dependencias` WHERE ue = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sub_entes[] = $row['actividad'];  // Añade cada actividad al array de sub_entes
        }
    }
    $stmt->close();

    $stmt = $conexion->prepare("DELETE FROM `entes` WHERE id = ? ");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        $stmt_2 = $conexion->prepare("DELETE FROM `entes_dependencias` WHERE ue = ? ");
        $stmt_2->bind_param("i", $id);
        $stmt_2->execute();
        $stmt_2->close();

        echo json_encode(['success' => 'Unidad eliminada']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar la unidad']);
    }
    $stmt->close();
}

// Procesar la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["accion"])) {
    $accion = $data["accion"];

    // Nuevos proyectos
    if ($accion === "registrar_ente" && isset($data["unidad"])) {
        echo guardarEnte($data["unidad"]); // TODO: LISTO
    } elseif ($accion === "guardar_suu" && isset($data["info"])) {
        echo guardar_suu($data["info"]); // TODO: LISTO
    } elseif ($accion === "update_ente" && isset($data["unidad"])) {
        echo actualizarEnte($data["unidad"]); // TODO: LISTO
    } elseif ($accion === 'eliminar_ente' && isset($data['id'])) {
        echo eliminarEnte($data['id']);
    } elseif ($accion === "get_unidades") {
        echo get_unidades(); // TODO: LISTO

    } elseif ($accion === "get_sub_unidades") {
        echo get_sub_unidades(); // TODO: LISTO
    } else {
        echo json_encode(["error" => "Acción inválida o datos faltantes."]);
    }
} else {
    echo json_encode(["error" => "Acción no especificada."]);
}
