<?php

namespace App\Telegram;

use App\database\Models\Admin;
use App\database\Models\AdminRole;
use App\database\Models\Channel;
use App\database\Models\Role;
use App\database\Models\User;
use Redis;

class Handler extends Bot {
    public function getLang (int $fromId, ?string $default = null): ?string {
        $user = User::where('fromid', $fromId)->first();
        if ($user) {
            return $user->language_code ?: $default;
        }
        return $default;
    }
    public function prepareHomeButtons (LanguageManager $lang): array {
        $button_texts = $lang->getText('start_buttons');
        return [[['text' => $button_texts->add_order, 'callback_data' => 'add_order'],], [['text' => $button_texts->orders, 'callback_data' => 'orders'], ['text' => $button_texts->help, 'callback_data' => 'help'],], [['text' => $button_texts->balance, 'callback_data' => 'balance'], ['text' => $button_texts->partnership, 'callback_data' => 'partnership']]];
    }
    public function prepareCancelButtons (LanguageManager $lang): array {
        $button_texts = $lang->getText('cancel_buttons');
        return [['text' => $button_texts->cancel, 'callback_data' => 'cancel'], ['text' => $button_texts->back, 'callback_data' => 'back'],];
    }
    public function startHandler (int $fromId): void {
        $lang = new LanguageManager();
        $lang->setLang($this->getLang($fromId, $this->update('language_code')));
        $keyBoard = $this->prepareHomeButtons($lang);
        User::myUser($fromId);
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($keyBoard)->sendMessage($lang->getText('start'));
    }
    public function handleLangCommand (int $fromId): void {
        $lang = new LanguageManager();
        $lang->setLang($this->getLang($fromId, $this->update('language_code')));
        $keyBoard = $this->prepareCancelButtons($lang);

        $files = scandir(basePath('resources/languages'));
        $files = array_diff($files, array('.', '..'));
        $buttons = [];
        foreach ($files as $file) {
            $buttons[] = ['text' => str_replace('.json', '', $file), 'callback_data' => 'language_' . str_replace('.json', '', $file)];
        }
        $buttons = array_chunk($buttons, 2);
        $buttons[] = $keyBoard;
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($buttons)->sendMessage($lang->getText('choose_lang'));
    }
    public function handleAboutCallback (int $fromId): void {
        $lang = new LanguageManager();
        $lang->setLang($this->getLang($fromId, $this->update('language_code')));
        $keyBoard = $this->prepareHomeButtons($lang);
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($keyBoard)->editMessageText($lang->getText('help'), $this->update('message')->message_id);
    }
    public function handleAddOrderCallback (int $fromId): void {
        $lang = new LanguageManager();
        $lang->setLang($this->getLang($fromId, $this->update('language_code')));
        $keyBoard[] = $this->prepareCancelButtons($lang);
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($keyBoard)->editMessageText($lang->getText('add_order'), $this->update('message')->message_id);
    }
    public function handleBalanceCallback (int $fromId): void {
        $lang = new LanguageManager();
        $lang->setLang($this->getLang($fromId, $this->update('language_code')));
        $keyBoard[] = $this->prepareCancelButtons($lang);
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($keyBoard)->editMessageText($lang->getText('my_balance', ['$balance' => 2.3]), $this->update('message')->message_id);
    }
    public function handleOrdersCallback (int $fromId): void {
        $lang = new LanguageManager();
        $lang->setLang($this->getLang($fromId, $this->update('language_code')));
        $keyBoard[] = $this->prepareCancelButtons($lang);
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($keyBoard)->editMessageText($lang->getText('my_orders'), $this->update('message')->message_id);
    }
    public function handlePartnershipCallback (int $fromId): void {
        $lang = new LanguageManager();
        $lang->setLang($this->getLang($fromId, $this->update('language_code')));
        $keyBoard = $this->prepareHomeButtons($lang);
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($keyBoard)->editMessageText($lang->getText('partnership'), $this->update('message')->message_id);
    }
    public function handleUpdateLangCallback (int $fromId, $data): void {
        $lang = explode('language_', $data)[1];
        User::where('fromid', $fromId)->update(['language_code' => $lang]);
        $this->startHandler($fromId);
    }


