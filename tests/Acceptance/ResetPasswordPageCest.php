<?php

namespace Tests\Acceptance;

use Codeception\Util\HttpCode;
use Tester;
use Tests\Support\TransferObject\User;

class ResetPasswordPageCest
{
    public function recoverUserPassword(Tester $I): void
    {
        $user = $I->findNotBannedUser();

        $this->sendRequest($I, $user);

        $email = $I->loadLastEmailMessage();
        $token = $this->getToken($email);

        $this->checkEmailMessage($I, $user, $email, $token);
        $this->reset($I, $user, $token);
    }

    private function sendRequest(Tester $I, User $user): void
    {
        $I->updateInDatabase('users', [
            'reset_password_token_requested_at' => null,
            'password' => '123',
        ], [
            'login' => $user->username,
        ]);

        $I->amOnPage('/password/');
        $I->fillField('send_request_reset_password[loginOrEmail]', $user->username);
        $I->click('Прислать пароль по e-mail');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function getToken($email): string
    {
        $token = '';
        $match = [];

        if (preg_match('/\/password\/reset\/(.*)\//i', $email, $match)) {
            $token = $match[1];
        }

        return $token;
    }

    private function checkEmailMessage(Tester $I, User $user, string $email, string $token): void
    {
        $I->assertStringContainsString('Уважаемый пользователь, '.$user->username, $email);
        $I->assertStringContainsString('Вами (либо кем-то другим) была запрошена процедура восстановления пароля на сайте', $email);

        $I->seeInDatabase('users', [
            'reset_password_token' => $token,
            'login' => $user->username,
        ]);
    }

    private function reset(Tester $I, User $user, string $token): void
    {
        $I->amOnPage('/password/reset/'.$token.'/');
        $I->see('Введите новый пароль');
        $I->seeResponseCodeIs(HttpCode::OK);

        $user->password = $I->getFaker()->password;
        $I->fillField('reset_password[newPassword]', $user->password);
        $I->click('Сохранить');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeAlert('success', 'Ваш пароль успешно изменен. Теперь Вы можете авторизоваться.');
        $I->authAs($user);
        $I->see('выход');
    }

    public function failureResetPassword(Tester $I): void
    {
        $I->amOnPage('/password/reset/INVALID_TOKEN/');
        $I->see('Восстановить пароль');
        $I->seeAlert('error', 'Видимо, такая ссылка больше не существует. Попробуйте запросить восстановление пароля еще раз.');
    }
}
