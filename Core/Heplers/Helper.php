<?php

function env ($env_key=null) {
    $envs = [];
    $get_env = explode("\n", file_get_contents('.env'));
    foreach ($get_env as $env) {
        $explode_env = explode("=", $env);
        if (strlen($explode_env[0])<=1) continue;
        $envs[$explode_env[0]] = $explode_env[1];
    }
    if ($env_key!==null){
        if (array_key_exists($env_key, $envs)){
            return $envs[$env_key];
        }
        return NULL;
    }
}


?>