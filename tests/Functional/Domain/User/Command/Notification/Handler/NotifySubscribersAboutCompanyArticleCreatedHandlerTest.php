<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Command\Notification\NotifySubscribersAboutCompanyArticleCreatedCommand;
use App\Domain\User\Command\Subscription\SubscribeOnCompanyCommand;
use App\Domain\User\Entity\Notification\CompanyArticleCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWherePublishedLater;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutCompanyArticleCreatedHandlerTest extends TestCase
{
    private CompanyArticle $companyArticle;
    private CompanyArticle $companyArticleWherePublishedLater;
    private User $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompanyArticle::class,
            LoadCompanyArticleWherePublishedLater::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $this->companyArticle = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME);
        $this->companyArticleWherePublishedLater = $referenceRepository->getReference(LoadCompanyArticleWherePublishedLater::REFERENCE_NAME);
        $this->subscriber = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->companyArticle, $this->subscriber);

        parent::tearDown();
    }

    public function testUserMustReceiveNotificationAfterHandle(): void
    {
        $this->subscribeOnCompany();

        $command = new NotifySubscribersAboutCompanyArticleCreatedCommand($this->companyArticle->getId());

        $this->getCommandBus()->handle($command);

        $actualNotification = $this->subscriber->getUnreadNotifications()->first();

        $this->assertInstanceOf(CompanyArticleCreatedNotification::class, $actualNotification);
        assert($actualNotification instanceof CompanyArticleCreatedNotification);

        $this->assertTrue($this->companyArticle === $actualNotification->getCompanyArticle());
    }

    public function testUserShouldNotReceiveNotificationAfterHandleIfCompanyArticlePublishedLater(): void
    {
        $this->subscribeOnCompany();

        $command = new NotifySubscribersAboutCompanyArticleCreatedCommand($this->companyArticleWherePublishedLater->getId());

        $this->getCommandBus()->handle($command);

        $actualNotifications = $this->subscriber->getUnreadNotifications();

        $this->assertCount(0, $actualNotifications);
    }

    private function subscribeOnCompany(): void
    {
        $command = new SubscribeOnCompanyCommand($this->subscriber, $this->companyArticle->getCompanyAuthor());

        $this->getCommandBus()->handle($command);
    }
}
