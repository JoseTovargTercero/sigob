<?php
// Obtener el año actual
$year = date("Y");

// Verificar si el año es bisiesto
$isLeapYear = ($year % 4 == 0 && ($year % 100 != 0 || $year % 400 == 0));

// Definir la cantidad de días en cada mes
$daysInMonth = [
    1 => 31,
    2 => $isLeapYear ? 29 : 28,
    3 => 31,
    4 => 30,
    5 => 31,
    6 => 30,
    7 => 31,
    8 => 31,
    9 => 30,
    10 => 31,
    11 => 30,
    12 => 31
];

// Inicializar el array para las semanas por mes
$weeksByMonth = [];

// Inicializar la semana previa
$previousWeek = null;

// Recorrer cada mes
foreach ($daysInMonth as $month => $days) {
    $weeksByMonth[$month] = [];

    // Recorrer cada día del mes
    for ($day = 1; $day <= $days; $day++) {
        $currentDate = strtotime("$year-$month-$day");
        $currentWeek = (int)date("W", $currentDate);  // Convertir la semana a entero

        // Verificar si la semana ya está registrada en el mes actual
        if ($currentWeek !== $previousWeek && !in_array($currentWeek, $weeksByMonth[$month])) {
            // Añadir la semana al mes actual
            $weeksByMonth[$month][] = $currentWeek;
            $previousWeek = $currentWeek;
        }
    }
}

// Eliminar la primera semana del año en diciembre, si aparece debido al desbordamiento
if (end($weeksByMonth[12]) === 1) {
    array_pop($weeksByMonth[12]);
}

// Imprimir el resultado en formato JSON
header('Content-Type: application/json');
echo json_encode($weeksByMonth);
?>
