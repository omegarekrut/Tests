<?php

namespace Tests\Unit\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector;

use App\Module\SpamChecker\SuspectComment;
use App\Module\SpamChecker\SuspectUser;
use App\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector\SpamByDuplicateSimilarCommentsDetector;
use Carbon\Carbon;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class SpamByDuplicateSimilarCommentsDetectorTest extends TestCase
{
    /** @var SpamByDuplicateSimilarCommentsDetector */
    private $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new SpamByDuplicateSimilarCommentsDetector();
    }

    protected function tearDown(): void
    {
        unset($this->detector);

        parent::tearDown();
    }

    public function testCommentWithTinyTextMustBeSkip(): void
    {
        $spamMessage = 'Вы будете в шоке!';

        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fd', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358ff', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fh', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
        ]);

        $comment = $this->createCommentWithText($author, $spamMessage, Carbon::today());

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithAnonymousAuthorMustBeSkip(): void
    {
        $spamMessage = 'Вы будете в шоке! Всем граждан.ам Р.Ф 20 лет начисля.лись сoц выплаты и кoмпeнcaции! Новость является потенциальным спамом!';

        $comment = $this->createCommentWithText(null, $spamMessage, Carbon::today());

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithHighLevelOfRelevanceTextMustBeMarkedAsSpam(): void
    {
        $spamMessage = 'Вы будете в шоке! Всем граждан.ам Р.Ф 20 лет начисля.лись сoц выплаты и кoмпeнcaции! Новость является потенциальным спамом!';

        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358ff', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fg', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fh', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
        ]);

        $comment = $this->createCommentWithText($author, $spamMessage, Carbon::today());

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isSpam());
    }

    public function testCommentWithHighLevelOfRelevanceForALateDateMustBeSkip(): void
    {
        $spamMessage = 'Вы будете в шоке! Всем граждан.ам Р.Ф 20 лет начисля.лись сoц выплаты и кoмпeнcaции! Новость является потенциальным спамом!';

        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => $spamMessage, 'createdAt' => Carbon::yesterday()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fd', 'text' => $spamMessage, 'createdAt' => Carbon::yesterday()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fg', 'text' => $spamMessage, 'createdAt' => Carbon::yesterday()],
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fh', 'text' => $spamMessage, 'createdAt' => Carbon::yesterday()],
        ]);

        $comment = $this->createCommentWithText($author, $spamMessage, Carbon::now());

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithLiteLevelOfRelevanceMustBeSkip(): void
    {
        $spamMessage = 'Вы будете в шоке! Всем граждан.ам Р.Ф 20 лет начисля.лись сoц выплаты и кoмпeнcaции! Новость является потенциальным спамом!';

        $author = $this->createUserWithComments([
            (object) ['id' => '004a8a5a-7937-457c-b691-6caccbe358fe', 'text' => $spamMessage, 'createdAt' => Carbon::today()],
        ]);

        $comment = $this->createCommentWithText($author, $spamMessage, Carbon::today());

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

    private function createCommentWithText(?SuspectUser $user, string $text, Carbon $createdAt): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($user);
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $stub;
    }
}
