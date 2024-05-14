<?php

 $data = json_decode(file_get_contents('php://input'), true);
 $valor = $data["valor"];
 $api_key = "4bfc66a740d312008475dded";
    $url = "https://v6.exchangerate-api.com/v6/{$api_key}/pair/USD/VES";
        $response = file_get_contents($url);
            $data = json_decode($response, true);
            
 
                $precio_dolar = $data['conversion_rate'];
                $cestaticket =  $valor * $data['conversion_rate'];
                echo round($cestaticket). " Bs";

?>