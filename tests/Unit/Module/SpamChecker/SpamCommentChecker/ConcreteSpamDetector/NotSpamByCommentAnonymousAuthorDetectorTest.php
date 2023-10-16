<?php

namespace Tests\Unit\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector;

use App\Module\SpamChecker\SuspectComment;
use App\Module\SpamChecker\SuspectUser;
use App\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector\NotSpamByCommentAnonymousAuthorDetector;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class NotSpamByCommentAnonymousAuthorDetectorTest extends TestCase
{
    /** @var NotSpamByCommentAnonymousAuthorDetector */
    private $detector;

    protected function setUp(): void
    {
        $this->detector = new NotSpamByCommentAnonymousAuthorDetector();
    }

    protected function tearDown(): void
    {
        unset($this->detector);

        parent::tearDown();
    }

    public function testCommentOwnedByAnonymousMustBeMarkedAsNotSpam(): void
    {
        $comment = $this->createCommentWithAuthor();

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isNotSpam());
    }

    public function testCommentOwnedByRealUserMustBeSkip(): void
    {
        $user = $this->createMock(SuspectUser::class);
        $comment = $this->createCommentWithAuthor($user);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    private function createCommentWithAuthor(?SuspectUser $author = null): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);

        return $stub;
    }
}
