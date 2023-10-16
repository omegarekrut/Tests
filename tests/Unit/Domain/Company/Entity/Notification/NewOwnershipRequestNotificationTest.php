<?php

namespace Tests\Unit\Domain\Company\Entity\Notification;

use App\Domain\Company\Entity\Notification\NewOwnershipRequestNotification;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Avatar;
use Tests\Unit\TestCase;

class NewOwnershipRequestNotificationTest extends TestCase
{
    public function testNewOwnershipRequestNotificationInitiatorShouldBeOwnershipRequestCreator(): void
    {
        $user = $this->createMock(User::class);
        $ownershipRequest = $this->createMock(OwnershipRequest::class);
        $ownershipRequest->method('getCreator')->willReturn($user);

        $notification = new NewOwnershipRequestNotification($ownershipRequest);

        $this->assertEquals($ownershipRequest->getCreator(), $notification->getInitiator());
    }

    public function testOwnershipRequestNotificationLogoShouldBeUserAvatar(): void
    {
        $user = $this->createMock(User::class);
        $avatar = $this->createMock(Avatar::class);
        $ownershipRequest = $this->createMock(OwnershipRequest::class);
        $user->method('getAvatar')->willReturn($avatar);
        $ownershipRequest->method('getCreator')->willReturn($user);

        $notification = new NewOwnershipRequestNotification($ownershipRequest);

        $this->assertEquals($avatar, $notification->getNotificationLogo());
    }

    public function testUserCanBeNotifiedAboutNewOwnershipRequest(): void
    {
        $user = $this->generateUser();
        $ownershipRequest = $this->createMock(OwnershipRequest::class);

        $notification = new NewOwnershipRequestNotification($ownershipRequest);
        $user->notify($notification);

        $unreadNotifications = $user->getUnreadNotifications();
        $actualNotification = $unreadNotifications->first();

        $this->assertCount(1, $unreadNotifications);
        $this->assertInstanceOf(NewOwnershipRequestNotification::class, $actualNotification);
        assert($actualNotification instanceof NewOwnershipRequestNotification);

        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