    # Admin panel
    public function prepareAdminHomeButtons (): array {
        return [
            [
                ['text'=>"Majburoy azolik on/off", 'callback_data'=>"on_of_channel"],
            ],
            [
                ['text' => "Kanal qo'shish", 'callback_data' => 'add_channel'],
                ['text' => "Kanal o'chirish", 'callback_data' => 'remove_channel'],
            ],
            [
                ['text' => "Admin qo'shish", 'callback_data' => 'add_admin'],
                ['text' => "Admin o'chirish", 'callback_data' => 'remove_admin'],
            ]
        ];
    }
    public function prepareAdminCancelButtons (): array {
        return [
            [
                ['text' => "Bekor qilish", 'callback_data' => 'admin_cancel'],
                ['text' => "Bosh sahifa", 'callback_data' => 'admin_home'],
            ]
        ];
    }
    public function handleAdminCommand (int $fromId, int $role_id): void {
        $role = Role::where('id', $role_id)->first();
        if ($role->name == AdminRole::superAdmin->value) {
            $users = User::count('id');
            $inactive_users = User::where('deleted_at', '>', 'NOW()')->count('id');
            $active_users = $users->id - isset($inactive_users->deleted_at) ?: 0;
            $responseText = "super admin\n\nhamma userlar:" . $users->id . "\nActive: " . $active_users;
            $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($this->prepareAdminHomeButtons())->sendMessage($responseText);
            return;
        }
        if ($role->name == AdminRole::Admin->value) {
            $this->sendMessage('oddiy admin', $fromId);
            return;
        }
        $this->sendMessage('Siz adminsiz, ammo bu yerga sizga ruxsat mavjud emas!', $fromId);
    }
    # Channel logic
    /*
     * TODO: add channel when forwarded message
     */
    public function handleAddChannelCallback (int $fromId,int $messageId, Redis $redis): void {
        $redis->set('admin_menu:' . $fromId, 'addChannel');
        $this->sendChatAction('typing', $fromId)
            ->setInlineKeyBoard(
                $this->prepareAdminCancelButtons()
            )->editMessageText("Kanal qo'shish uchun kanal username, id'si yoki kanal habaridan forward yuboring\n\n", $messageId);
    }
    public function handleAddChannelMessage (Redis $redis, int $fromId): void {
        if (isset($this->update('message')?->text)){
            $text = $this->update('message')?->text;
            if (is_numeric($text)){
                $result = $this->getChat(trim($text));
                if (!$result->ok){
                    $this->sendChatAction('typing', $fromId)
                        ->setInlineKeyBoard(
                            $this->prepareAdminCancelButtons()
                        )->sendMessage("Bot kanal adminstratori emas!");
                    return;
                }
                Channel::create(['channel_id'=>$result->result->id]);
            }elseif (mb_stripos($text, '@') !== false) {
                $result = $this->getChat(trim($text));
                if (!$result->ok){
                    $this->sendChatAction('typing', $fromId)
                        ->setInlineKeyBoard(
                            $this->prepareAdminCancelButtons()
                        )->sendMessage("Bot kanal adminstratori emas!");
                    return;
                }
                Channel::create(['channel_id'=>$result->result->id]);
            }
        }
        if ($forward = $this->update('forward_from_chat')){
            $result = $this->getChat(trim($forward->id));
            if (!$result->ok){
                $this->sendChatAction('typing', $fromId)
                    ->setInlineKeyBoard(
                        $this->prepareAdminCancelButtons()
                    )->sendMessage("Bot kanal adminstratori emas!");
                return;
            }
            Channel::create(['channel_id'=>$result->result->id]);
        }
        $this->sendChatAction('typing', $fromId)
            ->setInlineKeyBoard(
                $this->prepareAdminHomeButtons()
            )->sendMessage("Kanal saqlandi!");
        $redis->del('admin_menu:' . $fromId);
    }

