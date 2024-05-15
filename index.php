<?php

require 'Core/autoload.php';

require  'Core/Heplers/Functions.php';

require 'Core/Telegram/Updates.php';

use Core\database\Connector;
use Core\Telegram\Bot;

$db = new Connector();
$bot = new Bot(['botToken'=>env('bot_token')]);

require 'App.php';