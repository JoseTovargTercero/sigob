<?php
require_once '../sistema_global/conexion.php';

if (isset($_POST["tabla"])) {

    $columnas_sistema = ["id", "nacionalidad", "cedula", "nombres", "otros_años", "status", "observacion", "cod_cargo", "banco", "cuenta_bancaria", "hijos", "instruccion_academica", "discapacidades", "tipo_nomina", "id_dependencia", "verificado", "correcion", "beca", "fecha_ingreso"];

    $columnas = [];

    // necesito regresar el nombre de las columnas y el tipo (int, varchar, etc) y su longitud de la tabla empleados
    $sql = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'empleados'";
    $result = $conexion->query($sql);
    $columnas = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // verifica que no este en $columnas_sistema antes de agregarla a $columnas
            if (!in_array($row['COLUMN_NAME'], $columnas_sistema)) {
                $columnas[$row['COLUMN_NAME']] = $row;
            }
        }
    }
    foreach ($columnas as $item) {
        $columna = $item['COLUMN_NAME'];
        $valores = [];

        $sql = "SELECT DISTINCT `$columna` FROM empleados";
        $result = $conexion->query($sql);
        if ($result->num_rows > 0) {
            $valores = array();
            while($row = $result->fetch_assoc()) {
                if ($row[$columna]!='') {
                    $valores[] = $row[$columna];
                }
            }
        }

        $columnas[$columna]['valores'] =  $valores;


    }


    echo json_encode($columnas, JSON_PRETTY_PRINT);

}elseif(isset($_POST["valores"])){
 $columnaEditar = $_POST["columna"];
 // traer todos los valores distintos del campo y la cantidad de veces que aparece cada valor
    $sql = "SELECT `$columnaEditar` AS columna, COUNT(*) as cantidad FROM empleados GROUP BY `$columnaEditar`";
    $result = $conexion->query($sql);
    $valores = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['columna'] != '') {
                $valores[] = $row;
            }
        }
    }
    echo json_encode($valores, JSON_PRETTY_PRINT);

}elseif (isset($_POST["updates"])) {
    // Recibir y limpiar los datos de entrada
    $columna = $_POST["columna"];
    $valor = $_POST["valor"];
    $empleados = $_POST["empleados"]; // Ya es un array, no hace falta usar json_decode
    
    // Verificar que $empleados no esté vacío
    if (!empty($empleados) && is_array($empleados)) {
        // Crear una lista separada por comas de los IDs de empleados
        $empleados_lista = implode(',', array_map('intval', $empleados)); // Convertir cada elemento a entero para mayor seguridad
        
        // Construir la consulta SQL con marcadores de posición
        $sql = "UPDATE empleados SET $columna = ? WHERE id IN ($empleados_lista)";
        
        // Preparar la sentencia
        $stmt = mysqli_prepare($conexion, $sql);
        
        if ($stmt) {
            // Ligar el parámetro (tipo de dato string, si es otro tipo ajustarlo a 'i', 'd', etc.)
            mysqli_stmt_bind_param($stmt, 's', $valor);
            
            // Ejecutar la sentencia
            if (mysqli_stmt_execute($stmt)) {
                // Respuesta exitosa
                echo json_encode(['status' => 'success', 'mensaje' => 'Actualización exitosa.']);
            } else {
                // Error en la ejecución de la sentencia
                echo json_encode(['status' => 'error', 'mensaje' => 'Error al ejecutar la actualización: ' . mysqli_stmt_error($stmt)]);
            }
            
            // Cerrar la sentencia
            mysqli_stmt_close($stmt);
        } else {
            // Error en la preparación de la sentencia
            echo json_encode(['status' => 'error', 'mensaje' => 'Error en la preparación de la consulta: ' . mysqli_error($conexion)]);
        }
    } else {
        // No se proporcionaron empleados
        echo json_encode(['status' => 'error', 'mensaje' => 'No se proporcionaron empleados para actualizar.']);
    }
    
    

} elseif(isset($_POST["tabla_filtrada"])) {
    $columna = $_POST["columna"];
    $valor = $_POST["valor"];

    $valores = [];
    $sql = "SELECT * FROM empleados WHERE $columna=$valor";
    $result = $conexion->query($sql);
    if ($result->num_rows > 0) {
        $valores = array();
        while($row = $result->fetch_assoc()) {
            if ($row[$columna]!='') {
                $valores[$row['id']] = $row;
            }
        }
    }


echo json_encode($valores, JSON_PRETTY_PRINT);

}

$conexion->close();
?>