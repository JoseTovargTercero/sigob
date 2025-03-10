<?php
require_once '../sistema_global/conexion.php';

// Obtener el ID de compromiso mediante GET
$id_compromiso = $_GET["id"];

// Consultar los datos del compromiso
$queryCompromiso = "SELECT id, correlativo, descripcion, id_registro, id_ejercicio, tabla_registro, numero_compromiso FROM compromisos WHERE id = ?";
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

// Realizar una consulta dinámica en la tabla indicada por `tabla_registro` para obtener los datos de `id_registro`
$tablaRegistro = $dataCompromiso['tabla_registro'];
$idRegistro = $dataCompromiso['id_registro'];

if ($tablaRegistro == "gastos") {
    $queryRegistro = "SELECT * FROM $tablaRegistro WHERE id = ?";
    $stmtRegistro = $conexion->prepare($queryRegistro);
    $stmtRegistro->bind_param('i', $idRegistro);
    $stmtRegistro->execute();
    $resultRegistro = $stmtRegistro->get_result();
    $dataRegistro = $resultRegistro->fetch_assoc();
    $stmtRegistro->close();

    if (!$dataRegistro) {
        die("No se encontró el registro en la tabla 'gastos'.");
    }
} elseif ($tablaRegistro == "proyecto_credito") {
    // Consultar la tabla proyecto_credito uniendo con credito_adicional
    $queryRegistro = "
        SELECT pc.*, ca.* 
        FROM proyecto_credito pc
        JOIN credito_adicional ca ON pc.id_credito = ca.id
        WHERE pc.id_credito = ?
    ";
    $stmtRegistro = $conexion->prepare($queryRegistro);
    if (!$stmtRegistro) {
        die("Fallo al preparar la consulta de proyecto_credito y credito_adicional.");
    }
    $stmtRegistro->bind_param('i', $idRegistro);
    $stmtRegistro->execute();
    $resultRegistro = $stmtRegistro->get_result();
    $dataRegistro = $resultRegistro->fetch_assoc();
    $stmtRegistro->close();

    if (!$dataRegistro) {
        die("No se encontró el registro en la tabla 'proyecto_credito'.");
    }
} elseif ($tablaRegistro == "solicitud_dozavos") {
    // URL de la API
    $api_url = "https://sigob.net/sigob_entes/api/solicitudes";

    // Configuración de la solicitud cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . '?id=' . $idRegistro); // Agregar el ID a la URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retornar la respuesta como string
    curl_setopt($ch, CURLOPT_HTTPGET, true); // Método GET
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json', // Encabezado de tipo de contenido
        'Authorization: 123456789abcdef' // Sustituye con tu token si es necesario
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($ch);

    // Manejar errores de cURL
    if (curl_errno($ch)) {
        die("Error en la solicitud: " . curl_error($ch));
    }

    // Cerrar el recurso cURL
    curl_close($ch);

    // Decodificar la respuesta JSON
    $response_data = json_decode($response, true);

    // Verificar si la respuesta es válida
    if (isset($response_data['success']) && $response_data['success']) {
        $dataRegistro = $response_data['success']; // Asignar los datos de la API
    } else {
        die("No se encontró el registro en 'solicitud_dozavos' o hubo un error en la API.");
    }
} else {
    die("Tabla especificada no válida.");
}





