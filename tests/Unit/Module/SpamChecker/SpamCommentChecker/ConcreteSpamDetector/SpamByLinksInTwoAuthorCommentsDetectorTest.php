<?php

namespace Tests\Unit\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector;

use App\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector\SpamByLinksInTwoAuthorCommentsDetector;
use App\Module\SpamChecker\SuspectComment;
use App\Module\SpamChecker\SuspectUser;
use Carbon\Carbon;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class SpamByLinksInTwoAuthorCommentsDetectorTest extends TestCase
{
    /** @var SpamByLinksInTwoAuthorCommentsDetector */
    private $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new SpamByLinksInTwoAuthorCommentsDetector();
    }

    protected function tearDown(): void
    {
        unset($this->detector);

        parent::tearDown();
    }

    public function testCommentWithoutLinksShouldBeSkip(): void
    {
        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => 'some text', 'createdAt' => Carbon::yesterday()],
        ]);
        $comment = $this->createCommentWithAuthor($author, false);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithOneLinkShouldBeSkip(): void
    {
        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => 'https://some.link', 'createdAt' => Carbon::yesterday()],
        ]);
        $comment = $this->createCommentWithAuthor($author, true);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithAnonymousAuthorMustBeSkip(): void
    {
        $comment = $this->createCommentWithAuthor(null, true);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentsContainingDoubleLinksAndAuthorMustBeMarkedAsSpam(): void
    {
        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => 'https://some.link', 'createdAt' => Carbon::yesterday()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fg', 'text' => 'https://foo.bar', 'createdAt' => Carbon::yesterday()],
        ]);
        $comment = $this->createCommentWithAuthor($author, true);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isSpam());
    }

    /**
     * @param object[] $commentsInformation
     */
    private function createUserWithComments(array $commentsInformation = []): SuspectUser
    {
        return new SuspectUser(
            1,
            'login',
            'email@test.com',
            '34.67.122.1',
            false,
            $commentsInformation,
            Carbon::now()
        );
    }

    private function createCommentWithAuthor(?SuspectUser $author, bool $isContainsUrlInText): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);
        $stub
            ->method('isContainsUrlInText')
            ->willReturn($isContainsUrlInText);

        return $stub;
    }
}
