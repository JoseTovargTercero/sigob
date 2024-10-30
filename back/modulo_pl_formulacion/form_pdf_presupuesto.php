<?php
require_once '../sistema_global/conexion.php';

// Obtener el id_ejercicio
$id_ejercicio = $_GET['id_ejercicio'] ?? null;
if (!$id_ejercicio) {
    die("ID de ejercicio no proporcionado");
}

try {
    $conexion->begin_transaction();

    // Consultar a la tabla ejercicio_fiscal para obtener ano y situado
    $sqlEjercicio = "SELECT ano, situado FROM ejercicio_fiscal WHERE id = ?";
    $stmtEjercicio = $conexion->prepare($sqlEjercicio);
    $stmtEjercicio->bind_param("i", $id_ejercicio);
    $stmtEjercicio->execute();
    $resultadoEjercicio = $stmtEjercicio->get_result()->fetch_assoc();

    // Validar si se encontró el registro
    if (!$resultadoEjercicio) {
        throw new Exception("No se encontró el ejercicio fiscal con el ID proporcionado.");
    }

    $ano = $resultadoEjercicio['ano'];
    $situado = $resultadoEjercicio['situado'];

    // Consultar a la tabla plan_inversion para obtener monto_total
    $sqlInversion = "SELECT monto_total FROM plan_inversion WHERE id_ejercicio = ?";
    $stmtInversion = $conexion->prepare($sqlInversion);
    $stmtInversion->bind_param("i", $id_ejercicio);
    $stmtInversion->execute();
    $resultadoInversion = $stmtInversion->get_result()->fetch_assoc();
    $monto_total = $resultadoInversion ? $resultadoInversion['monto_total'] : "No disponible";
    $total = $situado + $monto_total;

    // Consultar todos los registros de la tabla titulo_1 con articulo y descripcion
    $sqlTitulo = "SELECT articulo, descripcion FROM titulo_1";
    $resultadoTitulo = $conexion->query($sqlTitulo);
    $tituloData = [];
    if ($resultadoTitulo && $resultadoTitulo->num_rows > 0) {
        while ($row = $resultadoTitulo->fetch_assoc()) {
            $tituloData[] = $row;
        }
    }

    $conexion->commit();

} catch (Exception $e) {
    $conexion->rollback();
    die("Error en la consulta: " . $e->getMessage());
}

