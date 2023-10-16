<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\User\Entity\Notification\SuggestedNewsCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;

/**
 * @group user
 * @group notification
 */
class SuggestedNewsCreatedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutSuggestedNewsCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedUserSuggestedNews = $this->createMock(SuggestedNews::class);
        assert($expectedUserSuggestedNews instanceof SuggestedNews);

        $notification = new SuggestedNewsCreatedNotification($expectedInitiator, $expectedUserSuggestedNews);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(SuggestedNewsCreatedNotification::class, $actualNotification);
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedUserSuggestedNews, $actualNotification->getSuggestedNews());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
