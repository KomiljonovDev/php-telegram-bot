<?php

	// namespace KomiljonovDev\TelegramBot;

	class TelegramErrorHandler extends Exception
	{
		function __construct($message)
		{
			echo $message, "\n\nUshbu xatolik, " . $this->file . " faylida, " . $this->line . "-qatorda.\n";
		}
	}

	/**
	 * 
	 * @author KomiljonovDev
	 * 
	 */

	class TelegramBot
	{
		private $apiUrl = 'https://api.telegram.org';
		private $botUrl = '/bot';
		private $fileUrl = '/file';

		private $botToken = null;
		private $showErrors = true;
		private $saveLogs = true;
		private $withProgress = true;
		private $logUrl = 'Telegram/uploads/logs/error.log';
		private $tmpUrl = 'Telegram/uploads/tmp/';
		private $parse_mode = 'html';
		private $settings = [];

		private $chat_id;
		private $message_id;
		private $result;
		private $request;
		private $reply_markup;

		private $forProgress;

		function __construct($dataSet)
		{
			if (count($dataSet)) {
				$this->settings = $dataSet;
				foreach ($dataSet as $key => $value) {
					$this->$key = $value;
				}
			}
			if (!$this->botToken) {
				new TelegramErrorHandler('Bot tokenni kiriting!');
			}
		}

		public function reset($resetKey = false)
		{
			/**
			 * 
			 * Aynan bir ma'lumotni o'zinigina tozalamoqchi bo'lingan
			 * holatda, tozalanishi kerak bo'lgan ma'lumot parametrda
			 * beriladi,agar berilmasa barcha ma'lumotlar tozalanadi
			 *
			*/
			
			if ($resetKey) {
				$this->$resetKey = null;
				return $this;
			}
			$this->chat_id = null;
			$this->result = null;
			$this->request = null;
			$this->reply_markup = null;

			return $this;
		}

		public function request($action, $content = [])
		{
			if (!is_null($this->reply_markup)) {
				$content['reply_markup'] = $this->reply_markup;
			}
			if (!is_null($this->reply_markup) && array_key_exists('inline_keyboard', $content['reply_markup']) && count($content['reply_markup']['inline_keyboard']) > 0) {
				unset($content['reply_markup']['keyboard']);
			}

			$this->saveLogs($content);

			$url = $this->apiUrl . $this->botUrl .  $this->botToken . '/' . $action;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			curl_close($ch);
			$this->request = json_decode($result);
			if ($this->showErrors && !$this->request->ok) {
				new TelegramErrorHandler($this->request->description);
			}
			return $this->request;
		}

		public function setWebHook($url,$certificate = null)
		{
			if (filter_var($url,FILTER_VALIDATE_URL) === false) {
				if ($this->showErrors) {
					new TelegramErrorHandler("Noto'g'ri Url kiritildi.");
				}
				return false;
			}
			if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
				if ($this->showErrors) {
					new TelegramErrorHandler("URL Noto'g'ri, https kabi bo'lishligi zarur!");
				}
				return false;
			}
			$this->request('setWebHook', compact('url', 'certificate'));
			return $this;
		}

		public function setInlineKeyBoard($keyboard = [])
		{
			if (!count($keyboard)) {
				if ($this->showErrors) {
					new TelegramErrorHandler("Keyboard bo'sh bo'lmasligi zarur!");
				}
				return false;
			}
			$this->reply_markup['inline_keyboard'] = $keyboard;
			return $this;
		}

		public function setReplyKeyboard($keyboard,$resize_keyboard = true, $remove_keyboard = false)
		{
			$this->reply_markup['keyboard'] = $keyboard;
			$this->reply_markup['resize_keyboard'] = $resize_keyboard;
			$this->reply_markup['remove_keyboard'] = $remove_keyboard;
			$this->reply_markup['input_field_placeholder'] = 'Ishlanmoqda...';
			return $this;
		}

		public function removeInlineKeyboard()
		{
			unset($this->reply_markup['inline_keyboard']);
			return $this;
		}

		public function removeReplyKeyboard()
		{
			unset($this->reply_markup['keyboard']);
			unset($this->reply_markup['resize_keyboard']);
			unset($this->reply_markup['remove_keyboard']);
			unset($this->reply_markup['input_field_placeholder']);
			return $this;
		}

		public function sendChatAction($action, $chat_id = null)
		{
			$chat_id = $chat_id ? $this->chat_id = $chat_id : $this->chat_id;
			$actions = array(
				'typing',
				'upload_photo',
				'upload_video',
				'upload_voice',
				'upload_document',
				'choose_sticker',
				'find_location',
			);
			if (isset($action) && in_array($action, $actions)) {
				$this->result = $this->request('sendChatAction', compact('chat_id', 'action'));
				return $this;
			}
			new TelegramErrorHandler('action topilmadi!');
		}

		public function sendMessage($text, $chat_id = null, $parse_mode = null)
		{
			$content['text'] = $text;
			$content['chat_id'] = (!is_null($chat_id)) ? $chat_id : $this->chat_id;
			$content['parse_mode'] = (!is_null($parse_mode)) ? $parse_mode : $this->parse_mode;
			$this->result = $this->request('sendMessage', $content);
			return $this;
		}

		public function editMessageText($text, $message_id = null, $chat_id = null, $parse_mode = null)
		{
			$content['text'] = $text;
			$content['chat_id'] = $chat_id ? $chat_id : $this->chat_id;
			$content['message_id'] = $message_id ? $message_id : $this->result->result->message_id;
			$content['parse_mode'] = $parse_mode ? $parse_mode : $this->parse_mode;
			$this->result = $this->request('editMessageText', $content);
			return $this;
		}

		public function sendPhoto($photo, $caption = null, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$caption = $caption ? $caption : '';
			$data = compact('chat_id', 'photo', 'caption');
			if (!file_exists($photo) && filter_var($photo, FILTER_VALIDATE_URL)) {
				$this->result = $this->request('sendPhoto', $data);
				if ($this->result->ok) {
					return $this;
				}
			}
			$this->result = $this->uploadFile('sendPhoto', $data);
			return $this;
		}

		public function sendVideo($video, $caption = null, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$caption = $caption ? $caption : '';
			$data = compact('chat_id', 'video', 'caption');
			if (!file_exists($video) && filter_var($video, FILTER_VALIDATE_URL)) {
				$this->result = $this->request('sendVideo', $data);
				if ($this->result->ok) {
					return $this;
				}
			}
			$this->result = $this->uploadFile('sendVideo', $data);
			return $this;
		}

		public function sendAudio($audio, $caption = null, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$caption = $caption ? $caption : '';
			$data = compact('chat_id', 'audio', 'caption');
			if (!file_exists($audio) && filter_var($audio, FILTER_VALIDATE_URL)) {
				$this->result = $this->request('sendAudio', $data);
				if ($this->result->ok) {
					return $this;
				}
			}
			$this->result = $this->uploadFile('sendAudio', $data);
			return $this;
		}

		public function sendDocument($document, $caption = null, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$caption = $caption ? $caption : '';
			$data = compact('chat_id', 'document', 'caption');
			if (!file_exists($document) && filter_var($document, FILTER_VALIDATE_URL)) {
				$this->result = $this->request('sendDocument', $data);
				if ($this->result->ok) {
					return $this;
				}
			}
			$this->result = $this->uploadFile('sendDocument',$data);
			return $this;
		}

		public function sendLocation($latitude, $longitude, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$data = compact('chat_id', 'latitude', 'longitude');
			$this->result = $this->request('sendLocation', $data);
			return $this;
		}

		public function sendContact($phone_number, $first_name, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$data = compact('chat_id', 'phone_number', 'first_name');
			$this->result = $this->request('sendContact', $data);
			return $this;
		}

		public function uploadFile($action, $content=[])
		{
			$this->sendMessage('Iltimos biroz kuting...');
			$methods = array(
				'sendPhoto'=>'photo',
				'sendAudio'=>'audio',
				'sendDocument'=>'document',
				'sendVideo'=>'video'
			);
			if (filter_var($content[$methods[$action]], FILTER_VALIDATE_URL)) {
				
				$file = $this->tmpUrl . rand(0, 10000);
				$byUrl = true;
				file_put_contents($file, file_get_contents($content[$methods[$action]]));
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
    			$mime_type = finfo_file($finfo, $file);
    			$extensions = array(
	                'image/jpeg' => '.jpg',
	                'image/png' => '.png',
	                'image/gif' => '.gif',
	                'image/bmp' => '.bmp',
	                'image/tiff' => '.tif',
	                'audio/ogg' => '.ogg',
	                'audio/mpeg' => '.mp3',
	                'video/mp4' => '.mp4',
	                'image/webp' => '.webp'
	            );
	            if (strtolower($action) != 'senddocument') {
	            	if (!array_key_exists($mime_type, $extensions)) {
	            		unlink($file);
	            		new TelegramErrorHandler("Noto'g'ri file turi kiritildi!");
	            		return;
	            	}
	            }
            	$newFile = $file . $extensions[$mime_type];
            	rename($file, $newFile);
            	$content[$methods[$action]] = new CURLFile($newFile, $mime_type, $newFile);
			}else{
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime_type = finfo_file($finfo, $content[$methods[$action]]);
				$newFile = $content[$methods[$action]];
				$content[$methods[$action]] = new CURLFile($content[$methods[$action]], $mime_type, $content[$methods[$action]]);
			}

			$url = $this->apiUrl . $this->botUrl .  $this->botToken . '/' . $action;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
			curl_setopt($ch, CURLOPT_NOPROGRESS, ($this->withProgress) ? false : true);
			curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $downloaded, $download_size, $upload_size, $uploaded) use($action, $content)
			{
				$this->uploadProgress($resource, $downloaded, $download_size, $upload_size, $uploaded, $action, $content);
			});
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);

			$this->request = json_decode($result, true);

			if (isset($byUrl)) unlink($newFile);

			if ($this->showErrors && !$this->result->ok) {
				new TelegramErrorHandler($this->result->description);
			}

			return $this->request;
		}

		public function uploadProgress($resource, $downloaded, $download_size, $upload_size, $uploaded,  $action, $content)
		{
			if (is_null($this->forProgress)) {
				$this->forProgress = microtime(true) + 1;
			}
			if ($this->forProgress <= microtime(true)) {
				$actions = array(
					'sendPhoto' => 'upload_photo',
					'sendAudio' => 'upload_audio',
					'sendVoice' => 'upload_voice',
					'sendDocument' => 'upload_document',
					'sendVideo' => 'upload_video'
				);
				if (array_key_exists($action, $actions)) {
					$action = $actions[$action];
				}else{
					$action = 'typing';
				}

				$this->message_id = $this->message_id ? $this->message_id : $this->result('message_id');

				$this->sendChatAction($action, $content['chat_id']);
				$this->editMessageText("Yuklanmoqda: " . round(100 * $uploaded / $upload_size, 0) . "%", $this->message_id);

				$this->forProgress = microtime(true) + 1;
			}
		}

		public function getMe()
		{
			$this->result = $this->request('getMe');
			return $this->result();
		}

		public function result($key = null)
		{
			if (!is_null($key)) {
				if (property_exists($this->result, 'result')) {
					if (property_exists($this->result->result, $key)) {
						return $this->result->result->$key;
					}
					return false;
				}
				return $this->result->result;
			}
			return $this->result;
		}

		public function saveLogs($content)
		{
			if ($this->saveLogs) {
				if (is_array($content)) $content = json_encode($content);
				$file = fopen($this->logUrl, 'a');
				fwrite($file, $content . "\n");
				fclose($file);
			}
		}

	}


?>