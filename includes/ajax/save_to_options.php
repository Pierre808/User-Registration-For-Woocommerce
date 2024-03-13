<?php

header('Content-type: application/json');

try{
    $params = $_POST['values'];
    for($i = 0; $i < count($params); $i++) {
        $params_name = $params[$i]['name'];
        $params_data = $params[$i]['data'];
        update_option($params_name, $params_data);
    }

    wp_send_json(['message' => "success!"]);
}
catch (Exception $e){
    wp_send_json(['message' => "error: " . $e->getMessage()]);
}

wp_send_json(["test" => "test value"]);

wp_die();