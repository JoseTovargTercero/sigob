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
function convertirNumeroLetra($numero) {
    $numero = number_format($numero, 2, '.', '');
    list($entero, $decimal) = explode('.', $numero);
    $numf = milmillon2($entero);
    
    if ($decimal == "00") {
        return strtoupper($numf) . " BOLÍVARES EXACTOS";
    } else {
        $decimal_letras = decena2($decimal);
        return strtoupper($numf) . " BOLÍVARES CON " . strtoupper($decimal_letras) . " CÉNTIMOS";
    }
}

function milmillon2($nummierod) {
    if ($nummierod >= 1000000000 && $nummierod < 2000000000) {
        $num_letrammd = "MIL " . cienmillon2($nummierod % 1000000000);
    }
    if ($nummierod >= 2000000000 && $nummierod < 10000000000) {
        $num_letrammd = unidad2(floor($nummierod / 1000000000)) . " MIL " . cienmillon2($nummierod % 1000000000);
    }
    if ($nummierod < 1000000000) {
        $num_letrammd = cienmillon2($nummierod);
    }
    return $num_letrammd;
}

function cienmillon2($numcmeros) {
    if ($numcmeros == 100000000) {
        $num_letracms = "CIEN MILLONES";
    }
    if ($numcmeros >= 100000000 && $numcmeros < 1000000000) {
        $num_letracms = centena2(floor($numcmeros / 1000000)) . " MILLONES " . millon2($numcmeros % 1000000);
    }
    if ($numcmeros < 100000000) {
        $num_letracms = decmillon2($numcmeros);
    }
    return $num_letracms;
}

function decmillon2($numerodm) {
    if ($numerodm == 10000000) {
        $num_letradmm = "DIEZ MILLONES";
    }
    if ($numerodm > 10000000 && $numerodm < 20000000) {
        $num_letradmm = decena2(floor($numerodm / 1000000)) . " MILLONES " . cienmiles2($numerodm % 1000000);
    }
    if ($numerodm >= 20000000 && $numerodm < 100000000) {
        $num_letradmm = decena2(floor($numerodm / 1000000)) . " MILLONES " . millon2($numerodm % 1000000);
    }
    if ($numerodm < 10000000) {
        $num_letradmm = millon2($numerodm);
    }
    return $num_letradmm;
}

function millon2($nummiero) {
    if ($nummiero >= 1000000 && $nummiero < 2000000) {
        $num_letramm = "UN MILLÓN " . cienmiles2($nummiero % 1000000);
    }
    if ($nummiero >= 2000000 && $nummiero < 10000000) {
        $num_letramm = unidad2(floor($nummiero / 1000000)) . " MILLONES " . cienmiles2($nummiero % 1000000);
    }
    if ($nummiero < 1000000) {
        $num_letramm = cienmiles2($nummiero);
    }
    return $num_letramm;
}

function cienmiles2($numcmero) {
    if ($numcmero == 100000) {
        $num_letracm = "CIEN MIL";
    }
    if ($numcmero >= 100000 && $numcmero < 1000000) {
        $num_letracm = centena2(floor($numcmero / 1000)) . " MIL " . centena2($numcmero % 1000);
    }
    if ($numcmero < 100000) {
        $num_letracm = decmiles2($numcmero);
    }
    return $num_letracm;
}

function decmiles2($numdmero) {
    if ($numdmero == 10000) {
        $numde = "DIEZ MIL";
    }
    if ($numdmero > 10000 && $numdmero < 20000) {
        $numde = decena2(floor($numdmero / 1000)) . " MIL " . centena2($numdmero % 1000);
    }
    if ($numdmero >= 20000 && $numdmero < 100000) {
        $numde = decena2(floor($numdmero / 1000)) . " MIL " . miles2($numdmero % 1000);
    }
    if ($numdmero < 10000) {
        $numde = miles2($numdmero);
    }
    return $numde;
}

function miles2($nummero) {
    if ($nummero >= 1000 && $nummero < 2000) {
        $numm = "MIL " . centena2($nummero % 1000);
    }
    if ($nummero >= 2000 && $nummero < 10000) {
        $numm = unidad2(floor($nummero / 1000)) . " MIL " . centena2($nummero % 1000);
    }
    if ($nummero < 1000) {
        $numm = centena2($nummero);
    }
    return $numm;
}

function centena2($numc) {
    if ($numc >= 100) {
        if ($numc >= 900 && $numc <= 999) {
            $numce = "NOVECIENTOS ";
            if ($numc > 900) {
                $numce = $numce . decena2($numc - 900);
            }
        } else if ($numc >= 800 && $numc <= 899) {
            $numce = "OCHOCIENTOS ";
            if ($numc > 800) {
                $numce = $numce . decena2($numc - 800);
            }
        } else if ($numc >= 700 && $numc <= 799) {
            $numce = "SETECIENTOS ";
            if ($numc > 700) {
                $numce = $numce . decena2($numc - 700);
            }
        } else if ($numc >= 600 && $numc <= 699) {
            $numce = "SEISCIENTOS ";
            if ($numc > 600) {
                $numce = $numce . decena2($numc - 600);
            }
        } else if ($numc >= 500 && $numc <= 599) {
            $numce = "QUINIENTOS ";
            if ($numc > 500) {
                $numce = $numce . decena2($numc - 500);
            }
        } else if ($numc >= 400 && $numc <= 499) {
            $numce = "CUATROCIENTOS ";
            if ($numc > 400) {
                $numce = $numce . decena2($numc - 400);
            }
        } else if ($numc >= 300 && $numc <= 399) {
            $numce = "TRESCIENTOS ";
            if ($numc > 300) {
                $numce = $numce . decena2($numc - 300);
            }
        } else if ($numc >= 200 && $numc <= 299) {
            $numce = "DOSCIENTOS ";
            if ($numc > 200) {
                $numce = $numce . decena2($numc - 200);
            }
        } else if ($numc >= 100 && $numc <= 199) {
            if ($numc == 100) {
                $numce = "CIEN ";
            } else {
                $numce = "CIENTO " . decena2($numc - 100);
            }
        }
    } else {
        $numce = decena2($numc);
    }
    return $numce;
}

