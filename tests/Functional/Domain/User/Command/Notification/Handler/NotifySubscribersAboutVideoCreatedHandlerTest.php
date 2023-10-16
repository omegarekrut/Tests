<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Record\Video\Entity\Video;
use App\Domain\User\Command\Notification\NotifySubscribersAboutVideoCreatedCommand;
use App\Domain\User\Command\Subscription\SubscribeOnUserCommand;
use App\Domain\User\Entity\Notification\VideoCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutVideoCreatedHandlerTest extends TestCase
{
    private Video $userVideo;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadVideos::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->userVideo = $referenceRepository->getReference(LoadVideos::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->userVideo, $this->user);

        parent::tearDown();
    }

    public function testUserMustReceiveNotificationAfterHandle(): void
    {
        $this->subscribeOnVideoAuthor();

        $command = new NotifySubscribersAboutVideoCreatedCommand($this->userVideo->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();
        $actualNotification = $unreadNotifications->first();

        $this->assertCount(1, $unreadNotifications);
        $this->assertInstanceOf(VideoCreatedNotification::class, $actualNotification);
        $this->assertEquals($this->userVideo, $actualNotification->getVideo());
        $this->assertEquals($this->userVideo->getAuthor(), $actualNotification->getInitiator());
    }

    public function testUserWithoutSubscriptionMustNotReceiveNotificationAfterHandle(): void
    {
        $command = new NotifySubscribersAboutVideoCreatedCommand($this->userVideo->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();

        $this->assertCount(0, $unreadNotifications);
    }

    private function subscribeOnVideoAuthor(): void
    {
        $command = new SubscribeOnUserCommand($this->user, $this->userVideo->getAuthor());

        $this->getCommandBus()->handle($command);
    }
}
