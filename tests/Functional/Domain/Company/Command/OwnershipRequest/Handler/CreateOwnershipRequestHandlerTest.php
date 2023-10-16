<?php

namespace Tests\Functional\Domain\Company\Command\OwnershipRequest\Handler;

use App\Domain\Company\Command\OwnershipRequest\CreateOwnershipRequestCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\ValueObject\OwnershipRequestStatus;
use App\Domain\Company\Repository\OwnershipRequestRepository;
use App\Domain\User\Entity\User;
use Tests\Functional\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadTestUser;

/**
 * @group company
 */
class CreateOwnershipRequestHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadTackleShopsCompany::class,
        ])->getReferenceRepository();

        /** @var User $creator */
        $creator = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadTackleShopsCompany::REFERENCE_NAME);

        $command = new CreateOwnershipRequestCommand($creator, $company);

        $this->getCommandBus()->handle($command);

        /** @var OwnershipRequestRepository $ownershipRequestRepository */
        $ownershipRequestRepository = $this->getContainer()->get(OwnershipRequestRepository::class);

        $ownershipRequest = $ownershipRequestRepository->findByCreatorAndCompany($creator, $company);

        $this->assertEquals($command->creator, $ownershipRequest->getCreator());
        $this->assertEquals($command->company, $ownershipRequest->getCompany());
        $this->assertEquals(OwnershipRequestStatus::waitingForModeration(), $ownershipRequest->getState());
    }
}
