<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Record\Map\Entity\Map;
use App\Domain\User\Command\Notification\NotifySubscribersAboutMapCreatedCommand;
use App\Domain\User\Command\Subscription\SubscribeOnUserCommand;
use App\Domain\User\Entity\Notification\MapCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Record\LoadMaps;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutMapCreatedHandlerTest extends TestCase
{
    private Map $userMap;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadMaps::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->userMap = $referenceRepository->getReference(LoadMaps::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->userMap, $this->user);

        parent::tearDown();
    }

    public function testUserMustReceiveNotificationAfterHandle(): void
    {
        $this->subscribeOnMapAuthor();

        $command = new NotifySubscribersAboutMapCreatedCommand($this->userMap->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();
        $actualNotification = $unreadNotifications->first();

        $this->assertCount(1, $unreadNotifications);
        $this->assertInstanceOf(MapCreatedNotification::class, $actualNotification);
        $this->assertEquals($this->userMap, $actualNotification->getMap());
        $this->assertEquals($this->userMap->getAuthor(), $actualNotification->getInitiator());
    }

    public function testUserWithoutSubscriptionMustNotReceiveNotificationAfterHandle(): void
    {
        $command = new NotifySubscribersAboutMapCreatedCommand($this->userMap->getId());

        $this->getCommandBus()->handle($command);

        $unreadNotifications = $this->user->getUnreadNotifications();

        $this->assertCount(0, $unreadNotifications);
    }

    private function subscribeOnMapAuthor(): void
    {
        $command = new SubscribeOnUserCommand($this->user, $this->userMap->getAuthor());

        $this->getCommandBus()->handle($command);
    }
}
