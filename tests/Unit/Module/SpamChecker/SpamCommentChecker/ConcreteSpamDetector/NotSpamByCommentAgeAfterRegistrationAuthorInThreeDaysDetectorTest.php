<?php

namespace Tests\Unit\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector;

use App\Module\SpamChecker\SuspectComment;
use App\Module\SpamChecker\SuspectUser;
use App\Module\SpamChecker\SpamCommentChecker\ConcreteSpamDetector\NotSpamByCommentAgeAfterRegistrationAuthorInThreeDaysDetector;
use Carbon\Carbon;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class NotSpamByCommentAgeAfterRegistrationAuthorInThreeDaysDetectorTest extends TestCase
{
    /** @var NotSpamByCommentAgeAfterRegistrationAuthorInThreeDaysDetector */
    private $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new NotSpamByCommentAgeAfterRegistrationAuthorInThreeDaysDetector();
    }

    protected function tearDown(): void
    {
        unset($this->detector);

        parent::tearDown();
    }

    public function testCommentWithAgeGreaterThanThreeDaysAfterAuthorCreationMustBeMarkedAsNotSpam(): void
    {
        $user = $this->createUserCreatedAt(Carbon::now());
        $fourDaysAfterRegistration = Carbon::instance($user->getCreatedAt())->addDays(4);
        $comment = $this->createUserCommentCreatedAt($user, $fourDaysAfterRegistration);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isNotSpam());
    }

    public function testCommentWithAnonymousAuthorMustBeSkip(): void
    {
        $comment = $this->createUserCommentCreatedAt(null, Carbon::now());

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    public function testCommentWithAgeLessThanThreeDaysAfterAuthorCreationMustBeSkip(): void
    {
        $user = $this->createUserCreatedAt(Carbon::now());
        $threeDaysAfterRegistration = Carbon::instance($user->getCreatedAt())->addDays(3);
        $comment = $this->createUserCommentCreatedAt($user, $threeDaysAfterRegistration);

        $decision = $this->detector->detect($comment);

        $this->assertTrue($decision->isUndefined());
    }

    private function createUserCreatedAt(\DateTimeInterface $createdAt): SuspectUser
    {
        $stub = $this->createMock(SuspectUser::class);
        $stub
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $stub;
    }

    private function createUserCommentCreatedAt(?SuspectUser $user, \DateTimeInterface $createdAt): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($user);
        $stub
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $stub;
    }
}
