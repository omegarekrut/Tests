<?php

namespace Tests\Unit\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector;

use App\Module\SpamChecker\SuspectComment;
use App\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector\NotSpamByTrustedLinkInCommentDetector;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class NotSpamByTrustedLinkInCommentDetectorTest extends TestCase
{
    public function testCommentWithLinkToTrustedResourceOnlyMustMarkedAsNotSpam(): void
    {
        $comment = $this->createCommentWithLinks('http://trusted.resource');

        $detector = new NotSpamByTrustedLinkInCommentDetector('trusted.resource');
        $decision = $detector->detect($comment);

        $this->assertTrue($decision->isNotSpam());
    }

    public function testCommentWithLinkToTrustedSubResourceOnlyMustMarkedAsNotSpam(): void
    {
        $comment = $this->createCommentWithLinks('https://sub.trusted.resource');

        $detector = new NotSpamByTrustedLinkInCommentDetector('trusted.resource');
        $decision = $detector->detect($comment);

        $this->assertTrue($decision->isNotSpam());
    }

    public function testCommentWithLinkNotOnlyToTrustedResourceMustBeSkip(): void
    {
        $comment = $this->createCommentWithLinks('http://trusted.resource', 'http://not-trusted.resource');

        $detector = new NotSpamByTrustedLinkInCommentDetector('trusted.resource');
        $decision = $detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithLinkToNotTrustedResourceMustBeSkip(): void
    {
        $comment = $this->createCommentWithLinks('http://not-trusted.resource');

        $detector = new NotSpamByTrustedLinkInCommentDetector('trusted.resource');
        $decision = $detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithoutLinksMustBeSkip(): void
    {
        $comment = $this->createCommentWithLinks();

        $detector = new NotSpamByTrustedLinkInCommentDetector('trusted.resource');
        $decision = $detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    private function createCommentWithLinks(string ...$urlsInText): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('isContainsUrlInText')
            ->willReturn((bool) count($urlsInText));
        $stub
            ->method('getUrlsFromText')
            ->willReturn($urlsInText);

        return $stub;
    }
}
