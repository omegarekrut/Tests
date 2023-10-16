<?php

namespace Tests\Functional\Domain\Company\Command\OwnershipRequest;

use App\Domain\Company\Command\OwnershipRequest\RejectOwnershipRequestCommand;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadOwnershipRequestToFutureReject;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
class RejectOwnershipRequestCommandValidationTest extends ValidationTestCase
{
    private OwnershipRequest $ownershipRequest;
    private User $moderator;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadOwnershipRequestToFutureReject::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->ownershipRequest = $referenceRepository->getReference(LoadOwnershipRequestToFutureReject::REFERENCE_NAME);

        $this->moderator = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset(
            $this->ownershipRequest,
            $this->moderator,
        );

        parent::tearDown();
    }

    public function testWithNonexistentModeratorId(): void
    {
        $command = new RejectOwnershipRequestCommand($this->ownershipRequest->getId(), 1);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('moderatorId', 'Модератор не найден.');
    }

    public function testWithNonexistentOwnershipRequest(): void
    {
        $command = new RejectOwnershipRequestCommand(Uuid::uuid4(), $this->moderator->getId());

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('ownershipRequestId', 'Запрос не найден.');
    }

    public function testWithHasNotWaitingStatusOwnershipRequest(): void
    {
        $this->ownershipRequest->approve($this->moderator);

        $command = new RejectOwnershipRequestCommand($this->ownershipRequest->getId(), $this->moderator->getId());

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('ownershipRequestId', 'Запрос на владение подтвержден или отклонен ранее.');
    }

    public function testWithValidData(): void
    {
        $command = new RejectOwnershipRequestCommand($this->ownershipRequest->getId(), $this->moderator->getId());

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
