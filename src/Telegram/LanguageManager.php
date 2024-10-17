<?php

namespace App\Telegram;

use stdClass;

class LanguageManager {
    private array $translations = [];
    private string $lang;
    public function __construct () {
        $files = scandir(basePath('resources/languages'));
        $files = array_diff($files, array('.', '..'));
        foreach ($files as $file) {
            $this->translations[str_replace('.json','',$file)] = $this->loadTranslation($file);
        }
    }

    public function loadTranslation (string $lang):stdClass {
        $source = basePath('resources/languages/');
        return (json_decode(file_get_contents($source . $lang)) ? : json_decode(file_get_contents($source . 'ru.json')));
    }
    public function setLang (string $lang): string {
        return $this->lang = $lang;
    }
    public function getText (string $key, ?array $replace=[]):string|stdClass {
        if (count($replace)){
            return str_replace(array_keys($replace),array_values($replace),$this->translations[$this->lang]->$key);
        }
        return $this->translations[$this->lang]->$key;
    }
}