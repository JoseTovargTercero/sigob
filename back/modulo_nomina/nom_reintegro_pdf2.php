<?php
require_once '../sistema_global/conexion.php';
 ?>
<!DOCTYPE html>
<html>
<head>
    <title>REINTEGRO</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div style="font-size: 10px;">
        <img src="../../img/logo.jpg" style="height: 110px; width: 250px;">
<?php
 function convertirNumeroLetra($numero){
    $numf = milmillon($numero);
    return $numf."";
    }
    function milmillon($nummierod){
        if ($nummierod >= 1000000000 && $nummierod <2000000000){
            $num_letrammd = "mil ".(cienmillon($nummierod%1000000000));
        }
        if ($nummierod >= 2000000000 && $nummierod <10000000000){
            $num_letrammd = unidad(Floor($nummierod/1000000000))." mil ".(cienmillon($nummierod%1000000000));
        }
        if ($nummierod < 1000000000)
            $num_letrammd = cienmillon($nummierod);
        
        return $num_letrammd;
    }
    function cienmillon($numcmeros){
        if ($numcmeros == 100000000)
            $num_letracms = "cien millones";
        if ($numcmeros >= 100000000 && $numcmeros <1000000000){
            $num_letracms = centena(Floor($numcmeros/1000000))." millones ".(millon($numcmeros%1000000));       
        }
        if ($numcmeros < 100000000)
            $num_letracms = decmillon($numcmeros);
        return $num_letracms;
    }
    function decmillon($numerodm){
        if ($numerodm == 10000000)
            $num_letradmm = "diez millones";
        if ($numerodm > 10000000 && $numerodm <20000000){
            $num_letradmm = decena(Floor($numerodm/1000000))."millones ".(cienmiles($numerodm%1000000));        
        }
        if ($numerodm >= 20000000 && $numerodm <100000000){
            $num_letradmm = decena(Floor($numerodm/1000000))." millones ".(millon($numerodm%1000000));      
        }
        if ($numerodm < 10000000)
            $num_letradmm = millon($numerodm);
        
        return $num_letradmm;
    }
    function millon($nummiero){
        if ($nummiero >= 1000000 && $nummiero <2000000){
            $num_letramm = "un millon ".(cienmiles($nummiero%1000000));
        }
        if ($nummiero >= 2000000 && $nummiero <10000000){
            $num_letramm = unidad(Floor($nummiero/1000000))." millones ".(cienmiles($nummiero%1000000));
        }
        if ($nummiero < 1000000)
            $num_letramm = cienmiles($nummiero);
        
        return $num_letramm;
    }
    function cienmiles($numcmero){
        if ($numcmero == 100000)
            $num_letracm = "cien mil";
        if ($numcmero >= 100000 && $numcmero <1000000){
            $num_letracm = centena(Floor($numcmero/1000))." mil ".(centena($numcmero%1000));        
        }
        if ($numcmero < 100000)
            $num_letracm = decmiles($numcmero);
        return $num_letracm;
    }
    function decmiles($numdmero){
        if ($numdmero == 10000)
            $numde = "diez mil";
        if ($numdmero > 10000 && $numdmero <20000){
            $numde = decena(Floor($numdmero/1000))."mil ".(centena($numdmero%1000));        
        }
        if ($numdmero >= 20000 && $numdmero <100000){
            $numde = decena(Floor($numdmero/1000))." mil ".(miles($numdmero%1000));     
        }       
        if ($numdmero < 10000)
            $numde = miles($numdmero);
        
        return $numde;
    }
    function miles($nummero){
        if ($nummero >= 1000 && $nummero < 2000){
            $numm = "mil ".(centena($nummero%1000));
        }
        if ($nummero >= 2000 && $nummero <10000){
            $numm = unidad(Floor($nummero/1000))." mil ".(centena($nummero%1000));
        }
        if ($nummero < 1000)
            $numm = centena($nummero);
        
        return $numm;
    }
    function centena($numc){
        if ($numc >= 100)
        {
            if ($numc >= 900 && $numc <= 999)
            {
                $numce = "novecientes ";
                if ($numc > 900)
                    $numce = $numce.(decena($numc - 900));
            }
            else if ($numc >= 800 && $numc <= 899)
            {
                $numce = "ochocientos ";
                if ($numc > 800)
                    $numce = $numce.(decena($numc - 800));
            }
            else if ($numc >= 700 && $numc <= 799)
            {
                $numce = "setecientos ";
                if ($numc > 700)
                    $numce = $numce.(decena($numc - 700));
            }
            else if ($numc >= 600 && $numc <= 699)
            {
                $numce = "seiscientos ";
                if ($numc > 600)
                    $numce = $numce.(decena($numc - 600));
            }
            else if ($numc >= 500 && $numc <= 599)
            {
                $numce = "quinientos ";
                if ($numc > 500)
                    $numce = $numce.(decena($numc - 500));
            }
            else if ($numc >= 400 && $numc <= 499)
            {
                $numce = "cuatrocientos ";
                if ($numc > 400)
                    $numce = $numce.(decena($numc - 400));
            }
            else if ($numc >= 300 && $numc <= 399)
            {
                $numce = "trescientos ";
                if ($numc > 300)
                    $numce = $numce.(decena($numc - 300));
            }
            else if ($numc >= 200 && $numc <= 299)
            {
                $numce = "doscientos ";
                if ($numc > 200)
                    $numce = $numce.(decena($numc - 200));
            }
            else if ($numc >= 100 && $numc <= 199)
            {
                if ($numc == 100)
                    $numce = "cien ";
                else
                    $numce = "ciento ".(decena($numc - 100));
            }
        }
        else
            $numce = decena($numc);
        
        return $numce;  
}
function decena($numdero){
    
        if ($numdero >= 90 && $numdero <= 99)
        {
            $numd = "noventa ";
            if ($numdero > 90)
                $numd = $numd."Y ".(unidad($numdero - 90));
        }
        else if ($numdero >= 80 && $numdero <= 89)
        {
            $numd = "ochenta ";
            if ($numdero > 80)
                $numd = $numd."Y ".(unidad($numdero - 80));
        }
        else if ($numdero >= 70 && $numdero <= 79)
        {
            $numd = "setenta ";
            if ($numdero > 70)
                $numd = $numd."Y ".(unidad($numdero - 70));
        }
        else if ($numdero >= 60 && $numdero <= 69)
        {
            $numd = "sesenta ";
            if ($numdero > 60)
                $numd = $numd."Y ".(unidad($numdero - 60));
        }
        else if ($numdero >= 50 && $numdero <= 59)
        {
            $numd = "cincuenta ";
            if ($numdero > 50)
                $numd = $numd."Y ".(unidad($numdero - 50));
        }
        else if ($numdero >= 40 && $numdero <= 49)
        {
            $numd = "cuarenta ";
            if ($numdero > 40)
                $numd = $numd."Y ".(unidad($numdero - 40));
        }
        else if ($numdero >= 30 && $numdero <= 39)
        {
            $numd = "treinta ";
            if ($numdero > 30)
                $numd = $numd."Y ".(unidad($numdero - 30));
        }
        else if ($numdero >= 20 && $numdero <= 29)
        {
            if ($numdero == 20)
                $numd = "veinte ";
            else
                $numd = "veinti".(unidad($numdero - 20));
        }
        else if ($numdero >= 10 && $numdero <= 19)
        {
            switch ($numdero){
            case 10:
            {
                $numd = "diez ";
                break;
            }
            case 11:
            {               
                $numd = "once ";
                break;
            }
            case 12:
            {
                $numd = "doce ";
                break;
            }
            case 13:
            {
                $numd = "trece ";
                break;
            }
            case 14:
            {
                $numd = "catorce ";
                break;
            }
            case 15:
            {
                $numd = "quince ";
                break;
            }
            case 16:
            {
                $numd = "dieciseis ";
                break;
            }
            case 17:
            {
                $numd = "diecisiete ";
                break;
            }
            case 18:
            {
                $numd = "dieciocho ";
                break;
            }
            case 19:
            {
                $numd = "diecinueve ";
                break;
            }
            }   
        }
        else
            $numd = unidad($numdero);
    return $numd;
}
function unidad($numuero){
    switch ($numuero)
    {
        case 9:
        {
            $numu = "nueve";
            break;
        }
        case 8:
        {
            $numu = "ocho";
            break;
        }
        case 7:
        {
            $numu = "siete";
            break;
        }       
        case 6:
        {
            $numu = "seis";
            break;
        }       
        case 5:
        {
            $numu = "cinco";
            break;
        }       
        case 4:
        {
            $numu = "cuatro";
            break;
        }       
        case 3:
        {
            $numu = "tres";
            break;
        }       
        case 2:
        {
            $numu = "dos";
            break;
        }       
        case 1:
        {
            $numu = "un";
            break;
        }       
        case 0:
        {
            $numu = "";
            break;
        }       
    }
    return $numu;   
}









