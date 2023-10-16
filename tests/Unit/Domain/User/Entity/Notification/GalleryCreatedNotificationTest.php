<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\User\Entity\Notification\GalleryCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;

/**
 * @group user
 * @group notification
 */
class GalleryCreatedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutGalleryCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedUserGallery = $this->createGallery();
        assert($expectedUserGallery instanceof Gallery);

        $notification = new GalleryCreatedNotification($expectedInitiator, $expectedUserGallery);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(GalleryCreatedNotification::class, $actualNotification);
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedUserGallery, $actualNotification->getGallery());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
