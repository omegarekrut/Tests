<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Validator\Constraint\UserIsNotAuthorOfRecord;
use App\Domain\User\Validator\Constraint\UserIsNotAuthorOfRecordValidator;
use App\Module\Author\AuthorInterface;
use App\Module\Author\Entity\HasAuthorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class UserIsNotAuthorOfRecordValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var UserIsNotAuthorOfRecordValidator */
    private $userIsNotAuthorOfRecordValidator;
    /** @var UserIsNotAuthorOfRecord */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->userIsNotAuthorOfRecordValidator = new UserIsNotAuthorOfRecordValidator(new PropertyAccessor());
        $this->userIsNotAuthorOfRecordValidator->initialize($this->executionContext);

        $this->constraint = new UserIsNotAuthorOfRecord();
    }

    public function testValidationPassWhenUserIsNotRecordAuthor(): void
    {
        $user = $this->createMock(AuthorInterface::class);
        $record = $this->createRecordWithAuthor($this->createMock(AuthorInterface::class));

        $userAndRecord = (object) [
            'user' => $user,
            'record' => $record,
        ];

        $this->userIsNotAuthorOfRecordValidator->validate($userAndRecord, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailWhenUserIsRecordAuthor(): void
    {
        $user = $this->createMock(AuthorInterface::class);
        $record = $this->createRecordWithAuthor($user);

        $userAndRecord = (object) [
            'user' => $user,
            'record' => $record,
        ];

        $this->userIsNotAuthorOfRecordValidator->validate($userAndRecord, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidatorNotSupportsOtherConstraints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->userIsNotAuthorOfRecordValidator->validate(null, $this->createMock(Constraint::class));
    }

    public function testValidatorRequiresAuthorInUserField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('userField should indicate');

        $this->userIsNotAuthorOfRecordValidator->validate((object) [
            'user' => $this,
            'record' => $this->createMock(HasAuthorInterface::class),
        ], $this->constraint);
    }

    public function testValidatorRequiresRecordWithAuthorInVotableField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('recordField should indicate');

        $this->userIsNotAuthorOfRecordValidator->validate((object) [
            'user' => $this->createMock(AuthorInterface::class),
            'record' => $this
        ], $this->constraint);
    }

    private function createRecordWithAuthor(AuthorInterface $author): HasAuthorInterface
    {
        $record = $this->createMock(HasAuthorInterface::class);
        $record
            ->method('getAuthor')
            ->willReturn($author);

        return $record;
    }
}
