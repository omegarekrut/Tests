<?php

namespace Tests\Unit\Domain\Comment\Collection;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Comment\Entity\Comment;
use Carbon\Carbon;
use Tests\Unit\TestCase;

/**
 * @group comment
 */
class CommentCollectionTest extends TestCase
{
    public function testCollectionCanFilterBestThreeCommentsIfSuchExist(): void
    {
        $comments = new CommentCollection([
            $this->createInactiveComment(),
            $this->createInactiveComment(),
            $this->createWorstComment(),
            $firstExpectedComment = $this->createBestComment(),
            $this->createInactiveComment(),
            $secondExpectedComment = $this->createBestComment(),
            $this->createWorstComment(),
            $thirdExpectedComment = $this->createBestComment(),
        ]);

        $bestThreeComments = $comments->getBestThreeComments();

        $this->assertCount(3, $bestThreeComments);
        $this->assertContains($firstExpectedComment, $bestThreeComments);
        $this->assertContains($secondExpectedComment, $bestThreeComments);
        $this->assertContains($thirdExpectedComment, $bestThreeComments);
    }

    public function testBestCommentsCantBeFilteredForSmallNumberOfComments(): void
    {
        $comments = new CommentCollection([
            $this->createBestComment(),
            $this->createBestComment(),
            $this->createBestComment(),
        ]);

        $bestThreeComments = $comments->getBestThreeComments();

        $this->assertCount(0, $bestThreeComments);
    }

    public function testNumberOfBestCommentsShouldNotExceedThree(): void
    {
        $comments = new CommentCollection([
            $this->createBestComment(),
            $this->createBestComment(),
            $this->createBestComment(),
            $this->createBestComment(),
            $this->createBestComment(),
            $this->createBestComment(),
        ]);

        $bestThreeComments = $comments->getBestThreeComments();

        $this->assertCount(3, $bestThreeComments);
    }

    public function testFirstCommentAddedMustBeYoungerThanRest(): void
    {
        $comments = new CommentCollection([
            $this->createCommentWithCreatedAt(Carbon::now()->addHour()),
            $expectedComment = $this->createCommentWithCreatedAt(Carbon::now()->subMinute()),
            $this->createCommentWithCreatedAt(Carbon::now()),
        ]);

        $actualComment = $comments->findFirstAdded();

        $this->assertTrue($expectedComment === $actualComment);
    }

    public function testCommentsWithLinksCanBeFoundedInCollection(): void
    {
        $comments = new CommentCollection([
            $firstExpectedCommentWithLink = $this->createCommentWithLink(),
            $unexpectedComment = $this->createCommentWithoutLink(),
            $secondExpectedCommentWithLink = $this->createCommentWithLink(),
        ]);

        $actualComments = $comments->filterContainingUrls();

        $this->assertContains($firstExpectedCommentWithLink, $actualComments);
        $this->assertContains($secondExpectedCommentWithLink, $actualComments);
        $this->assertNotContains($unexpectedComment, $actualComments);
    }

    private function createBestComment(): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('isActive')
            ->willReturn(true);
        $stub
            ->method('getPositiveRating')
            ->willReturn(2);
        $stub
            ->method('getNegativeRating')
            ->willReturn(0);
        $stub
            ->method('getCreatedAt')
            ->willReturn(Carbon::now());

        return $stub;
    }

    private function createWorstComment(): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('isActive')
            ->willReturn(true);
        $stub
            ->method('getPositiveRating')
            ->willReturn(0);
        $stub
            ->method('getNegativeRating')
            ->willReturn(2);
        $stub
            ->method('getCreatedAt')
            ->willReturn(Carbon::now());

        return $stub;
    }

    private function createInactiveComment(): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('isActive')
            ->willReturn(false);

        return $stub;
    }

    private function createCommentWithCreatedAt(\DateTime $createdAt): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $stub;
    }

    private function createCommentWithLink(): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('isContainsUrlInText')
            ->willReturn(true);

        return $stub;
    }

    private function createCommentWithoutLink(): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('isContainsUrlInText')
            ->willReturn(false);

        return $stub;
    }
}
