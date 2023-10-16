<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\User\Entity\Notification\ArticleCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use LogicException;

/**
 * @group user
 * @group notification
 */
class ArticleCreatedNotificationTest extends NotificationTest
{
    public function testUserCanBeNotifiedAboutArticleCreated(): void
    {
        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedUserArticle = $this->createArticle($expectedInitiator);
        assert($expectedUserArticle instanceof Article);

        $notification = new ArticleCreatedNotification($expectedInitiator, $expectedUserArticle);
        $this->user->notify($notification);

        $actualNotification = $this->getUserFirstUnreadNotification();

        $this->assertCount(1, $this->getUserUnreadNotification());
        $this->assertInstanceOf(ArticleCreatedNotification::class, $actualNotification);
        $this->assertSame($expectedInitiator, $actualNotification->getInitiator());
        $this->assertSame($expectedUserArticle, $actualNotification->getArticle());
        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
    }

    public function testUserCanNotBeNotifiedAboutArticleCreatedByItself(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The user cannot be notified about he created article');

        $expectedInitiator = $this->createMock(User::class);
        assert($expectedInitiator instanceof User);

        $expectedArticle = $this->createArticle($expectedInitiator);

        $notification = new ArticleCreatedNotification($expectedArticle->getAuthor(), $expectedArticle);
        $notification->withOwner($expectedInitiator);
    }
}
