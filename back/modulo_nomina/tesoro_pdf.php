<!DOCTYPE html>
<html>
<head>
    <title>NOMINA BONO ESPECIAL</title>
<meta charset="UTF-8">
<link rel="shortcut icon" type="image/png" href="img/favicon.png">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
    <div style="font-size: 10px;">
    <img src="../../img/logo.jpg" style="height: 110px; width: 250px; ">
    <center><h1>Relacion Deposito Banco</h1></center>
     <table style="width: 100%;" border="1">
                <thead>
                    <tr>
                        <th>Cedula</th>
                        <th>Nombre del Empleado</th>
                        <th>Cuenta Bancaria</th>
                        <th>Monto a Depositar</th>
                    </tr>
                </thead>
                <tbody>  
                
<?php
$correlativo = $_GET['correlativo'];
$identificador = $_GET['identificador'];

// Conexión a la base de datos
$conexion = mysqli_connect('localhost', 'root', '', 'sigob');

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Consulta para obtener los registros de informacion_pdf
$sql4 = "SELECT * FROM informacion_pdf WHERE correlativo='$correlativo' AND identificador='$identificador' AND banco='0163'";
$result4 = mysqli_query($conexion, $sql4);

if ($result4) {
    while ($mostrar4 = mysqli_fetch_array($result4)) {
        $cedula = $mostrar4['cedula'];
        $total_pagar = $mostrar4['total_pagar'];



        // Limpiar la cadena de cédulas
        $cedula = trim($cedula, '[]"');
        $cedulas = explode(',', $cedula);

        // Limpiar y convertir total_pagar en un array
        $total_pagar = str_replace(['[', ']', '"'], '', $total_pagar);
        $total_pagar_array = explode(',', $total_pagar);

        // Iterar sobre cada cédula para realizar la consulta
        foreach ($cedulas as $key => $cedula_individual) {
            $cedula_individual = trim($cedula_individual, ' "'); // Eliminar espacios y comillas adicionales

        

            // Obtener el total_pagar correspondiente
            $total_pagar_individual = isset($total_pagar_array[$key]) ? $total_pagar_array[$key] : 0;

            // Consulta para obtener los nombres y cuentas bancarias de cada empleado
            $sql_empleados = "
                SELECT e.cedula, e.nombres, e.cuenta_bancaria
                FROM empleados e
                WHERE e.cedula = '$cedula_individual'
            ";


            $result_empleados = mysqli_query($conexion, $sql_empleados);
            if ($result_empleados) {
                if (mysqli_num_rows($result_empleados) > 0) {
                    while ($empleado = mysqli_fetch_array($result_empleados)) {
                        ?>
                        <tr>
                            <td style="" align="right"><?php echo $empleado['cedula']; ?></td>
                            <td style=""><?php echo $empleado['nombres']; ?></td>
                            <td style=""><?php echo $empleado['cuenta_bancaria']; ?></td>
                            <td style="" align="right"><?php echo $total_pagar_individual; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='4'>No se encontraron empleados para la cédula $cedula_individual</td></tr>";
                }
            } else {
                echo "Error en la consulta de empleados: " . mysqli_error($conexion);
            }
        }
    }
} else {
    echo "Error en la consulta de informacion_pdf: " . mysqli_error($conexion);
}

// Cerrar la conexión
mysqli_close($conexion);
?>

 
                </tbody>
            </table>
        </div>
    </body>
</html>
