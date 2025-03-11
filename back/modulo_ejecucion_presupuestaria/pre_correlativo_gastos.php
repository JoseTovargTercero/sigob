<?php
require_once '../sistema_global/conexion.php';

header('Content-Type: application/json');
// recibe datos de inputs
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["id_ejercicio"]) && isset($data["id_tipo_gasto"])) {
    $id_ejercicio = $data["id_ejercicio"];
    $id_tipo_gasto = $data["id_tipo_gasto"];
    //$id_ejercicio = '1';
    //$id_tipo_gasto = '1';

    function asignarPrefijoUnico($conexion, $id_tipo_gasto)
    {
        $nombre = '';
        $prefijo = '';
        $count = 0;
        // Obtener nombre y prefijo actual
        $stmt = $conexion->prepare("SELECT nombre, prefijo FROM tipo_gastos WHERE id = ?");
        $stmt->bind_param('s', $id_tipo_gasto);
        $stmt->execute();
        $stmt->bind_result($nombre, $prefijo);
        $stmt->fetch();
        $stmt->close();

        // Si el prefijo está vacío o es null, generar uno nuevo
        if (empty($prefijo)) {
            $longitud = 1;
            $prefijo_nuevo = '';

            do {
                $prefijo_nuevo = substr($nombre, 0, $longitud);
                $check_stmt = $conexion->prepare("SELECT COUNT(*) FROM tipo_gastos WHERE prefijo = ?");
                $check_stmt->bind_param('s', $prefijo_nuevo);
                $check_stmt->execute();
                $check_stmt->bind_result($count);
                $check_stmt->fetch();
                $check_stmt->close();
                $longitud++;
            } while ($count > 0 && $longitud <= strlen($nombre));

            // Si encontramos un prefijo único, actualizamos
            if ($count == 0) {
                $update_stmt = $conexion->prepare("UPDATE tipo_gastos SET prefijo = ? WHERE id = ?");
                $update_stmt->bind_param('ss', $prefijo_nuevo, $id_tipo_gasto);
                $update_stmt->execute();
                $update_stmt->close();
                $prefijo = $prefijo_nuevo;
            }
        }

        // Devolver resultado como JSON
        return $prefijo;
    }

    $prefijo = asignarPrefijoUnico($conexion, $id_tipo_gasto);

    $ultimo_numero_compromiso = 0;
    $stmt = mysqli_prepare($conexion, "SELECT C.numero_compromiso FROM gastos INNER JOIN compromisos AS C ON C.id_registro = gastos.id AND C.tabla_registro = 'gastos' WHERE gastos.id_ejercicio = ? AND gastos.id_tipo = ? ORDER BY C.numero_compromiso DESC LIMIT 1");
    $stmt->bind_param('ss', $id_ejercicio, $id_tipo_gasto);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $numero = $row['numero_compromiso'];
            $ultimo_numero_compromiso = 0;

            if (strpos($numero, '-') !== false) {
                $partes = explode('-', $numero);
                if (isset($partes[1]) && is_numeric($partes[1])) {
                    $ultimo_numero_compromiso = (int) $partes[1];
                }
            }
        }
    }
    $stmt->close();

    $numero_recomendado = [
        'prefijo' => $prefijo,
        'numero_compromiso_recomendado' => $ultimo_numero_compromiso + 1
    ];

    echo json_encode($numero_recomendado);
}
