<?php

namespace Tests\Unit\Domain\Comment\Validator\Constraint;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Repository\CommentRepository;
use App\Domain\Comment\Validator\Constraint\AssertCommentSlug;
use App\Domain\Comment\Validator\Constraint\AssertCommentSlugValidator;
use App\Domain\Comment\Validator\Constraint\CommentAuthorIsUser;
use App\Domain\Comment\Validator\Constraint\CommentAuthorIsUserValidator;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group comment
 */
class AssertCommentSlugValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var CommentAuthorIsUserValidator */
    private $assertCommentSlugOrNullValidator;
    /** @var CommentRepository */
    private $expectedCommentRepository;
    /** @var CommentAuthorIsUser */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expectedCommentRepository = $this->createMock(CommentRepository::class);

        $this->executionContext = new ValidatorExecutionContextMock();

        $this->assertCommentSlugOrNullValidator = new AssertCommentSlugValidator($this->expectedCommentRepository);
        $this->assertCommentSlugOrNullValidator->initialize($this->executionContext);

        $this->constraint = new AssertCommentSlug();
    }

    public function testConstraintMustBeRightInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->assertCommentSlugOrNullValidator->validate('test', $this->createMock(Constraint::class));
    }

    public function testValidationPassWhenTargetCommentIsDefined(): void
    {
        $expectedComment = $this->createMock(Comment::class);
        $this->expectedCommentRepository
            ->method('findBySlug')
            ->willReturn($expectedComment);

        $this->assertCommentSlugOrNullValidator->validate('test', $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailWhenTargetCommentIsNotDefined(): void
    {
        $this->expectedCommentRepository
            ->method('findBySlug')
            ->willReturn(null);

        $this->assertCommentSlugOrNullValidator->validate('test', $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
        $this->assertEquals('Исходный комментарий не найден.', $this->executionContext->getViolationMessages()[0]);
    }
}
