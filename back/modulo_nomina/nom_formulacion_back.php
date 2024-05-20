<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

if (isset($_POST["tabla_empleados"])) {

    $tipo_filtro = $_POST["tipo_filtro"];
    $filtro = $_POST["filtro"];



    if ($tipo_filtro == 3) {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `empleados`WHERE $filtro ORDER BY cedula");
    }elseif ($tipo_filtro == 2) {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `empleados` ORDER BY cedula");
    }else {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM `empleados` ORDER BY cedula");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();

    echo json_encode($data);

}
$conexion->close();



/*
    Requiero un archivo, parecido al que validaba las condiciones de los conceptos.


    $tipo_filtro = $_POST["tipo_filtro"];
    $filtro = $_POST["filtro"];


    if ($tipo_filtro == 3) {
       // queda pendiente (aqui se mostraran empleados de otras nominas)
    }elseif ($tipo_filtro == 2) {
        // devolvera la lista de empleados con una condicion enviada por $filtro

        por antiguedad vas a recibir:
        "años_actuales>5" (Int) y se deben mostrar los empleados que tengan mas de N a;os indicados en base a la fecha de ingreso
        "años_total:5" se deben devolver todos los empleados
     
    }else {
       // todos los empleados
    }


    Para la opcion 2 se debe validar si la consulta tiene error o no.


    Retornas un json con (nacionalidad, cedula, cod_empleado, nombre, feche_ingreso, anios_actuales (claculado), otros_años, anios_totales (calculado), status, observacion, cod_cargo, hijos, instruccion_academica, discapacidades, id_dependencia)


