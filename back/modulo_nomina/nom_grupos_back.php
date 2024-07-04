<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

if (isset($_POST["tabla"])) {

    $stmt = mysqli_prepare($conexion, "SELECT 
    nominas_grupos.codigo, 
    nominas_grupos.nombre, 
    nominas_grupos.id,
    EXISTS (SELECT 1 FROM empleados_por_grupo WHERE empleados_por_grupo.id_grupo = nominas_grupos.id) AS grupo_existe
FROM 
    nominas_grupos 
ORDER BY  codigo");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();

    if (@$data) {
        echo json_encode($data);
    }
} elseif (isset($_POST["registro"])) {

    $codigo = clear($_POST["codigo"]);
    $nombre = $_POST["nombre"];
    //Comprobar que no exist
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `nominas_grupos` WHERE codigo = ? LIMIT 1");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo 'ye';
    } else {
        $stmt->close();
        $stmt = mysqli_prepare($conexion, "INSERT INTO `nominas_grupos` (codigo, nombre) VALUES (?, ?)");
        $stmt->bind_param("ss", $codigo, $nombre);

        if ($stmt->execute()) {
            echo 'ok';
        } else {
            echo "E: " . $stmt->error;
        }

        $stmt->close();

    }


} elseif (isset($_POST["eliminar"])) {
    $id = $_POST["id"];
    $stmt = mysqli_prepare($conexion, "DELETE FROM `nominas_grupos` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo 'ok';
}
$conexion->close();

exit();