<?php

namespace App\Notification\Infrastructure\Messaging;

use App\Notification\Domain\Model\Channel;
use App\Notification\Domain\Service\NotifierInterface;

/**
 * This class chooses the appropriate notifier for the channel, and has simple Failover functionality
 */
class NotifierRegistry
{
    /**
     * @var array<string, NotifierInterface[]>
     */
    private array $notifiersByChannel = [];
    private array $failoverCache = [];

    /**
     * @param iterable<NotifierInterface> $notifiers
     */
    public function __construct(iterable $notifiers)
    {
        foreach ($notifiers as $notifier) {
            foreach (Channel::values() as $channel) {
                if ($notifier->supportsChannel($channel)) {
                    $this->notifiersByChannel[$channel][] = $notifier;
                }
            }
        }
    }

    public function getNotifiersForChannel(string $channel): array
    {
        if (!isset($this->failoverCache[$channel])) {
            $notifiers = $this->notifiersByChannel[$channel] ?? [];

            if (empty($notifiers)) {
                throw new \RuntimeException("No notifier found for channel: {$channel}");
            }

            $this->failoverCache[$channel] = new FailoverNotifier($notifiers, $channel);
        }

        return [$this->failoverCache[$channel]];
    }
}
