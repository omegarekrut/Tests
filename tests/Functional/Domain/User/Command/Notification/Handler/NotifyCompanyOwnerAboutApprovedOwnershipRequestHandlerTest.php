<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Command\Notification\NotifyCompanyOwnerAboutApprovedOwnershipRequestCommand;
use App\Domain\User\Entity\Notification\OwnershipRequestApprovedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyCompanyOwnerAboutApprovedOwnershipRequestHandlerTest extends TestCase
{
    private Company $company;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        $this->admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->company, $this->admin);

        parent::tearDown();
    }

    public function testUserMustReceiveNotificationAfterHandle(): void
    {
        $owner = $this->company->getOwner();
        $command = new NotifyCompanyOwnerAboutApprovedOwnershipRequestCommand($owner, $this->company, $this->admin);

        $this->getCommandBus()->handle($command);

        $actualNotification = $owner->getUnreadNotifications()->first();

        $this->assertInstanceOf(OwnershipRequestApprovedNotification::class, $actualNotification);
        assert($actualNotification instanceof OwnershipRequestApprovedNotification);

        $this->assertTrue($this->company === $actualNotification->getCompany());
    }
}
