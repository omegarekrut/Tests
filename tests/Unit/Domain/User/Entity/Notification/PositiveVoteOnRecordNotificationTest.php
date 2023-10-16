<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\User\Entity\Notification\PositiveVoteOnRecordNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use LogicException;

/**
 * @group user
 * @group notification
 */
class PositiveVoteOnRecordNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutPositiveVotedSelfRecord(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedRecord = $this->createUserRecord($this->user);
        $expectedVote = $this->createVote($expectedInitiator, true, true);

        $notification = new PositiveVoteOnRecordNotification($expectedRecord, $expectedVote);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertSame($expectedVote, $actualNotification->getVote());
        $this->assertSame($expectedRecord, $actualNotification->getOwnerRecord());
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertTrue(NotificationCategory::like()->equals($actualNotification->getCategory()));
    }

    public function testUserCantBeNotifiedAboutNegativeVotedSelfRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about negative vote');

        $votingUser = $this->createMock(User::class);
        assert($votingUser instanceof User);

        $record = $this->createUserRecord($this->user);
        $negativeVote = $this->createVote($votingUser, true, false);

        new PositiveVoteOnRecordNotification($record, $negativeVote);
    }

    public function testUserCantBeNotifiedAboutPositiveNotOwnVotedRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about not own record');

        $votingUser = $this->createMock(User::class);
        assert($votingUser instanceof User);

        $notOwnRecord = $this->createUserRecord($this->createMock(User::class));
        $vote = $this->createVote($votingUser, true, true);

        $notification = new PositiveVoteOnRecordNotification($notOwnRecord, $vote);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutPositiveVotedNotOwnRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about self vote');

        $record = $this->createUserRecord($this->user);
        $selfVote = $this->createVote($this->user, true, true);

        $notification = new PositiveVoteOnRecordNotification($record, $selfVote);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutVoteForOtherRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Vote does not belong to record');

        $votingUser = $this->createMock(User::class);
        assert($votingUser instanceof User);

        $record = $this->createUserRecord($this->user);
        $otherVote = $this->createVote($votingUser, false, true);

        new PositiveVoteOnRecordNotification($record, $otherVote);
    }
}
