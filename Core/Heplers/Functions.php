<?php

use Core\database\Connector;

function html($text){
    return str_replace(['<','>'],['&#60;','&#62;'],$text);
}
function removeBotUserName($text){
    return explode('@', $text)[0];
}
function isAdmin ($fromid, Connector $Connection) {
    return (bool) $Connection->selectWhere(env('admins_table'), [['fromid' => $fromid, 'cn' => '=']])->rowCount();
}
function myUser ($fromid, Connector $Connection) {
    $users_table = env('users_table');
    $user = $Connection->selectWhere($users_table, [['fromid' => $fromid, 'cn' => '=']])->fetch();
    if ($user && $user['deleted_at']!==null) {
        $Connection->updateWhere($users_table, ['deleted_at' => NULL], ['fromid' => $fromid, 'cn' => '=']);
    }
    return (bool)count($user);
}