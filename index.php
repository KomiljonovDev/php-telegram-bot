<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;

	$bot = new Bot(['botToken'=>'']);

	$bot->sendMessage("<b>Assalomu alaykum</b>", 931026030);
?>