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
class CommentOnCommentedRecordNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutNewCommentOnCommentedRecord(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedRecord = $this->createUserRecord($expectedInitiator);
        $expectedComment = $this->createCommentOnRecord($expectedInitiator, $expectedRecord);

        $notification = new CommentOnCommentedRecordNotification($expectedRecord, $expectedComment);

        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertSame($expectedRecord, $actualNotification->getCommentedRecord());
        $this->assertSame($expectedComment, $actualNotification->getComment());
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertTrue(NotificationCategory::comment()->equals($actualNotification->getCategory()));
    }

    public function testUserCantBeNotifiedAboutNewCommentOnSelfCommentedRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about new comment to own record');

        $selfRecord = $this->createUserRecord($this->user);
        $comment = $this->createCommentOnRecord($this->createMock(User::class), $selfRecord);

        $notification = new CommentOnCommentedRecordNotification($selfRecord, $comment);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutNewSelfCommentOnCommentedRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive notifications from himself');

        $record = $this->createUserRecord($this->createMock(User::class));
        $selfComment = $this->createCommentOnRecord($this->user, $record);

        $notification = new CommentOnCommentedRecordNotification($record, $selfComment);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutNewCommentOnOtherCommentedRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Comment does not belong to record');

        $otherUser = $this->createMock(User::class);
        $record = $this->createUserRecord($otherUser);
        $comment = $this->createCommentOnRecord($otherUser, $record);
        $otherRecord = $this->createUserRecord($otherUser);

        new CommentOnCommentedRecordNotification($otherRecord, $comment);
    }
}
