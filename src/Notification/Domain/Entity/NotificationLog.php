<?php

namespace App\Notification\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notification_log')]
class NotificationLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $userId;

    #[ORM\Column(type: 'string')]
    private string $channel;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'string')]
    private string $status;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $provider;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeImmutable $sentAt;

    public function __construct(
        string $userId,
        string $channel,
        string $message,
        string $status,
        ?string $provider = null
    ) {
        $this->userId = $userId;
        $this->channel = $channel;
        $this->message = $message;
        $this->status = $status;
        $this->provider = $provider;
        $this->sentAt = new \DateTimeImmutable();
    }
}
