<?php
require_once '../sistema_global/conexion.php';

// Obtener el ID de compromiso mediante GET
$id_compromiso = $_GET["id"];

// Consultar los datos del compromiso
$queryCompromiso = "SELECT id, correlativo, descripcion, id_registro, tipo_beneficiario, id_beneficiario, id_ejercicio, tabla_registro FROM compromisos WHERE id = ?";
$stmtCompromiso = $conexion->prepare($queryCompromiso);
$stmtCompromiso->bind_param('i', $id_compromiso);
$stmtCompromiso->execute();
$resultCompromiso = $stmtCompromiso->get_result();
$dataCompromiso = $resultCompromiso->fetch_assoc();
$stmtCompromiso->close();

if (!$dataCompromiso) {
    die("No se encontró el compromiso con el ID especificado.");
}

$id_ejercicio = $dataCompromiso['id_ejercicio'];

// Consultar datos del ejercicio fiscal con el `id_ejercicio` obtenido
$queryEjercicio = "SELECT ano, situado FROM ejercicio_fiscal WHERE id = ?";
$stmtEjercicio = $conexion->prepare($queryEjercicio);
$stmtEjercicio->bind_param('i', $id_ejercicio);
$stmtEjercicio->execute();
$resultEjercicio = $stmtEjercicio->get_result();
$dataEjercicio = $resultEjercicio->fetch_assoc();
$stmtEjercicio->close();

$ano = $dataEjercicio['ano'];

// Determinar si buscar en `entes_dependencias` o en `empleados` según el `tipo_beneficiario`
if ($dataCompromiso['tipo_beneficiario'] == 0) {
    $queryBeneficiario = "SELECT * FROM entes_dependencias WHERE id = ?";
} else {
    $queryBeneficiario = "SELECT * FROM empleados WHERE id = ?";
}

$stmtBeneficiario = $conexion->prepare($queryBeneficiario);
$stmtBeneficiario->bind_param('i', $dataCompromiso['id_beneficiario']);
$stmtBeneficiario->execute();
$resultBeneficiario = $stmtBeneficiario->get_result();
$dataBeneficiario = $resultBeneficiario->fetch_assoc();
$stmtBeneficiario->close();

if (!$dataBeneficiario) {
    die("No se encontró el beneficiario correspondiente.");
}

// Realizar una consulta dinámica en la tabla indicada por `tabla_registro` para obtener los datos de `id_registro`
$tablaRegistro = $dataCompromiso['tabla_registro'];
$idRegistro = $dataCompromiso['id_registro'];

$queryRegistro = "SELECT * FROM $tablaRegistro WHERE id = ?";
$stmtRegistro = $conexion->prepare($queryRegistro);
$stmtRegistro->bind_param('i', $idRegistro);
$stmtRegistro->execute();
$resultRegistro = $stmtRegistro->get_result();
$dataRegistro = $resultRegistro->fetch_assoc();
$stmtRegistro->close();

if (!$dataRegistro) {
    die("No se encontró el registro en la tabla especificada.");
}

// Consultar el `id_partida` en la tabla `distribucion_presupuestaria` usando `id_distribucion`
$id_distribucion = $dataRegistro['id_distribucion'];
$queryDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
$stmtDistribucion = $conexion->prepare($queryDistribucion);
$stmtDistribucion->bind_param('i', $id_distribucion);
$stmtDistribucion->execute();
$resultDistribucion = $stmtDistribucion->get_result();
$dataDistribucion = $resultDistribucion->fetch_assoc();
$stmtDistribucion->close();

if (!$dataDistribucion) {
    die("No se encontró la distribución presupuestaria correspondiente.");
}

$id_partida = $dataDistribucion['id_partida'];

// Consultar los datos de `partidas_presupuestarias` usando `id_partida`
$queryPartida = "SELECT * FROM partidas_presupuestarias WHERE id = ?";
$stmtPartida = $conexion->prepare($queryPartida);
$stmtPartida->bind_param('i', $id_partida);
$stmtPartida->execute();
$resultPartida = $stmtPartida->get_result();
$dataPartida = $resultPartida->fetch_assoc();
$stmtPartida->close();

