<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>'5877866426:AAHOkyKw7xyvndcxPXEKjcuIXaMuKaB4uH4'];

	$bot = new Bot($dataSet);
	$bot->sendChatAction('typing', 931026030)->sendDocument('https://rr18---sn-n8v7kn7r.googlevideo.com/videoplayback?expire=1672595565&ei=DHSxY82lO4SG0u8P1KqCyAE&ip=185.104.212.44&id=o-AH9z7maU0JwvCm6cEB22GPfahWkCeNxqK0z-InvksJvK&itag=18&source=youtube&requiressl=yes&mh=70&mm=31%2C26&mn=sn-n8v7kn7r%2Csn-c0q7lnz7&ms=au%2Conr&mv=m&mvi=18&pl=22&initcwndbps=837500&spc=zIddbG8yuS8PfogBp1jvuz11hgIR2GI&vprv=1&mime=video%2Fmp4&ns=DGBwlDPOKmXsy_ZmcKD6YxgK&cnr=14&ratebypass=yes&dur=908.062&lmt=1649976500257249&mt=1672573521&fvip=1&fexp=24001373%2C24007246&c=WEB&txp=5538434&n=PEESRF7xGempYw&sparams=expire%2Cei%2Cip%2Cid%2Citag%2Csource%2Crequiressl%2Cspc%2Cvprv%2Cmime%2Cns%2Ccnr%2Cratebypass%2Cdur%2Clmt&sig=AOq0QJ8wRgIhAKXlzRBPYlvMOjDBea8jh0vZNkyHcHZlODxu_Njh9ichAiEAnbosWlRYQBV0v-mm9FiRSvR6sosUat3CSZhHO97AcE4%3D&lsparams=mh%2Cmm%2Cmn%2Cms%2Cmv%2Cmvi%2Cpl%2Cinitcwndbps&lsig=AG3C_xAwRgIhAMm2Gbr1mWBlFuSMHLhvb0CGbMv0wTF3g-QYco4CtjAHAiEAqYIpwXAK_kgmaELw4ZQuVcyYfHJf9m4pmyPMtKarI9o%3D');
	print_r($bot->result());
?>