<?php

namespace Tests\Functional\Domain\User\Command\EmailBounce;

use App\Domain\User\Command\EmailBounce\ResetEmailBounceCommand;
use App\Domain\User\Entity\User;
use Tests\Functional\ValidationTestCase;

class ResetEmailBounceCommandValidationTest extends ValidationTestCase
{
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ResetEmailBounceCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testUserMustBeDefined(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('user', 'Значение не должно быть пустым.');
    }

    public function testUserMustBeRealUserObject(): void
    {
        $this->command->user = 'not user';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('user', sprintf('Тип значения должен быть %s.', User::class));
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->user = $this->createMock(User::class);

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
