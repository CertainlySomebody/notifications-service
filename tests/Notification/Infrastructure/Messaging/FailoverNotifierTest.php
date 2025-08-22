<?php

namespace App\Tests\Notification\Infrastructure\Messaging;

use App\Notification\Domain\Model\Notification;
use App\Notification\Domain\Service\NotifierInterface;
use App\Notification\Infrastructure\Messaging\FailoverNotifier;
use PHPUnit\Framework\TestCase;

class FailoverNotifierTest extends TestCase
{
    public function testItUsesFirstNotifierIfItSucceeds(): void
    {
        $notification = $this->createMock(Notification::class);

        $primary = $this->createMock(NotifierInterface::class);
        $primary->expects($this->once())->method('send')->with($notification);

        $secondary = $this->createMock(NotifierInterface::class);
        $secondary->expects($this->never())->method('send');

        $failover = new FailoverNotifier([$primary, $secondary], 'sms');
        $failover->send($notification);
    }

    public function testItFailsOverToSecondNotifierIfFirstFails(): void
    {
        $notification = $this->createMock(Notification::class);

        $primary = $this->createMock(NotifierInterface::class);
        $primary->method('send')->willThrowException(new \RuntimeException("fail"));

        $secondary = $this->createMock(NotifierInterface::class);
        $secondary->expects($this->once())->method('send')->with($notification);

        $failover = new FailoverNotifier([$primary, $secondary], 'sms');
        $failover->send($notification);
    }

    public function testItRetriesWhenAllProvidersFail(): void
    {
        $notification = $this->createMock(Notification::class);

        $failingNotifier = $this->createMock(NotifierInterface::class);
        $failingNotifier->method('send')->willThrowException(new \RuntimeException("fail"));

        $failover = new FailoverNotifier([$failingNotifier], 'sms', maxRetries: 2, retryDelayMs: 10);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("All providers failed after 2 attempts");

        $failover->send($notification);
    }
}
