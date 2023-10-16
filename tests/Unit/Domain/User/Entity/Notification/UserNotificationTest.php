<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use LogicException;

/**
 * @group user
 * @group notification
 */
class UserNotificationTest extends NotificationTest
{
    public function testUserCanReadNotification(): void
    {
        $notification = $this->createNotification();
        $this->user->notify($notification);
        $unreadNotification = $this->getUserFirstUnreadNotification();

        $this->user->readNotification($unreadNotification);

        $this->assertTrue($unreadNotification->isRead());
        $this->assertEmpty($this->getUserUnreadNotification());
    }

    public function testUserCantReadAlreadyReadNotification(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Notification already read');

        $notification = $this->createNotification();
        $this->user->notify($notification);
        $unreadNotification = $this->getUserFirstUnreadNotification();

        $this->user->readNotification($unreadNotification);
        $this->user->readNotification($unreadNotification);
    }

    public function testUserCanDeleteNotification(): void
    {
        $notification = $this->createNotification();
        $notificationWithOwner = $notification->withOwner($this->user);
        $this->user->notify($notificationWithOwner);
        $unreadNotification = $this->getUserFirstUnreadNotification();

        $this->user->deleteNotification($unreadNotification);

        $this->assertCount(0, $this->getUserUnreadNotification());
        $this->assertCount(0, $this->user->getReadNotifications());
    }

    public function testUserCantDeleteAnotherUsersNotification(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Notification does not belong to the user');

        $notification = $this->createNotification();
        $notificationWithOwnerAnotherUser = $notification->withOwner($this->createMock(User::class));

        $this->user->deleteNotification($notificationWithOwnerAnotherUser);
    }

    public function testUserCantDeleteAlreadyReadNotification(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Notification already read');

        $notification = $this->createNotification();
        $this->user->notify($notification);
        $unreadNotification = $this->getUserFirstUnreadNotification();
        $this->user->readAllUnreadNotifications();

        $this->user->deleteUnreadNotification($unreadNotification);
    }

    private function createNotification(): ForumNotification
    {
        return new ForumNotification(
            '1',
            'some message',
            NotificationCategory::comment(),
            $this->createMock(User::class),
        );
    }
}
