<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;

	$bot = new Bot(['botToken'=>'BO TOKEN']);

	$bot->sendMessage("<b>Assalomu alaykum</b>", 931026030);
?>