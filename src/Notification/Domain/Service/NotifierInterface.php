<?php

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Model\Notification;

interface NotifierInterface
{
    // Sends the given notification
    public function send(Notification $notification): void;
    public function supportsChannel(string $channel): bool;
    public function getChannel(): string;
}
