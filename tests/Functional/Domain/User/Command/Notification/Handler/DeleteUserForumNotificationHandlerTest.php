<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\User\Command\Notification\DeleteUserForumNotificationCommand;
use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadUserWithUnreadNotifications;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class DeleteUserForumNotificationHandlerTest extends TestCase
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

    public function testAfterHandlingForumNotificationMustBeDeleted(): void
    {
        /** @var ForumNotification|null $forumNotification */
        $forumNotification = $this->user->getUnreadNotifications()->filterByType(ForumNotification::class)->first();
        $this->assertNotEmpty($forumNotification);

        $command = new DeleteUserForumNotificationCommand($this->user);
        $command->forumNotificationId = (int) $forumNotification->getExternalNotificationId();

        $this->getCommandBus()->handle($command);

        $this->assertNotContains($forumNotification, $this->user->getUnreadNotifications());
        $this->assertNotContains($forumNotification, $this->user->getReadNotifications());
    }

    public function testUserCantDeleteAlreadyReadNotification(): void
    {
        /** @var ForumNotification $forumNotification */
        $forumNotification = $this->user->getUnreadNotifications()->filterByType(ForumNotification::class)->first();

        $this->user->readAllUnreadNotifications();
        $this->getEntityManager()->flush();

        $command = new DeleteUserForumNotificationCommand($this->user);
        $command->forumNotificationId = $forumNotification->getExternalNotificationId();

        $this->getCommandBus()->handle($command);

        $this->assertContains($forumNotification, $this->user->getReadNotifications());
    }
}
