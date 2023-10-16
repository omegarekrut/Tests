<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion;

use App\Domain\User\Command\Deleting\MassDeleteUsersCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

class MassDeleteUsersCommandValidationTest extends ValidationTestCase
{
    private MassDeleteUsersCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new MassDeleteUsersCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNoUserIdsSelected(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('userIds', 'Не выбран ни один пользователь.');
    }

    public function testUserIdsMustBeArray(): void
    {
        $invalidUserIds = 'userIds';
        $this->command->userIds = $invalidUserIds;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('userIds', 'Тип значения должен быть array.');
    }

    public function testUsersMustBeExistsByIds(): void
    {
        $invalidUserIds = [-1, -2];
        $this->command->userIds = $invalidUserIds;

        $this->getValidator()->validate($this->command);

        foreach ($invalidUserIds as $key => $invalidUserId) {
            $this->assertFieldInvalid(sprintf('userIds[%d]', $key), 'Пользователь не найден.');
        }
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadModeratorUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var User $moderator */
        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);

        $userIds = [
            $user->getId(),
            $moderator->getId(),
        ];

        $this->command->userIds = $userIds;

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
