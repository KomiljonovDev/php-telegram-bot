<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>''];

	$bot = new Bot($dataSet);
	print_r($bot->getMe());
?>