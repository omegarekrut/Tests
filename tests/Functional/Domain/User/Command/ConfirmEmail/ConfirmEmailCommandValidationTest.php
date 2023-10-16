<?php

namespace Tests\Functional\Domain\User\Command\ConfirmEmail;

use App\Domain\User\Command\ConfirmEmail\ConfirmEmailCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadUserWithExpiredConfirmationToken;
use Tests\Functional\ValidationTestCase;

class ConfirmEmailCommandValidationTest extends ValidationTestCase
{
    public function testNotBlankField(): void
    {
        $this->getValidator()->validate(new ConfirmEmailCommand(''));

        $this->assertFieldInvalid(
            'token',
            'Значение не должно быть пустым.'
        );
    }

    public function testMoreThanMaxLength(): void
    {
        $this->getValidator()->validate(new ConfirmEmailCommand($this->getFaker()->realText(500)));

        $this->assertFieldInvalid(
            'token',
            'Максимальная длина 255 символов.'
        );
    }

    public function testUserExist(): void
    {
        $this->getValidator()->validate(new ConfirmEmailCommand('some-token'));

        $this->assertFieldInvalid(
            'token',
            'Пользователь с таким email не найден или email уже был подтвержден ранее.'
        );
    }

    public function testExpiredToken(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithExpiredConfirmationToken::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUserWithExpiredConfirmationToken::REFERENCE_NAME);

        $this->getValidator()->validate(new ConfirmEmailCommand($user->getEmail()->getConfirmationToken()->getToken()));

        $this->assertFieldInvalid(
            'token',
            'Время для подтверждение email истекло. Повторите попытку снова.'
        );
    }
}
