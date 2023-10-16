<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use InvalidArgumentException;
use LogicException;

/**
 * @group user
 * @group notification
 */
class ForumNotificationTest extends NotificationTest
{
    public function testUserCanReceiveForumNotificationAndItShouldBeUnread(): void
    {
        $notification = new ForumNotification(
            $expectedExternalNotificationId = '1',
            $expectedMessage = 'some message',
            $expectedCategory = NotificationCategory::comment(),
            $expectedInitiator = $this->createMock(User::class),
        );
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertEquals($expectedMessage, $actualNotification->getMessage());
        $this->assertTrue($expectedCategory->equals($actualNotification->getCategory()));
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedExternalNotificationId, $actualNotification->getExternalNotificationId());
    }

    public function testUserCantReceiveEmptyForumNotification(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message should not be empty');

        new ForumNotification(
            '1',
            '',
            NotificationCategory::comment(),
            $this->createMock(User::class),
        );
    }

    public function testUserCantReceiveForumNotificationFromSelf(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive notifications from himself');

        $notification = new ForumNotification(
            '1',
            'some message',
            NotificationCategory::comment(),
            $this->user,
        );

        $notification->withOwner($this->user);
    }
}
