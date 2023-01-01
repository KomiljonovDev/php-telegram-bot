<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>''];

	$bot = new Bot($dataSet);
	$bot->getMe();
	print_r($bot->result());
?>