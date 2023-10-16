<?php

namespace Tests\Unit\Domain\Company\Validator\Constraint;

use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\Company\Repository\OwnershipRequestRepository;
use App\Domain\Company\Validator\Constraint\OwnershipRequestHasStatusWaitingForModeration;
use App\Domain\Company\Validator\Constraint\OwnershipRequestHasStatusWaitingForModerationValidator;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;
use Tests\Unit\Mock\ValidatorExecutionContextMock;

/**
 * @group company
 */
class OwnershipRequestHasStatusWaitingForModerationValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private OwnershipRequestHasStatusWaitingForModeration $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->constraint = new OwnershipRequestHasStatusWaitingForModeration();
    }

    public function testValidationMustSkipForNotExistsOwnershipRequest(): void
    {
        $ownershipRequestRepository = $this->createOwnershipRequestRepositoryMock();

        $validator = new OwnershipRequestHasStatusWaitingForModerationValidator($ownershipRequestRepository);
        $validator->initialize($this->executionContext);
        $validator->validate(Uuid::uuid4(), $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationMustPassedForOwnershipRequestThatIsInWaitingStatus(): void
    {
        $ownershipRequest = $this->createOwnershipRequestMock(true);
        $ownershipRequestRepository = $this->createOwnershipRequestRepositoryMock($ownershipRequest);

        $validator = new OwnershipRequestHasStatusWaitingForModerationValidator($ownershipRequestRepository);
        $validator->initialize($this->executionContext);
        $validator->validate(Uuid::uuid4(), $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldFailForOwnershipRequestThatIsInWaitingStatus(): void
    {
        $ownershipRequest = $this->createOwnershipRequestMock(false);
        $ownershipRequestRepository = $this->createOwnershipRequestRepositoryMock($ownershipRequest);

        $validator = new OwnershipRequestHasStatusWaitingForModerationValidator($ownershipRequestRepository);
        $validator->initialize($this->executionContext);
        $validator->validate(Uuid::uuid4(), $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    private function createOwnershipRequestMock(bool $isWaitedForModeration): OwnershipRequest
    {
        $ownershipRequest = $this->createMock(OwnershipRequest::class);

        $ownershipRequest
            ->method('isWaitedForModeration')
            ->willReturn($isWaitedForModeration);

        return $ownershipRequest;
    }

    private function createOwnershipRequestRepositoryMock(?OwnershipRequest $ownershipRequest = null): OwnershipRequestRepository
    {
        $stub = $this->createMock(OwnershipRequestRepository::class);

        $stub
            ->method('findById')
            ->willReturn($ownershipRequest);

        return $stub;
    }
}
