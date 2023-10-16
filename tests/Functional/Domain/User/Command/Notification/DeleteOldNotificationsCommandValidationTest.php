<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\DeleteOldNotificationsCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

class DeleteOldNotificationsCommandValidationTest extends ValidationTestCase
{
    public function testWithNonexistentUserId(): void
    {
        $command = new DeleteOldNotificationsCommand(1);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('userId', 'Пользователь не найден.');
    }

    public function testWithValidData(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $command = new DeleteOldNotificationsCommand($user->getId());

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
