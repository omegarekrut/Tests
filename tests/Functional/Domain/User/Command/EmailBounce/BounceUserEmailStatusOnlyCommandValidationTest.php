<?php

namespace Tests\Functional\Domain\User\Command\EmailBounce;

use App\Domain\User\Command\EmailBounce\BounceUserEmailStatusOnlyCommand;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

class BounceUserEmailStatusOnlyCommandValidationTest extends ValidationTestCase
{
    /** @var BounceUserEmailStatusOnlyCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new BounceUserEmailStatusOnlyCommand();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->command);
    }

    public function testNotBlankField(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', 'Значение не должно быть пустым.');
    }

    public function testUserExists(): void
    {
        $this->command->email = 'email@email.test';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', sprintf('Пользователь %s на нашем сайте не найден.', $this->command->email));
    }

    public function testValidationShouldBeSuccessForCommandFilledWithCorrectData(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->command->email = $user->getEmailAddress();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
