<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\User\Entity\Notification\TidingsCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;

/**
 * @group user
 * @group notification
 */
class TidingsCreatedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutTidingsCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedUserTidings = $this->createTiding();
        assert($expectedUserTidings instanceof Tidings);

        $notification = new TidingsCreatedNotification($expectedInitiator, $expectedUserTidings);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(TidingsCreatedNotification::class, $actualNotification);
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedUserTidings, $actualNotification->getTidings());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
