<?php

namespace App\Notification\Infrastructure\Messaging;

use App\Notification\Domain\Model\Notification;
use App\Notification\Domain\Service\NotifierInterface;
use App\Notification\Infrastructure\Tracking\NoficationLogger;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailNotifier implements NotifierInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromMail,
        private NoficationLogger $logger
    ) {
    }

    public function send(Notification $notification): void
    {
        if (!in_array('email', $notification->channels)) {
            return;
        }

        $email = (new Email())
            ->from($this->fromMail)
            ->to($notification->userId)
            ->subject('Notification')
            ->text($notification->message);


        $this->logger->track(
            $notification->userId,
            $this->getChannel(),
            $notification->message,
            'success',
            (string) __CLASS__
        );

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->track(
                $notification->userId,
                $this->getChannel(),
                $notification->message,
                'failure',
                'email'
            );
            throw new \RuntimeException("SES Mailer failed: " . $e->getMessage(), 0, $e);
        }
    }

    public function supportsChannel(string $channel): bool
    {
        return $channel === 'email';
    }

    public function getChannel(): string
    {
        return 'email';
    }
}
