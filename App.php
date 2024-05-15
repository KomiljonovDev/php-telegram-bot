<?php

$keyBoard = [
    [
        ['text'=>'Bot haqida', 'callback_data'=>'about'],
        ['text'=>'Yordam', 'callback_data'=>'support'],
    ],
    [
        ['text'=>'Bizning kanallar', 'callback_data'=>'channels'],
        ['text'=>'Ariza qoldirish', 'callback_data'=>'feedback'],
    ],
];

if (isset($update)){
    if (isset($update->message)){
        if ($text == '/start'){
            if (!myUser($fromid, $db)){
                $db->insertInto('users',[
                    'fromid'=>$fromid
                ]);
            }
            $bot->sendChatAction('typing', $fromid)->setReplyKeyboard($keyBoard)->sendMessage('Assalomu alaykum');
        }
    }
}
