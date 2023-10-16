<?php

namespace Tests\Functional\Domain\User\Command\Notification\Custom\Handler;

use App\Domain\Notification\Entity\CustomNotification;
use App\Domain\User\Command\Notification\Custom\NotifyNotBannedUsersAboutCustomNotificationCommand;
use App\Domain\User\Entity\Notification\CustomNotification as UserCustomNotification;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Notification\LoadCustomNotification;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyNotBannedUsersAboutCustomNotificationHandlerTest extends TestCase
{
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadTestUser::class,
            LoadMostActiveUser::class,
            LoadCustomNotification::class,
        ])->getReferenceRepository();
    }

    public function testAfterHandlingUsersMustHaveCustomNotification(): void
    {
        $adminUser = $this->referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($adminUser instanceof User);

        $users = [
            $adminUser,
            $this->referenceRepository->getReference(LoadTestUser::USER_TEST),
            $this->referenceRepository->getReference(LoadMostActiveUser::USER_MOST_ACTIVE),
        ];

        $customNotification = $this->referenceRepository->getReference(LoadCustomNotification::REFERENCE_NAME);
        assert($customNotification instanceof CustomNotification);

        $command = new NotifyNotBannedUsersAboutCustomNotificationCommand();
        $command->initiatorId = $adminUser->getId();
        $command->notificationId = $customNotification->getId();

        $this->getCommandBus()->handle($command);

        foreach ($users as $user) {
            assert($user instanceof User);

            $actualNotification = $user->getUnreadNotifications()->first();
            $this->assertInstanceOf(UserCustomNotification::class, $actualNotification);
            $this->assertEquals($actualNotification->getExternalNotificationId(), $customNotification->getId());
        }
    }
}
