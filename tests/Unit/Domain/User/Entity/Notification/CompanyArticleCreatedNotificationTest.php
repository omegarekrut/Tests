<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Entity\Notification\CompanyArticleCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;

/**
 * @group user
 * @group notification
 */
class CompanyArticleCreatedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutCompanyArticleCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedCompany = $this->createCompanyWithOwner($this->user);
        assert($expectedCompany instanceof Company);

        $expectedCompanyArticle = $this->createCompanyArticle($expectedInitiator, $expectedCompany);
        assert($expectedCompanyArticle instanceof CompanyArticle);

        $notification = new CompanyArticleCreatedNotification($expectedInitiator, $expectedCompanyArticle);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(CompanyArticleCreatedNotification::class, $actualNotification);
        assert($actualNotification instanceof CompanyArticleCreatedNotification);

        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedCompany, $actualNotification->getCompany());
        $this->assertSame($expectedCompanyArticle, $actualNotification->getCompanyArticle());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }
}
