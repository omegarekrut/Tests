<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Domain\User\Command\Notification\NotifyEmployeesCompanyReviewCreatedCommand;
use App\Domain\User\Entity\Notification\CompanyReviewCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Record\CompanyReview\LoadCompanyReviews;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyEmployeesCompanyReviewCreatedHandlerTest extends TestCase
{
    private CompanyReview $companyReview;
    private User $companyEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyReviews::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->companyReview = $referenceRepository->getReference(LoadCompanyReviews::REFERENCE_NAME);
        $this->companyEmployee = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->companyReview, $this->companyEmployee);

        parent::tearDown();
    }

    public function testEmployeeOfCompanyMustReceiveNotificationAfterHandle(): void
    {
        $command = new NotifyEmployeesCompanyReviewCreatedCommand($this->companyReview);

        $this->getCommandBus()->handle($command);

        $actualNotification = $this->companyEmployee->getUnreadNotifications()->first();

        $this->assertInstanceOf(CompanyReviewCreatedNotification::class, $actualNotification);

        $this->assertTrue($this->companyReview === $actualNotification->getCompanyReview());
    }

    public function testNotEmployeeOfCompanyShouldNotReceiveNotificationAfterHandle(): void
    {
        $command = new NotifyEmployeesCompanyReviewCreatedCommand($this->companyReview);

        $this->getCommandBus()->handle($command);

        $companyNotEmployee = $this->companyReview->getAuthor();
        assert($companyNotEmployee instanceof User);

        $actualNotification = $companyNotEmployee->getUnreadNotifications();

        $this->assertCount(0, $actualNotification);
    }
}
