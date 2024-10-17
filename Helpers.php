<?php

use App\database\Models\Admin;
function html(string $text):string{
    return str_replace(['<','>'],['&#60;','&#62;'],$text);
}
function removeBotUserName(string $text):string{
    return explode('@', $text)[0];
}
function isAdmin (int $fromId): bool {
    return (bool) Admin::where($_ENV['admins_table'], [['fromid' => $fromId, 'cn' => '=']])->first();
}
function basePath (string $path=''): string {
    return __DIR__ . ($path ? '/' . $path : $path);
}