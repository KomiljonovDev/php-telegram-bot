<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>'TELEGRAM BOT TOKEN'];

	$bot = new Bot($dataSet);
	
?>