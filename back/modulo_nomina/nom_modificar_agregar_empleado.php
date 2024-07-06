<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

/*
{
    "accion":"agregar_empleado",  # La acción a realizar
    "empleado":875, # el ID del empleado en la tabla 'empleados'
    "grupo_nomina":"3", # El grupo de nomina al que se esta haciendo el cambio
    "nominas":[
        {"nomina":"5","conceptos":["sueldo_base","21","24","25","26","27"]}, # se exceptuando los tipo 6
        {"nomina":"7","conceptos":["sueldo_base"]}
        ], # Las nominas alteradas. 
          # [nomina] = ID nomina en la tabla nominas.
          # [conceptos] = Todos los conceptos que se le aplicaran al empleado
    "info_reintegro":{ # En caso de que se requiera reintegro
        "reintegro":{
            "reintegro":"1", # 1 =  Si, 0 = No
            "datos":{
                "pagarDesde":"1", # 1 = pagar desde la fecha de ingreso, 2 = pagar desde la fechaEspecifica
                "fechaIngreso":"2022-11-21",
                "fechaEspecifica":""}
                }
                }
            }

            * Al recorrer cada nomina, se debe buscar los conceptos formulados (tipo_calculo == 6) y verificar si corresponde aplicarlos al empleado en cuestión

*/
if($data['accion'] == "agregar_empleado"){
    $empleado = $data['empleado'];
    $grupo_nomina = $data['grupo_nomina'];
    $nominas = $data['nominas'];
    $info_reintegro = $data['info_reintegro'];

    // TODO: -- Registrar un nuevo empleado en la lista de nomina que se suministro y aplicar los conceptos indicados mas los conceptos formulados de la misma nomina si cumple con las condiciones

    // TODO: -- Posterior a realizar el registro, se debe registrar en 'empleados_por_grupo.sql' indicando 'id_empleado', 'id_grupo' (se recibe por: *grupo_nomina*)

    // TODO: -- En caso de aplicar reintegro se debe generar el archivo para su posterior pago
    // Es necesario indicar en el historico de la nomina, que se pago reintegro a un empleado

    /* Formato de Respuestas:
    * echo json_encode(["status"=> "ok", "mensaje"=>"Empleado agregado correctamente"]);
    * echo json_encode(["status"=> "error", "mensaje"=>"No se pudo agregar"]);
    */

}






$conexion->close();
