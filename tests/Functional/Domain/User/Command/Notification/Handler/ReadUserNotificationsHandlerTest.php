<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\User\Command\Notification\ReadUserNotificationsCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadUserWithUnreadNotifications;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class ReadUserNotificationsHandlerTest extends TestCase
{
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithUnreadNotifications::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithUnreadNotifications::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    public function testAfterHandlingAllUnreadNotificationMustRead(): void
    {
        $this->assertGreaterThan(0, count($this->user->getUnreadNotifications()));

        $command = new ReadUserNotificationsCommand($this->user);
        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->user->getUnreadNotifications());
    }
}
