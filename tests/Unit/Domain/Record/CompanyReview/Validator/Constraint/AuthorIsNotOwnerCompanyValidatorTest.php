<?php

namespace Tests\Unit\Domain\Record\CompanyReview\Validator\Constraint;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyReview\Validator\Constraint\AuthorIsNotOwnerCompany;
use App\Domain\Record\CompanyReview\Validator\Constraint\AuthorIsNotOwnerCompanyValidator;
use App\Module\Author\AuthorInterface;
use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class AuthorIsNotOwnerCompanyValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private AuthorIsNotOwnerCompany $constraint;
    private AuthorIsNotOwnerCompanyValidator $validator;

    protected function setUp(): void
    {
        $this->executionContext = new ValidatorExecutionContextMock();
        $this->constraint = new AuthorIsNotOwnerCompany([
            'authorField' => 'author',
            'companyField' => 'company',
        ]);
        $this->validator = new AuthorIsNotOwnerCompanyValidator(new PropertyAccessor());
        $this->validator->initialize($this->executionContext);
    }

    public function testValidationShouldFailForAuthorIsNotOwnerCompany(): void
    {
        $command = $this->createCommand(
            $this->createMock(AuthorInterface::class),
            $this->createCompanyMock(true)
        );

        $this->validator->validate($command, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationMustPassedForAuthorIsNotOwnerCompany(): void
    {
        $command = $this->createCommand(
            $this->createMock(AuthorInterface::class),
            $this->createCompanyMock(false)
        );

        $this->validator->validate($command, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationMustBeSkippedForEmptyCompany(): void
    {
        $command = $this->createCommand($this->createMock(AuthorInterface::class), null);

        $this->validator->validate($command, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationMustBeSkippedForEmptyAuthor(): void
    {
        $command = $this->createCommand(null, $this->createCompanyMock(false));

        $this->validator->validate($command, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldFailForUnsupportedConstraint(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->validator->validate(null, $this->createMock(Constraint::class));
    }

    private function createCommand(?AuthorInterface $author, ?Company $company): object
    {
        return (object) [
            'author' => $author,
            'company' => $company,
        ];
    }

    private function createCompanyMock(bool $userIsOwner): Company
    {
        $stub = $this->createMock(Company::class);

        $stub
            ->method('isOwnedByUser')
            ->willReturn($userIsOwner);

        return $stub;
    }
}
