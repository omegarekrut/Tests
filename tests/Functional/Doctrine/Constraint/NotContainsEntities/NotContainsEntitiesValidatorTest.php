<?php

namespace Tests\Functional\Doctrine\Constraint\NotContainsEntities;

use App\Doctrine\Constraint\NotContainsEntities\NotContainsEntities;
use App\Doctrine\Constraint\NotContainsEntities\NotContainsEntitiesValidator;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\Doctrine\Constraint\NotContainsEntities\Mock\ValidatorSubjectWithPrivateProperty;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\ValidatorExecutionContextMock;

class NotContainsEntitiesValidatorTest extends TestCase
{
    /** @var NotContainsEntitiesValidator */
    private $validator;
    /** @var User */
    private $entity;
    /** @var ValidatorExecutionContextMock */
    private $validatorExecutionContextMock;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->entity = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->validatorExecutionContextMock = new ValidatorExecutionContextMock();

        $this->validator = new NotContainsEntitiesValidator($this->getEntityManager());
        $this->validator->initialize($this->validatorExecutionContextMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->entity,
            $this->validatorExecutionContextMock,
            $this->validator
        );

        parent::tearDown();
    }

    public function testValidationShouldBeFailForEntitySubject(): void
    {
        $subjectWithEntity = $this->entity;

        $this->validator->validate($subjectWithEntity, new NotContainsEntities());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldBeFailForSubjectContainedEntity(): void
    {
        $subjectWithEntity = [
            new ArrayCollection([$this->entity]),
        ];

        $this->validator->validate($subjectWithEntity, new NotContainsEntities());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldBeFailForSubjectContainedEntityInProtectedField(): void
    {
        $subjectWithEntity = new ValidatorSubjectWithPrivateProperty($this->entity);

        $this->validator->validate($subjectWithEntity, new NotContainsEntities());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationMustBeSkippedForNullSubject(): void
    {
        $this->validator->validate(null, new NotContainsEntities());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationMustBePassedForSubjectWithoutEntities(): void
    {
        $subjectWithoutEntity = [
            new ArrayCollection([new \stdClass()]),
        ];

        $this->validator->validate($subjectWithoutEntity, new NotContainsEntities());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationByNotSupportedConstraintShouldThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->validator->validate(null, $this->createMock(Constraint::class));
    }
}
