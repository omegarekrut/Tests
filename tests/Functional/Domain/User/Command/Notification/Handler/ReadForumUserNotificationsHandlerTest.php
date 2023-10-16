<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\User\Command\Notification\ReadForumUserNotificationsCommand;
use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadUserWithUnreadNotifications;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class ReadForumUserNotificationsHandlerTest extends TestCase
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

    public function testAfterHandlingAllUnreadForumNotificationMustRead(): void
    {
        $forumNotifications = iterator_to_array($this->user->getUnreadNotifications()->filterByType(ForumNotification::class));
        $this->assertGreaterThan(0, count($forumNotifications));

        $command = new ReadForumUserNotificationsCommand($this->user);
        $this->getCommandBus()->handle($command);

        $actualReadNotifications = $this->user->getReadNotifications();

        $this->assertCount(0, $this->user->getUnreadNotifications()->filterByType(ForumNotification::class));

        foreach ($forumNotifications as $forumNotification) {
            $this->assertContains($forumNotification, $actualReadNotifications);
        }
    }
}
