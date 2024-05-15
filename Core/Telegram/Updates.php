<?php

$update = json_decode(file_get_contents('php://input'));
if (isset($update)) {
    if (isset($update->message)) {
        $message = $update->message;
        $chat_id = $message->chat->id;
        $type = $message->chat->type;
        $miid =$message->message_id;
        $name = $message->from->first_name;
        $lname = $message->from->last_name;
        $full_name = $name ."". $lname;
        $full_name = html($full_name);
        $user = $message->from->username ?? '';
        $fromid = $message->from->id;
        $text = html($message->text);
        $title = $message->chat->title;
        $chatuser = $message->chat->username;
        $chatuser = $chatuser ? $chatuser :"Shaxsiy Guruh!";
        $caption = $message->caption;
        $entities = $message->entities;
        $entities = $entities[0];
        $left_chat_member = $message->left_chat_member;
        $new_chat_member = $message->new_chat_member;
        $photo = $message->photo;
        $video = $message->video;
        $audio = $message->audio;
        $voice = $message->voice;
        $reply = $message->reply_markup;
        $fchat_id = $message->forward_from_chat->id;
        $fid = $message->forward_from_message_id;
    }else if(isset($update->callback_query)){
        $callback = $update->callback_query;
        $qid = $callback->id;
        $mes = $callback->message;
        $mid = $mes->message_id;
        $cmtx = $mes->text;
        $cid = $callback->message->chat->id;
        $ctype = $callback->message->chat->type;
        $cbid = $callback->from->id;
        $cbuser = $callback->from->username;
        $data = $callback->data;
    }
}
?>