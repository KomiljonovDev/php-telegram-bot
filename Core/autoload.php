<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include "Core/Heplers/Helper.php";

spl_autoload_register(function ($className){
    require str_replace("\\", "/", $className) . ".php";
});

