<?php

namespace Tests\Unit\Domain\Comment\Validator\Constraint;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Validator\Constraint\CommentAuthorIsUser;
use App\Domain\Comment\Validator\Constraint\CommentAuthorIsUserValidator;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group comment
 */
class CommentAuthorIsUserValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var CommentAuthorIsUserValidator */
    private $commentAuthorIsUserValidator;
    /** @var CommentAuthorIsUser */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();

        $this->commentAuthorIsUserValidator = new CommentAuthorIsUserValidator();
        $this->commentAuthorIsUserValidator->initialize($this->executionContext);

        $this->constraint = new CommentAuthorIsUser();
    }

    public function testValidationPassWhenCommentAuthorIsUser(): void
    {
        $comment = $this->createCommentWithAuthor($this->createMock(User::class));

        $this->commentAuthorIsUserValidator->validate($comment, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailWhenCommentAuthorIsNotUser(): void
    {
        $comment = $this->createCommentWithAuthor($this->createMock(AuthorInterface::class));

        $this->commentAuthorIsUserValidator->validate($comment, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationShouldBeSkippedForNotComment(): void
    {
        $this->commentAuthorIsUserValidator->validate(null, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldFailForUnsupportedConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->commentAuthorIsUserValidator->validate(null, $this->createMock(Constraint::class));
    }

    private function createCommentWithAuthor(AuthorInterface $author): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);

        return $stub;
    }
}
