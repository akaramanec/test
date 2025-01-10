<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{

    const TELEGRAM = 'telegram';
    const VIBER = 'viber';
    const MESSENGER = 'messenger';

    protected $table = 'bot';

    public $timestamps = false;

    public static function allPlatforms()
    {
        return [
            self::VIBER => 'Viber',
            self::TELEGRAM => 'Telegram',
            self::MESSENGER => 'Messenger'
        ];
    }

    public static function getBotName(string $platform)
    {
        return match ($platform) {
            self::TELEGRAM => self::getTelegramBotName()
        };
    }

    private static function getTelegramBotName()
    {
        preg_match('/https:\/\/t\.me\/([A-Za-z0-9_]+)/', config('app.CHAT_TM'), $matches);
        return '@' . $matches[1];
    }
}
