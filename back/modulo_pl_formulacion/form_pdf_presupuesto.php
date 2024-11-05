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
    $datosEjercicio = $stmtEjercicio->get_result()->fetch_assoc();

    // Validar si se encontró el registro
    if (!$datosEjercicio) {
        throw new Exception("No se encontró el ejercicio fiscal con el ID proporcionado.");
    }

    $ano = $datosEjercicio['ano'];
    $situado = $datosEjercicio['situado'];


    // Consulta a la tabla plan_inversion para obtener monto_total
    $sqlInversion = "SELECT monto_total FROM plan_inversion WHERE id_ejercicio = ?";
    $stmtInversion = $conexion->prepare($sqlInversion);
    $stmtInversion->bind_param("i", $id_ejercicio);
    $stmtInversion->execute();
    $datosInversion = $stmtInversion->get_result()->fetch_assoc();
    $monto_total = $datosInversion ? $datosInversion['monto_total'] : 0;
    $total = $situado + $monto_total;

    // Consulta de sectores y programas agrupados por sector
    $sqlSectores = "
    SELECT s.id AS sector_id, s.sector, s.denominacion AS sector_denominacion, 
           p.id AS programa_id, p.programa, p.denominacion AS programa_denominacion
    FROM pl_sectores AS s
    LEFT JOIN pl_programas AS p ON s.id = p.sector
    ORDER BY s.sector, p.programa
";
    $resultadoSectores = $conexion->query($sqlSectores);
    $sectoresData = [];
    if ($resultadoSectores && $resultadoSectores->num_rows > 0) {
        while ($row = $resultadoSectores->fetch_assoc()) {
            $sector_id = $row['sector_id'];
            $programa_id = $row['programa_id'];

            if (!isset($sectoresData[$sector_id])) {
                $sectoresData[$sector_id] = [
                    'sector_id' => $sector_id,
                    'sector' => $row['sector'],
                    'sector_denominacion' => $row['sector_denominacion'],
                    'programas' => []
                ];
            }

            // Solo agregar el programa si no está ya en el array
            if ($programa_id !== null && !isset($sectoresData[$sector_id]['programas'][$programa_id])) {
                $sectoresData[$sector_id]['programas'][$programa_id] = [
                    'programa_id' => $programa_id,
                    'programa' => $row['programa'],
                    'programa_denominacion' => $row['programa_denominacion'],
                    'monto' => 0  // Inicializamos el monto en 0
                ];
            }
        }
    }

    // Consultar montos en la tabla proyecto_inversion_partidas en una sola consulta
    $sqlMontos = "
    SELECT sector_id, programa_id, SUM(monto) AS total_monto 
    FROM proyecto_inversion_partidas 
    GROUP BY sector_id, programa_id
";
    $resultadoMontos = $conexion->query($sqlMontos);
    $montosData = [];
    if ($resultadoMontos && $resultadoMontos->num_rows > 0) {
        while ($row = $resultadoMontos->fetch_assoc()) {
            $montosData[$row['sector_id']][$row['programa_id']] = $row['total_monto'];
        }
    }

    // Consultar montos actuales en la tabla distribucion_presupuestaria
    $sqlDistribucion = "
    SELECT id_sector, id_programa, SUM(monto_actual) AS total_monto_actual
    FROM distribucion_presupuestaria
    GROUP BY id_sector, id_programa