$id_empleado = $_GET['id_empleado'];

// Variable para guardar nombre_nomina
$nombre_nomina = '';

// Consulta a la base de datos para obtener datos del empleado y sumatoria filtrada
$sql = "SELECT e.nombres, e.cedula, e.cod_cargo, cg.cargo AS nombre_cargo,
               h.nombre_nomina,
               GROUP_CONCAT(h.fecha SEPARATOR ', ') AS fechas, SUM(h.total_pagar) AS total_pagar_suma
        FROM empleados e
        LEFT JOIN historico_reintegros h ON e.id = h.id_empleado
        LEFT JOIN cargos_grados cg ON e.cod_cargo = cg.cod_cargo
        WHERE e.id = '$id_empleado'
          AND (h.nombre_nomina LIKE '%Nacional%' OR h.nombre_nomina LIKE '%nacional%')
        GROUP BY e.id, e.nombres, e.cedula, e.cod_cargo, cg.cargo, h.nombre_nomina";

$result = mysqli_query($conexion, $sql);

// Verificación y llenado de los datos obtenidos
if ($result && mysqli_num_rows($result) > 0) {
    $fecha_reintegros = array(); // Inicializamos el array de fechas
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Obtener las fechas y procesarlas
        $fechas_csv = $row['fechas'];
        $fechas_array = explode(', ', $fechas_csv); // Convertir la cadena CSV de fechas en un array PHP
        
        foreach ($fechas_array as $fecha) {
            // Procesar cada fecha para eliminar espacios en blanco u otros caracteres no deseados
            $fecha_procesada = trim($fecha);
            $fecha_reintegros[] = $fecha_procesada; // Agregar al array de fechas
        }
        
        // Otros datos del empleado y sumatoria
        $nombres = $row['nombres'];
        $cedula = $row['cedula'];
        $nombre_cargo = $row['nombre_cargo']; // Nombre del cargo desde cargos_grados
        $total_pagar_suma = $row['total_pagar_suma']; // Suma de los valores total_pagar
        $nombre_nomina = $row['nombre_nomina']; // Nombre de la nomina
        
    }
    
    // Aquí puedes continuar con el uso de los datos obtenidos
