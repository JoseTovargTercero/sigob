<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';


// Consulta SQL con LEFT JOIN
$sql = "SELECT empleados.*, cargos_grados.grado,
        TIMESTAMPDIFF(YEAR, empleados.fecha_ingreso, CURDATE()) + empleados.otros_anios AS paso
        FROM empleados
        LEFT JOIN cargos_grados ON empleados.cod_cargo = cargos_grados.cod_cargo";

$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    // Mostrar los datos obtenidos
    echo "<table><tr><th>ID</th><th>Nombre</th><th>Cargo</th><th>Grado</th><th>Paso</th><th>Monto</th></tr>";
    while ($row = $result->fetch_assoc()) {
        // Obtener el monto correspondiente a este empleado
        $monto = obtenerMonto($conexion, $row["paso"], $row["grado"]);

        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["nombres"] . "</td><td>" . $row["cargo"] . "</td><td>" . $row["grado"] . "</td><td>" . $row["paso"] . "</td><td>" . $monto . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No se encontraron resultados.";
}
$conexion->close();

function obtenerMonto($conexion, $grado, $paso)
{
    // Consulta SQL para obtener el monto
    $grado = "G" . $grado; // Agregar el prefijo 'G' al grado
    $paso = "P" . $paso;   // Agregar el prefijo 'P' al paso

    // Encerrar los valores entre comillas
    $grado = $conexion->real_escape_string($grado);
    $paso = $conexion->real_escape_string($paso);

    $sql = "SELECT monto FROM tabuladores_estr WHERE grado = '$grado' AND paso = '$paso'";
    $result = $conexion->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row["monto"];
        } else {
            return "No disponible";
        }
    } else {
        echo "Error en la consulta: " . $conexion->error;
        return "No disponible";
    }
}
