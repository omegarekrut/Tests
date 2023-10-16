<?php

namespace Tests\Functional\Domain\User\Command\UserRegistraion;

use App\Domain\User\Command\UserRegistration\RegisterUserOnForumCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group registration
 */
class RegisterUserOnForumCommandValidationTest extends ValidationTestCase
{
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new RegisterUserOnForumCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testUserMustBeExists(): void
    {
        $notExistingUserId = -1;
        $this->command->userId = $notExistingUserId;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('userId', 'Пользователь не найден.');
    }

    public function testPlainPasswordMustNotBeBlank(): void
    {
        $this->command->plainPassword = '';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('plainPassword', 'Пароль не должен быть пустым.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);

        $this->command->userId = $user->getId();
        $this->command->plainPassword = 'some password';

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
