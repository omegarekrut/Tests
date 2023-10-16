<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Record\Video\Entity\Video;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\Notification\VideoCreatedNotification;
use App\Domain\User\Entity\User;

/**
 * @group user
 * @group notification
 */
class VideoCreatedNotificationClass extends NotificationTest
{
    public function testUserCanBeNotifiedAboutVideoCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedUserVideo = $this->createVideo();
        assert($expectedUserVideo instanceof Video);

        $notification = new VideoCreatedNotification($expectedInitiator, $expectedUserVideo);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(VideoCreatedNotification::class, $actualNotification);
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedUserVideo, $actualNotification->getVideo());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
