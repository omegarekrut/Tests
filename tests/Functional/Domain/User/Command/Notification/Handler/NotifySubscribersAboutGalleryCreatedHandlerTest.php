<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\User\Command\Notification\NotifySubscribersAboutGalleryCreatedCommand;
use App\Domain\User\Command\Subscription\SubscribeOnUserCommand;
use App\Domain\User\Entity\Notification\GalleryCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutGalleryCreatedHandlerTest extends TestCase
{
    private Gallery $userGallery;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadGallery::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->userGallery = $referenceRepository->getReference(LoadGallery::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->userGallery, $this->user);

        parent::tearDown();
    }

    public function testUserMustReceiveNotificationAfterHandle(): void
    {
        $this->subscribeOnGalleryAuthor();

        $command = new NotifySubscribersAboutGalleryCreatedCommand($this->userGallery->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();
        $actualNotification = $unreadNotifications->first();

        $this->assertCount(1, $unreadNotifications);
        $this->assertInstanceOf(GalleryCreatedNotification::class, $actualNotification);
        $this->assertEquals($this->userGallery, $actualNotification->getGallery());
        $this->assertEquals($this->userGallery->getAuthor(), $actualNotification->getInitiator());
    }

    public function testUserWithoutSubscriptionMustNotReceiveNotificationAfterHandle(): void
    {
        $command = new NotifySubscribersAboutGalleryCreatedCommand($this->userGallery->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();

        $this->assertCount(0, $unreadNotifications);
    }

    private function subscribeOnGalleryAuthor(): void
    {
        $command = new SubscribeOnUserCommand($this->user, $this->userGallery->getAuthor());

        $this->getCommandBus()->handle($command);
    }
}
