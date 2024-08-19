<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';


    $sql = "
    SELECT 
        r.id_empleado, 
        r.asignaciones, 
        r.deducciones, 
        r.aportes, 
        r.total_pagar, 
        r.nombre_nomina, 
        r.time, 
        r.fecha,
        e.cedula, 
        e.nombres, 
        d.id_dependencia, 
        d.dependencia,
        e.nacionalidad, 
        e.fecha_ingreso, 
        e.otros_años, 
        e.status, 
        e.observacion, 
        e.cod_cargo, 
        e.banco, 
        e.cuenta_bancaria, 
        e.hijos, 
        e.instruccion_academica, 
        e.discapacidades, 
        c.cargo
    FROM 
        historico_reintegros AS r
    LEFT JOIN 
        empleados AS e ON e.id = r.id_empleado
    LEFT JOIN 
        dependencias AS d ON e.id_dependencia = d.id_dependencia
    LEFT JOIN 
        cargos_grados AS c ON e.cod_cargo = c.cod_cargo
    ORDER BY 
        r.id_empleado, r.time
";

   
    
$resultado = $conexion->query($sql);
$empleados = [];

while ($row = $resultado->fetch_assoc()) {
    $id_empleado = $row['id_empleado'];
    $time = $row['fecha'];

    if (!isset($empleados[$id_empleado])) {
        $empleados[$id_empleado] = [
            'cedula' => $row['cedula'],
            'nombres' => $row['nombres'],
            'id_dependencia' => $row['id_dependencia'],
            'dependencia' => $row['dependencia'],
            'nacionalidad' => $row['nacionalidad'],
            'fecha_ingreso' => $row['fecha_ingreso'],
            'otros_años' => $row['otros_años'],
            'status' => $row['status'],
            'observacion' => $row['observacion'],
            'cod_cargo' => $row['cod_cargo'],
            'banco' => $row['banco'],
            'cuenta_bancaria' => $row['cuenta_bancaria'],
            'hijos' => $row['hijos'],
            'instruccion_academica' => $row['instruccion_academica'],
            'discapacidades' => $row['discapacidades'],
            'cargo' => $row['cargo'],
            'time' => $row['time'],
            'reintegros' => [
                $row['time'] => []
            ]
        ];
    }

    // Agregar el reintegro a la fecha correspondiente
    $empleados[$id_empleado]['reintegros'][$row['time']][] = [
        'fecha' => $time,
        'asignaciones' => $row['asignaciones'],
        'deducciones' => $row['deducciones'],
        'aportes' => $row['aportes'],
        'total_pagar' => $row['total_pagar'],
        'nombre_nomina' => $row['nombre_nomina']
    ];
}


header('Content-Type: application/json');
// Imprimir en formato JSON
echo json_encode($empleados, JSON_PRETTY_PRINT);
$conexion->close();


?>
