<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\Notification\AddEmployeeNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;

class AddEmployeeNotificationTest extends NotificationTest
{
    public function testUserMustBeNotifiedOfAddingToCompanyEmployees(): void
    {
        $companyOwner = $this->createMock(User::class);
        assert($companyOwner instanceof User);

        $expectedCompany = $this->createCompanyWithOwner($companyOwner);
        assert($expectedCompany instanceof Company);

        $notification = new AddEmployeeNotification($companyOwner, $expectedCompany);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(AddEmployeeNotification::class, $actualNotification);
        $this->assertSame($companyOwner, $actualNotification->getInitiator());
        $this->assertSame($expectedCompany, $actualNotification->getCompany());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
