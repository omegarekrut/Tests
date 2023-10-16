<?php

namespace Tests\Functional\Domain\Company\Command\OwnershipRequest\Handler;

use App\Domain\Company\Command\OwnershipRequest\RejectOwnershipRequestCommand;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\Company\Entity\ValueObject\OwnershipRequestStatus;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadOwnershipRequestToFutureReject;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\Functional\TestCase;

/**
 * @group company
 */
class RejectOwnershipRequestHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadOwnershipRequestToFutureReject::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        /** @var OwnershipRequest $ownershipRequest */
        $ownershipRequest = $referenceRepository->getReference(LoadOwnershipRequestToFutureReject::REFERENCE_NAME);

        /** @var User $moderator */
        $moderator = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);

        $command = new RejectOwnershipRequestCommand($ownershipRequest->getId(), $moderator->getId());

        $this->getCommandBus()->handle($command);

        $this->assertEquals($moderator, $ownershipRequest->getModerator());
        $this->assertEquals(OwnershipRequestStatus::rejected(), $ownershipRequest->getState());
        $this->assertNotNull($ownershipRequest->getModerateAt());
    }
}
