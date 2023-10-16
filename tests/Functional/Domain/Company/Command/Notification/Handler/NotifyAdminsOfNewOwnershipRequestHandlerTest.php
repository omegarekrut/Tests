<?php

namespace Tests\Functional\Domain\Company\Command\Notification\Handler;

use App\Domain\Company\Command\Notification\NotifyAdminsOfNewOwnershipRequestCommand;
use App\Domain\Company\Entity\Notification\NewOwnershipRequestNotification;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\User\Entity\User;
use League\Tactician\CommandBus;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadOwnershipRequestToFutureApprove;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\Functional\TestCase;

class NotifyAdminsOfNewOwnershipRequestHandlerTest extends TestCase
{
    private CommandBus $commandBus;
    private User $adminUser;
    private OwnershipRequest $ownershipRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = $this->getCommandBus();

        $referenceRepository = $this->loadFixtures([
            LoadOwnershipRequestToFutureApprove::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $this->ownershipRequest = $referenceRepository->getReference(LoadOwnershipRequestToFutureApprove::REFERENCE_NAME);
        $this->adminUser = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBus,
            $this->adminUser,
            $this->ownershipRequest
        );

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $command = new NotifyAdminsOfNewOwnershipRequestCommand($this->ownershipRequest);

        $this->commandBus->handle($command);

        $receivedNotification = $this->adminUser->getUnreadNotifications()->last();

        $this->assertInstanceOf(NewOwnershipRequestNotification::class, $receivedNotification);
        /** @var NewOwnershipRequestNotification $receivedNotification */

        $this->assertEquals($this->ownershipRequest, $receivedNotification->getOwnershipRequest());
    }
}