function decena2($numdero) {
    if ($numdero >= 90 && $numdero <= 99) {
        $numd = "NOVENTA ";
        if ($numdero > 90) {
            $numd = $numd . "Y " . unidad2($numdero - 90);
        }
    } else if ($numdero >= 80 && $numdero <= 89) {
        $numd = "OCHENTA ";
        if ($numdero > 80) {
            $numd = $numd . "Y " . unidad2($numdero - 80);
        }
    } else if ($numdero >= 70 && $numdero <= 79) {
        $numd = "SETENTA ";
        if ($numdero > 70) {
            $numd = $numd . "Y " . unidad2($numdero - 70);
        }
    } else if ($numdero >= 60 && $numdero <= 69) {
        $numd = "SESENTA ";
        if ($numdero > 60) {
            $numd = $numd . "Y " . unidad2($numdero - 60);
        }
    } else if ($numdero >= 50 && $numdero <= 59) {
        $numd = "CINCUENTA ";
        if ($numdero > 50) {
            $numd = $numd . "Y " . unidad2($numdero - 50);
        }
    } else if ($numdero >= 40 && $numdero <= 49) {
        $numd = "CUARENTA ";
        if ($numdero > 40) {
            $numd = $numd . "Y " . unidad2($numdero - 40);
        }
    } else if ($numdero >= 30 && $numdero <= 39) {
        $numd = "TREINTA ";
        if ($numdero > 30) {
            $numd = $numd . "Y " . unidad2($numdero - 30);
        }
    } else if ($numdero >= 20 && $numdero <= 29) {
        if ($numdero == 20) {
            $numd = "VEINTE ";
        } else {
            $numd = "VEINTI" . unidad2($numdero - 20);
        }
    } else if ($numdero >= 10 && $numdero <= 19) {
        switch ($numdero) {
            case 10:
                $numd = "DIEZ ";
                break;
            case 11:
                $numd = "ONCE ";
                break;
            case 12:
                $numd = "DOCE ";
                break;
            case 13:
                $numd = "TRECE ";
                break;
            case 14:
                $numd = "CATORCE ";
                break;
            case 15:
                $numd = "QUINCE ";
                break;
            case 16:
                $numd = "DIECISEIS ";
                break;
            case 17:
                $numd = "DIECISIETE ";
                break;
            case 18:
                $numd = "DIECIOCHO ";
                break;
            case 19:
                $numd = "DIECINUEVE ";
                break;
        }
    } else {
        $numd = unidad2($numdero);
    }
    return $numd;
}

function unidad2($numuero) {
    switch ($numuero) {
        case 9:
            $numu = "NUEVE";
            break;
        case 8:
            $numu = "OCHO";
            break;
        case 7:
            $numu = "SIETE";
            break;
        case 6:
            $numu = "SEIS";
            break;
        case 5:
            $numu = "CINCO";
            break;
        case 4:
            $numu = "CUATRO";
            break;
        case 3:
            $numu = "TRES";
            break;
        case 2:
            $numu = "DOS";
            break;
        case 1:
            $numu = "UNO";
            break;
        case 0:
            $numu = "";
            break;
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

        // Reiniciar las diferencias para cada fecha
        $diferencias = [
            'asignaciones' => [],
            'deducciones' => [],
            'aportes' => []
        ];

        // Calcular diferencias de asignaciones
        foreach ($nacional['asignaciones'] as $key => $value) {
            $regional_value = $regional['asignaciones'][$key] ?? null;
            if ($regional_value === null) {
                $diferencias['asignaciones'][$key] = $value;
            } else {
                $diferencias['asignaciones'][$key] = abs($value - $regional_value);
            }
        }
        foreach ($regional['asignaciones'] as $key => $value) {
            if (!isset($nacional['asignaciones'][$key])) {
                $diferencias['asignaciones'][$key] = $value;
            }
        }

        // Calcular diferencias de deducciones
        foreach ($nacional['deducciones'] as $key => $value) {
            $regional_value = $regional['deducciones'][$key] ?? null;
            if ($regional_value === null) {
                $diferencias['deducciones'][$key] = $value;
            } else {
                $diferencias['deducciones'][$key] = abs($value - $regional_value);
            }
        }
        foreach ($regional['deducciones'] as $key => $value) {
            if (!isset($nacional['deducciones'][$key])) {
                $diferencias['deducciones'][$key] = $value;
            }
        }

        // Calcular diferencias de aportes
        foreach ($nacional['aportes'] as $key => $value) {
            $regional_value = $regional['aportes'][$key] ?? null;
            if ($regional_value === null) {
                $diferencias['aportes'][$key] = $value;
            } else {
                $diferencias['aportes'][$key] = abs($value - $regional_value);
            }
        }
        foreach ($regional['aportes'] as $key => $value) {
            if (!isset($nacional['aportes'][$key])) {
                $diferencias['aportes'][$key] = $value;
            }
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
                <strong>Total a Pagar Nacional:</strong> <?php echo $nacional['total_pagar'] / 2; ?><br>
                <strong>Total a Pagar Regional:</strong> <?php echo $regional['total_pagar'] / 2; ?>
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
