<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>''];

	$bot = new Bot($dataSet);
	$bot->sendChatAction('upload_voice',931026030)->setInlineKeyBoard([
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
	])->sendAudio("https://rr1---sn-vgqsknsk.googlevideo.com/videoplayback?expire=1672515269&ei=ZDqwY4vFNOWO_9EP5b6i-AU&ip=216.131.76.113&id=o-AKk_7_kCuTeIZ1-9if3CUTD0CghiCidhI82SEAeTONZ4&itag=140&source=youtube&requiressl=yes&mh=oD&mm=31%2C26&mn=sn-vgqsknsk%2Csn-q4flrney&ms=au%2Conr&mv=m&mvi=1&pl=23&initcwndbps=2137500&spc=zIddbHjBh9Lv-WHYY0uSDAf_pWRth0g&vprv=1&mime=audio%2Fmp4&ns=UGq4-sJUTh1Ra92gvSCKLrwK&gir=yes&clen=1530562&dur=94.528&lmt=1642670101399420&mt=1672493338&fvip=5&keepalive=yes&fexp=24001373%2C24007246&c=WEB&txp=5432434&n=KVevukJSrcyNyg&sparams=expire%2Cei%2Cip%2Cid%2Citag%2Csource%2Crequiressl%2Cspc%2Cvprv%2Cmime%2Cns%2Cgir%2Cclen%2Cdur%2Clmt&sig=AOq0QJ8wRQIgL3OrwrBDGwTlYiURNmMrf8t8rz1bWHPzOrXhe4-TopsCIQDqaL_-Xp4hWIm59uQ8MiXaPDmHEIBMncfn592vlWv3WA%3D%3D&lsparams=mh%2Cmm%2Cmn%2Cms%2Cmv%2Cmvi%2Cpl%2Cinitcwndbps&lsig=AG3C_xAwRQIgctNB7V_1yaG8TJ7bjPRJBGrOBJMuRFCv1pVUWJIiYUACIQC_ileTSYmdREKRqefpvOkOexlEUaElYhEVg6AWzWh0aA%3D%3D");
	print_r($bot->result());
?>