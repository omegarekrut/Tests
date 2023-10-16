<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\User\Entity\Notification\PositiveVoteOnCommentNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use LogicException;

/**
 * @group user
 * @group notification
 */
class PositiveVoteOnCommentNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutPositiveVotedSelfComment(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedComment = $this->createComment($this->user);
        $expectedVote = $this->createVote($expectedInitiator, true, true);

        $notification = new PositiveVoteOnCommentNotification($expectedComment, $expectedVote);

        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertSame($expectedVote, $actualNotification->getVote());
        $this->assertSame($expectedComment, $actualNotification->getOwnerComment());
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertTrue(NotificationCategory::like()->equals($actualNotification->getCategory()));
    }

    public function testUserCantBeNotifiedAboutNegativeVotedSelfComment(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about negative vote');

        $votingUser = $this->createMock(User::class);
        assert($votingUser instanceof User);

        $comment = $this->createComment($this->user);
        $negativeVote = $this->createVote($votingUser, true, false);

        new PositiveVoteOnCommentNotification($comment, $negativeVote);
    }

    public function testUserCantBeNotifiedAboutPositiveNotOwnVotedComment(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about not own comment');

        $votingUser = $this->createMock(User::class);
        assert($votingUser instanceof User);

        $notOwnComment = $this->createComment($this->createMock(User::class));
        $vote = $this->createVote($votingUser, true, true);

        $notification = new PositiveVoteOnCommentNotification($notOwnComment, $vote);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutPositiveVotedNotOwnComment(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about self vote');

        $comment = $this->createComment($this->user);
        $selfVote = $this->createVote($this->user, true, true);

        $notification = new PositiveVoteOnCommentNotification($comment, $selfVote);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutVoteForOtherComment(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Vote does not belong to comment');

        $votingUser = $this->createMock(User::class);
        assert($votingUser instanceof User);

        $comment = $this->createComment($this->user);
        $otherVote = $this->createVote($votingUser, false, true);

        new PositiveVoteOnCommentNotification($comment, $otherVote);
    }
}
