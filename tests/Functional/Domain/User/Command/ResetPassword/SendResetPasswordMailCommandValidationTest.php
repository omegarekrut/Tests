<?php

namespace Tests\Functional\Domain\User\Command\ResetPassword;

use App\Domain\User\Command\ResetPassword\SendResetPasswordMailCommand;
use Tests\DataFixtures\ORM\User\LoadResetPasswordUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group reset-password
 */
class SendResetPasswordMailCommandValidationTest extends ValidationTestCase
{
    /**
     * @var SendResetPasswordMailCommand
     */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new SendResetPasswordMailCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['loginOrEmail'], null, 'Значение не должно быть пустым.');
    }

    public function testInvalidLengthField(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['loginOrEmail'],
            $this->getFaker()->realText(500),
            'Длина не должна превышать 255 символов.'
        );
    }

    public function testUserExist(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['loginOrEmail'],
            'Invalid user login or email',
            'Пользователь Invalid user login or email на нашем сайте не найден.'
        );
    }

    public function testResetPasswordAvailability(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadResetPasswordUser::class,
        ])->getReferenceRepository();

        $availabilityUser = $referenceRepository->getReference(LoadResetPasswordUser::USER_RESET_PASSWORD);

        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['loginOrEmail'],
            $availabilityUser->getLogin(),
            'Вы уже запрашивали восстановление пароля, проверьте свой почтовый ящик. Вы сможете снова запросить восстановление пароля через 10 минут'
        );
    }
}
