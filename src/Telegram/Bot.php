<?php

/**
 *
 * @author KomiljonovDev
 *
 */

namespace App\Telegram;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use stdClass;

class Bot {

    private string $apiUrl = 'https://api.telegram.org';
    private string $botUrl = '/bot';
    private string $fileUrl = '/file';

    private string|null $botToken = null;
    private bool $showErrors = false;
    private bool $saveLogs = false;
    private bool $withProgress = false;
    private string $logUrl = 'resources/tmp/logs/error.log';
    private string $tmpUrl = 'resources/tmp/uploads/tmp/';
    private string $parse_mode = 'html';
    private array $settings = [];

    private ?int $chat_id;
    private int $message_id;
    private int $channel_id;
    private stdClass|null|array $result;
    private stdClass|null|array $request;
    private array|null $reply_markup = null;

    private string $forProgress;

    /**
     * @throws Exception
     */
    function __construct (array $dataSet) {
        $this->showErrors = $_ENV['DEBUG'];
        if (count($dataSet)) {
            $this->settings = $dataSet;
            foreach ($dataSet as $key => $value) {
                $this->$key = $value;
            }
        }
        if (!$this->botToken) {
            $this->exception('Bot tokenni kiriting');
        }
    }

    /**
     * @throws Exception
     */
    public function exception (string $message = null): Exception {
        return throw new Exception($message);
    }

    public function reset ($resetKey = false): Bot {
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

    /**
     * @throws GuzzleException
     */
    public function request (string $action, array $content = []): stdClass|null {
        $client = new Client(['base_uri' => $this->apiUrl . $this->botUrl . $this->botToken . '/', 'headers' => ['Content-Type' => 'application/json']]);

        if (!is_null($this->reply_markup)) {
            $content['reply_markup'] = $this->reply_markup;
        }

        if (!is_null($this->reply_markup) && array_key_exists('inline_keyboard', $content['reply_markup']) && count($content['reply_markup']['inline_keyboard']) > 0) {
            unset($content['reply_markup']['keyboard']);
        }

        $this->saveLogs($content);

        try {
            $response = $client->post($action, ['json' => $content, 'verify' => false]);

            $this->request = json_decode($response->getBody()->getContents());

            return $this->request;
        } catch (RequestException $e) {
            if ($this->showErrors) {
                $this->exception($e->getMessage());
            }
            return null;
        }
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function setWebHook (string $url, string $certificate = null): Bot|bool {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            if ($this->showErrors) {
                $this->exception("Noto'g'ri Url kiritildi.");
            }
            return false;
        }
        if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
            if ($this->showErrors) {
                $this->exception("URL Noto'g'ri, https kabi bo'lishligi zarur!");
            }
            return false;
        }
        $this->request('setWebHook', compact('url', 'certificate'));
        return $this;
    }

    /**
     * @throws Exception
     */
    public function setInlineKeyBoard (array $keyboard = []): Bot|bool {
        if (!count($keyboard)) {
            if ($this->showErrors) {
                $this->exception("Keyboard bo'sh bo'lmasligi zarur!");
            }
            return false;
        }
        $this->reply_markup['inline_keyboard'] = $keyboard;
        return $this;
    }

    public function setReplyKeyboard (array $keyboard, bool $resize_keyboard = true, bool $remove_keyboard = false): Bot {
        $this->reply_markup['keyboard'] = $keyboard;
        $this->reply_markup['resize_keyboard'] = $resize_keyboard;
        $this->reply_markup['remove_keyboard'] = $remove_keyboard;
        $this->reply_markup['input_field_placeholder'] = 'Ishlanmoqda...';
        return $this;
    }

    public function removeInlineKeyboard (): Bot {
        unset($this->reply_markup['inline_keyboard']);
        return $this;
    }

