<?php

namespace Tests\Functional\Domain\User\Command\ResetPassword;

use App\Domain\User\Command\ResetPassword\ResetPasswordCommand;
use Tests\Functional\ValidationTestCase;

/**
 * @group reset-password
 */
class ResetPasswordCommandValidationTest extends ValidationTestCase
{
    /**
     * @var ResetPasswordCommand
     */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ResetPasswordCommand('token');
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankField(): void
    {
        $this->command->token = null;

        $this->assertOnlyFieldsAreInvalid($this->command, ['token', 'newPassword'], null, 'Значение не должно быть пустым.');
    }

    public function testInvalidLengthField(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['token', 'newPassword'],
            $this->getFaker()->realText(500),
            'Длина не должна превышать 255 символов.'
        );
    }
}
