<?php

namespace Tests\Functional\Domain\User\Command\Forum\Handler;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\Mock\ProfileProvider;
use App\Domain\User\Command\Forum\ReadUserNotificationsOnForumCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class ReadUserNotificationsOnForumHandlerTest extends TestCase
{
    /** @var User $user */
    private $user;
    /** @var ForumApiInterface */
    private $forumApi;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->forumApi = $this->getContainer()->get(ForumApiInterface::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->forumApi
        );

        parent::tearDown();
    }

    public function testAfterHandlingNotificationsMustBeReadOnForum(): void
    {
        /** @var ProfileProvider $profileClient */
        $profileClient = $this->forumApi->profile();

        $this->assertFalse($profileClient->isReadNotifications());

        $command = new ReadUserNotificationsOnForumCommand();
        $command->userId = $this->user->getId();

        $this->getCommandBus()->handle($command);

        $this->assertTrue($profileClient->isReadNotifications());
    }
}
