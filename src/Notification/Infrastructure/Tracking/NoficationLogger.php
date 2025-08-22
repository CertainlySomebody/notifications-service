<?php

namespace App\Notification\Infrastructure\Tracking;

use App\Notification\Domain\Entity\NotificationLog;
use Doctrine\ORM\EntityManagerInterface;

class NoficationLogger
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    // persist log
    public function track(
        string $userId,
        string $channel,
        string $message,
        string $status,
        ?string $provider = null,
    ): void {
        $log = new NotificationLog($userId, $channel, $message, $status, $provider);
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
