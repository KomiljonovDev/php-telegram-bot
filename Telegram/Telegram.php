<?php

	class TelegramErrorHandler extends Exception
	{
		
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
		private $logUrl = 'uploads/logs/error.log';
		private $parseMode = 'html';
		private $settings = [];

		private $chat_id;
		private $result;
		private $request;
		private $reply_markup;

		function __construct($dataSet)
		{
			if (!$this->botToken) {
				new TelegramErrorHandler('Bot tokenni kiriting!');
			}
			if (count($dataSet)) {
				$this->settings = $dataSet;
				foreach ($dataSet as $key => $value) {
					$this->$key = $value;
				}
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
			$url = $this->apiUrl . $botUrl . $botToken . '/' . $action;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			curl_close($ch);
			$this->request = json_decode($result);
			if ($this->showErrors && !$this->request['ok']) {
				new TelegramErrorHandler($this->request['description']);
			}
			return $this->request;
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