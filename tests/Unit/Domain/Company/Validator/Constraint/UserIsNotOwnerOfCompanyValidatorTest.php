<?php

namespace Tests\Unit\Domain\Company\Validator\Constraint;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\Company\Validator\Constraint\UserIsNotOwnerOfCompany;
use App\Domain\Company\Validator\Constraint\UserIsNotOwnerOfCompanyValidator;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group company
 */
final class UserIsNotOwnerOfCompanyValidatorTest extends TestCase
{
    private PropertyAccessorInterface $propertyAccessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    protected function tearDown(): void
    {
        unset(
            $this->propertyAccessor,
        );

        parent::tearDown();
    }

    public function testNullUserLoginOrEmailMustNotCauseErrors(): void
    {
        $userRepository = $this->createUserRepositoryForFindById();
        $companyRepository = $this->createCompanyRepositoryForFindById();

        $executionContext = new ValidatorExecutionContextMock();
        $executionContext->setObject((object) [
            'companyId' => 'company id',
        ]);

        $validator = new UserIsNotOwnerOfCompanyValidator($userRepository, $companyRepository, $this->propertyAccessor);
        $validator->initialize($executionContext);

        $validator->validate(null, new UserIsNotOwnerOfCompany());
        $this->assertFalse($executionContext->hasViolations());
    }

    public function testNotExistsUserMustNotCauseErrors(): void
    {
        $userRepository = $this->createUserRepositoryForFindById();
        $companyRepository = $this->createCompanyRepositoryForFindById();

        $executionContext = new ValidatorExecutionContextMock();
        $executionContext->setObject((object) [
            'companyId' => 'company id',
        ]);

        $validator = new UserIsNotOwnerOfCompanyValidator($userRepository, $companyRepository, $this->propertyAccessor);
        $validator->initialize($executionContext);

        $validator->validate('user login or email', new UserIsNotOwnerOfCompany());
        $this->assertFalse($executionContext->hasViolations());
    }

    public function testNullCompanyIdMustNotCauseErrors(): void
    {
        $userRepository = $this->createUserRepositoryForFindById($this->createMock(User::class));
        $companyRepository = $this->createCompanyRepositoryForFindById();

        $executionContext = new ValidatorExecutionContextMock();
        $executionContext->setObject((object) [
            'companyId' => null,
        ]);

        $validator = new UserIsNotOwnerOfCompanyValidator($userRepository, $companyRepository, $this->propertyAccessor);
        $validator->initialize($executionContext);

        $validator->validate('user login or email', new UserIsNotOwnerOfCompany());
        $this->assertFalse($executionContext->hasViolations());
    }

    public function testNotExistsCompanyMustNotCauseErrors(): void
    {
        $userRepository = $this->createUserRepositoryForFindById($this->createMock(User::class));
        $companyRepository = $this->createCompanyRepositoryForFindById();

        $executionContext = new ValidatorExecutionContextMock();
        $executionContext->setObject((object) [
            'companyId' => 'company id',
        ]);

        $validator = new UserIsNotOwnerOfCompanyValidator($userRepository, $companyRepository, $this->propertyAccessor);
        $validator->initialize($executionContext);

        $validator->validate('user login or email', new UserIsNotOwnerOfCompany());
        $this->assertFalse($executionContext->hasViolations());
    }

    public function testUserWhichIsNotOwnerOfCompanyMustNotCauseErrors(): void
    {
        $userRepository = $this->createUserRepositoryForFindById($this->createMock(User::class));
        $companyRepository = $this->createCompanyRepositoryForFindById(
            $this->createConfiguredMock(Company::class, [
                'isOwnedByUser' => false,
            ]),
        );

        $executionContext = new ValidatorExecutionContextMock();
        $executionContext->setObject((object) [
            'companyId' => 'company id',
        ]);

        $validator = new UserIsNotOwnerOfCompanyValidator($userRepository, $companyRepository, $this->propertyAccessor);
        $validator->initialize($executionContext);

        $validator->validate('user login or email', new UserIsNotOwnerOfCompany());
        $this->assertFalse($executionContext->hasViolations());
    }

    public function testUserWhichIsOwnerOfCompanyMustCauseErrors(): void
    {
        $userRepository = $this->createUserRepositoryForFindById($this->createMock(User::class));
        $companyRepository = $this->createCompanyRepositoryForFindById(
            $this->createConfiguredMock(Company::class, [
                'isOwnedByUser' => true,
            ]),
        );

        $executionContext = new ValidatorExecutionContextMock();
        $executionContext->setObject((object) [
            'companyId' => 'company id',
        ]);

        $validator = new UserIsNotOwnerOfCompanyValidator($userRepository, $companyRepository, $this->propertyAccessor);
        $validator->initialize($executionContext);

        $validator->validate('user login or email', new UserIsNotOwnerOfCompany());
        $this->assertTrue($executionContext->hasViolations());
    }

    public function testUnsupportedConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance of');

        $validator = new UserIsNotOwnerOfCompanyValidator(
            $this->createUserRepositoryForFindById(),
            $this->createCompanyRepositoryForFindById(),
            $this->propertyAccessor,
        );
        $validator->validate('user id', $this->createMock(Constraint::class));
    }

    private function createUserRepositoryForFindById(?User $user = null): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->method('findOneByLoginOrEmail')
            ->willReturn($user);

        return $stub;
    }

    private function createCompanyRepositoryForFindById(?Company $company = null): CompanyRepository
    {
        $stub = $this->createMock(CompanyRepository::class);
        $stub
            ->method('findById')
            ->willReturn($company);

        return $stub;
    }
}
