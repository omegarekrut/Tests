<?php

namespace Tests\Functional\Domain\User\Command\Notification\Custom;

use App\Domain\Notification\Entity\CustomNotification;
use App\Domain\User\Command\Notification\Custom\NotifyUserAboutCustomNotificationCommand;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Notification\LoadCustomNotification;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
final class NotifyUserAboutCustomNotificationCommandValidationTest extends ValidationTestCase
{
    private NotifyUserAboutCustomNotificationCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new NotifyUserAboutCustomNotificationCommand();
    }

    public function testValidationFailForNotExistedUser(): void
    {
        $this->command->userId = 0;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('userId', 'Пользователь не найден.');
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
            LoadTestUser::class,
            LoadAdminUser::class,
            LoadCustomNotification::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);
        $initiator = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($initiator instanceof User);
        $notification = $referenceRepository->getReference(LoadCustomNotification::REFERENCE_NAME);
        assert($notification instanceof CustomNotification);

        $this->command->userId = $user->getId();
        $this->command->initiatorId = $initiator->getId();
        $this->command->notificationId = $notification->getId();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
