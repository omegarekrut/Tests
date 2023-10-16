<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\User\Entity\Notification\MentionInCommentNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;

/**
 * @group user
 * @group notification
 */
class MentionInCommentNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutMention(): void
    {
        $expectedComment = $this->createComment($this->user);

        $notification = new MentionInCommentNotification($expectedComment);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(MentionInCommentNotification::class, $actualNotification);
        $this->assertTrue(NotificationCategory::mention()->equals($actualNotification->getCategory()));
    }
}
