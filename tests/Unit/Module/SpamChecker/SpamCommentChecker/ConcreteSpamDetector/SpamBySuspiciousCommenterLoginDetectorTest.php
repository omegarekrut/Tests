<?php

namespace Tests\Unit\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector;

use App\Module\SpamChecker\SuspectComment;
use App\Module\SpamChecker\SuspectUser;
use App\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector\SpamBySuspiciousCommenterLoginDetector;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class SpamBySuspiciousCommenterLoginDetectorTest extends TestCase
{
    /** @var SpamBySuspiciousCommenterLoginDetector */
    private $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new SpamBySuspiciousCommenterLoginDetector();
    }

    protected function tearDown(): void
    {
        unset($this->detector);

        parent::tearDown();
    }

    public function testCommentContainingLinksMustBeSkip(): void
    {
        $comment = $this->createAuthorCommentWithLinks($this->createUser(), 'http://foo.bar');

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithAnonymousAuthorMustBeSkip(): void
    {
        $comment = $this->createAuthorCommentWithLinks(null);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithAuthorWithNotSuspicionAboutBannedOnAnotherAccountMustBeSkip(): void
    {
        $comment = $this->createAuthorCommentWithLinks($this->createUser(), 'http://foo.bar');

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithAuthorWithSuspicionAboutBannedOnAnotherAccountMustBeMarkedAsSpam(): void
    {
        $comment = $this->createAuthorCommentWithLinks($this->createUser(true), 'http://foo.bar');

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isSpam());
    }
    
    private function createUser(bool $isSuspicionAboutBannedOnAnotherAccount = false): SuspectUser
    {
        $stub = $this->createMock(SuspectUser::class);
        $stub
            ->method('isSuspicionAboutBannedOnAnotherAccount')
            ->willReturn($isSuspicionAboutBannedOnAnotherAccount);

        return $stub;
    }

    private function createAuthorCommentWithLinks(?SuspectUser $author, string ...$urlsInText): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);
        $stub
            ->method('isContainsUrlInText')
            ->willReturn((bool) count($urlsInText));
        $stub
            ->method('getUrlsFromText')
            ->willReturn($urlsInText);

        return $stub;
    }
}
