<?php

namespace Tests\Functional\Domain\User\Command\UserRegistration;

use App\Domain\SpamBlockList\Entity\SpamEmail;
use App\Domain\User\Command\UserRegistration\UserRegisterCommand;
use App\Domain\User\Entity\User;
use App\Module\MailCheck\MailCheckClientMock;
use App\Module\VerifyMail\VerifyMailClientMock;
use Tests\DataFixtures\ORM\SpamBlockList\LoadSpamEmail;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\Functional\ValidationTestCase;

/**
 * @group registration
 */
class UserRegisterCommandValidationTest extends ValidationTestCase
{
    /**
     * @var UserRegisterCommand
     */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UserRegisterCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['username'], null, 'Логин обязателен.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['password'], null, 'Пароль не должен быть пустым.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], null, 'E-mail обязателен.');
    }

    /**
     * @dataProvider getForbiddenSymbols
     */
    public function testUsingForbiddenSymbolsInLogin(string $forbiddenSymbols): void
    {
        $this->command->username = $forbiddenSymbols;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('username', sprintf('Логин не должен содержать символы "%s"', $forbiddenSymbols));
    }

    /**
     * @return mixed[]
     */
    public function getForbiddenSymbols(): array
    {
        return [['*']];
    }

    public function testBanUseCharactersFromDifferentAlphabets(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['username'], 'IvanИван', 'Использованы символы из разных алфавитов');
    }

    public function testUniqueFields(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNumberedUsers::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadNumberedUsers::getRandReferenceName());

        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['username'],
            $user->getUsername(),
            sprintf('К сожалению, логин \'%s\' уже занят.', $user->getUsername())
        );
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['email'],
            $user->getEmailAddress(),
            sprintf('Email \'%s\' уже зарегистрирован.', $user->getEmailAddress())
        );
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['email'],
            $user->getUsername(),
            sprintf('\'%s\' уже используется другим пользователем в качестве логина.', $user->getUsername())
        );
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['username'],
            $user->getEmailAddress(),
            sprintf('К сожалению, логин \'%s\' уже используется другим пользователем в качестве E-email.', $user->getEmailAddress())
        );
    }

    public function testEmailValidation(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], 'INVALID-EMAIL', 'Поле e-mail заполнено некорректно.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], 'test@fishsib.loc', 'На адрес test@fishsib.loc отправка почты невозможна');
    }

    public function testEmailInSpamBlockList(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSpamEmail::class,
        ])->getReferenceRepository();

        /** @var SpamEmail $spamEmail */
        $spamEmail = $referenceRepository->getReference(LoadSpamEmail::REFERENCE_NAME);
        $this->command->email = $spamEmail->getEmail();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', sprintf('Email %s заблокирован за спам.', $spamEmail->getEmail()));
    }

    public function testDisposableEmailInMailCheckClient(): void
    {
        $this->command->email = sprintf('test@%s', MailCheckClientMock::DISPOSABLE_DOMAIN);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', 'Использование временного адреса электронной почты запрещено');
    }

    public function testDisposableEmailInVerifyMailClient(): void
    {
        $this->command->email = VerifyMailClientMock::DISPOSABLE_EMAIL;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('email', 'Использование временного адреса электронной почты запрещено');
    }

    public function testAgreementValidation(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['isAgreedToNewsLetter'], 'NOT-BOOL', 'Тип значения должен быть boolean.');
    }
}
