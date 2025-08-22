<?php

namespace App\Notification\Domain\Model;

enum Channel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';

    public static function values(): array
    {
        return array_map(fn(self $channel) => $channel->value, self::cases());
    }
}
