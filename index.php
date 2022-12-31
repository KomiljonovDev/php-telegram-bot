<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>''];

	$bot = new Bot($dataSet);
	$bot->sendChatAction('typing', 931026030)->sendDocument('video2.mp4');
	print_r($bot->result());
?>