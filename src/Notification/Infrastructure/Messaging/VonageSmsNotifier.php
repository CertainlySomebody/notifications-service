<?php

namespace App\Notification\Infrastructure\Messaging;

use App\Notification\Domain\Model\Notification;
use App\Notification\Domain\Service\NotifierInterface;

class VonageSmsNotifier implements NotifierInterface
{
    public function __construct(
        private readonly string $fromNumber,
        private readonly string $apiKey,
        private readonly string $apiSecret,
    ) {
    }

    public function send(Notification $notification): void
    {
        // basic implementation of Vonage client
        $credentials = new \Vonage\Client\Credentials\Basic($this->apiKey, $this->apiSecret);
        $client = new \Vonage\Client($credentials);

        try {
            $message = new \Vonage\Messages\Channel\SMS\SMSText(
                $notification->phone,
                'Vonage',
                $notification->message
            );
            $client->messages()->send($message);

            $this->logger->track(
                $notification->userId,
                $this->getChannel(),
                $notification->message,
                'success',
                'Vonage SMS'
            );
        } catch (\Exception $e) {
            $this->logger->track(
                $notification->userId,
                $this->getChannel(),
                $notification->message,
                'failure',
                'Vonage SMS'
            );
            throw new \RuntimeException("Vonage error: " . $e->getMessage(), 0, $e);
        }
    }

    public function supportsChannel(string $channel): bool
    {
        return $channel === 'sms';
    }

    public function getChannel(): string
    {
        return 'sms';
    }
}
