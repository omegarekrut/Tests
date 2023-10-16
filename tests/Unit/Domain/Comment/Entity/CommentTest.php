<?php

namespace Tests\Unit\Domain\Comment\Entity;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Module\Author\AuthorInterface;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

/**
 * @group comment
 */
class CommentTest extends TestCase
{
    public function testCommentCanBeCreatedByAuthorForRecord(): void
    {
        $expectedText = 'some text';
        $expectedRecord = $this->createMock(Record::class);
        $expectedAuthor = $this->createMock(AuthorInterface::class);

        $comment = new Comment(Uuid::uuid4(), 'someslug', $expectedText, $expectedRecord, $expectedAuthor);

        $this->assertEquals($expectedText, $comment->getText());
        $this->assertTrue($expectedRecord === $comment->getRecord());
        $this->assertTrue($expectedAuthor === $comment->getAuthor());
        $this->assertTrue($comment->getAnswersList()->isEmpty());
    }

    /**
     * @dataProvider getCommentTextsWithUrls
     *
     * @param string[] $expectedUrls
     */
    public function testCommentShouldAwareOfUrlInText(string $commentText, bool $isContainsUrlInText, array $expectedUrls): void
    {
        $comment = $this->createCommentWithText($commentText);

        $this->assertEquals($isContainsUrlInText, $comment->isContainsUrlInText());
        $this->assertEquals($expectedUrls, $comment->getUrlsFromText());
    }

    public function getCommentTextsWithUrls(): \Generator
    {
        yield [
            'https://google.com', // commentText
            true, // isContainsUrlInText,
            ['https://google.com'], // expectedUrls
        ];

        yield [
            'http://кириллический.домен', // commentText
            true, // isContainsUrlInText,
            ['http://кириллический.домен'], // expectedUrls
        ];

        yield [
            'http://first.link some text and http://second.link', // commentText
            true, // isContainsUrlInText,
            ['http://first.link', 'http://second.link'], // expectedUrls
        ];
    }

    public function testCommentShouldAwareOfAbsenceOfLinkInText(): void
    {
        $comment = $this->createCommentWithText('comment without links');

        $this->assertFalse($comment->isContainsUrlInText());
    }

    private function createCommentWithText(string $text): Comment
    {
        return new Comment(
            Uuid::uuid4(),
            'someslug',
            $text,
            $this->createMock(Record::class),
            $this->createMock(AuthorInterface::class)
        );
    }

    public function testCommentCanBeCreatedWithTargetComment(): void
    {
        $expectedText = 'some text';
        $expectedRecord = $this->createMock(Record::class);
        $expectedComment = $this->createMock(Comment::class);
        $expectedAuthor = $this->createMock(AuthorInterface::class);

        $comment = new Comment(Uuid::uuid4(), 'someslug', $expectedText, $expectedRecord, $expectedAuthor);
        $comment->rewriteParentComment($expectedComment);

        $this->assertEquals($expectedText, $comment->getText());
        $this->assertTrue($expectedRecord === $comment->getRecord());
        $this->assertTrue($expectedAuthor === $comment->getAuthor());
        $this->assertTrue($expectedComment === $comment->getParentComment());
    }
}
