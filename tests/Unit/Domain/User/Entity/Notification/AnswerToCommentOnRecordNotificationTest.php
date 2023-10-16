<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\User\Entity\Notification\CommentOnCommentedRecordNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use LogicException;

/**
 * @group user
 * @group notification
 */
class AnswerToCommentOnRecordNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutNewCommentOnSelfRecord(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedRecord = $this->createUserRecord($this->user);
        $expectedComment = $this->createCommentOnRecord($this->user, $expectedRecord);
        $expectedAnswer = $this->createAnswerToCommentOnRecord($expectedInitiator, $expectedRecord, $expectedComment);

        $notification = new CommentOnCommentedRecordNotification($expectedRecord, $expectedAnswer);

        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertSame($expectedRecord, $actualNotification->getCommentedRecord());
        $this->assertSame($expectedAnswer, $actualNotification->getComment());
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertTrue(NotificationCategory::comment()->equals($actualNotification->getCategory()));
    }

    public function testUserCantBeNotifiedAboutNewCommentOnNotOwnRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about new comment to own record');

        $otherUser = $this->createMock(User::class);
        $record = $this->createUserRecord($this->user);
        $comment = $this->createCommentOnRecord($otherUser, $record);
        $answer = $this->createAnswerToCommentOnRecord($this->user, $record, $comment);

        $notification = new CommentOnCommentedRecordNotification($record, $answer);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutNewCommentOnSelfRecordFromSelf(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive notifications from himself');

        $otherUser = $this->createMock(User::class);
        $notOwnRecord = $this->createUserRecord($otherUser);
        $comment = $this->createCommentOnRecord($this->user, $notOwnRecord);
        $answer = $this->createAnswerToCommentOnRecord($this->user, $notOwnRecord, $comment);

        $notification = new CommentOnCommentedRecordNotification($notOwnRecord, $answer);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutNewCommentOnOtherRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Comment does not belong to record');

        $user = $this->createMock(User::class);
        $record = $this->createUserRecord($this->user);
        $comment = $this->createCommentOnRecord($user, $record);
        $answer = $this->createAnswerToCommentOnRecord($this->user, $record, $comment);
        $otherRecord = $this->createUserRecord($this->user);

        new CommentOnCommentedRecordNotification($otherRecord, $answer);
    }
}
