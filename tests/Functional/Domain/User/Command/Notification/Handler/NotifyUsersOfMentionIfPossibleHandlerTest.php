<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Comment\Entity\Comment;
use App\Domain\User\Collection\NotificationCollection;
use App\Domain\User\Command\Notification\NotifyUsersOfMentionIfPossibleCommand;
use App\Domain\User\Entity\Notification\MentionInCommentNotification;
use App\Domain\User\Service\MentionService;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithMentionedUser;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithMentioningHimself;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithThreeMentionedUsers;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyUsersOfMentionIfPossibleHandlerTest extends TestCase
{
    /** @var MentionService */
    private $mentionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentionService = $this->getContainer()->get(MentionService::class);
    }

    protected function tearDown(): void
    {
        unset($this->mentionService);

        parent::tearDown();
    }

    public function testMentionedUsersInCommentMustReceiveNotification(): void
    {
        $referenceRepository = $this->loadFixtures([LoadCommentWithThreeMentionedUsers::class])->getReferenceRepository();

        /** @var Comment $commentWithThreeMentionedUsers */
        $commentWithThreeMentionedUsers = $referenceRepository->getReference(LoadCommentWithThreeMentionedUsers::REFERENCE_NAME);

        $mentionedUsers = $this->mentionService->getMentionedUsersFromText($commentWithThreeMentionedUsers->getText());

        $this->assertGreaterThan(0, count($mentionedUsers));

        foreach ($mentionedUsers as $user) {
            $mentionInCommentNotification = $user->getUnreadNotifications();

            $this->assertCount(0, $mentionInCommentNotification);
        }

        $command = new NotifyUsersOfMentionIfPossibleCommand($commentWithThreeMentionedUsers->getId());
        $this->getCommandBus()->handle($command);

        foreach ($mentionedUsers as $user) {
            $mentionInCommentNotification = $this->findMentionInCommentNotificationByComment($user->getUnreadNotifications(), $commentWithThreeMentionedUsers);

            $this->assertCount(1, $mentionInCommentNotification);
        }
    }

    public function testMentionedUserInCommentMustReceiveNotification(): void
    {
        $referenceRepository = $this->loadFixtures([LoadCommentWithMentionedUser::class])->getReferenceRepository();

        /** @var Comment $commentWithMentionedUser */
        $commentWithMentionedUser = $referenceRepository->getReference(LoadCommentWithMentionedUser::REFERENCE_NAME);

        $mentionedUsers = $this->mentionService->getMentionedUsersFromText($commentWithMentionedUser->getText());
        $mentionInCommentNotification = $this->findMentionInCommentNotificationByComment($mentionedUsers->first()->getUnreadNotifications(), $commentWithMentionedUser);

        $this->assertCount(0, $mentionInCommentNotification);

        $command = new NotifyUsersOfMentionIfPossibleCommand($commentWithMentionedUser->getId());
        $this->getCommandBus()->handle($command);

        $mentionInCommentNotification = $this->findMentionInCommentNotificationByComment($mentionedUsers->first()->getUnreadNotifications(), $commentWithMentionedUser);

        $this->assertCount(1, $mentionInCommentNotification);
    }

    public function testDoNotReceiveUserMentionIfCommentAuthorIsCreator(): void
    {
        $referenceRepository = $this->loadFixtures([LoadCommentWithMentioningHimself::class])->getReferenceRepository();

        /** @var Comment $commentWithMentioningHimself */
        $commentWithMentioningHimself = $referenceRepository->getReference(LoadCommentWithMentioningHimself::REFERENCE_NAME);

        $mentionedUsers = $this->mentionService->getMentionedUsersFromText($commentWithMentioningHimself->getText());
        $mentionInCommentNotification = $this->findMentionInCommentNotificationByComment($mentionedUsers->first()->getUnreadNotifications(), $commentWithMentioningHimself);

        $this->assertCount(0, $mentionInCommentNotification);

        $command = new NotifyUsersOfMentionIfPossibleCommand($commentWithMentioningHimself->getId());
        $this->getCommandBus()->handle($command);

        $mentionInCommentNotification = $this->findMentionInCommentNotificationByComment($mentionedUsers->first()->getUnreadNotifications(), $commentWithMentioningHimself);

        $this->assertCount(0, $mentionInCommentNotification);
    }

    private function findMentionInCommentNotificationByComment(NotificationCollection $notifications, Comment $comment): NotificationCollection
    {
        $notifications
            ->filterByType(MentionInCommentNotification::class)
            ->findOne(static function (MentionInCommentNotification $notification) use ($comment) {
                return $notification->getComment() === $comment;
            });

        return $notifications;
    }
}
