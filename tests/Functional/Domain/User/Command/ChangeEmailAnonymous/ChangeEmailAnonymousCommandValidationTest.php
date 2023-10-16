<?php

namespace Tests\Functional\Domain\User\Command\ChangeEmailAnonymous;

use App\Domain\Ban\Entity\BanUser;
use App\Domain\User\Command\ChangeEmailAnonymous\ChangeEmailAnonymousCommand;
use App\Domain\User\Entity\User;
use App\Module\MailCheck\MailCheckClientMock;
use App\Module\VerifyMail\VerifyMailClientMock;
use Tests\DataFixtures\ORM\Ban\LoadBanUsers;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

class ChangeEmailAnonymousCommandValidationTest extends ValidationTestCase
{
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ChangeEmailAnonymousCommand();
    }

    protected function tearDown(): void
    {
        unset($this->comment);

        parent::tearDown();
    }

    public function testNotBlankField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['loginOrEmail', 'password', 'email'], null, 'Значение не должно быть пустым.');
    }

    public function testLessThanMinLength(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['loginOrEmail'], 'a', 'Минимальная длина 2 символа.');
    }

    public function testInvalidEmail(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], $this->getFaker()->realText(10), 'Поле e-mail заполнено некорректно.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['email'], 'test@fishsib.loc', 'На адрес test@fishsib.loc отправка почты невозможна');
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

    public function testUserMustBeNotBanned(): void
    {
        $referenceRepository = $this->loadFixtures([LoadBanUsers::class])->getReferenceRepository();

        /** @var BanUser $banUser */
        $banUser = $referenceRepository->getReference(LoadBanUsers::BAN_USER);
        $this->command->loginOrEmail = $banUser->getUser()->getLogin();
        $this->command->password = $banUser->getUser()->getPassword();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('loginOrEmail', 'Пользователь заблокирован.');
    }

    public function testUserCanBeAuthenticated(): void
    {
        $this->command->loginOrEmail = 'Invalid user login or email';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid(
            'credentials',
            'Ошибка! Проверьте правильность заполнения формы.'
        );
    }

    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->command->loginOrEmail = $user->getLogin();
        $this->command->password = '123456';
        $this->command->email = 'newrand@email.ru';

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
