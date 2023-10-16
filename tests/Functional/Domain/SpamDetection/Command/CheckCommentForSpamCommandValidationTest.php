<?php

namespace Tests\Functional\Domain\SpamDetection\Command;

use App\Domain\Comment\Entity\Comment;
use App\Domain\SpamDetection\Command\CheckCommentForSpamCommand;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use Tests\Functional\ValidationTestCase;

/**
 * @group comment
 */
class CheckCommentForSpamCommandValidationTest extends ValidationTestCase
{
    public function testCommentAuthorMustBeUser(): void
    {
        $comment = $this->createCommentWithAuthor($this->createMock(AuthorInterface::class));
        $command = new CheckCommentForSpamCommand($comment);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('comment', 'Автор комментарий анонимный пользователь.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $comment = $this->createCommentWithAuthor($this->createMock(User::class));
        $command = new CheckCommentForSpamCommand($comment);

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
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