if (!$dataPartida) {
    die("No se encontró la partida presupuestaria correspondiente.");
}

// Resultado final con la información de compromiso, ejercicio fiscal, beneficiario, registro específico, y partida presupuestaria
$response = [
    'compromiso' => $dataCompromiso,
    'beneficiario' => $dataBeneficiario,
    'registro_especifico' => $dataRegistro,
    'partida_presupuestaria' => $dataPartida
];



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
    <!-- Encabezado -->
    <?php
    echo "
    <div style='font-size: 9px;'>
        <table class='header-table bt br bb bl bc-lightgray'>
            <tr>
           
                <td class='text-left' style='vertical-align: top; padding-top: 13px;'>
                    <b>
                    REPÚBLICA BOLIVARIANA DE VENEZUELA <br>
                    GOBERNACIÓN DEL ESTADO AMAZONAS  <br>
                    <br>
                    <br>
                    </b>
                </td>
                <td class='text-right' style='vertical-align: top; padding: 13px 10px 0 0;'>
                    <b>
                    Fecha: " . date('d/m/Y') . " 
                    </b>
                    <br>
                    <img src='../../img/logo.jpg' class='logo'>

                </td>
            </tr>
            <tr>
                <td colspan='3'>
                    <h2 align='center'>COMPROMISO</h2>
                </td>
            </tr>
  
        </table>
    ";
    ?>

    <!-- Tabla principal -->
<table>
    <tr>
        <th class="bl bt bb">NRO DE COMPROMISO <?php echo $dataCompromiso['correlativo']; ?></th>
        <th class="bl bt bb br">Tipo: COMPROMISO PRESUPUESTARIO</th>
        <th class="bl bt bb br">Fecha: <?php echo date('d/m/Y'); ?></th>
    </tr>
    <?php if ($dataRegistro['tipo_beneficiario'] == 1) { ?>
        <tr>
            <th class="bl bt bb">Beneficiario: <?php echo $dataBeneficiario['nombres']; ?></th>
            <th class="bl bt bb br">RIF: </th>
            <th class="bl bt bb br"> <?php echo $dataBeneficiario['cedula'] ?? ''; ?></th>
        </tr>
    <?php } else { ?>
        <tr>
            <th class="bl bt bb">Beneficiario: <?php echo $dataBeneficiario['ente_nombre']; ?></th>
            <th class="bl bt bb br">RIF: <?php echo $dataBeneficiario['rif'] ?? ''; ?></th>
            <th class="bl bt bb br"></th>
        </tr>
    <?php } ?>
   
    <tr>
        <th class="bl bt bb br" colspan="3">Concepto: <?php echo $dataCompromiso['descripcion']; ?></th>
    </tr>
    <tr>
        <th class="bl bt bb br" colspan="3">Bolivares: <?php echo number_format($dataRegistro['monto'], 2, ',', '.'); ?></th>
    </tr>
    <tr>
        <th class="bl bt bb br" colspan="3">Monto en letras: <?php echo convertirNumeroLetra2($dataRegistro['monto']); ?></th>
    </tr>
    <tr>
        <th class="bl bt bb">CODIGO PRESUPUESTARIO:<br>ST-PG-PY-AC-PAR-GE-ES-SE-AUXI</th>
        <th class="bl bt bb br">DENOMINACION:</th>
        <th class="bl bt bb br">MONTO:</th>
    </tr>
    <tr>
        <th class="bl bt bb"><?php echo $dataPartida['partida'] ?? ''; ?></th>
        <th class="bl bt bb br"><?php echo $dataPartida['descripcion'] ?? ''; ?></th>
        <th class="bl bt bb br"><?php echo number_format($dataRegistro['monto'], 2, ',', '.'); ?></th>
    </tr>
</table>


<br>
<br>
<br>

<div style="display: flex; justify-content: space-between; width: 100%;">
    <p style="margin: 0;">NOMBRES Y APELLIDOS DEL ANALISTA ______________________________________________ C.I NRO: _________________________________</p>
    <p style="margin: 0;">JEFE DE OFICINA DE PRESUPUESTO _______________________________________________</p>
</div>


</body>

</html>