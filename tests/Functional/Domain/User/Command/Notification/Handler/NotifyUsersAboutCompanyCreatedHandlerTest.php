<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Command\Notification\NotifyUsersAboutCompanyCreatedCommand;
use App\Domain\User\Entity\Notification\CompanyCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyUsersAboutCompanyCreatedHandlerTest extends TestCase
{
    private Company $companyWithOwner;
    private Company $companyWithoutOwner;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
            LoadCompanyWithoutOwner::class,
            LoadAdminUser::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->companyWithOwner = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        $this->companyWithoutOwner = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        $this->user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->companyWithOwner,
            $this->companyWithoutOwner,
            $this->user
        );

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $this->handleNotifyUsersAboutCompanyCreatedCommand($this->companyWithOwner);

        $unreadUserNotification = $this->user->getUnreadNotifications()->first();

        $this->assertInstanceOf(CompanyCreatedNotification::class, $unreadUserNotification);
        assert($unreadUserNotification instanceof CompanyCreatedNotification);

        $this->assertSame($this->companyWithOwner, $unreadUserNotification->getCompany());
        $this->assertSame($this->companyWithOwner->getOwner(), $unreadUserNotification->getInitiator());
    }

    public function testOwnerOfCompanyShouldNotBeNotified(): void
    {
        $this->handleNotifyUsersAboutCompanyCreatedCommand($this->companyWithOwner);

        $companyOwner = $this->companyWithOwner->getOwner();
        assert($companyOwner instanceof User);

        $unreadNotificationsOfCompanyOwner = $companyOwner->getUnreadNotifications();

        $this->assertEmpty($unreadNotificationsOfCompanyOwner);
    }

    public function testNotificationWithInvalidInitiatorShouldNotBeSent(): void
    {
        $this->handleNotifyUsersAboutCompanyCreatedCommand($this->companyWithoutOwner);

        $unreadUserNotifications = $this->user->getUnreadNotifications();

        $this->assertEmpty($unreadUserNotifications);
    }

    private function handleNotifyUsersAboutCompanyCreatedCommand(Company $company): void
    {
        $command = new NotifyUsersAboutCompanyCreatedCommand($company->getId());

        $this->getCommandBus()->handle($command);
    }
}
