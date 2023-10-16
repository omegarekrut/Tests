<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Record\Map\Entity\Map;
use App\Domain\User\Entity\Notification\MapCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;

/**
 * @group user
 * @group notification
 */
class MapCreatedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutMapCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedUserMap = $this->createMap();
        assert($expectedUserMap instanceof Map);

        $notification = new MapCreatedNotification($expectedInitiator, $expectedUserMap);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(MapCreatedNotification::class, $actualNotification);
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedUserMap, $actualNotification->getMap());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