?>
    <p style="font-size:15px; text-align:justify;">Anexo envío recibo a favor de: <strong><?php echo $nombres ?> , C.I.N° <?php echo $cedula ?></strong> por un monto de: <strong>(Bs. <?php echo $total_pagar_suma ?> ). </strong>Por concepto de cancelación de: <strong>SUELDO Y PASIVO DE LOS MESES DE <?php echo implode(', ', $fecha_reintegros) ?>.</strong> Que le corresponden como: <strong><?php echo $nombre_cargo ?></strong> del personal adscrito al <?php echo $nombre_nomina ?>. Para su proceso de pago correspondiente.</p>
    <p style="font-size:15px; text-align:justify;">He recibido de la Tesorería General de la Gobernación del Estado Amazonas, la
cantidad de: <strong><?php echo convertirNumeroLetra($total_pagar_suma) ?>.  (Bs <?php echo $total_pagar_suma ?> ) </strong> por concepto de cancelación de: <strong>SUELDO Y PASIVO DE LOS MESES DE <?php echo implode(', ', $fecha_reintegros) ?>. </strong> Que le corresponden como: <strong><?php echo $nombre_cargo ?> </strong>, del
personal adscrito al <?php echo $nombre_nomina ?>.
Para su proceso de pago correspondiente.
</p>
<strong><p style="font-size:15px; text-align:justify;">N.º BENEFICIARIO :1</p></strong>
<?php
} else {
    echo "No se encontraron resultados para el empleado con ID $id_empleado.";
}
?>




        <?php

        function obtener_mes_en_letras($fecha) {
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $fecha_parts = explode('-', $fecha);
            if (count($fecha_parts) == 2) {
                $mes_numero = (int)$fecha_parts[0];
                $año = $fecha_parts[1];
                return $meses[$mes_numero] . ' ' . $año;
            }
            return "Fecha inválida";
        }
        ?>
        <h1 align="center">Resumen de Reintegro <?php echo $nombres ?></h1>
        <?php


        // Consulta a la base de datos
        $sql4 = "SELECT * FROM historico_reintegros WHERE id_empleado='$id_empleado'";
        $result4 = mysqli_query($conexion, $sql4);

        // Arrays para almacenar los datos por fecha
        $datos_por_fecha = [];

        // Verificación y llenado de los datos obtenidos
        if ($result4 && mysqli_num_rows($result4) > 0) {
            while ($mostrar4 = mysqli_fetch_assoc($result4)) {
                // Decodificación de los arrays JSON
                $asignaciones = json_decode($mostrar4['asignaciones'], true);
                $deducciones = json_decode($mostrar4['deducciones'], true);
                $aportes = json_decode($mostrar4['aportes'], true);
                $total_pagar = $mostrar4['total_pagar'];
                $nombre_nomina = strtolower($mostrar4['nombre_nomina']);
                $fecha = $mostrar4['fecha'];

                // Almacenar los datos en el array según la fecha y el tipo de nómina
                if (!isset($datos_por_fecha[$fecha])) {
                    $datos_por_fecha[$fecha] = [
                        'nacional' => null,
                        'regional' => null
                    ];
                }

                if (strpos($nombre_nomina, 'nacional') !== false) {
                    $datos_por_fecha[$fecha]['nacional'] = [
                        'asignaciones' => $asignaciones,
                        'deducciones' => $deducciones,
                        'aportes' => $aportes,
                        'total_pagar' => $total_pagar
                    ];
                } elseif (strpos($nombre_nomina, 'regional') !== false) {
                    $datos_por_fecha[$fecha]['regional'] = [
                        'asignaciones' => $asignaciones,
                        'deducciones' => $deducciones,
                        'aportes' => $aportes,
                        'total_pagar' => $total_pagar
                    ];
                }
            }
        } else {
            echo "<p>No se encontraron Reintegros</p>";
        }

        // Mostrar los datos en tablas
        foreach ($datos_por_fecha as $fecha => $datos) {
            if ($datos['nacional'] && $datos['regional']) {
                $nacional = $datos['nacional'];
                $regional = $datos['regional'];

                // Calcular diferencias
                $diferencias = [
                    'asignaciones' => [],
                    'deducciones' => [],
                    'aportes' => []
                ];

                foreach ($nacional['asignaciones'] as $key => $value) {
                    $regional_value = $regional['asignaciones'][$key] ?? 0;
                    $diferencias['asignaciones'][$key] = abs($value - $regional_value);
                }

                foreach ($nacional['deducciones'] as $key => $value) {
                    $regional_value = $regional['deducciones'][$key] ?? 0;
                    $diferencias['deducciones'][$key] = abs($value - $regional_value);
                }

                foreach ($nacional['aportes'] as $key => $value) {
                    $regional_value = $regional['aportes'][$key] ?? 0;
                    $diferencias['aportes'][$key] = abs($value - $regional_value);
                }
        ?>
        <h1 align="center"><?php echo obtener_mes_en_letras($fecha); ?></h1>
        <table>
            <thead>
                <tr>
                    <th colspan="1">Nacional</th>
                    <th colspan="1">Regional</th>
                    <th rowspan="2">Diferencia</th>
                    <th colspan="2" style="text-align: center;">Total a Pagar</th>
                    <th rowspan="2">Fecha</th>
                </tr>
                <tr>
                    <th>Conceptos</th>
                    <th>Conceptos</th>
                    <th colspan="2" style="text-align: center;">Quincena / Mensual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Columna Nacional -->
                    <td>
                        <strong>Asignaciones:</strong><br>
                        <?php
                        foreach ($nacional['asignaciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Deducciones:</strong><br>
                        <?php
                        foreach ($nacional['deducciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Aportes:</strong><br>
                        <?php
                        foreach ($nacional['aportes'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                    </td>

                    <!-- Columna Regional -->
                    <td>
                        <strong>Asignaciones:</strong><br>
                        <?php
                        foreach ($regional['asignaciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Deducciones:</strong><br>
                        <?php
                        foreach ($regional['deducciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Aportes:</strong><br>
                        <?php
                        foreach ($regional['aportes'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                    </td>

                    <!-- Columna Diferencia -->
                    <td>
                        <strong>Asignaciones:</strong><br>
                        <?php
                        foreach ($diferencias['asignaciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Deducciones:</strong><br>
                        <?php
                        foreach ($diferencias['deducciones'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                        <br><strong>Aportes:</strong><br>
                        <?php
                        foreach ($diferencias['aportes'] as $key => $value) {
                            echo "$key: $value<br>";
                        }
                        ?>
                    </td>

                    <!-- Columna Total a Pagar -->
                    <td>
                        <strong>Total a Pagar Nacional:</strong> <?php echo $nacional['total_pagar']/2; ?><br>
                        <strong>Total a Pagar Regional:</strong> <?php echo $regional['total_pagar']/2; ?>
                    </td>
                    <td>
                        <strong>Total a Pagar Nacional:</strong> <?php echo $nacional['total_pagar']; ?><br>
                        <strong>Total a Pagar Regional:</strong> <?php echo $regional['total_pagar']; ?>
                    </td>

                    <!-- Columna Fecha -->
                    <td><?php echo $fecha; ?></td>
                </tr>
            </tbody>
        </table>
        <?php
            }
        }

        // Cierre de la conexión
        mysqli_close($conexion);
        ?>
    </div>
</body>
</html>
