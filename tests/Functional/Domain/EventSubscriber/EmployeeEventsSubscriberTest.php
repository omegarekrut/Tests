<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Event\AddEmployeeEvent;
use App\Domain\EventSubscriber\EmployeeEventsSubscriber;
use App\Domain\User\Command\Notification\NotifyUserOfAddingToCompanyEmployeesCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

class EmployeeEventsSubscriberTest extends TestCase
{
    public function testNotificationMustBeSentAfterAddUserToEmployeeCompany(): void
    {
        $commandBusMock = new CommandBusMock();
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $eventDispatcher->addSubscriber(new EmployeeEventsSubscriber($commandBusMock));

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($user instanceof User);

        $eventDispatcher->dispatch(new AddEmployeeEvent($company, $user));

        $this->assertTrue($commandBusMock->isHandled(NotifyUserOfAddingToCompanyEmployeesCommand::class));
    }
}