    public function handleRemoveChannelListCallback (int $fromId, int $messageId): void {
        $channels = Channel::all();
        if (!$channels) {
            $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($this->prepareAdminHomeButtons())->editMessageText("Kannallar mavjud emas!", $messageId);
            return;
        }
        $channel_buttons = [];
        foreach ($channels as $channel) {
            $channelInfo = $this->getChat($channel->channel_id);
            $channel_title = $channelInfo->result->title;
            $channel_buttons[][] = ['text'=>$channel_title, 'callback_data'=>'rm_ch_' . $channel->channel_id];
        }
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($channel_buttons)->editMessageText("Qay birini o'chirmoqchisiz?", $messageId);
    }
    public function handleRemoveChannelCallback (int $fromId, int $messageId, string $data): void {
        $channel_id = explode('rm_ch_',$data)[1];
        Channel::where('channel_id', $channel_id)->delete();
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($this->prepareAdminHomeButtons())->editMessageText("Kanal muvoffaqiyatli o'chirildi", $messageId);
    }
    public function handleChannelOnOfCallback (int $fromId, int $messageId, ?string $additionalText = null): void {
        $channels = Channel::all();
        if (!$channels) {
            $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($this->prepareAdminHomeButtons())->sendMessage('Kanallar mavjud emas!');
            return;
        }
        $channel_buttons = [];
        $responseText = "Qay on/of qilmoqchisiz?\n\n";
        $i = 0;
        foreach ($channels as $channel) {
            $i++;
            $channelInfo = $this->getChat($channel->channel_id);
            $channel_title = ($channel->status == 'active' ? '✅' : '❌') .  $channelInfo->result->title;
            $responseText .= $i . ") " . $channel_title . "\n";
            $channel_buttons[][] = ['text'=>$channel_title, 'callback_data'=>'tgg_ch_' . $channel->channel_id];
        }

        $channel_buttons[][] = ['text'=>'❌ Barchasini inactive qilish', 'callback_data'=>'inactive_all_channel'];

        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($channel_buttons)->editMessageText($responseText . ($additionalText ? "\n\n" . $additionalText : ''), $messageId);
    }
    public function handleToggleChannelCallback (int $fromId, int $messageId, string $data): void {
        $channel_id = explode('tgg_ch_',$data)[1];
        $channel = Channel::where('channel_id', $channel_id)->first();
        if ($channel->status == 'active'){
            $messageText = '❌ inactive';
            Channel::where('channel_id', $channel_id)->update(['status' => 'inactive']);
        }else{
            $messageText = '✅ active';
            Channel::where('channel_id', $channel_id)->update(['status' => 'active']);
        }
        $this->handleChannelOnOfCallback($fromId, $messageId, $messageText);
    }
    public function handleInActiveAllChannelCallback (int $fromId, int $messageId): void {
        Channel::update(['status' => 'inactive']);
        $this->handleChannelOnOfCallback($fromId, $messageId, '❌ inactive all');
    }
    # Admin logic
    public function handleAddAdminCallback (int $fromId, int $messageId, Redis $redis): void {
        $redis->set('admin_menu:' . $fromId, 'addAdmin');
        $this->sendChatAction('typing', $fromId)
            ->setInlineKeyBoard(
                $this->prepareAdminCancelButtons()
            )->editMessageText("Kanal qo'shish uchun kanal username, id'si yoki kanal habaridan forward yuboring\n\n", $messageId);
    }
    public function handleAddAdminMessage (Redis $redis, int $fromId): void {
        if (isset($this->update('message')?->text)){
            $text = $this->update('message')?->text;
            if (is_numeric($text)){
                $result = $this->getChat(trim($text));
                if (!$result->ok){
                    $this->sendChatAction('typing', $fromId)
                        ->setInlineKeyBoard(
                            $this->prepareAdminCancelButtons()
                        )->sendMessage("Bunday odam topilmadi!");
                    return;
                }
                $user = User::where('from_id', $result->result->id)->first();
                Admin::create(['user_id'=>$user->id]);
            }elseif (mb_stripos($text, '@') !== false) {
                $result = $this->getChat(trim($text));
                if (!$result->ok){
                    $this->sendChatAction('typing', $fromId)
                        ->setInlineKeyBoard(
                            $this->prepareAdminCancelButtons()
                        )->sendMessage("Bunday odam topilmadi!");
                    return;
                }
                $user = User::where('from_id', $result->result->id)->first();
                Admin::create(['user_id'=>$user->id]);
            }
        }
        if ($forward = $this->update('forward_from_chat')){
            $result = $this->getChat(trim($forward->id));
            if (!$result->ok){
                $this->sendChatAction('typing', $fromId)
                    ->setInlineKeyBoard(
                        $this->prepareAdminCancelButtons()
                    )->sendMessage("Bunday odam topilmadi!");
                return;
            }
            $user = User::where('from_id', $result->result->id)->first();
            Admin::create(['user_id'=>$user->id]);
        }
        $this->sendChatAction('typing', $fromId)
            ->setInlineKeyBoard(
                $this->prepareAdminHomeButtons()
            )->sendMessage("Admin qo'shildi!");
        $redis->del('admin_menu:' . $fromId);
    }
    public function handleRemoveAdminListCallback (int $fromId, int $messageId): void {
        $admins = Admin::all();
        $admin_buttons = [];
        foreach ($admins as $admin) {
            $user = User::where('id', $admin->user_id)->first();
            $userInfo = $this->getChat($user->fromid);
            $firstName = $userInfo->result->first_name;
            $admin_buttons[][] = ['text'=>$firstName, 'callback_data'=>'rm_ad_' . $admin->user_id];
        }
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($admin_buttons)->editMessageText("Qay birini o'chirmoqchisiz?", $messageId);
    }
    public function handleRemoveAdminCallback (int $fromId, int $messageId, string $data): void {
        $channel_id = explode('rm_ad_',$data)[1];
        Admin::where('user_id', $channel_id)->delete();
        $this->sendChatAction('typing', $fromId)->setInlineKeyBoard($this->prepareAdminHomeButtons())->editMessageText("Admin muvoffaqiyatli o'chirildi", $messageId);
    }
}