";
    $resultadoDistribucion = $conexion->query($sqlDistribucion);
    $distribucionData = [];
    if ($resultadoDistribucion && $resultadoDistribucion->num_rows > 0) {
        while ($row = $resultadoDistribucion->fetch_assoc()) {
            $distribucionData[$row['id_sector']][$row['id_programa']] = $row['total_monto_actual'];
        }
    }

    // Asociar los montos de proyecto_inversion_partidas y distribucion_presupuestaria a los programas y acumular total de sector
    foreach ($sectoresData as &$sector) {
        $sectorTotal = 0; // Inicializar acumulador de total del sector
        foreach ($sector['programas'] as &$programa) {
            // Agregar monto de proyecto_inversion_partidas si existe
            $programaMonto = 0;
            if (isset($montosData[$sector['sector_id']][$programa['programa_id']])) {
                $programaMonto += $montosData[$sector['sector_id']][$programa['programa_id']];
            }
            // Agregar monto_actual de distribucion_presupuestaria si existe
            if (isset($distribucionData[$sector['sector_id']][$programa['programa_id']])) {
                $programaMonto += $distribucionData[$sector['sector_id']][$programa['programa_id']];
            }
            $programa['monto'] = $programaMonto; // Asignar monto acumulado al programa
            $sectorTotal += $programaMonto; // Acumular al total del sector
        }
        $sector['total'] = $sectorTotal; // Asignar total del sector
    }
    unset($sector, $programa); // Liberar referencias para evitar errores



    // Consultar todos los registros de la tabla titulo_1 con articulo y descripcion
    $sqlTitulo = "SELECT articulo, descripcion FROM titulo_1";
    $resultadoTitulo = $conexion->query($sqlTitulo);
    $tituloData = [];
    $articulo27 = null; // Inicializa la variable para almacenar el artículo 27

    if ($resultadoTitulo && $resultadoTitulo->num_rows > 0) {
        while ($row = $resultadoTitulo->fetch_assoc()) {
            // Agrega todos los registros a la lista
            $tituloData[] = $row;

            // Verifica si el artículo es "ARTICULO 27" y lo guarda en la variable
            if ($row['articulo'] === 'ARTICULO 27:') {
                $articulo27 = $row;
            }
        }
    }

    // Nueva consulta para obtener todos los registros de la tabla informacion_personas
    $sqlPersonas = "SELECT nombres, cargo FROM informacion_personas";
    $resultadoPersonas = $conexion->query($sqlPersonas);
    $personasData = [];

    if ($resultadoPersonas && $resultadoPersonas->num_rows > 0) {
        while ($row = $resultadoPersonas->fetch_assoc()) {
            $personasData[] = $row; // Guarda cada registro en el array
        }
    }

    $conexion->commit();

} catch (Exception $e) {
    $conexion->rollback();
    die("Error en la consulta: " . $e->getMessage());
}







