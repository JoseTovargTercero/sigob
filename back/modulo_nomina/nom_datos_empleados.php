<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');


$cedula = $_POST["cedula"];


$stmt = mysqli_prepare($conexion, "SELECT 
        e.id, 
        e.cedula, 
        e.nombres, 
        d.dependencia,
        e.status, 
        c.cargo,
        nominas_grupos.nombre AS nombreNomina
    FROM 
        empleados AS e
    LEFT JOIN 
        dependencias AS d ON e.id_dependencia = d.id_dependencia
    LEFT JOIN 
        cargos_grados AS c ON e.cod_cargo = c.cod_cargo
    LEFT JOIN empleados_por_grupo AS g ON g.id_empleado = e.id
    LEFT JOIN nominas_grupos ON g.id_grupo = nominas_grupos.id
    WHERE e.cedula = ?");
$stmt->bind_param('s', $cedula);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo json_encode($row);

}
}else {
    echo json_encode(['error' => 'NE']);
}
$stmt->close();

$conexion->close();

?>
