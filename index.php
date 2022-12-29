<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;

	$bot = new Bot(['botToken'=>'5877866426:AAHOkyKw7xyvndcxPXEKjcuIXaMuKaB4uH4']);

	$bot->sendMessage("<b>Assalomu alaykum</b>", 931026030);
?>