# PHP TELEGRAM BOT
php dasturlash tilida bot yozish uchun ishlab chiqilgan kichik kutubxona.
# Ishga tushurish
```php
<?php
require 'Telegram/TelegramBot.php';
use TelegramBot as Bot;
$dataSet = ['botToken'=>'TELEGRAM BOT TOKEN'];
$bot = new Bot($dataSet);
```