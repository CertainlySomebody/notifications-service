<?php

namespace App\Notification\Infrastructure\Messaging;

use App\Notification\Domain\Model\Notification;
use App\Notification\Domain\Service\NotifierInterface;
use App\Notification\Infrastructure\Tracking\NoficationLogger;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class TwilioSmsNotifier implements NotifierInterface
{
    public function __construct(
        private readonly Client $client,
        private string $fromNumber,
        private readonly NoficationLogger $logger
    ) {
    }

    public function send(Notification $notification): void
    {
        try {
            $this->client->messages->create(
                $notification->phone,
                [
                'from' => $this->fromNumber,
                'body' => $notification->message
                ]
            );

            $this->logger->track(
                $notification->userId,
                $this->getChannel(),
                $notification->message,
                'success',
                'Twilio SMS'
            );
        } catch (TwilioException $e) {
            $this->logger->track(
                $notification->userId,
                $this->getChannel(),
                $notification->message,
                'failure',
                'Twilio SMS'
            );
            throw new \RuntimeException("Twilio error: " . $e->getMessage(), 0, $e);
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