function convertirNumeroLetra2($numero) {
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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Créditos Presupuestarios del Sector por Programa</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            margin: 10px;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            text-align: center;
        }

        td {
            padding: 5px;
        }

        th {
            font-weight: bold;
            text-align: center;
        }

        .py-0 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .pb-0 {
            padding-bottom: 0 !important;
        }

        .pt-0 {
            padding-top: 0 !important;
        }

        .b-1 {
            border: 1px solid;
        }

        .bc-lightgray {
            border-color: lightgray !important;
        }

        .bc-gray {
            border-color: gray;
        }

        .pt-1 {
            padding-top: 1rem !important;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        h2 {
            font-size: 16px;
            margin: 0;
        }

        .header-table {
            margin-bottom: 20px;
        }

        .small-text {
            font-size: 8px;
        }

        .w-50 {
            width: 50%;
        }

        .table-title {
            font-size: 10px;
            margin-top: 10px;
        }

        .logo {
            width: 120px;
        }

        .t-border-0>tr>td {
            border: none !important;
        }

        .fz-6 {
            font-size: 5px !important;
        }

        .fz-8 {
            font-size: 8px !important;
        }

        .fz-9 {
            font-size: 9px !important;
        }

        .fz-10 {
            font-size: 10px !important;
        }

        .bl {
            border-left: 1px solid;
        }

        .br {
            border-right: 1px solid;
        }

        .bb {
            border-bottom: 1px solid;
        }

        .bt {
            border-top: 1px solid;
        }

        .dw-nw {
            white-space: nowrap !important
        }

        @media print {
            .page-break {
                page-break-after: always;
            }
        }

        .t-content {
            page-break-inside: avoid;
        }

        .p-2 {
            padding: 10px;
        }
    </style>
</head>

<body>
    <p>REPUBLICA BOLIVARIANA DE VENEZUELA</p>
    <div class='text-left' style='width: 20px'>
        <img src='../../img/logo_2_amazona.jpg' class='logo'>
    </div>
    <p>EL CONSEJO LEGISLATIVO DEL ESTADO AMAZONAS</p>
    <p>Decreta lo siguiente</p>
    <p>LEY DE PRESUPUESTO DE INGRESOS Y GASTOS</p>
    <p>DEL ESTADO AMAZONAS PARA EL EJERCICIO FISCAL</p>
    <p>TITULO I</p>
    <p>DISPOSICIONES GENERALES</p>
    <p><strong>ARTICULO 1:</strong> Se aprueba la estimación de los Ingresos y Gastos Públicos para el Ejercicio Fiscal <?= $ano ?> en la cantidad de <?php echo convertirNumeroLetra2($total); ?> (Bs. <?php echo number_format($total, 2) ?>), la cual está constituida por los siguientes rubros de ingresos: </p>
    
    <table>
        <tr>
            <th class="bl bt bb">SITUADO CONSTITUCIONAL</th>
            <th class="bl bt bb br"><?= number_format($situado, 2) ?></th>
        </tr>
        <tr>
            <th class="bl bt bb">FONDO DE COMPENSACIÓN INTERTERRITORIAL</th>
            <th class="bl bt bb br"><?= number_format($monto_total, 2) ?></th>
        </tr>
        <tr>
            <th class="bl bt bb">TOTAL Bs.</th>
            <th class="bl bt bb br"><?= number_format($total, 2) ?></th>
        </tr>
    </table>

    <p>Esta distribución se hace de acuerdo a lo que se prevé en el Título II de esta Ley, denominado “Presupuesto de Ingresos” por un monto de <?php echo convertirNumeroLetra2($total); ?> (Bs. <?php echo number_format($total, 2) ?>)</p>

    <!-- Sección para mostrar los artículos y descripciones de la tabla titulo_1 -->
    <?php foreach ($tituloData as $titulo): ?>
        <p><strong><?= htmlspecialchars($titulo['articulo']) ?>:</strong> <?= htmlspecialchars($titulo['descripcion']) ?></p>
    <?php endforeach; ?>
    <p>TITULO II</p>
    <p>PRESUPUESTO DE INGRESOS</p>
    <p><strong>ARTICULO 25:</strong> Apruébese la estimación de los Ingresos Públicos para el Ejercicio Fiscal  <?= $ano ?> la cantidad de <?php echo convertirNumeroLetra2($total); ?> (Bs. <?php echo number_format($total, 2) ?>), según la distribución siguiente:: </p>
   <table>
    <tr>
        <th class="bl bt bb" colspan="4">CÓDIGO DE RECURSOS</th>
        <th class="bl bt bb br" rowspan="2">DENOMINACIÓN</th>
        <th class="bl bt bb br" rowspan="2">MONTO Bs.</th>
    </tr>
    <tr>
        <th class="bl bt bb">RAMO</th>
        <th class="bl bt bb br">GEN</th>
        <th class="bl bt bb br">ESP</th>
        <th class="bl bt bb br">SUB ESP</th>
    </tr>
    <tr>
        <td class="bl bt bb">3.00</td>
        <td class="bl bt bb br">00</td>
        <td class="bl bt bb br">00</td>
        <td class="bl bt bb br">00</td>
        <td class="bl bt bb br" class="bold">RECURSOS</td>
        <td class="bl bt bb br"><?= number_format($total, 2) ?></td>
    </tr>
    <tr>
        <td class="bl bt bb">3.05</td>
        <td class="bl bt bb br">00</td>
        <td class="bl bt bb br">00</td>
        <td class="bl bt bb br">00</td>
        <td class="bl bt bb br">TRANSFERENCIAS Y DONACIONES</td>
        <td class="bl bt bb br"><?= number_format($total, 2) ?></td>
    </tr>
    <tr>
        <td class="bl bt bb">3.05</td>
        <td class="bl bt bb br">03</td>
        <td class="bl bt bb br">01</td>
        <td class="bl bt bb br">01</td>
        <td class="bl bt bb br">SITUADO ESTATAL</td>
        <td class="bl bt bb br"><?= number_format($situado, 2) ?></td>
    </tr>
    <tr>
        <td class="bl bt bb">3.05</td>
        <td class="bl bt bb br">08</td>
        <td class="bl bt bb br">01</td>
        <td class="bl bt bb br">01</td>
        <td class="bl bt bb br">FONDO DE COMPENSACIÓN INTERTERRITORIAL</td>
        <td class="bl bt bb br"><?= number_format($monto_total, 2) ?></td>
    </tr>
    <tr>
        <td colspan="5" class="bl bt bb">TOTAL</td>
        <td class="bl bt bb br"><?= number_format($total, 2) ?></td>
    </tr>
</table>
<p>TITULO III</p>
    <p>PRESUPUESTO DE GASTOS</p>
    <p><strong>ARTÍCULO 26:</strong> Se acuerda la estimación de los Ingresos Públicos para el Ejercicio Fiscal  <?= $ano ?> en la cantidad de <?php echo convertirNumeroLetra2($total); ?> (Bs. <?php echo number_format($total, 2) ?>), según la distribución siguiente:</p>
   <table>
    <tr>
        <th class="bl bt bb" colspan="2">PROGRAMA</th>
        <th class="bl bt bb br" rowspan="1">MONTO Bs.</th>
    </tr>
    <tr>
        <th class="bl bt bb">RAMO</th>
        <th class="bl bt bb br">GEN</th>
        <th class="bl bt bb br">ESP</th>
    </tr>
</table>
</body>
</html>
</body>

</html>