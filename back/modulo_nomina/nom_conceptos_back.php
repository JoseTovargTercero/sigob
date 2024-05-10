<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

if (isset($_POST["tabla"])) {

    $stmt = mysqli_prepare($conexion, "SELECT * FROM `conceptos` ORDER BY nom_concepto");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();

    echo json_encode($data);
} elseif (isset($_POST["registro"])) {

    $nombre = clear($_POST["nombre"]);
    $tipo = $_POST["tipo"];
    $partida = clear($_POST["partida"]);
    //Comprobar que no exist
    $stmt = mysqli_prepare($conexion, "SELECT * FROM `conceptos` WHERE nom_concepto = ? LIMIT 1");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo 'ye';
    } else {
        $stmt->close();
        $stmt = mysqli_prepare($conexion, "INSERT INTO `conceptos` (nom_concepto, tipo_concepto, cod_partida) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $tipo, $partida);
        $stmt->execute();
        $stmt->close();
        echo 'ok';
    }


} elseif (isset($_POST["eliminar"])) {
    $id = $_POST["id"];
    $stmt = mysqli_prepare($conexion, "DELETE FROM `conceptos` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo 'ok';
}
$conexion->close();

exit();