<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\User\Command\Notification\NotifySubscribersAboutArticleCreatedCommand;
use App\Domain\User\Command\Subscription\SubscribeOnUserCommand;
use App\Domain\User\Entity\Notification\ArticleCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutArticleCreatedHandlerTest extends TestCase
{
    private Article $userArticle;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->userArticle = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->userArticle, $this->user);

        parent::tearDown();
    }

    public function testUserMustReceiveNotificationAfterHandle(): void
    {
        $this->subscribeOnArticleAuthor();

        $command = new NotifySubscribersAboutArticleCreatedCommand($this->userArticle->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();
        $actualNotification = $unreadNotifications->first();

        $this->assertCount(1, $unreadNotifications);
        $this->assertInstanceOf(ArticleCreatedNotification::class, $actualNotification);
        $this->assertEquals($this->userArticle, $actualNotification->getArticle());
        $this->assertEquals($this->userArticle->getAuthor(), $actualNotification->getInitiator());
    }

    public function testUserWithoutSubscriptionMustNotReceiveNotificationAfterHandle(): void
    {
        $command = new NotifySubscribersAboutArticleCreatedCommand($this->userArticle->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();

        $this->assertCount(0, $unreadNotifications);
    }

    private function subscribeOnArticleAuthor(): void
    {
        $command = new SubscribeOnUserCommand($this->user, $this->userArticle->getAuthor());

        $this->getCommandBus()->handle($command);
    }
}
