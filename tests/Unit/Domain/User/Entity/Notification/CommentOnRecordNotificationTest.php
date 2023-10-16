<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\User\Entity\Notification\CommentOnRecordNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use LogicException;

/**
 * @group user
 * @group notification
 */
class CommentOnRecordNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutNewCommentOnSelfRecord(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedRecord = $this->createUserRecord($this->user);
        $expectedComment = $this->createCommentOnRecord($expectedInitiator, $expectedRecord);

        $notification = new CommentOnRecordNotification($expectedRecord, $expectedComment);

        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertSame($expectedRecord, $actualNotification->getOwnerRecord());
        $this->assertSame($expectedComment, $actualNotification->getComment());
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertTrue(NotificationCategory::comment()->equals($actualNotification->getCategory()));
    }

    public function testUserCantBeNotifiedAboutNewCommentOnNotOwnRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive this notification about not own record');

        $otherUser = $this->createMock(User::class);
        $notOwnRecord = $this->createUserRecord($otherUser);
        $comment = $this->createCommentOnRecord($otherUser, $notOwnRecord);

        $notification = new CommentOnRecordNotification($notOwnRecord, $comment);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutNewCommentOnSelfRecordFromSelf(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot receive notifications from himself');

        $record = $this->createUserRecord($this->user);
        $selfComment = $this->createCommentOnRecord($this->user, $record);

        $notification = new CommentOnRecordNotification($record, $selfComment);
        $notification->withOwner($this->user);
    }

    public function testUserCantBeNotifiedAboutNewCommentOnOtherRecord(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Comment does not belong to record');

        $user = $this->createMock(User::class);
        $record = $this->createUserRecord($this->user);
        $comment = $this->createCommentOnRecord($user, $record);
        $otherRecord = $this->createUserRecord($this->user);

        new CommentOnRecordNotification($otherRecord, $comment);
    }
}
