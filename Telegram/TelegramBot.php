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
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
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

		public function sendMessage($text, $chat_id = null, $parse_mode = null)
		{
			$content['text'] = $text;
			$content['chat_id'] = (!is_null($chat_id)) ? $chat_id : $this->chat_id;
			$content['parse_mode'] = (!is_null($parse_mode)) ? $parse_mode : $this->parse_mode;
			$this->result = $this->request('sendMessage', $content);
			return $this;
		}

		public function result()
		{
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