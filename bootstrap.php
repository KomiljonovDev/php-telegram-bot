<?php

require "vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require "vendor/larapack/dd/src/helper.php";
require 'Helpers.php';