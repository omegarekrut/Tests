<?php

namespace Tests\Unit\Domain\Record\CompanyArticle\Validator\Constraint;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Validator\Constraint\AuthorIsAllowedToPublishRecordByCompanyAuthor;
use App\Domain\Company\Validator\Constraint\AuthorIsAllowedToPublishRecordByCompanyAuthorValidator;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group company
 */
class AuthorIsAllowedToPublishCompanyArticleValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private AuthorIsAllowedToPublishRecordByCompanyAuthor $constraint;
    private AuthorIsAllowedToPublishRecordByCompanyAuthorValidator $validator;

    protected function setUp(): void
    {
        $this->executionContext = new ValidatorExecutionContextMock();
        $this->constraint = new AuthorIsAllowedToPublishRecordByCompanyAuthor([
            'authorField' => 'author',
            'companyField' => 'company',
        ]);
        $this->validator = new AuthorIsAllowedToPublishRecordByCompanyAuthorValidator(new PropertyAccessor());
        $this->validator->initialize($this->executionContext);
    }

    public function testValidationMustPassedForAuthorIsOwnerCompany(): void
    {
        $command = $this->createCommand(
            $this->createMock(User::class),
            $this->createCompanyMock(true, false)
        );

        $this->validator->validate($command, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldFailForAuthorIsOwnerCompany(): void
    {
        $command = $this->createCommand(
            $this->createMock(User::class),
            $this->createCompanyMock(false, false)
        );

        $this->validator->validate($command, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationMustPassedForAuthorIsEditorCompany(): void
    {
        $command = $this->createCommand(
            $this->createMock(User::class),
            $this->createCompanyMock(false, true)
        );

        $this->validator->validate($command, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldFailForAuthorIsEditorCompany(): void
    {
        $command = $this->createCommand(
            $this->createMock(User::class),
            $this->createCompanyMock(false, false)
        );

        $this->validator->validate($command, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationMustBeSkippedForEmptyCompany(): void
    {
        $command = $this->createCommand($this->createMock(User::class), null);

        $this->validator->validate($command, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationMustBeSkippedForEmptyAuthor(): void
    {
        $command = $this->createCommand(null, $this->createCompanyMock(false, false));

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

    private function createCompanyMock(bool $userIsOwner, bool $userIsCompanyEditor): Company
    {
        $stub = $this->createMock(Company::class);

        $stub
            ->method('isOwnedByUser')
            ->willReturn($userIsOwner);

        $stub
            ->method('isUserCompanyEditor')
            ->willReturn($userIsCompanyEditor);

        return $stub;
    }
}
