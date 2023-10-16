<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Command\Notification\NotifyUserOfAddingToCompanyEmployeesCommand;
use App\Domain\User\Entity\Notification\AddEmployeeNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\DataFixtures\ORM\User\LoadUserWithRealEmail;
use Tests\Functional\TestCase;

class NotifyUserOfAddingToCompanyEmployeeHandlerTest extends TestCase
{
    private Company $company;
    private User $userWhoWasAddedToEmployees;
    private User $userWhoWasNotAddedToEmployees;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadUserWithAvatar::class,
            LoadUserWithRealEmail::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $this->userWhoWasAddedToEmployees = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $this->userWhoWasNotAddedToEmployees = $referenceRepository->getReference(LoadUserWithRealEmail::USER_WITH_REAL_EMAIL);
    }

    public function testUserWhoWasAddedToCompanyEmployeesMustReceiveNotification(): void
    {
        $command = new NotifyUserOfAddingToCompanyEmployeesCommand(
            $this->userWhoWasAddedToEmployees->getId(),
            $this->company->getId()
        );

        $this->getCommandBus()->handle($command);

        $actualNotification = $this->userWhoWasAddedToEmployees->getUnreadNotifications()->first();

        $this->assertInstanceOf(AddEmployeeNotification::class, $actualNotification);
        $this->assertEquals($this->company->getName(), $actualNotification->getCompany()->getName());
    }

    public function testUserWhoWasNotAddedToCompanyEmployeesShouldNotReceiveNotification(): void
    {
        $command = new NotifyUserOfAddingToCompanyEmployeesCommand(
            $this->userWhoWasAddedToEmployees->getId(),
            $this->company->getId()
        );

        $this->getCommandBus()->handle($command);

        $actualNotification = $this->userWhoWasNotAddedToEmployees->getUnreadNotifications();

        $this->assertCount(0, $actualNotification);
    }
}
