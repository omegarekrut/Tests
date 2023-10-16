<?php

namespace Tests\Functional\Domain\User\Command\Notification\Custom;

use App\Domain\Notification\Entity\CustomNotification;
use App\Domain\User\Command\Notification\Custom\NotifyNotBannedUsersAboutCustomNotificationCommand;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Notification\LoadCustomNotification;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
final class NotifyNotBannedUsersAboutCustomNotificationCommandValidationTest extends ValidationTestCase
{
    private NotifyNotBannedUsersAboutCustomNotificationCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new NotifyNotBannedUsersAboutCustomNotificationCommand();
    }

    public function testValidationFailForNotExistedInitiator(): void
    {
        $this->command->initiatorId = 0;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('initiatorId', 'Инициатор уведомления не найден.');
    }

    public function testValidationFailForNotExistedNotification(): void
    {
        $notExistingNotificationId = Uuid::uuid4();
        $this->command->notificationId = $notExistingNotificationId;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('notificationId', 'Уведомление не найдено.');
    }

    public function testValidationPassedForCorrectCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadCustomNotification::class,
        ])->getReferenceRepository();

        $initiator = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($initiator instanceof User);
        $notification = $referenceRepository->getReference(LoadCustomNotification::REFERENCE_NAME);
        assert($notification instanceof CustomNotification);

        $this->command->initiatorId = $initiator->getId();
        $this->command->notificationId = $notification->getId();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
