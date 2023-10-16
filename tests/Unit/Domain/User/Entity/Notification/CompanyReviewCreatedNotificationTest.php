<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Domain\User\Entity\Notification\CompanyReviewCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;

/**
 * @group user
 * @group notification
 */
class CompanyReviewCreatedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutCompanyReviewCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedCompany = $this->createCompanyWithOwner($this->user);
        assert($expectedCompany instanceof Company);

        $expectedCompanyReview = $this->createCompanyReview($expectedInitiator, $expectedCompany);
        assert($expectedCompanyReview instanceof CompanyReview);

        $notification = new CompanyReviewCreatedNotification($expectedInitiator, $expectedCompanyReview);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(CompanyReviewCreatedNotification::class, $actualNotification);
        assert($actualNotification instanceof CompanyReviewCreatedNotification);

        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedCompany, $actualNotification->getCompany());
        $this->assertSame($expectedCompanyReview, $actualNotification->getCompanyReview());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