    public function removeReplyKeyboard (): Bot {
        unset($this->reply_markup['keyboard']);
        unset($this->reply_markup['resize_keyboard']);
        unset($this->reply_markup['remove_keyboard']);
        unset($this->reply_markup['input_field_placeholder']);
        return $this;
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function sendChatAction (string $action, int $chat_id = null): Bot|null {
        $chat_id = $chat_id ? $this->chat_id = $chat_id : $this->chat_id;
        $actions = array('typing', 'upload_photo', 'upload_video', 'upload_audio', 'upload_voice', 'upload_document', 'choose_sticker', 'find_location',);
        if (in_array($action, $actions)) {
            $this->result = $this->request('sendChatAction', compact('chat_id', 'action'));
            return $this;
        }
        $this->exception('action topilmadi!');
        return null;
    }

    public function sendMessage (string $text, int $chat_id = null, string $parse_mode = null): Bot {
        $content['text'] = $text;
        $content['chat_id'] = (!is_null($chat_id)) ? $chat_id : $this->chat_id;
        $content['parse_mode'] = (!is_null($parse_mode)) ? $parse_mode : $this->parse_mode;
        $this->result = $this->request('sendMessage', $content);
        return $this;
    }

    public function editMessageText (string $text, int $message_id = null, int $chat_id = null, string $parse_mode = null): Bot {
        $content['text'] = $text;
        $content['chat_id'] = $chat_id ?: $this->chat_id;
        $content['message_id'] = $message_id ?: $this->result->result->message_id;
        $content['parse_mode'] = $parse_mode ?: $this->parse_mode;
        $this->result = $this->request('editMessageText', $content);
        return $this;
    }

    public function deleteMessage (int $message_id = null, int $chat_id = null): Bot {
        $content['chat_id'] = $chat_id ?: $this->chat_id;
        $content['message_id'] = $message_id ?: $this->result->result->message_id;
        $this->result = $this->request('deleteMessage', $content);
        return $this;
    }

    public function sendPhoto (string $photo, string $caption = null, int $chat_id = null): Bot {
        $chat_id = $chat_id ?: $this->chat_id;
        $caption = $caption ?: '';
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

    public function sendVideo (string $video, string $caption = null, int $chat_id = null): Bot {
        $chat_id = $chat_id ?: $this->chat_id;
        $caption = $caption ?: '';
        $data = compact('chat_id', 'video', 'caption');
        $data['parse_mode'] = $this->parse_mode;

        if (!file_exists($video) && filter_var($video, FILTER_VALIDATE_URL)) {
            $this->result = $this->request('sendVideo', $data);
            if ($this->result->ok) {
                return $this;
            }
        }
        $this->result = $this->uploadFile('sendVideo', $data);
        return $this;
    }

    public function sendAudio (string $audio, string $caption = null, int $chat_id = null): Bot {
        $chat_id = $chat_id ?: $this->chat_id;
        $caption = $caption ?: '';
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

    public function sendDocument (string $document, string $caption = null, int $chat_id = null): Bot {
        $chat_id = $chat_id ?: $this->chat_id;
        $caption = $caption ?: '';
        $data = compact('chat_id', 'document', 'caption');
        $data['parse_mode'] = $this->parse_mode;

        if (!file_exists($document) && filter_var($document, FILTER_VALIDATE_URL)) {
            $this->result = $this->request('sendDocument', $data);
            if ($this->result->ok) {
                return $this;
            }
        }
        $this->result = $this->uploadFile('sendDocument', $data);
        return $this;
    }

    public function sendLocation (float $latitude, float $longitude, int $chat_id = null): Bot {
        $chat_id = $chat_id ?: $this->chat_id;
        $data = compact('chat_id', 'latitude', 'longitude');
        $this->result = $this->request('sendLocation', $data);
        return $this;
    }

    public function sendContact (int $phone_number, string $first_name, int $chat_id = null): Bot {
        $chat_id = $chat_id ?: $this->chat_id;
        $data = compact('chat_id', 'phone_number', 'first_name');
        $this->result = $this->request('sendContact', $data);
        return $this;
    }

    /**
     * @throws GuzzleException
     */
    public function uploadFile (string $action, array $content = []): stdClass|null {
        $methods = array('sendPhoto' => 'photo', 'sendAudio' => 'audio', 'sendDocument' => 'document', 'sendVideo' => 'video');

        $client = new Client(['base_uri' => $this->apiUrl . $this->botUrl . $this->botToken . '/', 'headers' => ['Content-Type' => 'multipart/form-data']]);

        if (filter_var($content[$methods[$action]], FILTER_VALIDATE_URL)) {
            $file = $this->tmpUrl . rand(0, 10000);
            $byUrl = true;
            file_put_contents($file, file_get_contents($content[$methods[$action]]));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file);
            $extensions = array('image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif', 'image/bmp' => '.bmp', 'image/tiff' => '.tif', 'audio/ogg' => '.ogg', 'audio/mpeg' => '.mp3', 'video/mp4' => '.mp4', 'image/webp' => '.webp');

            if (strtolower($action) != 'senddocument') {
                if (!array_key_exists($mime_type, $extensions)) {
                    unlink($file);
                    $this->exception("Noto'g'ri file turi kiritildi!");
                }
            }

            $newFile = $file . $extensions[$mime_type];
            rename($file, $newFile);
            $content[$methods[$action]] = $newFile;
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $content[$methods[$action]]);
            $newFile = $content[$methods[$action]];
        }

        $multipart = [['name' => $methods[$action], 'contents' => fopen($newFile, 'r'), 'filename' => basename($newFile), 'headers' => ['Content-Type' => $mime_type]]];

        foreach ($content as $key => $value) {
            if ($key !== $methods[$action]) {
                $multipart[] = ['name' => $key, 'contents' => $value];
            }
        }

        try {
            $response = $client->post($action, ['multipart' => $multipart, 'progress' => function ($downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes) use ($action, $content) {
                $this->uploadProgress(null, $downloadedBytes, $downloadTotal, $uploadTotal, $uploadedBytes, $action, $content);
            }]);

            $this->request = json_decode($response->getBody()->getContents());

            if (isset($byUrl)) unlink($newFile);

            if ($this->showErrors && !$this->request->ok) {
                $this->exception($this->request->description);
            }

            return $this->request;
        } catch (RequestException $e) {
            if ($this->showErrors) {
                $this->exception($e->getMessage());
            }
            return null;
        }
    }

    public function uploadProgress ($resource, $downloaded, $download_size, $upload_size, $uploaded, $action, $content): float|string|bool {
        if (is_null($this->forProgress)) {
            $this->forProgress = microtime(true) + 1;
        }
        if ($this->forProgress <= microtime(true)) {
            $actions = array('sendPhoto' => 'upload_photo', 'sendAudio' => 'upload_audio', 'sendVoice' => 'upload_voice', 'sendDocument' => 'upload_document', 'sendVideo' => 'upload_video');
            if (array_key_exists($action, $actions)) {
                $action = $actions[$action];
            } else {
                $action = 'typing';
            }
            $this->sendChatAction($action, $content['chat_id']);
            $this->editMessageText("Yuklanmoqda: " . round(100 * $uploaded / $upload_size) . "%", $this->message_id);

            return $this->forProgress = microtime(true) + 1;
        }
        return false;
    }

    public function setMessageId (int $message_id): Bot {
        $this->message_id = $message_id;
        return $this;
    }

    public function setChatId (int $chat_id): Bot {
        $this->chat_id = $chat_id;
        return $this;
    }

    public function setChannelId (int $channel_id): Bot {
        $this->channel_id = $channel_id;
        return $this;
    }

    public function getMe (): stdClass {
        $this->result = $this->request('getMe');
        return $this->result();
    }

    public function getChat (int|string $chat_id = null): bool|stdClass {
        $chat_id = $chat_id ?: $this->channel_id;
        $this->result = $this->request('getChat', compact('chat_id'));
        return $this->result();
    }

    public function getChatMember (int|string $user_id = null, int|string $chat_id = null): Bot {
        $user_id = $user_id ?: $this->chat_id;
        $chat_id = $chat_id ?: $this->channel_id;
        $this->result = $this->request('getChatMember', compact('chat_id', 'user_id'));
        return $this;
    }

    public function getFile (string $file_id): Bot {
        $this->result = $this->request('getFile', compact('file_id'));
        return $this;
    }

    public function getFilePath (string $file_id): bool|string {
        $this->request('getFile', compact('file_id'));
        if ($this->request->ok) {
            $file_path = $this->apiUrl . $this->fileUrl . $this->botUrl . $this->botToken . '/' . $this->request->result->file_path;
            if ($this->httpResponseCode($file_path) == '200') {
                return file_get_contents($file_path);
            }
            return false;
        }
        return false;
    }

    public function getUpdates (int $offset = null): stdClass {
        return $this->request('getUpdates', compact('offset'));
    }

    public function update (?string $key=null, ?stdClass $obj=null):mixed {
        if (is_null($key)){
            return $this->getWebhookUpdates();
        }
        if (is_null($obj)){
            $obj = $this->getWebhookUpdates();
            if (isset($obj->$key)){
                return $obj->$key;
            }
        }
        if (isset($obj->$key)){
            return $obj->$key;
        }
        if (is_object($obj)){
            foreach ($obj as $value) {
                if (is_object($value)){
                    return $this->update($key, $value);
                }
            }
        }
        return null;
    }

    public function getWebhookUpdates (): ?stdClass {
        return json_decode(file_get_contents('php://input'));
    }

    public function getWebhookInfo (): stdClass {
        return $this->request('getWebhookInfo');
    }

    public function result (string $key = null): stdClass|bool {
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

    public function saveLogs (array|string $content): void {
        if ($this->saveLogs) {
            if (is_array($content)) $content = json_encode($content);
            $file = fopen($this->logUrl, 'a');
            fwrite($file, $content . "\n");
            fclose($file);
        }
    }

    public function httpResponseCode (string $url): string {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    private function settings (): array {
        return $this->settings;
    }
}