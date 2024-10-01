<?php
header('Content-Type: application/json');

$response = array(
    [
        'value' => '407',
        'total_restante' => rand(0, 600),
        'total_inicial' =>  1000
    ],
    [
        'value' => '408',
        'total_restante' => rand(0, 600),
        'total_inicial' =>  1200
    ],

);

echo json_encode($response);