if ($tablaRegistro == "gastos") {
    // Consultar las distribuciones asociadas al compromiso
    $queryGastos = "SELECT * FROM gastos WHERE id = ?";
    $stmtGastos = $conexion->prepare($queryGastos);
    $stmtGastos->bind_param('i', $idRegistro);
    $stmtGastos->execute();
    $resultGastos = $stmtGastos->get_result();
    $dataGastos = $resultGastos->fetch_assoc();
    $stmtGastos->close();

    if (!$dataGastos) {
        die("No se encontraron los gastos asociados al compromiso.");
    }

    // Consultar los detalles de la distribución presupuestaria usando el campo `distribuciones`
    $distribuciones = json_decode($dataGastos['distribuciones'], true);
    $detalleDistribuciones = [];

    foreach ($distribuciones as $distribucion) {
        $id_distribucion = $distribucion['id_distribucion'];
        $queryDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
        $stmtDistribucion = $conexion->prepare($queryDistribucion);
        $stmtDistribucion->bind_param('i', $id_distribucion);
        $stmtDistribucion->execute();
        $resultDistribucion = $stmtDistribucion->get_result();
        $dataDistribucion = $resultDistribucion->fetch_assoc();
        $stmtDistribucion->close();

        if ($dataDistribucion) {
            // Consultar los datos de la partida presupuestaria
            $id_partida = $dataDistribucion['id_partida'];
            $queryPartida = "SELECT * FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $conexion->prepare($queryPartida);
            $stmtPartida->bind_param('i', $id_partida);
            $stmtPartida->execute();
            $resultPartida = $stmtPartida->get_result();
            $dataPartida = $resultPartida->fetch_assoc();
            $stmtPartida->close();

            if ($dataPartida) {
                $detalleDistribuciones[] = [
                    'distribucion' => $distribucion,
                    'partida_presupuestaria' => $dataPartida
                ];
            } else {
                die("No se encontró la partida presupuestaria correspondiente.");
            }
        } else {
            die("No se encontró la distribución presupuestaria correspondiente.");
        }
    }

    // Resultado final con la información de compromiso, ejercicio fiscal, registro específico, gastos y distribuciones presupuestarias
    $response = [
        'compromiso' => $dataCompromiso,
        'registro_especifico' => $dataRegistro,
        'gastos' => $dataGastos,
        'distribuciones' => $detalleDistribuciones
    ];
} elseif ($tablaRegistro == "proyecto_credito") {
    // Consultar el proyecto de crédito y unirlo con crédito adicional
    $queryProyecto = "
        SELECT pc.*, ca.*
        FROM proyecto_credito pc
        JOIN credito_adicional ca ON pc.id_credito = ca.id
        WHERE pc.id_credito = ?
    ";
    $stmtProyecto = $conexion->prepare($queryProyecto);
    $stmtProyecto->bind_param('i', $idRegistro);
    $stmtProyecto->execute();
    $resultProyecto = $stmtProyecto->get_result();
    $dataProyecto = $resultProyecto->fetch_assoc();
    $stmtProyecto->close();

    if (!$dataProyecto) {
        die("No se encontró el registro en la tabla 'proyecto_credito'.");
    }

    // Consultar la información del ente usando el id_ente de credito_adicional
    $id_ente = $dataProyecto['id_ente']; // Asumimos que `id_ente` viene de `credito_adicional`
    $queryEnte = "SELECT * FROM entes WHERE id = ?";
    $stmtEnte = $conexion->prepare($queryEnte);
    $stmtEnte->bind_param('i', $id_ente);
    $stmtEnte->execute();
    $resultEnte = $stmtEnte->get_result();
    $dataEnte = $resultEnte->fetch_assoc();
    $stmtEnte->close();

    if (!$dataEnte) {
        die("No se encontró el registro en la tabla 'entes'.");
    }

    // Consultar los detalles de la distribución presupuestaria usando el campo `distribuciones`
    $distribuciones = json_decode($dataProyecto['distribuciones'], true);
    $detalleDistribuciones = [];

    foreach ($distribuciones as $distribucion) {
        $id_distribucion = $distribucion['id_distribucion'];
        $queryDistribucion = "SELECT id_partida FROM distribucion_presupuestaria WHERE id = ?";
        $stmtDistribucion = $conexion->prepare($queryDistribucion);
        $stmtDistribucion->bind_param('i', $id_distribucion);
        $stmtDistribucion->execute();
        $resultDistribucion = $stmtDistribucion->get_result();
        $dataDistribucion = $resultDistribucion->fetch_assoc();
        $stmtDistribucion->close();

        if ($dataDistribucion) {
            // Consultar los datos de la partida presupuestaria
            $id_partida = $dataDistribucion['id_partida'];
            $queryPartida = "SELECT * FROM partidas_presupuestarias WHERE id = ?";
            $stmtPartida = $conexion->prepare($queryPartida);
            $stmtPartida->bind_param('i', $id_partida);
            $stmtPartida->execute();
            $resultPartida = $stmtPartida->get_result();
            $dataPartida = $resultPartida->fetch_assoc();
            $stmtPartida->close();

            if ($dataPartida) {
                $detalleDistribuciones[] = [
                    'distribucion' => $distribucion,
                    'partida_presupuestaria' => $dataPartida
                ];
            } else {
                die("No se encontró la partida presupuestaria correspondiente.");
            }
        } else {
            die("No se encontró la distribución presupuestaria correspondiente.");
        }
    }

    // Resultado final con la información del proyecto de crédito, entes y distribuciones presupuestarias
    $response = [
        'compromiso' => $dataCompromiso,
        'registro_especifico' => $dataRegistro,
        'proyecto_credito' => $dataProyecto,
        'ente' => $dataEnte,
        'distribuciones' => $detalleDistribuciones
    ];
} elseif ($tablaRegistro == "solicitud_dozavos") {
    // URL de la API
    $api_url = "https://sigob.net/sigob_entes/api/solicitudes";

    // ID del registro del compromiso


    // Configuración de la solicitud cURL
    $ch = curl_init();

    // Configurar los parámetros de la solicitud
    curl_setopt($ch, CURLOPT_URL, $api_url . '?id=' . $idRegistro);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retornar la respuesta como string
    curl_setopt($ch, CURLOPT_HTTPGET, true); // Método GET
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json', // Encabezado de tipo de contenido
        'Authorization: 123456789abcdef' // Sustituye con tu token si es necesario
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($ch);

    // Manejar errores
    if (curl_errno($ch)) {
        echo "Error en la solicitud: " . curl_error($ch);
        curl_close($ch);
        exit;
    }

    // Cerrar el recurso cURL
    curl_close($ch);

    // Decodificar la respuesta JSON
    $response_data = json_decode($response, true);

    if ($response_data) {
        // Procesar los datos de la respuesta
        echo "Respuesta de la API:\n";
        print_r($response_data);

        // Obtener el mes desde la respuesta
        $meses = array("ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE");
        $mesIndex = $response_data['success']['mes']; // Índice del mes obtenido de la API
        $date2 = isset($meses[$mesIndex]) ? $meses[$mesIndex] : "MES NO ENCONTRADO";

        // Consultar el ente desde la información de la API
        $dataEnte = $response_data['success']['ente'];

        // Verificar si existen partidas
        $partidas = $response_data['success']['partidas'];
        if (empty($partidas)) {
            die("La solicitud no contiene partidas asociadas.");
        }

        $detallePartidas = [];

        foreach ($partidas as $partida) {
            $id_distribucion = $partida['id'];
            $monto_partida = $partida['monto'];

            // Consultar la distribución presupuestaria asociada a la partida
            $queryDistribucion = "SELECT * FROM distribucion_presupuestaria WHERE id = ?";
            $stmtDistribucion = $conexion->prepare($queryDistribucion);
            $stmtDistribucion->bind_param('i', $id_distribucion);
            $stmtDistribucion->execute();
            $resultDistribucion = $stmtDistribucion->get_result();
            $dataDistribucion = $resultDistribucion->fetch_assoc();
            $stmtDistribucion->close();

            if ($dataDistribucion) {
                // Consultar los detalles de la partida presupuestaria
                $queryPartida = "SELECT * FROM partidas_presupuestarias WHERE id = ?";
                $stmtPartida = $conexion->prepare($queryPartida);
                $stmtPartida->bind_param('i', $dataDistribucion['id_partida']);
                $stmtPartida->execute();
                $resultPartida = $stmtPartida->get_result();
                $dataPartida = $resultPartida->fetch_assoc();
                $stmtPartida->close();

                if ($dataPartida) {
                    $detallePartidas[] = [
                        'partida' => [
                            'id' => $dataDistribucion['id_partida'],
                            'monto' => $monto_partida
                        ],
                        'distribucion_presupuestaria' => $dataDistribucion,
                        'partida_presupuestaria' => $dataPartida
                    ];
                } else {
                    die("No se encontró la información de la partida presupuestaria para el ID {$dataDistribucion['id_partida']}.");
                }
            } else {
                die("No se encontró la distribución presupuestaria para el ID de la partida $id_distribucion.");
            }
        }

        // Resultado final con la información de la solicitud, las partidas y distribuciones presupuestarias
        $response = [
    'compromiso' => $dataCompromiso,
    'registro_especifico' => $response_data['success'],
    'solicitud' => [
        'id' => $response_data['success']['id'],
        'numero_orden' => $response_data['success']['numero_orden'],
        'numero_compromiso' => $response_data['success']['numero_compromiso'],
        'descripcion' => $response_data['success']['descripcion'],
        'tipo' => $response_data['success']['tipo'],
        'monto' => $response_data['success']['monto'],
        'fecha' => $response_data['success']['fecha'],
        'id_ente' => $response_data['success']['id_ente'],
        'status' => $response_data['success']['status'],
        'id_ejercicio' => $response_data['success']['id_ejercicio'],
        'mes' => $date2 // Incluir el mes en formato textual
    ],
    'partidas' => $detallePartidas
];


    }
}
$dataSolicitud = $response; // Asegurar que la vista accede a los datos







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
            padding-top: 6px;
            padding-bottom: 6px;
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

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .w-100 h2 {
            font-size: 16px;
            margin: 0;
        }

        .w-33 {
            width: 33.33%;
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
    <div style='font-size: 9px;'>
        <table class='header-table bt br bb bl'>
            <tr>
                <td style='width:100px'>
                    <img src='../../img/logo.jpg' class='logo'>
                </td>
                <td class='text-center' style='vertical-align: middle; padding-top: 13px;'>
                    <b>REPUBLICA BOLIVARIANA DE VENEZUELA</b><br>
                    <b>ESTADO AMAZONAS</b><br>
                    <b>GOBERNACION DEL ESTADO AMAZONAS</b>
                </td>
                <td class='text-right' style='width:100px'>
                    <b>
                        Fecha: <?php echo date('d/m/Y') ?>
                    </b>


                </td>
            </tr>

        </table>


        <h2 class='text-center'>COMPROMISO</h2>

        <!-- Tabla principal -->
        <table>
    <tr class="bb bt bb br bl">
        <td class="w-33 br"><b>Nro. de Compromiso:</b> <?php echo $dataCompromiso['numero_compromiso'] ?? 'No disponible'; ?></td>
        <td class="br w-33"><b>Tipo:</b> COMPROMISO PRESUPUESTARIO</td>
        <td class="br w-33"><b>Fecha:</b> <?php echo date('d/m/Y'); ?></td>
    </tr>

    <!-- Mostrar Beneficiario o Solicitante según la tablaRegistro -->
    <?php if (!empty($dataRegistro)) : ?>
        <?php if ($tablaRegistro === 'gastos') : ?>
            <tr>
                <td class="bl bt bb" colspan="2"><b>Beneficiario:</b> <?php echo $dataRegistro['beneficiario'] ?? 'No especificado'; ?></td>
                <td class="bl bt bb br"><b>RIF:</b> <?php echo $dataRegistro['identificador'] ?? 'No disponible'; ?></td>
            </tr>
        <?php elseif ($tablaRegistro === 'solicitud_dozavos') : ?>
            <tr>
                <td class="bl bt bb w-50"><b>Solicitante:</b> <?php echo $dataEnte['ente_nombre'] ?? 'No disponible'; ?></td>
                <td class="bl bt bb br w-50"><b>Motivo:</b> SOLICITUD DE DOZAVO CORRESPONDIENTE AL MES DE <?php echo $date2 ?? 'No definido'; ?></td>
            </tr>
        <?php elseif ($tablaRegistro === 'proyecto_credito') : ?>
            <tr>
                <td class="bl bt bb w-100"><b>Solicitante:</b> <?php echo $dataEnte['ente_nombre'] ?? 'No disponible'; ?></td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>

    <tr class="bl bt bb br">
        <td colspan="3" class="text-left"><b>Concepto:</b> <?php echo $dataCompromiso['descripcion'] ?? 'Sin descripción'; ?></td>
    </tr>
    <tr class="bl bt bb br">
        <td colspan="3" class="text-left"><b>Bolívares:</b>
            <?php echo number_format($dataRegistro['monto'] ?? 0, 2, ',', '.'); ?> Bs
        </td>
    </tr>
    <tr class="bl bt bb br">
        <td colspan="3"><b>Monto en letras:</b>
            <?php echo isset($dataRegistro['monto']) ? convertirNumeroLetra2($dataRegistro['monto']) : 'No disponible'; ?>
        </td>
    </tr>
    <tr class="bl bt bb br">
        <td class="br fw-bold">CÓDIGO PRESUPUESTARIO:<br>ST-PG-PY-AC-PAR-GE-ES-SE-AUXI</td>
        <td class="br fw-bold">DENOMINACIÓN:</td>
        <td class="fw-bold">MONTO:</td>
    </tr>

    <!-- Mostrar distribuciones según la tablaRegistro -->
    <?php if (($tablaRegistro === 'gastos' || $tablaRegistro === 'proyecto_credito') && !empty($detalleDistribuciones) && is_array($detalleDistribuciones)) : ?>
        <?php foreach ($detalleDistribuciones as $distribucion) : 
            $partidaPresupuestaria = $distribucion['partida_presupuestaria'] ?? null;
            ?>
            <tr>
                <td class="bl bt bb"><?php echo $partidaPresupuestaria['partida'] ?? 'N/A'; ?></td>
                <td class="bl bt bb br"><?php echo $partidaPresupuestaria['descripcion'] ?? 'Sin descripción'; ?></td>
                <td class="bl bt bb br">
                    <?php echo number_format($distribucion['distribucion']['monto'] ?? 0, 2, ',', '.'); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php elseif ($tablaRegistro === 'solicitud_dozavos' && !empty($dataSolicitud)) : ?>
        <?php 
        $partidas = !empty($dataRegistro['partidas']) && is_string($dataRegistro['partidas']) 
    ? json_decode($dataRegistro['partidas'], true) 
    : (is_array($dataRegistro['partidas']) ? $dataRegistro['partidas'] : []);

        ?>
        <?php foreach ($detallePartidas as $detalle) : ?>
            <tr>
                <td class="bl bt bb"><?php echo $detalle['partida_presupuestaria']['partida'] ?? 'N/A'; ?></td>
                <td class="bl bt bb"><?php echo htmlspecialchars($detalle['partida_presupuestaria']['descripcion'] ?? 'Sin descripción'); ?></td>
                <td class="bl bt bb"><?php echo number_format($detalle['partida']['monto'] ?? 0, 2, ',', '.'); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>




        <br>
        <br>
        <br>

        <div style="display: flex; justify-content: space-between; width: 100%;">
            <p style="margin: 0;">NOMBRES Y APELLIDOS DEL ANALISTA ______________________________________________ C.I
                NRO:
                _________________________________</p>
            <p style="margin: 0;">JEFE DE OFICINA DE PRESUPUESTO _______________________________________________</p>
        </div>


</body>

</html>