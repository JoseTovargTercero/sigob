<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// Verificar si el parámetro 'id' está presente en la URL y el método de solicitud es GET
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Consulta SQL para obtener los datos del empleado y su dependencia
    $sql = "SELECT e.id, e.cedula, e.nombres, e.tipo_nomina, d.id_dependencia, d.dependencia,
                   e.nacionalidad, e.cod_empleado, e.fecha_ingreso, e.otros_años, e.status, 
                   e.observacion, e.cod_cargo, e.banco, e.cuenta_bancaria, e.hijos, 
                   e.instruccion_academica, e.discapacidades, e.tipo_cuenta, e.tipo_nomina, c.cargo 
            FROM empleados AS e
            INNER JOIN dependencias AS d ON e.id_dependencia = d.id_dependencia
            LEFT JOIN cargos_grados AS c ON e.cod_cargo = c.cod_cargo
            WHERE e.id = ?";

    // Preparar la declaración SQL
    $stmt = $conexion->prepare($sql);

    // Comprobar si la preparación de la declaración fue exitosa
    if (!$stmt) {
        die("Error en la preparación de la declaración: " . $conexion->error);
    }

    // Vincular el parámetro y ejecutar la consulta
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Crear un array para almacenar los datos
    $datos = array();

    if ($result->num_rows > 0) {
        // Llenar el array con los datos obtenidos de la consulta
        while ($row = $result->fetch_assoc()) {
            $empleado = array(
                "id_empleado" => $row["id"],
                "cedula" => $row["cedula"], // LISTO
                "nombres" => $row["nombres"], // LISTO
                "tipo_nomina" => $row["tipo_nomina"],
                "id_dependencia" => $row["id_dependencia"],
                "dependencia" => $row["dependencia"], // LISTO
                "nacionalidad" => $row["nacionalidad"], // LISTO
                "cod_empleado" => $row["cod_empleado"],
                "fecha_ingreso" => $row["fecha_ingreso"], // LISTO
                "otros_años" => $row["otros_años"], // LISTO
                "status" => $row["status"], 
                "observacion" => $row["observacion"], //listo
                "cod_cargo" => $row["cod_cargo"], // LISTO
                "cargo" => $row["cargo"], // CARGO
                "banco" => $row["banco"], //LISTO
                "cuenta_bancaria" => $row["cuenta_bancaria"], //LISTO
                "hijos" => $row["hijos"], // LISTO
                "instruccion_academica" => $row["instruccion_academica"], // listo
                "discapacidades" => $row["discapacidades"], // lISTO
                "tipo_cuenta" => $row["tipo_cuenta"] //LISTO
            );
            $datos[] = $empleado;
        }
    } else {
        echo json_encode(["mensaje" => "No se encontraron resultados."]);
        exit();
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    echo json_encode(["mensaje" => "No se ha proporcionado un ID o el método de solicitud no es GET."]);
    exit();
}

// Cerrar la conexión a la base de datos
$conexion->close();

// Pasar el array a la vista (puedes utilizar un archivo de vista o imprimir los datos aquí mismo)
header('Content-Type: application/json');
echo json_encode($datos);
?>
