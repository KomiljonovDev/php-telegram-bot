<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>''];

	$bot = new Bot($dataSet);

	$bot->setInlineKeyBoard([
		[
			[
				'text'=>'Test uchun',
				'callback_data'=>'test1'
			],
			[
				'text'=>'Test uchun',
				'callback_data'=>'test2'
			]
		],
	])->sendMessage("<b>Assalomu alaykum</b>", 931026030);
	// print_r($bot->result());
?>