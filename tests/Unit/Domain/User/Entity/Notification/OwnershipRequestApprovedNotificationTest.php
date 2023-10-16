<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\Notification\OwnershipRequestApprovedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use LogicException;

/**
 * @group user
 * @group notification
 */
class OwnershipRequestApprovedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutApprovedOwnershipRequest(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedCompany = $this->createCompanyWithOwner($this->user);
        assert($expectedCompany instanceof Company);

        $notification = new OwnershipRequestApprovedNotification($expectedInitiator, $expectedCompany);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(OwnershipRequestApprovedNotification::class, $actualNotification);
        assert($actualNotification instanceof OwnershipRequestApprovedNotification);

        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedCompany, $actualNotification->getCompany());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }

    public function testNotCompanyOwnerCannotReceiveNotificationAboutCompanyOwnershipApproved(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive notification of ownership of the company without owning it');

        $companyWithoutOwner = $this->createCompanyWithoutOwner();
        assert($companyWithoutOwner instanceof Company);

        $notification = new OwnershipRequestApprovedNotification($this->createMock(User::class), $companyWithoutOwner);
        $notification->withOwner($this->user);
    }
}