function convertirNumeroLetra2($numero)
{
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

function milmillon2($nummierod)
{
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

function cienmillon2($numcmeros)
{
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

function decmillon2($numerodm)
{
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

function millon2($nummiero)
{
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

function cienmiles2($numcmero)
{
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

function decmiles2($numdmero)
{
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

function miles2($nummero)
{
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

function centena2($numc)
{
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

function decena2($numdero)
{
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

function unidad2($numuero)
{
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
            font-family: Calibri, sans-serif;
            font-size: 9px;
            font-style: italic;
        }

        .font-arial {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;

            margin-top: 15px;
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

        .w-20 {
            width: 20%;
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

        .bb-light-blue {
            border-bottom: 1px solid darkcyan;
        }

        .bt {
            border-top: 1px solid;
        }

        .dw-nw {
            white-space: nowrap !important;
        }

        .mt-0 {
            margin-top: 0px !important;
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
    <div style="text-align: center; font-size: 15px"><strong>
            <p>REPUBLICA BOLIVARIANA DE VENEZUELA</p>
            <div style='width: 100%;  '>
                <img style="margin-left: auto; margin-right: auto; width: 10%" src='../../img/logo_2_amazona.jpg'
                    class='logo'>
            </div>
            <p>EL CONSEJO LEGISLATIVO DEL ESTADO AMAZONAS</p>
            <p>Decreta lo siguiente</p>
            <p>LEY DE PRESUPUESTO DE INGRESOS Y GASTOS</p>
            <p>DEL ESTADO AMAZONAS PARA EL EJERCICIO FISCAL</p>
            <p>TITULO I</p>
            <p>DISPOSICIONES GENERALES</p>
        </strong></div>
    <div style="font-size: 15px">
        <p><strong>Artículo 1:</strong> Se aprueba la estimación de los Ingresos y Gastos Públicos para el Ejercicio
            Fiscal <?= $ano ?> en la cantidad de <span class="font-arial"><?php echo convertirNumeroLetra2($total); ?>
                (Bs.
                <?php echo number_format($total, 2) ?>),</span> la cual está constituida por los siguientes rubros de
            ingresos:
        </p>

        <table>
            <tr class="bb-light-blue">
                <td class="text-left">SITUADO CONSTITUCIONAL</td>
                <td>Bs.</td>
                <td class="text-right"><?= number_format($situado, 2) ?></td>
            </tr>
            <tr class="bb-light-blue">
                <td class="text-left">FONDO DE COMPENSACIÓN INTERTERRITORIAL</td>
                <td>Bs.</td>
                <td class="text-right"><?= number_format($monto_total, 2) ?></td>
            </tr>
            <tr class="bb-light-blue fw-bold">
                <td class="text-left">TOTAL Bs.</td>
                <td>Bs.</td>
                <td class="text-right"><?= number_format($total, 2) ?></td>
            </tr>
        </table>

        <p>Esta distribución se hace de acuerdo a lo que se prevé en el Título II de esta Ley, denominado “Presupuesto
            de Ingresos” por un monto de <span class="font-arial"><?php echo convertirNumeroLetra2($total); ?> (Bs.
                <?php echo number_format($total, 2) ?>)</span>.
        </p>

        <!-- Sección para mostrar los artículos y descripciones de la tabla titulo_1 -->
        <div style="text-align: justify;">
            <?php foreach ($tituloData as $titulo): ?>
                <?php if ($titulo['articulo'] !== 'ARTICULO 27:'): ?>
                    <p><strong><?= htmlspecialchars($titulo['articulo']) ?></strong>
                        <?= htmlspecialchars($titulo['descripcion']) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center;"><strong>
                <p>TITULO II</p>
                <p>PRESUPUESTO DE INGRESOS</p>
            </strong>
        </div>
        <p><strong>ARTICULO 25:</strong> <span class="font-arial">Apruébese la estimación de los Ingresos Públicos para
                el Ejercicio Fiscal
                <?= $ano ?> la cantidad de <?php echo convertirNumeroLetra2($total); ?> (Bs.
                <?php echo number_format($total, 2) ?>)</span>, según la distribución siguiente:
        </p>
        <table>
            <tr>
                <th class="bl bt bb" colspan="4">CÓDIGO DE RECURSOS</th>
                <th class="bl bt bb br" rowspan="2">DENOMINACIÓN</th>
                <th class="bl bt bb br w-20" rowspan="2">MONTO Bs.</th>
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
                <td class="bl bt bb br text-left">RECURSOS</td>
                <td class="bl bt bb br text-right"><?= number_format($total, 2) ?></td>
            </tr>
            <tr>
                <td class="bl bt bb">3.05</td>
                <td class="bl bt bb br">00</td>
                <td class="bl bt bb br">00</td>
                <td class="bl bt bb br">00</td>
                <td class="bl bt bb text-left">TRANSFERENCIAS Y DONACIONES</td>
                <td class="bl bt bb br text-right"><?= number_format($total, 2) ?></td>
            </tr>
            <tr>
                <td class="bl bt bb">3.05</td>
                <td class="bl bt bb br">03</td>
                <td class="bl bt bb br">01</td>
                <td class="bl bt bb br">01</td>
                <td class="bl bt bb br text-left">SITUADO ESTATAL</td>
                <td class="bl bt bb br text-right"><?= number_format($situado, 2) ?></td>
            </tr>
            <tr>
                <td class="bl bt bb">3.05</td>
                <td class="bl bt bb br">08</td>
                <td class="bl bt bb br">01</td>
                <td class="bl bt bb br">01</td>
                <td class="bl bt bb br text-left">FONDO DE COMPENSACIÓN INTERTERRITORIAL</td>
                <td class="bl bt bb br text-right"><?= number_format($monto_total, 2) ?></td>
            </tr>
            <tr>
                <td colspan="5" class="bl bt bb text-right fw-bold">TOTAL</td>
                <td class="bl bt bb br text-right fw-bold"><?= number_format($total, 2) ?></td>
            </tr>
        </table>
        <div style="text-align: center;">
            <strong>
                <p>TITULO III</p>
                <p>PRESUPUESTO DE GASTOS</p>
            </strong>
        </div>
        <p><strong>ARTÍCULO 26:</strong> Se acuerda la estimación de los Ingresos Públicos para el Ejercicio Fiscal
            <?= $ano ?> en la cantidad de <span class="font-arial"><?php echo convertirNumeroLetra2($total); ?> (Bs.
                <?php echo number_format($total, 2) ?>)</span>, según la distribución siguiente:
        </p>

        <!-- Tabla HTML -->

        <table>
        </table>

        <?php
        $sectoresImprimidos = [];

        foreach ($sectoresData as $sectorData) {
            $sectorKey = $sectorData['sector'];


            // Verificar si el sector ya ha sido impreso
            if (!in_array($sectorKey, $sectoresImprimidos)) {
                // Imprimir información del sector con el total acumulado
                echo "<table><tr><td colspan='3' class='text-left pb-0 pt-0'><strong>Sector:</strong> " . htmlspecialchars($sectorData['sector']) . "</td></tr>";
                echo "<tr><td class='fw-bold text-left pt-0'>" . htmlspecialchars($sectorData['sector_denominacion']) . "</td>";
                echo "<td colspan='2' class='fw-bold pt-0 text-right'>TOTAL: " . number_format($sectorData['total'], 2) . " Bs.</td></tr></table>";

                // Si hay programas
                if (!empty($sectorData['programas']) && is_array($sectorData['programas'])) {
                    echo "<table class='mt-0'><tr><th class='bl bt bb' colspan='2'>PROGRAMA</th><th class='bl bt bb br'>MONTO Bs.</th></tr>";
                    foreach ($sectorData['programas'] as $programa2) {
                        echo "<tr>";
                        echo "<td class='bl bt bb fw-bold'>" . htmlspecialchars($programa2['programa']) . "</td>";
                        echo "<td class='bl bt bb br text-left'>" . htmlspecialchars($programa2['programa_denominacion']) . "</td>";
                        echo "<td class='bl bt bb br text-right w-20'>" . number_format($programa2['monto'], 2) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<table class='mt-0'><tr><td class='bl bt bb br' colspan='3'>No hay programas disponibles para este sector.</td></tr></table>";
                }




                // Agregar el sector a la lista de impresos
                $sectoresImprimidos[] = $sectorKey;
            }
        }
        ?>


        <p><strong><?= htmlspecialchars($articulo27['articulo']) ?></strong>
            <?= htmlspecialchars($articulo27['descripcion']) ?></p>
        <div style="">
            <!-- Impresión de información de personas -->
            <?php
            $cantidadPersonas = 0;
            $alineacionDerecha = true; // Variable para determinar la alineación
            
            foreach ($personasData as $persona):
                if ($cantidadPersonas < 2) {
                    // Mostrar persona
                    ?>
                    <div class="<?= $alineacionDerecha ? 'text-right' : 'text-left'; ?>" style="margin-bottom: 80px;">
                        <p><?= htmlspecialchars($persona['nombres']); ?></p>
                        <p><strong><?= htmlspecialchars($persona['cargo']) ?></strong></p>
                    </div>
                    <?php
                    $cantidadPersonas++;
                }

                // Cada 2 personas, cambia la alineación y resetea el contador
                if ($cantidadPersonas == 2) {
                    $cantidadPersonas = 0;
                    $alineacionDerecha = !$alineacionDerecha; // Alterna la alineación
                }

            endforeach;
            ?>
        </div>
    </div>
</body>

</html>