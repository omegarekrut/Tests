<?php

namespace Tests\Functional\Domain\User\Command\Subscription;

use App\Domain\User\Command\Subscription\DoNotDisturbUserByEmailCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group user-subscription
 */
class DoNotDisturbUserByEmailCommandValidationTest extends ValidationTestCase
{
    /** @var DoNotDisturbUserByEmailCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new DoNotDisturbUserByEmailCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testUserEmailCantBeBlank(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', 'Значение не должно быть пустым.');
    }

    public function testUserMustBeExistsByEmail(): void
    {
        $notExistsUserEmail = 'foo@bar.com';
        $this->command->email = $notExistsUserEmail;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', "Пользователь $notExistsUserEmail на нашем сайте не найден.");
    }

    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->command->email = $user->getEmailAddress();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
