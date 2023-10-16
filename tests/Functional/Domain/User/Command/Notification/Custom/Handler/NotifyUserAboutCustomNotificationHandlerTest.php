<?php

namespace Tests\Functional\Domain\User\Command\Notification\Custom\Handler;

use App\Domain\Notification\Entity\CustomNotification;
use App\Domain\User\Command\Notification\Custom\NotifyUserAboutCustomNotificationCommand;
use App\Domain\User\Entity\Notification\CustomNotification as UserCustomNotification;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Notification\LoadCustomNotification;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
final class NotifyUserAboutCustomNotificationHandlerTest extends TestCase
{
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadAdminUser::class,
            LoadCustomNotification::class,
        ])->getReferenceRepository();
    }

    public function testAfterHandlingUserMustHaveCustomNotification(): void
    {
        $user = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);
        $initiator = $this->referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($initiator instanceof User);
        $notification = $this->referenceRepository->getReference(LoadCustomNotification::REFERENCE_NAME);
        assert($notification instanceof CustomNotification);

        $command = new NotifyUserAboutCustomNotificationCommand();
        $command->userId = $user->getId();
        $command->initiatorId = $initiator->getId();
        $command->notificationId = $notification->getId();

        $this->getCommandBus()->handle($command);

        $actualNotification = $user->getUnreadNotifications()->first();

        $this->assertInstanceOf(UserCustomNotification::class, $actualNotification);
        $this->assertEquals($actualNotification->getExternalNotificationId(), $notification->getId());
    }

    public function testIgnoringRepeatedNotifyingUserAboutCustomNotification(): void
    {
        $user = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);
        $initiator = $this->referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($initiator instanceof User);
        $notification = $this->referenceRepository->getReference(LoadCustomNotification::REFERENCE_NAME);
        assert($notification instanceof CustomNotification);

        $command = new NotifyUserAboutCustomNotificationCommand();
        $command->userId = $user->getId();
        $command->initiatorId = $initiator->getId();
        $command->notificationId = $notification->getId();

        $this->getCommandBus()->handle($command);

        $this->assertCount(1, $user->getUnreadNotifications());

        $this->getCommandBus()->handle($command);

        $this->assertCount(1, $user->getUnreadNotifications());
    }
}
