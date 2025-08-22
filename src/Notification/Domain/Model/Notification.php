<?php

namespace App\Notification\Domain\Model;

class Notification
{
    public function __construct(
        public readonly string $userId,
        public readonly string $message,
        public array $channels,
        public readonly ?string $phone = ''
    ) {
    }
}
