<?php

namespace App\Notification\Infrastructure\Messaging;

use App\Notification\Domain\Model\Notification;
use App\Notification\Domain\Service\NotifierInterface;

class FailoverNotifier implements NotifierInterface
{
    private array $notifiers;

    /**
     * @param array<NotifierInterface> $notifiers
     */
    public function __construct(
        array $notifiers,
        private readonly string $channel,
        private readonly int $maxRetries = 3,
        private readonly int $retryDelayMs = 1000
    ) {
        $this->notifiers = $notifiers;
    }

    public function send(Notification $notification): void
    {
        $lastException = null;
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            foreach ($this->notifiers as $notifier) {
                try {
                    $notifier->send($notification);
                    return;
                } catch (\Throwable $e) {
                    $lastException = $e;
                    continue;
                }
            }

            usleep($this->retryDelayMs * 1000);
            $attempt++;
        }

        throw new \RuntimeException(
            "All providers failed after {$this->maxRetries} attempts for channel: {$this->channel}",
            0,
            $lastException
        );
    }

    public function supportsChannel(string $channel): bool
    {
        return false;
    }

    public function getChannel(): string
    {
        return false;
    }
}
