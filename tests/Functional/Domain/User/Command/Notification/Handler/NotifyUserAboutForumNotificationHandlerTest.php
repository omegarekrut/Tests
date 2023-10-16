<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\User\Command\Notification\NotifyUserAboutForumNotificationCommand;
use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyUserAboutForumNotificationHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var User */
    private $initiator;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadNumberedUsers::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->initiator = $referenceRepository->getReference(LoadNumberedUsers::getRandReferenceName());
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->initiator
        );

        parent::tearDown();
    }

    public function testAfterHandlingUserShouldGetNotificationWithForumNotificationDetails(): void
    {
        $command = new NotifyUserAboutForumNotificationCommand($this->user, $this->initiator);
        $command->forumCategory = 'some unknown category';
        $command->forumMessage = 'some message';
        $command->forumNotificationId = '2';

        $this->getCommandBus()->handle($command);

        /** @var Notification|null $actualNotification */
        $actualNotification = $this->user->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(ForumNotification::class, $actualNotification);
        /** @var ForumNotification $actualNotification */

        $this->assertTrue(NotificationCategory::info()->equals($actualNotification->getCategory()));
        $this->assertTrue($command->getInitiator() === $actualNotification->getInitiator());
        $this->assertTrue($command->forumMessage === $actualNotification->getMessage());
        $this->assertTrue($command->forumNotificationId === $actualNotification->getExternalNotificationId());
    }

    /**
     * @dataProvider getNotificationCategoryMatchingMap
     */
    public function testForumActionCategoryShouldBeReplacedWithNotificationCategory(NotificationCategory $expectedCategory, string $forumCategory): void
    {
        $command = new NotifyUserAboutForumNotificationCommand($this->user, $this->initiator);
        $command->forumCategory = $forumCategory;
        $command->forumMessage = 'some message';
        $command->forumNotificationId = '2';

        $this->getCommandBus()->handle($command);

        /** @var Notification|null $actualNotification */
        $actualNotification = $this->user->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(ForumNotification::class, $actualNotification);
        /** @var ForumNotification $actualNotification */

        $this->assertTrue($expectedCategory->equals($actualNotification->getCategory()));
    }

    public function getNotificationCategoryMatchingMap(): \Generator
    {
        yield [
            NotificationCategory::comment(),
            'insert',
        ];

        yield [
            NotificationCategory::mention(),
            'mention',
        ];

        yield [
            NotificationCategory::like(),
            'like',
        ];

        yield [
            NotificationCategory::quote(),
            'quote',
        ];

        yield [
            NotificationCategory::info(),
            'any-other-category',
        ];
    }
}
