<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\User\Entity\Notification\NewSubscriberNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;

/**
 * @group user
 * @group notification
 */
class NewSubscriberNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutNewSubscriber(): void
    {
        $expectedSubscriber = $this->createMock(User::class);
        assert($expectedSubscriber instanceof User);

        $notification = new NewSubscriberNotification($expectedSubscriber);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(NewSubscriberNotification::class, $actualNotification);
        $this->assertSame($expectedSubscriber, $actualNotification->getInitiator());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
