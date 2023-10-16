<?php

namespace Tests\Unit\Module\SpamChecker\Collection;

use App\Module\SpamChecker\Collection\SuspectCommentCollection;
use App\Module\SpamChecker\SuspectComment;
use Carbon\Carbon;
use Tests\Unit\TestCase;

final class SuspectCommentCollectionTest extends TestCase
{
    public function testCommentWithEqualsLinksCanBeFiltered(): void
    {
        $comments = new SuspectCommentCollection([
            $firstExpectedComment = $this->createCommentWithUrlsInText(['http://should.have/duplicate']),
            $unexpectedComment = $this->createCommentWithUrlsInText(['http://unique.link']),
            $secondExpectedComment = $this->createCommentWithUrlsInText($firstExpectedComment->getUrlsFromText()),
        ]);

        $actualComments = $comments->filterContainingOneOfUrls($firstExpectedComment->getUrlsFromText());

        $this->assertContains($firstExpectedComment, $actualComments);
        $this->assertContains($secondExpectedComment, $actualComments);
        $this->assertNotContains($unexpectedComment, $actualComments);
    }

    public function testGreaterCommentsCanBeFiltered(): void
    {
        $now = Carbon::today();

        $comments = new SuspectCommentCollection([
            $oldComment = $this->createCommentWithCreatedAt((clone $now)->subHours(3)),
            $newComment = $this->createCommentWithCreatedAt((clone $now)->addHours(3)),
        ]);

        $actualComments = $comments->filterOutGreaterThan($now);

        $this->assertNotContains($oldComment, $actualComments);
        $this->assertContains($newComment, $actualComments);
    }

    public function testFilterContainingWithUrls(): void
    {
        $comments = new SuspectCommentCollection([
            $commentWithoutUrl = $this->createCommentWithUrlsInText([]),
            $commentWithUrl = $this->createCommentWithUrlsInText(['http://some.link']),
        ]);

        $actualComments = $comments->filterContainingWithUrls();

        $this->assertNotContains($commentWithoutUrl, $actualComments);
        $this->assertContains($commentWithUrl, $actualComments);
    }

    private function createCommentWithCreatedAt(\DateTime $createdAt): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $stub;
    }

    /**
     * @param string[]
     */
    private function createCommentWithUrlsInText(array $urls): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('getUrlsFromText')
            ->willReturn($urls);

        $stub
            ->method('isContainsUrlInText')
            ->willReturn(!empty($urls));

        return $stub;
    }
}
