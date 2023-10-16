<?php

namespace Tests\Unit\Domain\Company\Validator\Constraint;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\Company\Repository\OwnershipRequestRepository;
use App\Domain\Company\Validator\Constraint\OwnershipRequestExist;
use App\Domain\Company\Validator\Constraint\OwnershipRequestExistValidator;
use App\Domain\User\Entity\User;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group company
 */
class OwnershipRequestExistValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private OwnershipRequestExist $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new OwnershipRequestExist([
            'userField' => 'creator',
            'companyField' => 'company',
        ]);

        $this->executionContext = new ValidatorExecutionContextMock();
    }

    public function testValidationMustBeSkippedForEmptyCompany(): void
    {
        $validationSubject = $this->createValidationSubject($this->createMock(User::class), null);

        $validator = new OwnershipRequestExistValidator($this->createMock(OwnershipRequestRepository::class), new PropertyAccessor());
        $validator->initialize($this->executionContext);
        $validator->validate($validationSubject, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationMustBeSkippedForEmptyCreator(): void
    {
        $validationSubject = $this->createValidationSubject(null, $this->createMock(Company::class));

        $validator = new OwnershipRequestExistValidator($this->createMock(OwnershipRequestRepository::class), new PropertyAccessor());
        $validator->initialize($this->executionContext);
        $validator->validate($validationSubject, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationMustPassedForNothingDouble(): void
    {
        $ownershipRequestRepository = $this->createOwnershipRequestRepositoryMock($this->createOwnershipRequestMock(false));

        $validationSubject = $this->createValidationSubject($this->createMock(User::class), $this->createMock(Company::class));

        $validator = new OwnershipRequestExistValidator($ownershipRequestRepository, new PropertyAccessor());
        $validator->initialize($this->executionContext);
        $validator->validate($validationSubject, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationMustPassedForNothingOwnershipRequest(): void
    {
        $ownershipRequestRepository = $this->createOwnershipRequestRepositoryMock();

        $validationSubject = $this->createValidationSubject($this->createMock(User::class), $this->createMock(Company::class));

        $validator = new OwnershipRequestExistValidator($ownershipRequestRepository, new PropertyAccessor());
        $validator->initialize($this->executionContext);
        $validator->validate($validationSubject, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationMustFailedForDouble(): void
    {
        $ownershipRequestRepository = $this->createOwnershipRequestRepositoryMock($this->createOwnershipRequestMock(true));

        $validationSubject = $this->createValidationSubject($this->createMock(User::class), $this->createMock(Company::class));

        $validator = new OwnershipRequestExistValidator($ownershipRequestRepository, new PropertyAccessor());
        $validator->initialize($this->executionContext);
        $validator->validate($validationSubject, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    private function createValidationSubject(?User $creator, ?Company $company): object
    {
        return (object) [
            'creator' => $creator,
            'company' => $company,
        ];
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
            ->method('findByCreatorAndCompany')
            ->willReturn($ownershipRequest);

        return $stub;
    }
}
