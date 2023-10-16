<?php

namespace Tests\Functional\Domain\User\Command\Rating;

use App\Domain\User\Command\Rating\RecalculateUserGlobalRatingCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group user
 * @group rating
 */
class RecalculateUserGlobalRatingValidationTest extends ValidationTestCase
{
    public function testNonExistingUser(): void
    {
        $invalidUserId = -1;

        $command = new RecalculateUserGlobalRatingCommand();
        $command->userId = $invalidUserId;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('userId', 'Пользователь не найден.');
    }

    public function testExistingUser(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $command = new RecalculateUserGlobalRatingCommand();
        $command->userId = $user->getId();

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
