<?php

namespace Tests\Functional\Domain\User\Command\ChangePassword;

use App\Domain\User\Command\ChangePassword\ChangePasswordCommand;
use App\Domain\User\Entity\User;
use Tests\Functional\ValidationTestCase;

/**
 * @group change-password
 */
class ChangePasswordCommandValidationTest extends ValidationTestCase
{
    /** @var ChangePasswordCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ChangePasswordCommand($this->createMock(User::class));
    }

    protected function tearDown(): void
    {
        unset($this->comment);

        parent::tearDown();
    }

    public function testNotBlankField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['newPassword'], null, 'Значение не должно быть пустым.');
    }

    public function testInvalidLengthField(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['newPassword'],
            $this->getFaker()->realText(500),
            'Максимальная длина 255 символов.'
        );

        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['newPassword'],
            'a',
            'Минимальная длина 6 символов.'
        );
    }
}
