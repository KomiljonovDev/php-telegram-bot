<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>''];

	$bot = new Bot($dataSet);
	$bot->sendChatAction('typing', 931026030)->sendContact('+998940545197',"Komiljonov");
	print_r($bot->result());
?>