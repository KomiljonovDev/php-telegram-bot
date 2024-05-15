# PHP TELEGRAM BOT
php dasturlash tilida telegram bot yozish uchun ishlab chiqilgan kichik kutubxona.
# Ishga tushurish
Fayllarni sozlash
1. `.env` fayli `.env.example` kabi ochiladi
2. `.env` fayliga kerak ma'lumotlar kiritiladi:
```env
HOSTNAME=YOUR_HOSTNAME
DATABASE_USERNAME=YOUR_USERNAME
DATABASE_PASSWORD=YOUR_PASSWORD
DATABASE_NAME=YOUR_DATABASE_NAME

users_table=users
admins_table=admins

bot_token=YOUR_BOT_TOKEN
bot_username=@YOUR_BOT_USERNAME
```
Ma'lumotlar bazasi bilan ishlash bo'yicha [bu yerda o'qing](https://github.com/KomiljonovDev/db-connector)

Habar yuborish
```php
$bot->sendMessage('Assalomu alaykum', 931026030);
```
Action (harakat) ni vujudga keltirish va habar yuborish
```php
$bot->sendChatAction('typing', 931026030)->sendMessage("Assalomu alaykum");
```
Inline tugmachalar hosil qilish
```php
$bot->setInlineKeyBoard([
	[
		['text'=>'Bot haqida', 'callback_data'=>'about'],
		['text'=>'Yordam', 'callback_data'=>'support'],
	],
	[
		['text'=>'Bizning kanallar', 'callback_data'=>'channels'],
		['text'=>'Ariza qoldirish', 'callback_data'=>'feedback'],
	],
])->sendMessage('Assalomu alaykum', 931026030);
```
Matnli tugmachalar hosil qilish
```php
$bot->setReplyKeyboard([
	[
		['text'=>'Bot haqida'],
		['text'=>'Yordam'],
	],
	[
		['text'=>'Bizning kanallar'],
		['text'=>'Ariza qoldirish'],
	],
])->sendMessage('Assalomu alaykum', 931026030);
```
Rasm yuborish (Boshqa turdagi multimedialarni yuborish ham shu kabi, asosiy faylni ko'ring)
```php
$bot->sendChatAction('upload_photo', 931026030)->sendPhoto('rasm.png')
```
Fayl local yoki globaldan yuklanishligi mumkin, yuklanish progressini asosiy fayldagi $withProgress orqali o'chirishingiz mumkin

Bot haqida ma'lumot olish
```php
$bot->getMe();
```
So'rov natijalarini olish
```php
print_r($bot->result());
```