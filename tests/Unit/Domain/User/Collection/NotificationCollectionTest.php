<?php

namespace Tests\Unit\Domain\User\Collection;

use App\Domain\User\Collection\NotificationCollection;
use App\Domain\User\Entity\Notification\Notification;
use Tests\Unit\TestCase;

class NotificationCollectionTest extends TestCase
{
    public function testExcludeLast(): void
    {
        $expectedNotification = $this->createMock(Notification::class);
        $excludedNotification = $this->createMock(Notification::class);

        $notifications = new NotificationCollection([
            $excludedNotification,
            $expectedNotification,
        ]);

        $notificationsWithoutFirst = $notifications->excludeFirst(1);

        $this->assertCount(1, $notificationsWithoutFirst);
        $this->assertTrue($expectedNotification === $notificationsWithoutFirst->first());
    }
}
