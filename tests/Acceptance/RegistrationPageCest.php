<?php

namespace Tests\Acceptance;

use App\Domain\User\Entity\User;
use Codeception\Util\HttpCode;
use Tester;

/**
 * @group registration
 */
class RegistrationPageCest
{
    public function seeRegisterCompletionPage(Tester $I): void
    {
        $I->amOnPage('/users/register/completion/');
        $I->see('На email, указанный при регистрации, вам было отправлено письмо. Для завершения регистрации, пожалуйста, пройдите по ссылке в письме');
    }

    public function complexRegister(Tester $I): void
    {
        $uniqueLogin = 'ul'.uniqid('', false);

        $this->register($I, $uniqueLogin);
        $this->changeEmail($I, $uniqueLogin);
        $this->requestEmailConfirm($I, $uniqueLogin);
        $this->confirmEmail($I);
    }

    private function register(Tester $I, string $uniqueLogin): void
    {
        // Первоначальная регистрация
        $I->amOnPage('/users/register/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Регистрация на сайте');

        $I->submitForm('form[name="registration"]', [
            'registration' => [
                'username' => $uniqueLogin,
                'password' => 'TEST_PASSWORD',
                'email' => $uniqueLogin.'@gmail.com',
            ],
        ], 'Зарегистрироваться');

        $I->canSeeCurrentUrlEquals('/users/register/completion/');

        $email = $I->loadLastEmailMessage();

        $I->assertStringContainsString($uniqueLogin.'@gmail.com', $email);
    }

    private function changeEmail(Tester $I, string $uniqueLogin): void
    {
        /**
         * @see User::isConfirmationEmailAlreadySent
         */
        $I->updateInDatabase('users', [
            'confirmation_email_token_requested_at' => '2000-12-12 00:00:00',
        ], [
            'login' => $uniqueLogin,
        ]);

        // Смена почты
        $I->amOnPage('/users/change_email/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->submitForm('form[name=change_email_anonymous]', [
            'change_email_anonymous' => [
                'loginOrEmail' => $uniqueLogin.'@gmail.com',
                'password' => 'TEST_PASSWORD',
                'email' => $uniqueLogin.'@fishingsib.ru',
            ],
        ], 'Изменить email');
        $I->seeAlert('success', 'Email успешно изменен. Письмо для подтверждения емейла было выслано на указанный вами адрес.');

        $email = $I->loadLastEmailMessage();
        $I->assertStringContainsString($uniqueLogin.'@fishingsib.ru', $email);
    }

    private function requestEmailConfirm(Tester $I, string $uniqueLogin): void
    {
        // Повторный запрос почты
        $I->amOnPage('/users/request_confirmation/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->submitForm('form[name=request_confirmation]', [
            'request_confirmation' => [
                'login' => $uniqueLogin,
                'password' => 'TEST_PASSWORD',
            ],
        ], 'Отправить письмо');
        $I->seeAlert('success', 'Письмо для подтверждения емейла было выслано на адрес, указанный вами при регистрации.');

        $email = $I->loadLastEmailMessage();
        $I->assertStringContainsString($uniqueLogin.'@fishingsib.ru', $email);
        $I->assertEquals(1, preg_match('/\/users\/confirm_email\/([\S]+)\//i', $email, $match));
    }

    private function confirmEmail(Tester $I): void
    {
        $email = $I->loadLastEmailMessage();
        $I->assertEquals(1, preg_match('/\/users\/confirm_email\/([\S]+)\//i', $email, $match));
        $confirmEmail = $match[0];

        // Завершающий этап регистрации - подтверждение email
        $I->amOnPage($confirmEmail);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', '<strong>Поздравляем! Регистрация завершена.</strong><br> Теперь Вы можете заполнить свой профиль. Общение на сайте будет гораздо проще и приятнее, если вы представитесь в своем профиле, и другие пользователи будут понимать, кто вы, откуда и как относитесь к рыбалке.
');
    }

    public function invalid(Tester $I): void
    {
        $I->logout();

        $user = $I->findNotBannedUser();

        $I->amOnPage('/users/register/');

        $I->submitForm('form[name="registration"]', [
            'registration' => [
                'username' => $user->username,
                'password' => 'TEST_PASSWORD',
                'email' => $user->email,
            ],
        ], 'Зарегистрироваться');

        $I->see(sprintf('К сожалению, логин \'%s\' уже занят.', $user->username));
    }
}
