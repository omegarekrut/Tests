<?php

namespace Tests\Functional\Domain\Company\Command\OwnershipRequest\Handler;

use App\Domain\Company\Command\OwnershipRequest\ApproveOwnershipRequestCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\Company\Entity\ValueObject\OwnershipRequestStatus;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadFakeOwnershipRequestToFutureApprove;
use Tests\Functional\TestCase;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadOwnershipRequestToFutureApprove;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

/**
 * @group company
 */
class ApproveOwnershipRequestHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::class,
            LoadOwnershipRequestToFutureApprove::class,
            LoadFakeOwnershipRequestToFutureApprove::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        /** @var OwnershipRequest $originalOwnershipRequest */
        $originalOwnershipRequest = $referenceRepository->getReference(LoadOwnershipRequestToFutureApprove::REFERENCE_NAME);

        /** @var OwnershipRequest $fakeOwnershipRequest */
        $fakeOwnershipRequest = $referenceRepository->getReference(LoadFakeOwnershipRequestToFutureApprove::REFERENCE_NAME);

        /** @var User $moderator */
        $moderator = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::REFERENCE_NAME);

        $command = new ApproveOwnershipRequestCommand($originalOwnershipRequest->getId(), $moderator->getId());

        $this->getCommandBus()->handle($command);

        $this->assertEquals($moderator, $originalOwnershipRequest->getModerator());
        $this->assertEquals(OwnershipRequestStatus::approved(), $originalOwnershipRequest->getState());
        $this->assertNotNull($originalOwnershipRequest->getModerateAt());

        $this->assertEquals($moderator, $fakeOwnershipRequest->getModerator());
        $this->assertEquals(OwnershipRequestStatus::rejected(), $fakeOwnershipRequest->getState());
        $this->assertNotNull($fakeOwnershipRequest->getModerateAt());

        $this->assertEquals($company->getOwner()->getId(), $originalOwnershipRequest->getCreator()->getId());
    }
}
