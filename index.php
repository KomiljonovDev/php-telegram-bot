<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>'5877866426:AAHOkyKw7xyvndcxPXEKjcuIXaMuKaB4uH4'];

	$bot = new Bot($dataSet);
	$bot->sendChatAction('typing',931026030)->setInlineKeyBoard([
		[
			[
				'text'=>'biir',
				'callback_data'=>'ok1'
			],
			[
				'text'=>'biir',
				'callback_data'=>'ok1'
			]
		],
	])->sendMessage("<b>Assalomu alaykum</b>");
	print_r($bot->result('reply_markup'));
?>