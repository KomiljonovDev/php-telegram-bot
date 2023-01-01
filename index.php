<?php
	
	require 'Telegram/TelegramBot.php';

	use TelegramBot as Bot;
	$dataSet = ['botToken'=>''];

	$bot = new Bot($dataSet);
	$bot->sendChatAction('typing', 931026030)->sendDocument('https://rr1---sn-cxxapox01-u5nz.googlevideo.com/videoplayback?expire=1672572962&ei=whuxY7_vDtOY2_gP_Zqs2Ao&ip=216.131.77.202&id=o-AALaNXn8AjHvnBZIzyYnH4ANOAzJuqal8ayOXFE73yZ4&itag=18&source=youtube&requiressl=yes&spc=zIddbB50EvOkSDoRLEliyqANO-JwvA8&vprv=1&mime=video%2Fmp4&ns=hVw2jj8mACVBPedYYlpB1WkK&cnr=14&ratebypass=yes&dur=908.062&lmt=1649976500257249&fexp=24001373,24007246&c=WEB&txp=5538434&n=owoXaxAsRxYArw&sparams=expire%2Cei%2Cip%2Cid%2Citag%2Csource%2Crequiressl%2Cspc%2Cvprv%2Cmime%2Cns%2Ccnr%2Cratebypass%2Cdur%2Clmt&sig=AOq0QJ8wRgIhAIvY1dBkwx4KkC5WvMTkkOdU7L0iXZn6qvDkrWFkc_FTAiEA6PHdGAEZH41G-IWB-pad2acEP_3NOguWYrsiDm-HEXc%3D&redirect_counter=1&rm=sn-vgqes77s&req_id=2d7a239dc469a3ee&cms_redirect=yes&cmsv=e&ipbypass=yes&mh=70&mip=95.214.211.44&mm=31&mn=sn-cxxapox01-u5nz&ms=au&mt=1672551209&mv=m&mvi=1&pcm2cms=yes&pl=24&lsparams=ipbypass,mh,mip,mm,mn,ms,mv,mvi,pcm2cms,pl&lsig=AG3C_xAwRQIhAP8fDkEK0NADndi_JNXm8VBFjBNQJrdLa7JIkoMGgyx_AiAwINPctjW67a33pbPsjq61IUvwgekIG6Db_tjVHzM8Dg%3D%3D');
	print_r($bot->result());
?>