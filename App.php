<?php

use App\database\Models\Admin;
use App\database\Models\User;
use App\Telegram\LanguageManager;
use App\Telegram\Handler;


$bot = new Handler(['botToken' => $_ENV['BOT_TOKEN']]);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$lang = new LanguageManager();
$lang->setLang('en');

$update = $bot->getWebhookUpdates();
$callback = $bot->update('callback_query');
if (isset($update->message)) {
    $text = $bot->update('text');
    $fromId = $bot->update('from')->id;
    $lang = $bot->update('from')->language_code;
    // Handle start command
    if ($text == '/start') {
        // Use with OOP style
        $bot->startHandler($fromId);
        // Or use like this syntax
//        $keyBoard = [
//            [
//                ['text' => 'Bot haqida', 'callback_data' => 'about'], ['text' => 'Yordam', 'callback_data' => 'support'],],
//                [['text' => 'Bizning kanallar', 'callback_data' => 'channels'], ['text' => 'Ariza qoldirish', 'callback_data' => 'feedback'],
//            ]
//        ];
//        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($keyBoard)->sendMessage("Xush kelibsiz");
        return;
    }
    if ($text == '/lang') {
        $bot->handleLangCommand($fromId);
        return;
    }

    # Admin panel
    $user = User::where('fromid', $fromId)->first();
    if ($admin = Admin::isAdmin($user->id)) {
        if ($text == '/admin') {
            $bot->handleAdminCommand($fromId, $admin->role_id);
            return;
        }
        # Admin messages
        if ($redis->exists('admin_menu:' . $fromId)) {
            # Channel logic
            if ($redis->get('admin_menu:' . $fromId) == 'addChannel') {
                $bot->handleAddChannelMessage($redis, $fromId);
                return;
            }
            # Admin logic
            if ($redis->get('admin_menu:' . $fromId) == 'addAdmin') {
                $bot->handleAddAdminMessage($redis, $fromId);
                return;
            }
        }
    }
}
if ($callback) {
    $data = $bot->update('data');
    $fromId = $bot->update('from')->id;
    $messageId = $bot->update('message')->message_id;
    if ($data == 'add_order') {
        $bot->handleAddOrderCallback($fromId);
        return;
    }
    if ($data == 'orders') {
        $bot->handleOrdersCallback($fromId);
        return;
    }
    if ($data == 'balance') {
        $bot->handleBalanceCallback($fromId);
        return;
    }
    if ($data == 'help') {
        $bot->handleAboutCallback($fromId);
        return;
    }
    if ($data == 'partnership') {
        $bot->handlePartnershipCallback($fromId);
        return;
    }

    # end of callback logic
    if (mb_stripos($data, 'language_') !== false) {
        $bot->handleUpdateLangCallback($fromId, $data);
        return;
    }

    # Admin panel
    $user = User::where('fromid', $fromId)->first();
    if (Admin::isAdmin($user->id)) {
        # Channel logic
        if ($data == 'add_channel') {
            $bot->handleAddChannelCallback($fromId, $messageId, $redis);
            return;
        }
        if ($data == 'remove_channel') {
            $bot->handleRemoveChannelListCallback($fromId, $messageId);
        }
        if ($data == 'on_of_channel') {
            $bot->handleChannelOnOfCallback($fromId, $messageId);
        }
        if (mb_stripos($data, 'tgg_ch_') !== false) {
            $bot->handleToggleChannelCallback($fromId, $messageId, $data);
        }
        if ($data == 'inactive_all_channel') {
            $bot->handleInActiveAllChannelCallback($fromId, $messageId);
        }

        # Admin logic

        if ($data == 'add_admin') {
            $bot->handleAddAdminCallback($fromId, $messageId, $redis);
        }
        if ($data == 'remove_admin') {
            $bot->handleRemoveAdminListCallback($fromId, $messageId);
        }
        # end of admin callback logic
        if (mb_stripos($data, 'rm_ch_') !== false) {
            $bot->handleRemoveChannelCallback($fromId, $messageId, $data);
        }
        if (mb_stripos($data, 'rm_ad_') !== false) {
            $bot->handleRemoveAdminCallback($fromId, $messageId, $data);
        }
    }
}