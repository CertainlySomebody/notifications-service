<?php

namespace App\Notification\Application\Service;

use App\Notification\Domain\Model\Notification;
use App\Notification\Infrastructure\Messaging\NotifierRegistry;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class NotificationSender
{
    public function __construct(
        private readonly NotifierRegistry $registry,
        private RateLimiterFactory $userNotificationsLimiter
    ) {
    }

    public function send(Notification $notification): void
    {
        $limiter = $this->userNotificationsLimiter->create($notification->userId);

        foreach ($notification->channels as $channel) {
            $notifiers = $this->registry->getNotifiersForChannel($channel);

            foreach ($notifiers as $notifier) {
                $limit = $limiter->consume(1);

                // Check remaining tokens in throttling (rate_limiter)
                //        dump([
                //           'remainingTokens' => $limit->getRemainingTokens(),
                //           'retryAfter' => $limit->getRetryAfter()?->format('Y-m-d H:i:s'),
                //        ]);

                if (!$limit->isAccepted()) {
                    throw new \RuntimeException('Rate limit exceeded for user ' . $notification->userId);
                }
                $notifier->send($notification);
            }
        }
    }
}
