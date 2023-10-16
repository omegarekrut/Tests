<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\User\Command\Notification\NotifySubscribersAboutTidingsCreatedCommand;
use App\Domain\User\Command\Subscription\SubscribeOnUserCommand;
use App\Domain\User\Entity\Notification\TidingsCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Record\Tidings\LoadNumberedTidings;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutTidingsCreatedHandlerTest extends TestCase
{
    private Tidings $userTidings;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadNumberedTidings::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->userTidings = $referenceRepository->getReference(LoadNumberedTidings::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->userTidings, $this->user);

        parent::tearDown();
    }

    public function testUserMustReceiveNotificationAfterHandle(): void
    {
        $this->subscribeOnTidingsAuthor();

        $command = new NotifySubscribersAboutTidingsCreatedCommand($this->userTidings->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();
        $actualNotification = $unreadNotifications->first();

        $this->assertCount(1, $unreadNotifications);
        $this->assertInstanceOf(TidingsCreatedNotification::class, $actualNotification);
        $this->assertEquals($this->userTidings, $actualNotification->getTidings());
        $this->assertEquals($this->userTidings->getAuthor(), $actualNotification->getInitiator());
    }

    public function testUserWithoutSubscriptionMustNotReceiveNotificationAfterHandle(): void
    {
        $command = new NotifySubscribersAboutTidingsCreatedCommand($this->userTidings->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();

        $this->assertCount(0, $unreadNotifications);
    }

    private function subscribeOnTidingsAuthor(): void
    {
        $command = new SubscribeOnUserCommand($this->user, $this->userTidings->getAuthor());

        $this->getCommandBus()->handle($command);
    }
}
