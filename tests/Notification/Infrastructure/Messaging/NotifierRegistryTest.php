<?php

namespace App\Tests\Notification\Infrastructure\Messaging;

use App\Notification\Infrastructure\Messaging\NotifierRegistry;
use PHPUnit\Framework\TestCase;

class NotifierRegistryTest extends TestCase
{
    public function testGetNotifiersForChannelThrowsIfNoneAvailable(): void
    {
        $this->expectException(\RuntimeException::class);
        $registry = new NotifierRegistry([]);

        $registry->getNotifiersForChannel('email');
    }
}
