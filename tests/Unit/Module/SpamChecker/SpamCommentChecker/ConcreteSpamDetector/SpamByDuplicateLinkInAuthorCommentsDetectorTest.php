<?php

namespace Tests\Unit\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector;

use App\Module\SpamChecker\SuspectComment;
use App\Module\SpamChecker\SuspectUser;
use App\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector\SpamByDuplicateLinkInAuthorCommentsDetector;
use Carbon\Carbon;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class SpamByDuplicateLinkInAuthorCommentsDetectorTest extends TestCase
{
    /** @var SpamByDuplicateLinkInAuthorCommentsDetector */
    private $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new SpamByDuplicateLinkInAuthorCommentsDetector();
    }

    protected function tearDown(): void
    {
        unset($this->detector);

        parent::tearDown();
    }

    public function testCommentWithoutLinksShouldBeSkip(): void
    {
        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => 'https://some.link', 'createdAt' => Carbon::yesterday()],
        ]);
        $comment = $this->createCommentWithUrls($author, []);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithAnonymousAuthorMustBeSkip(): void
    {
        $comment = $this->createCommentWithUrls(null, []);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentContainingDuplicateLinksInOtherCommentMustBeMarkedAsSpam(): void
    {
        $notUniqueLink = 'https://foo.bar';

        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => $notUniqueLink, 'createdAt' => Carbon::yesterday()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fg', 'text' => $notUniqueLink, 'createdAt' => Carbon::yesterday()],
        ]);
        $comment = $this->createCommentWithUrls($author, [$notUniqueLink]);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isSpam());
    }

    public function testCommentWithoutDuplicateLinksMustBeSkip(): void
    {
        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => 'https://other.link', 'createdAt' => Carbon::yesterday()],
        ]);
        $comment = $this->createCommentWithUrls($author, ['https://foo.bar']);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
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

    private function createCommentWithUrls(?SuspectUser $author, array $urlsInText): SuspectComment
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
