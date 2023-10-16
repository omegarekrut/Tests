<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion;

use App\Domain\User\Command\Deleting\DeleteUserWithContentCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group user
 */
class DeleteUserWithContentCommandValidationTest extends ValidationTestCase
{
    /** @var DeleteUserWithContentCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new DeleteUserWithContentCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testUserMustBeExistsById(): void
    {
        $invalidUserId = -1;
        $this->command->userId = $invalidUserId;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('userId', 'Пользователь не найден.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();


        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->command->userId = $user->getId();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
