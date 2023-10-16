<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\Notification\CompanyCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use LogicException;

/**
 * @group user
 * @group notification
 */
class CompanyCreatedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutCompanyCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedCompany = $this->createCompanyWithOwner($expectedInitiator);
        assert($expectedCompany instanceof Company);

        $notification = new CompanyCreatedNotification($expectedInitiator, $expectedCompany);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(CompanyCreatedNotification::class, $actualNotification);
        assert($actualNotification instanceof CompanyCreatedNotification);

        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedCompany, $actualNotification->getCompany());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }

    public function testUserCanNotBeNotifiedAboutCompanyCreatedByItself(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The user cannot be notified about the creation of his company.');

        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedCompany = $this->createCompanyWithOwner($expectedInitiator);

        $notification = new CompanyCreatedNotification($expectedInitiator, $expectedCompany);
        $notification->withOwner($expectedInitiator);
    }
}
