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
		private $logUrl = 'Telegram/uploads/logs/error.log';
		private $parse_mode = 'html';
		private $settings = [];

		private $chat_id;
		private $result;
		private $request;
		private $reply_markup;

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

		public function sendPhoto($photo, $caption = null, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$caption = $caption ? $caption : '';
			$data = compact('chat_id', 'photo', 'caption');
			if (!file_exists($photo) && filter_var($photo, FILTER_VALIDATE_URL)) {
				$this->result = $this->request('sendPhoto', $data);
				return $this;
			}
			// $this->result = $this->uploadFile();
			// return $this;
		}

		public function sendVideo($video, $caption = null, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$caption = $caption ? $caption : '';
			$data = compact('chat_id', 'video', 'caption');
			if (!file_exists($video) && filter_var($video, FILTER_VALIDATE_URL)) {
				$this->result = $this->request('sendVideo', $data);
				return $this;
			}
			// $this->result = $this->uploadFile();
			// return $this;
		}

		public function sendAudio($audio, $caption = null, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$caption = $caption ? $caption : '';
			$data = compact('chat_id', 'audio', 'caption');
			if (!file_exists($audio) && filter_var($audio, FILTER_VALIDATE_URL)) {
				$this->result = $this->request('sendAudio', $data);
				return $this;
			}
			// $this->result = $this->uploadFile();
			// return $this;
		}

		public function sendDocument($document, $caption = null, $chat_id = null)
		{
			$chat_id = $chat_id ? $chat_id : $this->chat_id;
			$caption = $caption ? $caption : '';
			$data = compact('chat_id', 'document', 'caption');
			if (!file_exists($document) && filter_var($document, FILTER_VALIDATE_URL)) {
				$this->result = $this->request('sendDocument', $data);
				return $this;
			}
			// $this->result = $this->uploadFile();
			// return $this;
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